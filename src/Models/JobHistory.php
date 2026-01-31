<?php

namespace JSandaruwan\LaravelJobMonitor\Models;

use Illuminate\Database\Eloquent\Model;

class JobHistory extends Model
{
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return config('job-monitor.table_name', 'job_histories');
    }

    protected $fillable = [
        'job_id',
        'job_class',
        'queue',
        'status',
        'progress',
        'attempt',
        'payload',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'payload' => 'array',
        'attempt' => 'integer',
        'progress' => 'integer',
    ];
}
