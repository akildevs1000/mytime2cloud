<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceLogMissingController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

Route::post('seed_default_data', [AttendanceController::class, "seedDefaultDataManual"]);
Route::get('attendance_avg_clock', [AttendanceController::class, "attendance_avg_clock"]);
Route::get('company_stats', [AttendanceController::class, "companyStats"]);
Route::get('company_stats_hourly_trends', [AttendanceController::class, "companyStatsHourlyTrends"]);
Route::get('company_stats_day_trends', [AttendanceController::class, "companyStatsDayTrends"]);
Route::get('company_stats_department_breakdown', [AttendanceController::class, "companyStatsDepartmentBreakdown"]);
Route::get('company_stats_punctuality', [AttendanceController::class, "companyStatsPunctuality"]);
Route::get('company_stats_daily_attendance', [AttendanceController::class, "companyStatsDailyAttendance"]);
Route::get('company_stats_summary_payload', [AttendanceController::class, "companyStatsSummaryPayload"]);
Route::get('company_stats_summary_pdf', [AttendanceController::class, "companyStatsSummaryPdf"]);
Route::get('get_attendance_tabs', [AttendanceController::class, "getAttendanceTabsDisplay"]);
Route::get('regenerate-attendance', [AttendanceController::class, "regenerateAttendance"]);

Route::get('attendance-logs-missing', [AttendanceLogMissingController::class, "GetMissingLogs"]);

Route::post('/generate-pdf-after-regeneration', function (Request $request) {

    // only temporary for testing, to be removed after testing is done
    return response()->json([
        'status' => 'success',
        'message' => 'PDF generated successfully',
        'details' => "This is a placeholder response. The actual PDF generation logic is executed in the background and will return the real result once completed."
    ]);

    // 1. Prepare your data (simulating your $items array)
    $id = $request->input('company_id');
    $template = $request->input('template');
    $employee_ids = $request->input('employee_ids', []);

    // 2. The "Callback" logic - what happens after execution
    $onComplete = function ($exitCode, $output) use ($id) {
        if ($exitCode === 0) {
            Log::info("PDF Command Success for Company $id");
            return response()->json([
                'status' => 'success',
                'message' => 'PDF generated successfully',
                'details' => $output
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'PDF generation failed',
            'exit_code' => $exitCode
        ], 500);
    };

    // 3. Execute the Artisan Command
    $exitCode = Artisan::call('pdf:generatev1', [
        'company_id'  => $id,
        'template'    => $template,
        '--employees' => $employee_ids,
        'from'        => Carbon::now()->startOfMonth()->toDateString(),
        'to'          => Carbon::now()->endOfMonth()->toDateString(),
    ]);

    // 4. Return the result of the callback
    return $onComplete($exitCode, Artisan::output());
});
