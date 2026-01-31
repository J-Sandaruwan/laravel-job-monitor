<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = config('job-monitor.table_name', 'job_histories');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->nullable()->index();
            $table->string('job_class');
            $table->string('queue');
            $table->string('status')->default('pending')->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->json('payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('job-monitor.table_name', 'job_histories');
        
        Schema::dropIfExists($tableName);
    }
};
