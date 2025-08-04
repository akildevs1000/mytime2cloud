<?php
namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class SlowQueryLoggingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        DB::listen(function ($query) {
            if ($query->time > 2000) { // 2000 ms = 2 seconds
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

                $caller = collect($backtrace)->first(function ($trace) {
                    return isset($trace['file'])
                    && str_starts_with($trace['file'], app_path())
                    && str_ends_with($trace['file'], '.php');
                });

                Log::channel('slowqueries')->warning('⏱️ Slow Query Detected', [
                    'sql'         => $query->sql,
                    'bindings'    => $query->bindings,
                    'time_ms'     => $query->time,
                    'caller_file' => $caller['file'] ?? 'N/A',
                    'caller_line' => $caller['line'] ?? 'N/A',
                ]);
            }
        });

    }
}
