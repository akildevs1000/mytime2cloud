<?php

use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('theme', ThemeController::class);
Route::get('theme_count', [ThemeController::class, "theme_count"]);
Route::get('dashbaord_attendance_count', [ThemeController::class, "dashboardCount"]);
Route::get('dashboard_counts_last_7_days', [ThemeController::class, "dashboardGetCountslast7Days"]);
Route::get('dashboard_get_count_department', [ThemeController::class, "dashboardGetCountDepartment"]);
Route::get('dashboard_get_count_previous_month', [ThemeController::class, "dashboardGetCountPreviousMonth"]);
Route::get('dashboard_Get_Counts_today_multi_general', [ThemeController::class, "dashboardGetCountsTodayMultiGeneral"]);
Route::get('dashboard_get_counts_today_hour_in_out', [ThemeController::class, "dashboardGetCountsTodayHourInOut"]);
