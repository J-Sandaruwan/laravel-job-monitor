<?php

namespace JSandaruwan\LaravelJobMonitor;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use JSandaruwan\LaravelJobMonitor\Models\JobHistory;

class JobMonitorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/job-monitor.php', 'job-monitor'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/job-monitor.php' => config_path('job-monitor.php'),
            ], 'job-monitor-config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations/create_job_histories_table.php' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_job_histories_table.php'),
            ], 'job-monitor-migrations');
        }

        // Register routes if enabled
        if (config('job-monitor.route.enabled', true)) {
            $this->registerRoutes();
        }

        // Register queue event listeners for auto-tracking
        if (config('job-monitor.enabled', true)) {
            $this->registerQueueListeners();
        }
    }

    /**
     * Register API routes.
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => config('job-monitor.route.prefix', 'api/job-monitor'),
            'middleware' => config('job-monitor.route.middleware', ['api']),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }

    /**
     * Register queue event listeners for automatic job tracking.
     */
    protected function registerQueueListeners(): void
    {
        // Before job processing
        Queue::before(function (\Illuminate\Queue\Events\JobProcessing $event) {
            try {
                $job = $event->job;
                $payload = $job->payload();

                // Check if this job should be tracked
                if (!$this->shouldTrackJob($payload, $job->getQueue())) {
                    return;
                }

                JobHistory::create([
                    'job_id' => $job->getJobId(),
                    'job_class' => $payload['displayName'],
                    'queue' => $job->getQueue(),
                    'status' => 'processing',
                    'progress' => 0,
                    'attempt' => $payload['attempts'] ?? 1,
                    'payload' => $payload,
                    'started_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // Fail silently to avoid breaking the queue
                \Illuminate\Support\Facades\Log::error('JobMonitor: Failed to create job history', [
                    'error' => $e->getMessage()
                ]);
            }
        });

        // After job completed successfully
        Queue::after(function (\Illuminate\Queue\Events\JobProcessed $event) {
            try {
                JobHistory::where('job_id', $event->job->getJobId())
                    ->update([
                        'status' => 'completed',
                        'progress' => 100,
                        'finished_at' => now(),
                    ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('JobMonitor: Failed to update job history (success)', [
                    'error' => $e->getMessage()
                ]);
            }
        });

        // After job failed
        Queue::failing(function (\Illuminate\Queue\Events\JobFailed $event) {
            try {
                JobHistory::where('job_id', $event->job->getJobId())
                    ->update([
                        'status' => 'failed',
                        'error_message' => $event->exception->getMessage(),
                        'finished_at' => now(),
                    ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('JobMonitor: Failed to update job history (failed)', [
                    'error' => $e->getMessage()
                ]);
            }
        });
    }

    /**
     * Determine if a job should be tracked.
     */
    protected function shouldTrackJob(array $payload, string $queue): bool
    {
        $jobClass = $payload['displayName'] ?? '';

        // Check if job class is in skip list
        $skipJobs = config('job-monitor.skip_jobs', []);
        if (in_array($jobClass, $skipJobs)) {
            return false;
        }

        // Check if queue is in track list (empty means track all)
        $trackQueues = config('job-monitor.track_queues', []);
        if (!empty($trackQueues) && !in_array($queue, $trackQueues)) {
            return false;
        }

        return true;
    }
}
