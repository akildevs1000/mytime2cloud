<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        DB::listen(function ($query) {
            // Only care about attendances table
            if (strpos($query->sql, 'from "attendances"') !== false) {

                // Get small backtrace to find who called this
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);

                $simplified = [];

                foreach ($trace as $frame) {
                    if (!isset($frame['file'])) {
                        continue;
                    }

                    // Only keep things from /app folder (your code)
                    if (strpos($frame['file'], base_path('app')) === false) {
                        continue;
                    }

                    $simplified[] = [
                        'file' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $frame['file']),
                        'line' => $frame['line'] ?? null,
                        'function' => $frame['function'] ?? null,
                        'class' => $frame['class'] ?? null,
                    ];
                }

                Log::info('ATTENDANCE QUERY TRACE', [
                    'sql'      => $query->sql,
                    'bindings' => $query->bindings,
                    'time_ms'  => $query->time,
                    'trace'    => $simplified,
                ]);
            }
        });
    }
}
