<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Models\Product;
use App\Models\ImportLog;
use App\Services\ShopifyService;
use Throwable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use SplFileObject;

class ProcessCsvJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 1800;

    public $tries = 3;

    protected $uploadId;

    public function __construct($uploadId)
    {
        $this->uploadId = $uploadId;
    }

    public function handle(): void
    {
        logger('Job started', ['upload_id' => $this->uploadId]);
        $this->writeImportLog('Job started', ['upload_id' => $this->uploadId]);
        $upload = Upload::find($this->uploadId);

        if (!$upload) {
            $this->writeImportLog('Upload not found', ['upload_id' => $this->uploadId]);
            return;
        }

        $upload->update(['status' => Upload::STATUS_PROCESSING]);

        $disk = Storage::disk(config('filesystems.default'));
        $filePath = $disk->path($upload->file_path);

        logger('File path', ['path' => $filePath]);
        $this->writeImportLog('File path resolved', [
            'upload_id' => $upload->id,
            'path' => $filePath,
            'stored_path' => $upload->file_path,
        ]);

        if (!$disk->exists($upload->file_path)) {
            logger('File missing', ['path' => $filePath, 'stored_path' => $upload->file_path]);
            $this->writeImportLog('File missing', [
                'upload_id' => $upload->id,
                'path' => $filePath,
                'stored_path' => $upload->file_path,
            ]);
            $upload->update(['status' => Upload::STATUS_FAILED]);
            return;
        }

        $file = new SplFileObject($filePath);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $headers = $this->readHeaders($file);

        if ($headers === []) {
            $this->writeImportLog('CSV has no data rows', ['upload_id' => $upload->id]);
            $upload->update(['status' => Upload::STATUS_FAILED]);
            return;
        }

        $this->writeImportLog('Headers loaded', [
            'upload_id' => $upload->id,
            'headers' => $headers,
        ]);

        $shopify = new ShopifyService();
        $processedCount = 0;
        $failedCount = 0;
        $rowIndex = 1;

        foreach ($file as $row) {
            $rowIndex++;

            try {
                if (!is_array($row) || $this->isEmptyRow($row)) {
                    continue;
                }

                $data = $this->mapRow($headers, $row);
                $title = $data['title'];
                $description = $data['description'];
                $price = $data['price'];

                if (!$title || $price === null || $price === '') {
                    $failedCount++;

                    Product::create([
                        'upload_id' => $upload->id,
                        'title' => $title,
                        'description' => $description,
                        'price' => is_numeric($price) ? $price : null,
                        'status' => Product::STATUS_FAILED,
                        'error_message' => 'Missing required title or price column in CSV row.',
                    ]);

                    $this->writeImportLog('Row failed validation', [
                        'upload_id' => $upload->id,
                        'row_index' => $rowIndex,
                        'title' => $title,
                        'price' => $price,
                    ]);

                    continue;
                }

                $product = Product::create([
                    'upload_id' => $upload->id,
                    'title' => $title,
                    'description' => $description,
                    'price' => $price,
                    'status' => Product::STATUS_PENDING,
                ]);

                $result = $shopify->createProduct([
                    'title' => $title,
                    'description' => $description,
                    'price' => $price,
                ]);

                if (!$result['success']) {
                    $failedCount++;
                    $product->update([
                        'status' => Product::STATUS_FAILED,
                        'error_message' => $result['error'],
                    ]);
                    $this->writeImportLog('Shopify product create failed', [
                        'upload_id' => $upload->id,
                        'row_index' => $rowIndex,
                        'title' => $title,
                        'error' => $result['error'],
                    ]);
                    continue;
                }

                if (!$shopify->addToCollection($result['product_id'])) {
                    logger('Collection add failed', ['product_id' => $result['product_id']]);
                    $this->writeImportLog('Collection add failed', [
                        'upload_id' => $upload->id,
                        'row_index' => $rowIndex,
                        'product_id' => $result['product_id'],
                    ]);
                }

                $product->update([
                    'status' => Product::STATUS_SUCCESS,
                    'shopify_product_id' => $result['product_id'],
                ]);
                $processedCount++;

                if ($processedCount % 100 === 0) {
                    $this->writeImportLog('Import progress', [
                        'upload_id' => $upload->id,
                        'processed_count' => $processedCount,
                        'failed_count' => $failedCount,
                        'last_row_index' => $rowIndex,
                    ]);
                }

            } catch (Throwable $e) {
                $failedCount++;
                logger('Row import failed', [
                    'upload_id' => $upload->id,
                    'row_index' => $rowIndex,
                    'error' => $e->getMessage(),
                ]);
                $this->writeImportLog('Row import exception', [
                    'upload_id' => $upload->id,
                    'row_index' => $rowIndex,
                    'error' => $e->getMessage(),
                ]);

                Product::create([
                    'upload_id' => $upload->id,
                    'status' => Product::STATUS_FAILED,
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        $upload->update([
            'status' => $processedCount > 0 ? Upload::STATUS_COMPLETED : Upload::STATUS_FAILED,
        ]);
        $this->writeImportLog('Job finished', [
            'upload_id' => $upload->id,
            'processed_count' => $processedCount,
            'failed_count' => $failedCount,
            'status' => $processedCount > 0 ? Upload::STATUS_COMPLETED : Upload::STATUS_FAILED,
        ]);
    }

    public function failed(Throwable $e): void
    {
        $this->writeImportLog('Job failed', [
            'upload_id' => $this->uploadId,
            'error' => $e->getMessage(),
        ]);
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
            'title' => $rowData['Title'] ?? $row[0] ?? null,
            'description' => $rowData['Body HTML'] ?? $rowData['Description'] ?? $row[1] ?? null,
            'price' => $rowData['Variant Price'] ?? $rowData['Price'] ?? $row[2] ?? null,
        ];
    }

    protected function readHeaders(SplFileObject $file): array
    {
        $headers = $file->fgetcsv();

        if (!is_array($headers)) {
            return [];
        }

        return array_map(fn ($header) => trim((string) $header), $headers);
    }

    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function writeImportLog(string $message, array $context = []): void
    {
        try {
            ImportLog::create([
                'upload_id' => $context['upload_id'] ?? $this->uploadId,
                'message' => $message,
                'context' => $context,
            ]);
        } catch (Throwable $e) {
            logger('Import log write failed', [
                'message' => $message,
                'context' => $context,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
