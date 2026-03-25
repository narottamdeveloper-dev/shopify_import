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

        return back()->with('success', 'File uploaded and processing started');
    }

    protected function dashboardPayload(): array
    {
        return [
            'stats' => [
                'uploads' => Upload::count(),
                'products' => Product::count(),
                'completed' => Upload::where('status', Upload::STATUS_COMPLETED)->count(),
                'failed' => Upload::where('status', Upload::STATUS_FAILED)->count(),
            ],
            'recent_uploads' => Upload::query()
                ->latest()
                ->take(6)
                ->get()
                ->map(fn (Upload $upload) => [
                    'id' => $upload->id,
                    'file_name' => $upload->file_name,
                    'status' => $upload->status,
                    'created_at' => optional($upload->created_at)->format('d M Y, h:i A'),
                ])
                ->values(),
            'generated_at' => now()->format('d M Y, h:i:s A'),
        ];
    }
}
