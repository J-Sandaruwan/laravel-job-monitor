# Changelog

All notable changes to `laravel-job-monitor` will be documented in this file.

## 1.0.0 - 2026-01-31

### Added

- Initial release
- Automatic job tracking with queue event listeners
- `TracksJobProgress` trait for manual progress tracking
- `JobHistory` model with configurable table name
- Migration for `job_histories` table
- Service provider with auto-discovery
- Configuration file with retention, queue filtering, and route options
- REST API with endpoints:
    - GET `/api/job-monitor/jobs` - List jobs with filtering
    - GET `/api/job-monitor/jobs/{id}` - Show single job
    - GET `/api/job-monitor/jobs/stats` - Get statistics
    - POST `/api/job-monitor/jobs/{id}/retry` - Retry failed job
- Comprehensive documentation and examples
- MIT License
