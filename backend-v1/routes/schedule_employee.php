<?php

use App\Http\Controllers\ScheduleEmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::apiResource('schedule_employees', ScheduleEmployeeController::class);
Route::get('schedule_employees_logs', [ScheduleEmployeeController::class, 'logs']);
Route::post('schedule_employees_delete', [ScheduleEmployeeController::class, 'schedule_employees_delete']);
Route::get('employees_by_departments', [ScheduleEmployeeController::class, 'employees_by_departments']);
Route::put('scheduled_employee/{id}', [ScheduleEmployeeController::class, 'update']);

Route::get('scheduled_employees', [ScheduleEmployeeController::class, 'scheduled_employees']);
Route::get('not_scheduled_employees', [ScheduleEmployeeController::class, 'not_scheduled_employees']);
Route::post('schedule_employee/delete/selected', [ScheduleEmployeeController::class, 'deleteSelected']);

Route::post('schedule_employee/delete-all', [ScheduleEmployeeController::class, 'deleteAll']);


Route::post('/assignSchedule', [ScheduleEmployeeController::class, 'assignSchedule']);
Route::post('/assignScheduleByManual', [ScheduleEmployeeController::class, 'assignScheduleByManual']);


Route::get('scheduled_employees_index', [ScheduleEmployeeController::class, 'scheduled_employees_index']);

// Route::get('scheduled_employees_list', [EmployeeController::class, 'scheduled_employees_list']);
Route::get('scheduled_employees_with_type', [ScheduleEmployeeController::class, 'scheduled_employees_with_type']);
Route::get('scheduled_employees_with_type_new', [ScheduleEmployeeController::class, 'scheduled_employees_with_type_new']);
Route::get('/get_shifts_by_employee/{id}', [ScheduleEmployeeController::class, 'getShiftsByEmployee']);
Route::get('/employee_related_shift/{id}', [ScheduleEmployeeController::class, 'employee_related_shift']);

Route::get('/employees_with_schedule_count', [ScheduleEmployeeController::class, 'employeesWithScheduleCount']);
Route::get('/schedule_stats', [ScheduleEmployeeController::class, 'scheduleStats']);


// Route::get('scheduled_employees/search/{key}', [EmployeeController::class, 'scheduled_employees_search']);

Route::get('schedule-employees-full', function () {
    // We don't need $q here unless we are passing parameters
    return DB::table('schedule_employees')
        ->where('company_id', 60)
        ->get()->toArray(); // Laravel automatically converts Collection to JSON/Array for the response
});

Route::post('sync-schedule-employees', function (Request $request) {
    // 1. Get the data from the request body
    $data = $request->all();

    if (empty($data)) {
        return response()->json(['error' => 'No data provided'], 400);
    }

    // 2. Wrap in a transaction for safety
    return DB::transaction(function () use ($data) {
        $companyId = 60; // Hardcoded as per your requirement

        // 3. Delete existing records for this company
        DB::table('schedule_employees')->where('company_id', $companyId)->delete();

        // 4. Insert the new payload
        // Note: Using chunk if the data is massive, otherwise a direct insert works
        DB::table('schedule_employees')->insert($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Data overridden successfully',
            'count' => count($data)
        ]);
    });
});
