<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ShopifyStore;
use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CollectionProductsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_streams_a_collection_export_csv_with_import_status(): void
    {
        $store = ShopifyStore::create([
            'name' => 'Main Store',
            'store_url' => 'example.myshopify.com',
            'access_token' => 'test-token',
            'api_version' => '2024-01',
            'collection_id' => '123',
            'is_default' => true,
            'is_active' => true,
        ]);

        $upload = Upload::create([
            'shopify_store_id' => $store->id,
            'file_name' => 'import.csv',
            'file_path' => 'uploads/import.csv',
            'status' => Upload::STATUS_COMPLETED,
        ]);

        Product::create([
            'upload_id' => $upload->id,
            'shopify_store_id' => $store->id,
            'title' => 'Imported Product',
            'price' => 12.50,
            'status' => Product::STATUS_SUCCESS,
            'shopify_product_id' => 'gid://shopify/Product/101',
            'source_handle' => 'imported-product',
            'source_sku' => 'SKU-1',
            'source_key' => 'handle:imported-product',
        ]);

        Http::fake([
            'https://example.myshopify.com/admin/api/2024-01/graphql.json' => Http::sequence()
                ->push([
                    'data' => [
                        'collection' => [
                            'id' => 'gid://shopify/Collection/123',
                            'title' => 'Spring Collection',
                            'products' => [
                                'pageInfo' => [
                                    'hasNextPage' => true,
                                    'endCursor' => 'cursor-1',
                                ],
                                'nodes' => [
                                    [
                                        'id' => 'gid://shopify/Product/101',
                                        'title' => 'Imported Product',
                                        'handle' => 'imported-product',
                                        'status' => 'ACTIVE',
                                        'variants' => [
                                            'nodes' => [
                                                [
                                                    'id' => 'gid://shopify/ProductVariant/201',
                                                    'sku' => 'SKU-1',
                                                    'price' => '12.50',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ])
                ->push([
                    'data' => [
                        'collection' => [
                            'id' => 'gid://shopify/Collection/123',
                            'title' => 'Spring Collection',
                            'products' => [
                                'pageInfo' => [
                                    'hasNextPage' => false,
                                    'endCursor' => null,
                                ],
                                'nodes' => [
                                    [
                                        'id' => 'gid://shopify/Product/102',
                                        'title' => 'External Product',
                                        'handle' => 'external-product',
                                        'status' => 'ACTIVE',
                                        'variants' => [
                                            'nodes' => [
                                                [
                                                    'id' => 'gid://shopify/ProductVariant/202',
                                                    'sku' => 'SKU-2',
                                                    'price' => '18.00',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
        ]);

        $response = $this->get('/collection-products/export?shopify_store_id=' . $store->id);

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('collection_id,collection_title,shopify_product_id,title,handle,status,variant_sku,variant_price,imported,local_product_id,local_upload_id,local_status,source_handle,source_sku,source_key,local_error_message,local_created_at', $csv);
        $this->assertStringContainsString('gid://shopify/Collection/123,Spring Collection,gid://shopify/Product/101,Imported Product,imported-product,ACTIVE,SKU-1,12.50,yes,', $csv);
        $this->assertStringContainsString('gid://shopify/Collection/123,Spring Collection,gid://shopify/Product/102,External Product,external-product,ACTIVE,SKU-2,18.00,no,', $csv);
    }

    public function test_it_removes_a_single_product_from_the_collection_via_api(): void
    {
        $store = ShopifyStore::create([
            'name' => 'Main Store',
            'store_url' => 'example.myshopify.com',
            'access_token' => 'test-token',
            'api_version' => '2024-01',
            'collection_id' => '123',
            'is_default' => true,
            'is_active' => true,
        ]);

        $upload = Upload::create([
            'shopify_store_id' => $store->id,
            'file_name' => 'import.csv',
            'file_path' => 'uploads/import.csv',
            'status' => Upload::STATUS_COMPLETED,
        ]);

        Product::create([
            'upload_id' => $upload->id,
            'shopify_store_id' => $store->id,
            'title' => 'Imported Product',
            'price' => 12.50,
            'status' => Product::STATUS_SUCCESS,
            'shopify_product_id' => 'gid://shopify/Product/101',
            'source_handle' => 'imported-product',
            'source_sku' => 'SKU-1',
            'source_key' => 'handle:imported-product',
        ]);

        Http::fake([
            'https://example.myshopify.com/admin/api/2024-01/graphql.json' => Http::response([
                'data' => [
                    'collectionRemoveProducts' => [
                        'job' => [
                            'id' => 'gid://shopify/Job/1',
                        ],
                        'userErrors' => [],
                    ],
                ],
            ]),
        ]);

        $response = $this->postJson('/collection-products/remove', [
            'shopify_store_id' => $store->id,
            'shopify_product_id' => 'gid://shopify/Product/101',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Product removed from the configured collection.',
                'store_id' => $store->id,
                'collection_id' => '123',
                'shopify_product_id' => 'gid://shopify/Product/101',
            ]);

        $this->assertSame('gid://shopify/Product/101', $response->json('shopify_product_id'));
    }
}
