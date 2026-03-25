<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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

        $url = "https://{$this->store}/admin/api/{$this->version}/products.json";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
            'Content-Type' => 'application/json',
        ])->retry(3, 250)->timeout(30)->post($url, [
            'product' => [
                'title' => $data['title'],
                'body_html' => $data['description'],
                'handle' => $data['handle'] ?? null,
                'variants' => [
                    [
                        'price' => $data['price'],
                        'sku' => $data['sku'] ?? null,
                    ]
                ]
            ]
        ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => $response->body()
            ];
        }

        $product = $response->json('product');

        return [
            'success' => true,
            'product_id' => $product['id']
        ];
    }
    public function addToCollection($productId)
    {
        if (!$this->store || !$this->token || !$this->collectionId) {
            return false;
        }

        $url = "https://{$this->store}/admin/api/{$this->version}/collects.json";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
            'Content-Type' => 'application/json',
        ])->retry(3, 250)->timeout(30)->post($url, [
            'collect' => [
                'product_id' => $productId,
                'collection_id' => $this->collectionId,
            ]
        ]);

        return $response->successful();
    }

    public function deleteProduct($productId): bool
    {
        if (!$this->store || !$this->token) {
            return false;
        }

        $url = "https://{$this->store}/admin/api/{$this->version}/products/{$productId}.json";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
            'Content-Type' => 'application/json',
        ])->retry(3, 250)->timeout(30)->delete($url);

        return $response->successful();
    }

    public function testConnection(): array
    {
        if (!$this->store || !$this->token) {
            return [
                'success' => false,
                'error' => 'Shopify configuration is missing store URL or access token.',
            ];
        }

        $url = "https://{$this->store}/admin/api/{$this->version}/shop.json";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
        ])->retry(2, 200)->timeout(20)->get($url);

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => $response->body(),
            ];
        }

        return [
            'success' => true,
            'shop' => $response->json('shop'),
        ];
    }


}
