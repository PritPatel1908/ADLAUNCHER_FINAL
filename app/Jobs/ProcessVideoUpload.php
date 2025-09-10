<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProcessVideoUpload implements ShouldQueue
{
    use Queueable;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 3;

    protected $fileId;
    protected $fileName;
    protected $fileSize;
    protected $fileType;
    protected $totalChunks;
    protected $chunkSize;

    /**
     * Create a new job instance.
     */
    public function __construct($fileId, $fileName, $fileSize, $fileType, $totalChunks, $chunkSize)
    {
        $this->fileId = $fileId;
        $this->fileName = $fileName;
        $this->fileSize = $fileSize;
        $this->fileType = $fileType;
        $this->totalChunks = $totalChunks;
        $this->chunkSize = $chunkSize;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting video upload processing', [
                'file_id' => $this->fileId,
                'file_name' => $this->fileName,
                'file_size' => $this->fileSize
            ]);

            // Check if all chunks are uploaded
            $sessionData = Cache::get("chunked_upload_{$this->fileId}");
            if (!$sessionData) {
                throw new \Exception('Upload session not found or expired');
            }

            // Verify all chunks are uploaded
            $expectedChunks = range(0, $this->totalChunks - 1);
            $uploadedChunks = $sessionData['uploaded_chunks'];

            if (count($uploadedChunks) !== $this->totalChunks) {
                throw new \Exception('Not all chunks uploaded. Missing: ' . implode(',', array_diff($expectedChunks, $uploadedChunks)));
            }

            // Combine chunks into final file
            $finalFileName = time() . '_' . $this->fileName;
            $finalPath = "schedule_media/{$finalFileName}";

            $finalFile = Storage::disk('public')->path($finalPath);
            $finalFileHandle = fopen($finalFile, 'wb');

            for ($i = 0; $i < $this->totalChunks; $i++) {
                $chunkPath = "chunks/{$this->fileId}/chunk_{$i}";
                $chunkContent = Storage::disk('local')->get($chunkPath);
                fwrite($finalFileHandle, $chunkContent);
            }

            fclose($finalFileHandle);

            // Clean up chunks
            Storage::disk('local')->deleteDirectory("chunks/{$this->fileId}");

            // Update session data with final file info
            $sessionData['final_file_path'] = $finalPath;
            $sessionData['final_file_name'] = $finalFileName;
            $sessionData['processing_completed'] = true;
            Cache::put("chunked_upload_{$this->fileId}", $sessionData, 7200);

            Log::info('Video upload processing completed', [
                'file_id' => $this->fileId,
                'final_path' => $finalPath
            ]);
        } catch (\Exception $e) {
            Log::error('Video upload processing failed', [
                'file_id' => $this->fileId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Clean up on error
            Storage::disk('local')->deleteDirectory("chunks/{$this->fileId}");
            Cache::forget("chunked_upload_{$this->fileId}");

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Video upload job failed permanently', [
            'file_id' => $this->fileId,
            'error' => $exception->getMessage()
        ]);

        // Clean up on permanent failure
        Storage::disk('local')->deleteDirectory("chunks/{$this->fileId}");
        Cache::forget("chunked_upload_{$this->fileId}");
    }
}
