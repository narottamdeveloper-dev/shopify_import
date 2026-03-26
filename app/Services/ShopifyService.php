<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ShopifyService
{
    protected $store;
    protected $token;
    protected $version;
    protected $collectionId;

    public function __construct(array $config = [])
    {
        $this->store = preg_replace('#^https?://#', '', (string) ($config['store_url'] ?? config('services.shopify.store_url')));
        $this->token = $config['access_token'] ?? config('services.shopify.access_token');
        $this->version = $config['api_version'] ?? config('services.shopify.api_version');
        $this->collectionId = $config['collection_id'] ?? config('services.shopify.collection_id');
    }

    public function createProduct($data)
    {
        if (!$this->store || !$this->token) {
            return [
                'success' => false,
                'error' => 'Shopify configuration is missing store URL or access token.',
            ];
        }

        $response = $this->createProductRequest($data);

        if (!$response['success']) {
            return [
                'success' => false,
                'error' => $response['error'],
            ];
        }

        $product = data_get($response['data'], 'productCreate.product');
        $createErrors = data_get($response['data'], 'productCreate.userErrors', []);

        if (!empty($createErrors) && $this->handleAlreadyInUseError($createErrors) && !empty($data['handle'])) {
            $fallbackData = $data;
            unset($fallbackData['handle']);

            $response = $this->createProductRequest($fallbackData);

            if (!$response['success']) {
                return [
                    'success' => false,
                    'error' => $response['error'],
                ];
            }

            $product = data_get($response['data'], 'productCreate.product');
            $createErrors = data_get($response['data'], 'productCreate.userErrors', []);
        }

        if (!empty($createErrors)) {
            return [
                'success' => false,
                'error' => $this->formatUserErrors($createErrors),
            ];
        }

        $productId = data_get($product, 'id');
        $variantId = data_get($product, 'variants.nodes.0.id');

        if (!$productId || !$variantId) {
            return [
                'success' => false,
                'error' => 'Shopify did not return a product or variant identifier.',
            ];
        }

        $variantUpdate = $this->graphqlRequest(<<<'GQL'
mutation UpdateVariant($productId: ID!, $variants: [ProductVariantsBulkInput!]!) {
  productVariantsBulkUpdate(productId: $productId, variants: $variants) {
    product {
      id
    }
    userErrors {
      field
      message
    }
  }
}
GQL, [
            'productId' => $productId,
            'variants' => [
                [
                    'id' => $variantId,
                    'price' => (string) $data['price'],
                ],
            ],
        ]);

        if (!$variantUpdate['success']) {
            return [
                'success' => false,
                'error' => $variantUpdate['error'],
            ];
        }

        $variantErrors = data_get($variantUpdate['data'], 'productVariantsBulkUpdate.userErrors', []);

        if (!empty($variantErrors)) {
            return [
                'success' => false,
                'error' => $this->formatUserErrors($variantErrors),
            ];
        }

        return [
            'success' => true,
            'product_id' => $productId,
        ];
    }

    protected function createProductRequest(array $data): array
    {
        $response = $this->graphqlRequest(<<<'GQL'
mutation CreateProduct($product: ProductCreateInput!) {
  productCreate(product: $product) {
    product {
      id
      title
      variants(first: 1) {
        nodes {
          id
        }
      }
    }
    userErrors {
      field
      message
    }
  }
}
GQL, [
            'product' => [
                'title' => $data['title'],
                'descriptionHtml' => $data['description'] ?? null,
                'handle' => $data['handle'] ?? null,
                'status' => 'ACTIVE',
            ],
        ]);

        return $response;
    }

    protected function handleAlreadyInUseError(array $errors): bool
    {
        return collect($errors)
            ->pluck('message')
            ->filter()
            ->contains(fn ($message) => Str::contains(Str::lower($message), 'already in use'));
    }

    public function addToCollection($productId)
    {
        if (!$this->store || !$this->token || !$this->collectionId) {
            return false;
        }

        $response = $this->graphqlRequest(<<<'GQL'
mutation AddProductsToCollection($id: ID!, $productIds: [ID!]!) {
  collectionAddProductsV2(id: $id, productIds: $productIds) {
    job {
      id
    }
    userErrors {
      field
      message
    }
  }
}
GQL, [
            'id' => $this->toGid('Collection', $this->collectionId),
            'productIds' => [$this->normalizeGid($productId, 'Product')],
        ]);

        if (!$response['success']) {
            return false;
        }

        $errors = data_get($response['data'], 'collectionAddProductsV2.userErrors', []);

        return empty($errors);
    }

    public function removeFromCollection(array $productIds): bool
    {
        if (!$this->store || !$this->token || !$this->collectionId || empty($productIds)) {
            return false;
        }

        $response = $this->graphqlRequest(<<<'GQL'
mutation RemoveProductsFromCollection($id: ID!, $productIds: [ID!]!) {
  collectionRemoveProducts(id: $id, productIds: $productIds) {
    job {
      id
    }
    userErrors {
      field
      message
    }
  }
}
GQL, [
            'id' => $this->toGid('Collection', $this->collectionId),
            'productIds' => array_values(array_filter(array_map(
                fn ($productId) => $this->normalizeGid($productId, 'Product'),
                $productIds
            ))),
        ]);

        if (!$response['success']) {
            return false;
        }

        $errors = data_get($response['data'], 'collectionRemoveProducts.userErrors', []);

        return empty($errors);
    }

    public function deleteProduct($productId): bool
    {
        if (!$this->store || !$this->token) {
            return false;
        }

        $response = $this->graphqlRequest(<<<'GQL'
mutation DeleteProduct($productId: ID!) {
  productDelete(input: {id: $productId}) {
    deletedProductId
    userErrors {
      field
      message
    }
  }
}
GQL, [
            'productId' => $this->normalizeGid($productId, 'Product'),
        ]);

        if (!$response['success']) {
            return false;
        }

        $errors = data_get($response['data'], 'productDelete.userErrors', []);

        return empty($errors);
    }

    public function testConnection(): array
    {
        if (!$this->store || !$this->token) {
            return [
                'success' => false,
                'error' => 'Shopify configuration is missing store URL or access token.',
            ];
        }

        $response = $this->graphqlRequest(<<<'GQL'
query TestConnection {
  shop {
    id
    name
    myshopifyDomain
  }
}
GQL);

        if (!$response['success']) {
            return [
                'success' => false,
                'error' => $response['error'],
            ];
        }

        $shop = data_get($response['data'], 'shop');

        return [
            'success' => true,
            'shop' => $shop,
        ];
    }

    public function findProductByHandle(string $handle): ?array
    {
        $handle = trim($handle);

        if ($handle === '' || !$this->store || !$this->token) {
            return null;
        }

        $response = $this->graphqlRequest(<<<'GQL'
query FindProductByHandle($identifier: ProductIdentifierInput!) {
  product: productByIdentifier(identifier: $identifier) {
    id
    handle
    title
  }
}
GQL, [
            'identifier' => [
                'handle' => $handle,
            ],
        ]);

        if (!$response['success']) {
            return null;
        }

        return data_get($response['data'], 'product');
    }

    public function getCollectionProducts(): array
    {
        if (!$this->store || !$this->token || !$this->collectionId) {
            return [
                'success' => false,
                'error' => 'Shopify configuration is missing store URL, access token, or collection id.',
            ];
        }

        $products = [];
        $cursor = null;
        $hasNextPage = true;

        while ($hasNextPage) {
            $response = $this->graphqlRequest(<<<'GQL'
query CollectionProducts($id: ID!, $first: Int!, $after: String) {
  collection(id: $id) {
    id
    title
    products(first: $first, after: $after) {
      pageInfo {
        hasNextPage
        endCursor
      }
      nodes {
        id
        title
        handle
        status
        variants(first: 1) {
          nodes {
            id
            sku
            price
          }
        }
      }
    }
  }
}
GQL, [
                'id' => $this->toGid('Collection', $this->collectionId),
                'first' => 100,
                'after' => $cursor,
            ]);

            if (!$response['success']) {
                return [
                    'success' => false,
                    'error' => $response['error'],
                ];
            }

            $collection = data_get($response['data'], 'collection');

            if (!$collection) {
                return [
                    'success' => false,
                    'error' => 'Shopify collection not found.',
                ];
            }

            foreach (data_get($collection, 'products.nodes', []) as $product) {
                $products[] = $product;
            }

            $pageInfo = data_get($collection, 'products.pageInfo', []);
            $hasNextPage = (bool) data_get($pageInfo, 'hasNextPage', false);
            $cursor = data_get($pageInfo, 'endCursor');
        }

        return [
            'success' => true,
            'collection' => [
                'id' => data_get($collection, 'id'),
                'title' => data_get($collection, 'title'),
            ],
            'products' => $products,
        ];
    }

    protected function graphqlRequest(string $query, array $variables = []): array
    {
        $url = "https://{$this->store}/admin/api/{$this->version}/graphql.json";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
            'Content-Type' => 'application/json',
        ])->retry(3, 250)->timeout(30)->post($url, [
            'query' => $query,
            'variables' => $variables,
        ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => $response->body(),
            ];
        }

        $payload = $response->json();

        if (!is_array($payload)) {
            return [
                'success' => false,
                'error' => 'Shopify returned an invalid GraphQL response.',
            ];
        }

        if (!empty($payload['errors'])) {
            return [
                'success' => false,
                'error' => $this->formatGraphqlErrors($payload['errors']),
                'errors' => $payload['errors'],
                'data' => $payload['data'] ?? null,
            ];
        }

        return [
            'success' => true,
            'data' => $payload['data'] ?? null,
        ];
    }

    protected function formatGraphqlErrors(array $errors): string
    {
        return collect($errors)
            ->pluck('message')
            ->filter()
            ->implode(' | ') ?: 'Shopify GraphQL request failed.';
    }

    protected function formatUserErrors(array $errors): string
    {
        return collect($errors)
            ->pluck('message')
            ->filter()
            ->implode(' | ') ?: 'Shopify rejected the request.';
    }

    protected function normalizeGid($value, string $type): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (Str::startsWith($value, 'gid://shopify/')) {
            return $value;
        }

        return 'gid://shopify/' . $type . '/' . $value;
    }

    protected function toGid(string $type, $value): string
    {
        return $this->normalizeGid($value, $type);
    }
}
