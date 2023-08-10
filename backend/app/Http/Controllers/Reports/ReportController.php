<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $model = (new Attendance)->processAttendanceModel($request);

        if ($request->main_shift_type == 1) {
            return $this->general($model, $request->per_page);
        }

        return $this->multiInOut($model->get(), $request->per_page);
    }

    public function general($model, $per_page = 100)
    {
        return $model->paginate($per_page);
    }

    public function multiInOut($model, $per_page = 100)
    {
        foreach ($model as $value) {
            $logs = $value->logs ?? [];
            $count = count($logs);
            $requiredLogs = max($count, 7); // Ensure at least 8 logs

            for ($a = 0; $a < $requiredLogs; $a++) {
                $log = $logs[$a] ?? [];
                $value["in" . ($a + 1)] = $log["in"] ?? "---";
                $value["out" . ($a + 1)] = $log["out"] ?? "---";
            }
        }

        // foreach ($model as $value) {
        //     $count = count($value->logs ?? []);
        //     if ($count > 0) {
        //         if ($count < 8) {
        //             $diff = 7 - $count;
        //             $count = $count + $diff;
        //         }
        //         for ($a = 0; $a < $count; $a++) {

        //             $holder = $a;
        //             $holder_key = ++$holder;

        //             $value["in" . $holder_key] = $value->logs[$a]["in"] ?? "---";
        //             $value["out" . $holder_key] = $value->logs[$a]["out"] ?? "---";
        //         }
        //     }
        // }

        return $this->paginate($model, $per_page);
    }

    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        $perPage == 0 ? 50 : $perPage;

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
