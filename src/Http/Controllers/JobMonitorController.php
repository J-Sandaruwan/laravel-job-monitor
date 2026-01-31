<?php

namespace JSandaruwan\LaravelJobMonitor\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JSandaruwan\LaravelJobMonitor\Models\JobHistory;

class JobMonitorController extends Controller
{
    /**
     * List all job histories with filtering and pagination.
     */
    public function index(Request $request)
    {
        $query = JobHistory::query()
            ->orderBy('id', 'desc');

        // Search by Job ID, Class Name, or Error Message
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('job_id', 'like', "%{$search}%")
                  ->orWhere('job_class', 'like', "%{$search}%")
                  ->orWhere('error_message', 'like', "%{$search}%");
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by Queue
        if ($request->filled('queue')) {
            $query->where('queue', $request->input('queue'));
        }

        // Filter by Job Class/Type
        if ($request->filled('job_type')) {
            $jobType = $request->input('job_type');
            $query->where('job_class', 'like', "%{$jobType}%");
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        $perPage = $request->input('per_page', config('job-monitor.per_page', 25));
        
        return response()->json($query->paginate($perPage));
    }

    /**
     * Get a specific job history record.
     */
    public function show($id)
    {
        $job = JobHistory::findOrFail($id);
        
        return response()->json($job);
    }

    /**
     * Get job statistics.
     */
    public function stats()
    {
        $totalJobs = JobHistory::count();
        $pendingJobs = JobHistory::where('status', 'pending')->count();
        $completedJobs = JobHistory::where('status', 'completed')->count();
        $failedJobs = JobHistory::where('status', 'failed')->count();

        // Calculate runtime statistics
        $runtimeStats = JobHistory::whereNotNull('started_at')
            ->whereNotNull('finished_at')
            ->selectRaw('
                SUM(TIMESTAMPDIFF(SECOND, started_at, finished_at)) as total_runtime,
                AVG(TIMESTAMPDIFF(SECOND, started_at, finished_at)) as average_runtime
            ')
            ->first();

        return response()->json([
            'total_jobs' => $totalJobs,
            'pending_jobs' => $pendingJobs,
            'completed_jobs' => $completedJobs,
            'failed_jobs' => $failedJobs,
            'total_runtime' => $runtimeStats->total_runtime ?? 0,
            'average_runtime' => round($runtimeStats->average_runtime ?? 0, 2),
        ]);
    }

    /**
     * Retry a failed job.
     */
    public function retry($id)
    {
        $jobHistory = JobHistory::findOrFail($id);

        if ($jobHistory->status !== 'failed') {
            return response()->json([
                'success' => false,
                'message' => 'Only failed jobs can be retried'
            ], 400);
        }

        if (!$jobHistory->payload) {
            return response()->json([
                'success' => false,
                'message' => 'Job payload not available for retry'
            ], 400);
        }

        try {
            // Re-dispatch the job with original payload
            $jobClass = $jobHistory->job_class;
            
            if (!class_exists($jobClass)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job class not found: ' . $jobClass
                ], 404);
            }

            // Unserialize and dispatch
            $payload = $jobHistory->payload;
            $job = unserialize($payload['data']['command']);
            
            dispatch($job);

            return response()->json([
                'success' => true,
                'message' => 'Job successfully re-queued'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retry job: ' . $e->getMessage()
            ], 500);
        }
    }
}
