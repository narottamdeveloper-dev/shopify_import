<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;
use App\Jobs\ProcessCsvJob;
use App\Models\Product;

class UploadController extends Controller
{
    public function index()
    {
        return response()->view('upload')->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
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
        ]);

        $file = $request->file('file');

        $path = $file->store('uploads');

        $upload = Upload::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'status' => Upload::STATUS_PENDING,
        ]);

        ProcessCsvJob::dispatch($upload->id);

        return back()
            ->with('success', 'File uploaded and queued for background processing.')
            ->with('tracked_upload_id', $upload->id);
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

        return [
            'stats' => [
                'uploads' => Upload::count(),
                'products' => Product::count(),
                'completed' => Upload::where('status', Upload::STATUS_COMPLETED)->count(),
                'failed' => Upload::where('status', Upload::STATUS_FAILED)->count(),
                'skipped' => Product::where('status', Product::STATUS_SKIPPED)->count(),
            ],
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
}
