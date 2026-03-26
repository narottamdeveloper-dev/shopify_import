<?php

namespace App\Jobs;

use App\Models\ImportLog;
use App\Models\Product;
use App\Models\ShopifyStore;
use App\Models\Upload;
use App\Services\ShopifyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ProcessCsvChunkJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 1800;

    public $tries = 3;

    protected $uploadId;

    protected $shopifyStoreId;

    protected $headers;

    protected $rows;

    public function __construct($uploadId, $shopifyStoreId, array $headers, array $rows)
    {
        $this->uploadId = $uploadId;
        $this->shopifyStoreId = $shopifyStoreId;
        $this->headers = $headers;
        $this->rows = $rows;
    }

    public function handle(): void
    {
        $upload = Upload::find($this->uploadId);

        if (!$upload) {
            return;
        }

        $store = $this->resolveStore($upload);
        $shopify = new ShopifyService($store?->toShopifyConfig() ?? []);
        $collectionIndex = $this->loadCollectionIndex($shopify);

        foreach ($this->rows as $rowIndex => $row) {
            try {
                $data = $this->mapRow($this->headers, $row);
                $title = $data['title'];
                $description = $data['description'];
                $price = $data['price'];
                $sourceHandle = $this->normalizeHandle($data['handle'] ?? null, $title);
                $sourceSku = $this->normalizeText($data['sku'] ?? null);
                $sourceKey = $this->buildSourceKey($sourceHandle, $sourceSku, $title, $price);

                if (!$title || $price === null || $price === '') {
                    $this->createProductRecord($upload->id, [
                        'title' => $title,
                        'description' => $description,
                        'price' => is_numeric($price) ? $price : null,
                        'status' => Product::STATUS_FAILED,
                        'error_message' => 'Missing required title or price column in CSV row.',
                        'shopify_store_id' => $store?->id,
                        'source_handle' => $sourceHandle,
                        'source_sku' => $sourceSku,
                        'source_key' => $sourceKey,
                    ]);

                    $this->incrementUploadCounts($upload->id, 0, 0, 1, 1);
                    $this->writeImportLog($upload->id, 'Row failed validation', [
                        'title' => $title,
                        'price' => $price,
                    ]);
                    continue;
                }

                if (!is_numeric($price)) {
                    $this->createProductRecord($upload->id, [
                        'title' => $title,
                        'description' => $description,
                        'price' => null,
                        'status' => Product::STATUS_FAILED,
                        'error_message' => 'Invalid price value in CSV row.',
                        'shopify_store_id' => $store?->id,
                        'source_handle' => $sourceHandle,
                        'source_sku' => $sourceSku,
                        'source_key' => $sourceKey,
                    ]);

                    $this->incrementUploadCounts($upload->id, 0, 0, 1, 1);
                    $this->writeImportLog($upload->id, 'Row failed validation', [
                        'title' => $title,
                        'price' => $price,
                        'reason' => 'non_numeric_price',
                    ]);
                    continue;
                }

                if ($this->productAlreadyExistsInCollection($collectionIndex, $sourceHandle, $sourceSku)) {
                    $this->createProductRecord($upload->id, [
                        'title' => $title,
                        'description' => $description,
                        'price' => $price,
                        'status' => Product::STATUS_SKIPPED,
                        'error_message' => 'Product already present in the Shopify collection and skipped.',
                        'shopify_store_id' => $store?->id,
                        'source_handle' => $sourceHandle,
                        'source_sku' => $sourceSku,
                        'source_key' => $sourceKey,
                    ]);

                    $this->incrementUploadCounts($upload->id, 0, 1, 0, 1);
                    $this->writeImportLog($upload->id, 'Duplicate product skipped', [
                        'title' => $title,
                        'source_key' => $sourceKey,
                    ]);
                    continue;
                }

                $product = $this->createProductRecord($upload->id, [
                    'title' => $title,
                    'description' => $description,
                    'price' => (float) $price,
                    'status' => Product::STATUS_PENDING,
                    'shopify_store_id' => $store?->id,
                    'source_handle' => $sourceHandle,
                    'source_sku' => $sourceSku,
                    'source_key' => $sourceKey,
                ]);

                $result = $shopify->createProduct([
                    'title' => $title,
                    'description' => $description,
                    'price' => $price,
                    'handle' => $sourceHandle,
                    'sku' => $sourceSku,
                ]);

                if (!$result['success']) {
                    $product->update([
                        'status' => Product::STATUS_FAILED,
                        'error_message' => $result['error'],
                    ]);
                    $this->incrementUploadCounts($upload->id, 0, 0, 1, 1);
                    $this->writeImportLog($upload->id, 'Shopify product create failed', [
                        'title' => $title,
                        'error' => $result['error'],
                    ]);
                    continue;
                }

                if (!$shopify->addToCollection($result['product_id'])) {
                    $this->writeImportLog($upload->id, 'Collection add failed', [
                        'product_id' => $result['product_id'],
                    ]);
                } else {
                    $createdCollectionProduct = [
                        'id' => $result['product_id'],
                        'title' => $title,
                        'handle' => $sourceHandle,
                        'variants' => [
                            'nodes' => [
                                [
                                    'sku' => $sourceSku,
                                    'price' => (string) $price,
                                ],
                            ],
                        ],
                    ];

                    $this->indexCollectionProduct($collectionIndex, $createdCollectionProduct);
                }

                $product->update([
                    'status' => Product::STATUS_SUCCESS,
                    'shopify_product_id' => $result['product_id'],
                ]);

                $this->incrementUploadCounts($upload->id, 1, 0, 0, 1);
            } catch (Throwable $e) {
                $this->createProductRecord($upload->id, [
                    'title' => $title ?? null,
                    'description' => $description ?? null,
                    'price' => is_numeric($price ?? null) ? (float) $price : null,
                    'status' => Product::STATUS_FAILED,
                    'error_message' => $e->getMessage(),
                    'shopify_store_id' => $store?->id,
                    'source_handle' => $sourceHandle ?? null,
                    'source_sku' => $sourceSku ?? null,
                    'source_key' => $sourceKey ?? null,
                ]);

                $this->incrementUploadCounts($upload->id, 0, 0, 1, 1);
                $this->writeImportLog($upload->id, 'Row import exception', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->refreshUploadStatus($upload->id);
    }

    protected function mapRow(array $headers, array $row): array
    {
        $rowData = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $rowData[$header] = $row[$index] ?? null;
        }

        return [
            'handle' => $rowData['Handle'] ?? $rowData['handle'] ?? null,
            'sku' => $rowData['Variant SKU'] ?? $rowData['SKU'] ?? $rowData['sku'] ?? null,
            'title' => $rowData['Title'] ?? $row[0] ?? null,
            'description' => $rowData['Body HTML'] ?? $rowData['Description'] ?? $row[1] ?? null,
            'price' => $rowData['Variant Price'] ?? $rowData['Price'] ?? $row[2] ?? null,
        ];
    }

    protected function normalizeHandle($handle, $title): string
    {
        $value = $this->normalizeText($handle);

        if ($value !== '') {
            return Str::slug($value);
        }

        return Str::slug($this->normalizeText($title));
    }

    protected function normalizeText($value): string
    {
        return trim((string) $value);
    }

    protected function buildSourceKey(string $handle, string $sku, ?string $title, $price): string
    {
        if ($handle !== '') {
            return 'handle:' . $handle;
        }

        if ($sku !== '') {
            return 'sku:' . Str::lower($sku);
        }

        return 'title:' . Str::lower(Str::slug($this->normalizeText($title))) . '|price:' . $this->normalizePrice($price);
    }

    protected function productAlreadyExistsInCollection(array $collectionIndex, string $sourceHandle, string $sourceSku): bool
    {
        $cleanHandle = $this->normalizeText($sourceHandle);
        $cleanSku = $this->normalizeText($sourceSku);

        if ($cleanHandle !== '' && isset($collectionIndex['handles'][$cleanHandle])) {
            return true;
        }

        if ($cleanSku !== '' && isset($collectionIndex['skus'][Str::lower($cleanSku)])) {
            return true;
        }

        return false;
    }

    protected function loadCollectionIndex(ShopifyService $shopify): array
    {
        $result = $shopify->getCollectionProducts();

        if (!$result['success']) {
            $this->writeImportLog($this->uploadId, 'Collection lookup failed', [
                'error' => $result['error'] ?? 'Unable to load collection products.',
            ]);

            return [
                'handles' => [],
                'skus' => [],
            ];
        }

        $index = [
            'handles' => [],
            'skus' => [],
        ];

        foreach ($result['products'] ?? [] as $product) {
            $this->indexCollectionProduct($index, $product);
        }

        return $index;
    }

    protected function indexCollectionProduct(array &$collectionIndex, array $product): void
    {
        $handle = $this->normalizeHandle(data_get($product, 'handle'), data_get($product, 'title'));

        if ($handle !== '') {
            $collectionIndex['handles'][$handle] = true;
        }

        $variant = data_get($product, 'variants.nodes.0', []);
        $sku = $this->normalizeText(data_get($variant, 'sku'));

        if ($sku !== '') {
            $collectionIndex['skus'][Str::lower($sku)] = true;
        }

        $title = $this->normalizeText(data_get($product, 'title'));
    }

    protected function normalizePrice($price): string
    {
        return number_format((float) $price, 2, '.', '');
    }

    protected function createProductRecord(int $uploadId, array $attributes): Product
    {
        return Product::create(array_merge([
            'upload_id' => $uploadId,
        ], $attributes));
    }

    protected function incrementUploadCounts(int $uploadId, int $successful, int $skipped, int $failed, int $processed): void
    {
        DB::table('uploads')
            ->where('id', $uploadId)
            ->update([
                'successful_rows' => DB::raw('successful_rows + ' . (int) $successful),
                'skipped_rows' => DB::raw('skipped_rows + ' . (int) $skipped),
                'failed_rows' => DB::raw('failed_rows + ' . (int) $failed),
                'processed_rows' => DB::raw('processed_rows + ' . (int) $processed),
            ]);
    }

    protected function refreshUploadStatus(int $uploadId): void
    {
        $upload = Upload::find($uploadId);

        if (!$upload || $upload->total_rows === 0) {
            return;
        }

        if ($upload->processed_rows >= $upload->total_rows) {
            $upload->update(['status' => Upload::STATUS_COMPLETED]);
            $this->writeImportLog($uploadId, 'Chunk completed', [
                'successful_rows' => $upload->successful_rows,
                'skipped_rows' => $upload->skipped_rows,
                'failed_rows' => $upload->failed_rows,
            ]);
        }
    }

    protected function writeImportLog(int $uploadId, string $message, array $context = []): void
    {
        try {
            ImportLog::create([
                'upload_id' => $uploadId,
                'message' => $message,
                'context' => $context,
            ]);
        } catch (Throwable) {
        }
    }

    protected function resolveStore(Upload $upload): ?ShopifyStore
    {
        if ($this->shopifyStoreId) {
            return ShopifyStore::find($this->shopifyStoreId);
        }

        if ($upload->shopify_store_id) {
            return ShopifyStore::find($upload->shopify_store_id);
        }

        return ShopifyStore::where('is_default', true)->where('is_active', true)->first();
    }
}
