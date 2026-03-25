<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use App\Models\Product;
use App\Models\ShopifyStore;
use App\Models\Upload;
use App\Services\ShopifyService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $stores = $this->stores();

        return response()->view('admin', [
            'stores' => $stores,
            'uploads' => Upload::query()
                ->with('shopifyStore')
                ->latest()
                ->take(8)
                ->get(),
            'duplicateGroups' => $this->duplicateGroups(),
            'cleanupLogs' => ImportLog::query()
                ->where('message', 'Duplicate cleanup completed')
                ->latest()
                ->take(6)
                ->get(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'store_url' => 'required|string|max:255',
            'access_token' => 'required|string|max:500',
            'api_version' => 'required|string|max:20',
            'collection_id' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        $store = ShopifyStore::updateOrCreate(
            ['store_url' => $this->normalizeStoreUrl($data['store_url'])],
            [
                'name' => $data['name'],
                'access_token' => $data['access_token'],
                'api_version' => $data['api_version'],
                'collection_id' => $data['collection_id'] ?? null,
                'is_active' => true,
                'is_default' => (bool) ($data['is_default'] ?? false),
            ]
        );

        if ($store->is_default) {
            ShopifyStore::where('id', '!=', $store->id)->update(['is_default' => false]);
        }

        return back()->with('success', 'Store connection saved.');
    }

    public function destroyStore(ShopifyStore $store)
    {
        if ($store->is_default) {
            return back()->withErrors(['store' => 'Unset the default store before deleting it.']);
        }

        $store->delete();

        return back()->with('success', 'Store connection deleted.');
    }

    public function setDefaultStore(ShopifyStore $store)
    {
        ShopifyStore::query()->update(['is_default' => false]);
        $store->update(['is_default' => true, 'is_active' => true]);

        return back()->with('success', 'Default store updated.');
    }

    public function testStore(ShopifyStore $store)
    {
        $result = (new ShopifyService($store->toShopifyConfig()))->testConnection();

        if (!$result['success']) {
            return back()->withErrors(['store' => 'Connection failed: ' . $result['error']]);
        }

        return back()->with('success', 'Connection successful for ' . ($result['shop']['name'] ?? $store->name) . '.');
    }

    public function cleanupDuplicates(ShopifyStore $store)
    {
        $service = new ShopifyService($store->toShopifyConfig());
        $products = Product::query()
            ->where('shopify_store_id', $store->id)
            ->where('status', Product::STATUS_SUCCESS)
            ->whereNotNull('shopify_product_id')
            ->orderBy('created_at')
            ->get();

        $seen = [];
        $deleted = 0;

        foreach ($products as $product) {
            $key = $product->source_key ?: $this->buildProductKey($product->title, $product->price);

            if ($key === null) {
                continue;
            }

            if (!isset($seen[$key])) {
                $seen[$key] = $product->id;
                continue;
            }

            if ($service->deleteProduct($product->shopify_product_id)) {
                ImportLog::create([
                    'upload_id' => $product->upload_id,
                    'message' => 'Duplicate product removed from Shopify',
                    'context' => [
                        'store_id' => $store->id,
                        'product_id' => $product->id,
                        'shopify_product_id' => $product->shopify_product_id,
                        'source_key' => $key,
                    ],
                ]);

                $product->delete();
                $deleted++;
            }
        }

        ImportLog::create([
            'upload_id' => null,
            'message' => 'Duplicate cleanup completed',
            'context' => [
                'store_id' => $store->id,
                'deleted' => $deleted,
            ],
        ]);

        return back()->with('success', "Duplicate cleanup finished. {$deleted} products removed.");
    }

    protected function stores()
    {
        $stores = ShopifyStore::query()->orderByDesc('is_default')->orderBy('name')->get();

        if ($stores->isEmpty()) {
            $default = $this->seedDefaultStore();
            return $default ? ShopifyStore::query()->orderByDesc('is_default')->orderBy('name')->get() : $stores;
        }

        return $stores;
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

    protected function duplicateGroups(): array
    {
        $storeNames = ShopifyStore::query()->pluck('name', 'id');

        return Product::query()
            ->selectRaw('shopify_store_id, source_key, title, price, COUNT(*) as duplicate_count')
            ->whereNotNull('shopify_store_id')
            ->whereIn('status', [Product::STATUS_SUCCESS, Product::STATUS_SKIPPED])
            ->groupBy('shopify_store_id', 'source_key', 'title', 'price')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->map(fn (Product $product) => [
                'store_id' => $product->shopify_store_id,
                'store_name' => $storeNames[$product->shopify_store_id] ?? 'Store',
                'source_key' => $product->source_key ?: $this->buildProductKey($product->title, $product->price),
                'title' => $product->title,
                'price' => $product->price,
                'duplicate_count' => (int) $product->duplicate_count,
            ])
            ->values()
            ->all();
    }

    protected function normalizeStoreUrl(string $url): string
    {
        return preg_replace('#^https?://#', '', trim($url));
    }

    protected function buildProductKey(?string $title, $price): ?string
    {
        $title = trim((string) $title);

        if ($title === '' || $price === null || $price === '') {
            return null;
        }

        return strtolower(str_replace(' ', '-', $title)) . '|' . number_format((float) $price, 2, '.', '');
    }
}
