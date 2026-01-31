<?php

namespace JSandaruwan\LaravelJobMonitor\Traits;

use JSandaruwan\LaravelJobMonitor\Models\JobHistory;
use Illuminate\Support\Facades\Log;

trait TracksJobProgress
{
    /**
     * Update the progress of the current job.
     *
     * @param int $percentage Progress percentage (0-100)
     * @return void
     */
    public function updateProgress(int $percentage): void
    {
        try {
            // Ensure percentage is within bounds
            $percentage = max(0, min(100, $percentage));

            // Get the job UUID from the payload
            $jobUuid = $this->job?->uuid() ?? null;

            if (!$jobUuid) {
                return;
            }

            // Update progress in job_histories table
            JobHistory::where('job_id', $jobUuid)
                ->update(['progress' => $percentage]);

        } catch (\Throwable $e) {
            // Don't fail the job if progress tracking fails
            Log::warning('Failed to update job progress', [
                'job_class' => static::class,
                'progress' => $percentage,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Alias for compatibility with existing queueProgress() calls.
     *
     * @param int $percentage
     * @return void
     */
    public function queueProgress(int $percentage): void
    {
        $this->updateProgress($percentage);
    }
}
