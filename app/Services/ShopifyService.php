<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ShopifyService
{
    protected $store;
    protected $token;
    protected $version;

    public function __construct()
    {
        $this->store = preg_replace('#^https?://#', '', (string) config('services.shopify.store_url'));
        $this->token = config('services.shopify.access_token');
        $this->version = config('services.shopify.api_version');
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
    $collectionId = config('services.shopify.collection_id');

    if (!$this->store || !$this->token || !$collectionId) {
        return false;
    }

    $url = "https://{$this->store}/admin/api/{$this->version}/collects.json";

    $response = Http::withHeaders([
        'X-Shopify-Access-Token' => $this->token,
        'Content-Type' => 'application/json',
    ])->retry(3, 250)->timeout(30)->post($url, [
        'collect' => [
            'product_id' => $productId,
            'collection_id' => $collectionId
        ]
    ]);

    return $response->successful();
}


}
