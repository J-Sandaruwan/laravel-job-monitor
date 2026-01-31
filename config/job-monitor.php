<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Table Name
    |--------------------------------------------------------------------------
    |
    | The name of the database table used to store job histories.
    |
    */
    'table_name' => env('JOB_MONITOR_TABLE', 'job_histories'),

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Tracking
    |--------------------------------------------------------------------------
    |
    | Master switch to enable or disable job tracking globally.
    |
    */
    'enabled' => env('JOB_MONITOR_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Track Specific Queues
    |--------------------------------------------------------------------------
    |
    | Specify which queues to track. Leave empty to track all queues.
    | Example: ['default', 'high-priority']
    |
    */
    'track_queues' => [],

    /*
    |--------------------------------------------------------------------------
    | Skip Specific Job Classes
    |--------------------------------------------------------------------------
    |
    | Jobs to exclude from tracking by their class names.
    | Example: ['App\Jobs\SomeInternalJob']
    |
    */
    'skip_jobs' => [],

    /*
    |--------------------------------------------------------------------------
    | Retention Policy
    |--------------------------------------------------------------------------
    |
    | Number of days to retain job history records. Set to null to keep forever.
    |
    */
    'retention_days' => env('JOB_MONITOR_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | API Route Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the built-in API routes.
    |
    */
    'route' => [
        'enabled' => env('JOB_MONITOR_ROUTES_ENABLED', true),
        'prefix' => env('JOB_MONITOR_ROUTE_PREFIX', 'api/job-monitor'),
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default number of items per page for API responses.
    |
    */
    'per_page' => env('JOB_MONITOR_PER_PAGE', 25),

];
