<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;
use App\Jobs\ProcessCsvJob;
use App\Models\Product;
use App\Models\ShopifyStore;
use App\Models\ImportLog;
use App\Services\ShopifyService;

class UploadController extends Controller
{
    public function index()
    {
        return response()->view('upload', [
            'stores' => $this->stores(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
    }

    public function dashboardData()
    {
        return response()->json($this->dashboardPayload())
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:51200',
            'shopify_store_id' => 'nullable|exists:shopify_stores,id',
        ]);

        $file = $request->file('file');
        $store = $request->filled('shopify_store_id')
            ? ShopifyStore::find($request->integer('shopify_store_id'))
            : $this->defaultStore();

        $path = $file->store('uploads');

        $upload = Upload::create([
            'shopify_store_id' => $store?->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'status' => Upload::STATUS_PENDING,
        ]);

        ProcessCsvJob::dispatch($upload->id);

        return back()
            ->with('success', 'File uploaded and queued for background processing.')
            ->with('tracked_upload_id', $upload->id);
    }

    public function removeFromCollection(Upload $upload)
    {
        $store = $upload->shopifyStore ?: $this->defaultStore();

        if (!$store || !$store->collection_id) {
            return back()->withErrors(['upload' => 'Collection cleanup is not configured for this store.']);
        }

        $productIds = Product::query()
            ->where('upload_id', $upload->id)
            ->where('status', Product::STATUS_SUCCESS)
            ->whereNotNull('shopify_product_id')
            ->pluck('shopify_product_id')
            ->all();

        if (empty($productIds)) {
            return back()->withErrors(['upload' => 'No imported products were found for collection cleanup.']);
        }

        $service = new \App\Services\ShopifyService($store->toShopifyConfig());

        if (!$service->removeFromCollection($productIds)) {
            return back()->withErrors(['upload' => 'Collection cleanup failed.']);
        }

        ImportLog::create([
            'upload_id' => $upload->id,
            'message' => 'Products removed from collection',
            'context' => [
                'store_id' => $store->id,
                'collection_id' => $store->collection_id,
                'product_count' => count($productIds),
            ],
        ]);

        return back()->with('success', 'Products were removed from the configured collection only.');
    }

    public function exportCollectionProducts(Request $request)
    {
        $validated = $request->validate([
            'shopify_store_id' => 'nullable|exists:shopify_stores,id',
        ]);

        $store = !empty($validated['shopify_store_id'])
            ? ShopifyStore::find($validated['shopify_store_id'])
            : $this->defaultStore();

        if (!$store || !$store->collection_id) {
            return response()->json([
                'message' => 'Collection export is not configured for this store.',
            ], 422);
        }

        $service = new ShopifyService($store->toShopifyConfig());
        $result = $service->getCollectionProducts();

        if (!$result['success']) {
            return response()->json([
                'message' => $result['error'] ?? 'Failed to load collection products.',
            ], 502);
        }

        $collection = $result['collection'] ?? [];
        $products = $result['products'] ?? [];
        $localProducts = Product::query()
            ->where('shopify_store_id', $store->id)
            ->whereIn('shopify_product_id', collect($products)->pluck('id')->filter()->all())
            ->latest('id')
            ->get()
            ->keyBy('shopify_product_id');

        $fileName = sprintf(
            'collection-%s-products-%s.csv',
            $store->collection_id,
            now()->format('Ymd_His')
        );

        return response()->streamDownload(function () use ($collection, $products, $localProducts) {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'collection_id',
                'collection_title',
                'shopify_product_id',
                'title',
                'handle',
                'status',
                'variant_sku',
                'variant_price',
                'imported',
                'local_product_id',
                'local_upload_id',
                'local_status',
                'source_handle',
                'source_sku',
                'source_key',
                'local_error_message',
                'local_created_at',
            ]);

            foreach ($products as $product) {
                $localProduct = $localProducts->get(data_get($product, 'id'));
                $variant = data_get($product, 'variants.nodes.0', []);

                fputcsv($output, [
                    data_get($collection, 'id'),
                    data_get($collection, 'title'),
                    data_get($product, 'id'),
                    data_get($product, 'title'),
                    data_get($product, 'handle'),
                    data_get($product, 'status'),
                    data_get($variant, 'sku'),
                    data_get($variant, 'price'),
                    $localProduct ? 'yes' : 'no',
                    $localProduct?->id,
                    $localProduct?->upload_id,
                    $localProduct?->status,
                    $localProduct?->source_handle,
                    $localProduct?->source_sku,
                    $localProduct?->source_key,
                    $localProduct?->error_message,
                    optional($localProduct?->created_at)->toDateTimeString(),
                ]);
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function removeCollectionProduct(Request $request)
    {
        $validated = $request->validate([
            'shopify_product_id' => 'required|string',
            'shopify_store_id' => 'nullable|exists:shopify_stores,id',
        ]);

        $store = !empty($validated['shopify_store_id'])
            ? ShopifyStore::find($validated['shopify_store_id'])
            : $this->defaultStore();

        if (!$store || !$store->collection_id) {
            return response()->json([
                'message' => 'Collection cleanup is not configured for this store.',
            ], 422);
        }

        $shopifyProductId = trim($validated['shopify_product_id']);
        $service = new ShopifyService($store->toShopifyConfig());

        if (!$service->removeFromCollection([$shopifyProductId])) {
            return response()->json([
                'message' => 'Collection cleanup failed.',
            ], 502);
        }

        $localProduct = Product::query()
            ->where('shopify_store_id', $store->id)
            ->where('shopify_product_id', $shopifyProductId)
            ->latest('id')
            ->first();

        if ($localProduct) {
        ImportLog::create([
            'upload_id' => $localProduct->upload_id,
            'message' => 'Product removed from collection',
            'context' => [
                'store_id' => $store->id,
                    'collection_id' => $store->collection_id,
                    'shopify_product_id' => $shopifyProductId,
                    'local_product_id' => $localProduct->id,
                ],
            ]);
        }

        return response()->json([
            'message' => 'Product removed from the configured collection.',
            'store_id' => $store->id,
            'collection_id' => $store->collection_id,
            'shopify_product_id' => $shopifyProductId,
            'local_product_id' => $localProduct?->id,
        ]);
    }

    protected function dashboardPayload(): array
    {
        $recentUploads = Upload::query()
            ->latest()
            ->take(6)
            ->get();

        $latestCompleted = Upload::query()
            ->where('status', Upload::STATUS_COMPLETED)
            ->latest()
            ->first();

        $latestLogs = ImportLog::query()
            ->with(['upload:id,file_name,status'])
            ->latest()
            ->take(10)
            ->get();

        return [
            'stats' => [
                'uploads' => Upload::count(),
                'products' => Product::count(),
                'completed' => Upload::where('status', Upload::STATUS_COMPLETED)->count(),
                'failed' => Upload::where('status', Upload::STATUS_FAILED)->count(),
                'skipped' => Product::where('status', Product::STATUS_SKIPPED)->count(),
            ],
            'stores' => $this->stores()->map(fn (ShopifyStore $store) => [
                'id' => $store->id,
                'name' => $store->name,
                'store_url' => $store->store_url,
                'is_default' => $store->is_default,
            ])->values(),
            'recent_uploads' => $recentUploads->map(fn (Upload $upload) => $this->uploadPayload($upload))->values(),
            'failed_rows' => Product::query()
                ->where('status', Product::STATUS_FAILED)
                ->latest()
                ->take(6)
                ->get()
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'title' => $product->title ?: 'Untitled product',
                    'error_message' => $product->error_message ?: 'Import validation failed.',
                    'upload_id' => $product->upload_id,
                    'created_at' => optional($product->created_at)->format('d M Y, h:i A'),
                ])
                ->values(),
            'logs' => $latestLogs->map(fn (ImportLog $log) => [
                'id' => $log->id,
                'message' => $log->message,
                'upload_id' => $log->upload_id,
                'upload_name' => $log->upload?->file_name,
                'upload_status' => $log->upload?->status,
                'context' => $log->context ?: [],
                'severity' => $this->logSeverity($log->message),
                'created_at' => optional($log->created_at)->format('d M Y, h:i A'),
            ])->values(),
            'latest_notice' => $latestCompleted ? $this->buildNotice($latestCompleted) : null,
            'generated_at' => now()->format('d M Y, h:i:s A'),
        ];
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            Upload::STATUS_PENDING => 'Queued',
            Upload::STATUS_PROCESSING => 'Processing',
            Upload::STATUS_COMPLETED => 'Completed',
            Upload::STATUS_FAILED => 'Failed',
            default => ucfirst($status),
        };
    }

    protected function uploadPayload(Upload $upload): array
    {
        $processed = max((int) $upload->processed_rows, (int) $upload->successful_rows + (int) $upload->skipped_rows + (int) $upload->failed_rows);
        $total = max(1, (int) $upload->total_rows);

        return [
            'id' => $upload->id,
            'file_name' => $upload->file_name,
            'status' => $upload->status,
            'status_label' => $this->statusLabel($upload->status),
            'created_at' => optional($upload->created_at)->format('d M Y, h:i A'),
            'total_rows' => (int) $upload->total_rows,
            'processed_rows' => $processed,
            'successful_rows' => (int) $upload->successful_rows,
            'skipped_rows' => (int) $upload->skipped_rows,
            'failed_rows' => (int) $upload->failed_rows,
            'progress' => min(100, (int) round(($processed / $total) * 100)),
        ];
    }

    protected function buildNotice(Upload $upload): array
    {
        $skipped = (int) $upload->skipped_rows;
        $failed = (int) $upload->failed_rows;
        $successful = (int) $upload->successful_rows;

        return [
            'type' => $failed > 0 ? 'warning' : 'success',
            'title' => $failed > 0 ? 'Import completed with some issues' : 'Import completed successfully',
            'message' => $skipped > 0
            ? "{$successful} products imported and {$skipped} already-present products skipped."
            : "{$successful} products imported successfully.",
        ];
    }

    protected function logSeverity(string $message): string
    {
        $message = strtolower($message);

        return match (true) {
            str_contains($message, 'failed'),
            str_contains($message, 'error') => 'error',
            str_contains($message, 'skipped'),
            str_contains($message, 'missing') => 'warning',
            default => 'info',
        };
    }

    protected function stores()
    {
        $stores = ShopifyStore::query()->orderByDesc('is_default')->orderBy('name')->get();

        if ($stores->isEmpty()) {
            $seeded = $this->seedDefaultStore();

            return $seeded
                ? ShopifyStore::query()->orderByDesc('is_default')->orderBy('name')->get()
                : $stores;
        }

        return $stores;
    }

    protected function defaultStore(): ?ShopifyStore
    {
        $store = ShopifyStore::where('is_default', true)->where('is_active', true)->first() ?: $this->seedDefaultStore();

        if ($store) {
            $this->backfillLegacyRecords($store);
        }

        return $store;
    }

    protected function seedDefaultStore(): ?ShopifyStore
    {
        $storeUrl = $this->normalizeStoreUrl((string) config('services.shopify.store_url'));
        $token = (string) config('services.shopify.access_token');

        if ($storeUrl === '' || $token === '') {
            return null;
        }

        return tap(ShopifyStore::firstOrCreate(
            ['store_url' => $storeUrl],
            [
                'name' => config('app.name', 'Shopify Importer') . ' Default',
                'access_token' => $token,
                'api_version' => config('services.shopify.api_version', '2024-01'),
                'collection_id' => config('services.shopify.collection_id'),
                'is_default' => true,
                'is_active' => true,
            ]
        ), function (ShopifyStore $store) {
            $this->backfillLegacyRecords($store);
        });
    }

    protected function backfillLegacyRecords(ShopifyStore $store): void
    {
        Upload::whereNull('shopify_store_id')->update(['shopify_store_id' => $store->id]);
        Product::whereNull('shopify_store_id')->update(['shopify_store_id' => $store->id]);
    }

    protected function normalizeStoreUrl(string $url): string
    {
        return preg_replace('#^https?://#', '', trim($url));
    }
}
