# Laravel Job Monitor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/j-sandaruwan/laravel-job-monitor.svg?style=flat-square)](https://packagist.org/packages/j-sandaruwan/laravel-job-monitor)
[![Total Downloads](https://img.shields.io/packagist/dt/j-sandaruwan/laravel-job-monitor.svg?style=flat-square)](https://packagist.org/packages/j-sandaruwan/laravel-job-monitor)

A lightweight Laravel package for tracking queue jobs with status, progress, execution history, and detailed error logging.

## Features

✅ **Automatic Job Tracking** - Zero configuration needed, works out of the box  
✅ **Real-Time Progress** - Track job progress (0-100%) with simple trait  
✅ **Detailed History** - Status, attempts, errors, start/finish times  
✅ **REST API** - Pre-built endpoints for job listing, stats, and retry  
✅ **Retry Failed Jobs** - One-click retry functionality  
✅ **Configurable** - Flexible options for tracking, retention, and routes  
✅ **Zero Dependencies** - No external monitoring services required

---

## Installation

Install the package via Composer:

```bash
composer require j-sandaruwan/laravel-job-monitor
```

### Publish Configuration & Migrations

```bash
php artisan vendor:publish --provider="JSandaruwan\LaravelJobMonitor\JobMonitorServiceProvider"
```

This publishes:

- `config/job-monitor.php` - Configuration file
- Migration: `create_job_histories_table.php`

### Run Migrations

```bash
php artisan migrate
```

---

## Usage

### Basic Tracking (Automatic)

All queue jobs are **automatically tracked** with no code changes needed! The package listens to Laravel's queue events and tracks:

- Job start/finish times
- Status (pending → processing → completed/failed)
- Attempt count
- Errors (if failed)
- Progress (0% → 100%)

### Progress Tracking (Manual)

To track job progress, add the `TracksJobProgress` trait to your job:

```php
use JSandaruwan\LaravelJobMonitor\Traits\TracksJobProgress;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessVideoJob implements ShouldQueue
{
    use TracksJobProgress;

    public function handle()
    {
        $this->updateProgress(10);  // 10% complete

        // Download video
        $this->updateProgress(30);

        // Process video
        $this->updateProgress(70);

        // Upload result
        $this->updateProgress(90);

        // Job completes → auto-set to 100%
    }
}
```

**Alternative Method:** Use `queueProgress()` for backward compatibility

```php
$this->queueProgress(50);  // Same as updateProgress(50)
```

---

## API Endpoints

The package provides REST API endpoints for accessing job data:

### List All Jobs

```http
GET /api/job-monitor/jobs?status=failed&per_page=50
```

**Query Parameters:**

- `search` - Search by job ID, class name, or error message
- `status` - Filter by status (pending, processing, completed, failed)
- `queue` - Filter by queue name
- `job_type` - Filter by job class name
- `date_from` - Filter jobs after this date
- `date_to` - Filter jobs before this date
- `per_page` - Items per page (default: 25)

### Get Single Job

```http
GET /api/job-monitor/jobs/{id}
```

### Get Statistics

```http
GET /api/job-monitor/jobs/stats
```

**Response:**

```json
{
    "total_jobs": 1534,
    "pending_jobs": 12,
    "completed_jobs": 1480,
    "failed_jobs": 42,
    "total_runtime": 45230,
    "average_runtime": 29.5
}
```

### Retry Failed Job

```http
POST /api/job-monitor/jobs/{id}/retry
```

---

## Configuration

Edit `config/job-monitor.php`:

```php
return [
    // Database table name
    'table_name' => 'job_histories',

    // Enable/disable tracking
    'enabled' => true,

    // Track specific queues only (empty = all queues)
    'track_queues' => [],  // e.g., ['default', 'high-priority']

    // Skip specific job classes
    'skip_jobs' => [],  // e.g., ['App\Jobs\InternalJob']

    // Retention policy (days)
    'retention_days' => 30,  // null = keep forever

    // API routes configuration
    'route' => [
        'enabled' => true,
        'prefix' => 'api/job-monitor',
        'middleware' => ['api'],
    ],

    // Pagination
    'per_page' => 25,
];
```

### Environment Variables

```env
JOB_MONITOR_ENABLED=true
JOB_MONITOR_TABLE=job_histories
JOB_MONITOR_RETENTION_DAYS=30
JOB_MONITOR_ROUTES_ENABLED=true
JOB_MONITOR_ROUTE_PREFIX=api/job-monitor
JOB_MONITOR_PER_PAGE=25
```

---

## Frontend Integration

Build your own UI using the provided API endpoints. Example with Vue.js:

```vue
<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const jobs = ref([]);

const fetchJobs = async () => {
    const { data } = await axios.get("/api/job-monitor/jobs");
    jobs.value = data.data;
};

const retryJob = async (jobId) => {
    await axios.post(`/api/job-monitor/jobs/${jobId}/retry`);
    fetchJobs(); // Refresh list
};

onMounted(fetchJobs);
</script>

<template>
    <div v-for="job in jobs" :key="job.id">
        <h3>{{ job.job_class }}</h3>
        <p>Status: {{ job.status }}</p>
        <div v-if="job.status === 'processing'">
            Progress: {{ job.progress }}%
        </div>
        <button v-if="job.status === 'failed'" @click="retryJob(job.id)">
            Retry
        </button>
    </div>
</template>
```

---

## Data Retention

Clean up old job records automatically:

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $retentionDays = config('job-monitor.retention_days', 30);

    if ($retentionDays) {
        $schedule->command('db:table', [
            'table' => config('job-monitor.table_name'),
            '--where' => "created_at < DATE_SUB(NOW(), INTERVAL {$retentionDays} DAY)"
        ])->daily();
    }
}
```

---

## Testing

```bash
composer test
```

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

---

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## Security

If you discover any security-related issues, please email janithsandaruwan29@gmail.com instead of using the issue tracker.

---

## Credits

- [J Sandaruwan](https://github.com/j-sandaruwan)
- [All Contributors](../../contributors)

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
