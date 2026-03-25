<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Models\ShopifyStore;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use SplFileObject;
use Throwable;

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
        $upload = Upload::find($this->uploadId);

        if (!$upload) {
            return;
        }

        $store = $this->resolveStore($upload);

        $upload->update([
            'status' => Upload::STATUS_PROCESSING,
            'total_rows' => 0,
            'processed_rows' => 0,
            'successful_rows' => 0,
            'skipped_rows' => 0,
            'failed_rows' => 0,
            'shopify_store_id' => $store?->id,
        ]);

        $disk = Storage::disk(config('filesystems.default'));

        if (!$disk->exists($upload->file_path)) {
            $upload->update(['status' => Upload::STATUS_FAILED]);
            $this->writeImportLog($upload->id, 'File missing', [
                'path' => $upload->file_path,
            ]);
            return;
        }

        $filePath = $disk->path($upload->file_path);
        $file = new SplFileObject($filePath);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $headers = $file->fgetcsv();

        if ($headers === []) {
            $upload->update(['status' => Upload::STATUS_FAILED]);
            $this->writeImportLog($upload->id, 'CSV headers missing', []);
            return;
        }

        $headers = array_map(fn ($header) => trim((string) $header), $headers);

        $chunk = [];
        $chunks = [];
        $chunkSize = 100;
        $totalRows = 0;

        while (!$file->eof()) {
            $row = $file->fgetcsv();

            if (!is_array($row) || $this->isEmptyRow($row)) {
                continue;
            }

            $chunk[] = $row;
            $totalRows++;

            if (count($chunk) === $chunkSize) {
                $chunks[] = $chunk;
                $chunk = [];
            }
        }

        if ($chunk !== []) {
            $chunks[] = $chunk;
        }

        if ($totalRows === 0) {
            $upload->update(['status' => Upload::STATUS_FAILED]);
            $this->writeImportLog($upload->id, 'CSV has no data rows', []);
            return;
        }

        $upload->update([
            'total_rows' => $totalRows,
            'status' => Upload::STATUS_PROCESSING,
        ]);

        foreach ($chunks as $rows) {
            ProcessCsvChunkJob::dispatch($upload->id, $store?->id, $headers, $rows);
        }

        $this->writeImportLog($upload->id, 'Import queued', [
            'total_rows' => $totalRows,
            'chunk_count' => count($chunks),
            'chunk_size' => $chunkSize,
        ]);
    }

    public function failed(Throwable $e): void
    {
        $this->writeImportLog($this->uploadId, 'Import dispatcher failed', [
            'error' => $e->getMessage(),
        ]);
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

    protected function resolveStore(Upload $upload): ?ShopifyStore
    {
        if ($upload->shopify_store_id) {
            return ShopifyStore::find($upload->shopify_store_id);
        }

        return ShopifyStore::where('is_default', true)->where('is_active', true)->first();
    }

    protected function writeImportLog(int $uploadId, string $message, array $context = []): void
    {
        try {
            \App\Models\ImportLog::create([
                'upload_id' => $uploadId,
                'message' => $message,
                'context' => $context,
            ]);
        } catch (Throwable) {
        }
    }
}
