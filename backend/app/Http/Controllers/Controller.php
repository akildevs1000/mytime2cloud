<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Controllers\Reports\ReportController;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function FilterCompanyList($model, $request, $model_name = null)
    {
        $model = $model::query();

        if (is_null($model_name)) {
            $model->when($request->company_id > 0, function ($q) use ($request) {
                return $q->where('company_id', $request->company_id);
            });

            $model->when(!$request->company_id, function ($q) use ($request) {
                return $q->where('company_id', 0);
            });
        }

        return $model;
    }

    public static function process($action, $job, $model, $id = null)
    {
        try {
            $m = '\\App\\Models\\' . $model;
            $last_id = gettype($job) == 'object' ? $job->id : $id;

            $response = [
                'status' => true,
                'record' => $m::find($last_id),
                'message' => $model . ' has been ' . $action,
            ];

            if ($last_id) {
                return response()->json($response, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'record' => null,
                    'message' => $model . ' cannot ' . $action,
                ], 200);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function response($msg, $record, $status)
    {
        return response()->json(['record' => $record, 'message' => $msg, 'status' => $status], 200);
    }

    public function process_search($model, $input, $fields = [])
    {
        $model->where('id', 'LIKE', "%$input%");

        foreach ($fields as $key => $value) {
            if (is_string($value)) {
                $model->orWhere($value, 'LIKE', "%$input%");
            } else {
                foreach ($value as $relation_value) {
                    $model->orWhereHas($key, function ($query) use ($input, $relation_value) {
                        $query->where($relation_value, 'like', '%' . $input . '%');
                    });
                }
            }
        }
        return $model;
    }

    public function getStatusText($status)
    {
        $report_type = "Summary";

        if ($status == 'P') {
            $report_type = "Present";
        } else if ($status == 'A') {
            $report_type = "Absent";
        } else if ($status == '---') {
            $report_type = "Missing";
        } else if ($status == 'ME') {
            $report_type = "Manual Entry";
        }

        return $report_type;
    }

    public function processPDF($request)
    {
        $company = Company::whereId($request->company_id)->first(["logo", "name", "company_code", "location"]);
        $model = new ReportController;
        $info = (object) [
            'total_absent' => $model->report($request)->where('status', 'A')->count(),
            'total_present' => $model->report($request)->where('status', 'P')->count(),
            'total_missing' => $model->report($request)->where('status', '---')->count(),
            'department' => $request->department_id == -1 ? 'All' :  Department::find($request->department_id)->name,
            "daily_date" => $request->daily_date,
            "report_type" => $this->getStatusText($request->status)
        ];
        $data = $model->report($request)->get();

        return Pdf::loadView('pdf.daily', compact("company", "info", "data"));
    }

    public function daily(Request $request)
    {
        return $this->processPDF($request)->stream();
    }
    public function daily_download_pdf(Request $request)
    {
        return $this->processPDF($request)->download();
    }

    public function daily_download_csv(Request $request)
    {
        $model = new ReportController;
        
        $data = $model->report($request)->get();

        $fileName = 'report.csv';

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            $i = 0;

            fputcsv($file,["#","Date","E.ID","Name","Dept","Shift Type","Shift","Status","In","Out","Total Hrs","OT","Late coming","Early Going","D.In","D.Out"]);
            foreach ($data as $col) {
                fputcsv($file, [
                    ++$i,
                    $col['date'],
                    $col['employee_id'] ?? "---",
                    $col['employee']["first_name"] ?? "---",
                    $col['employee']["department"]["name"] ?? "---",
                    $col['schedule']["shift_type"]["name"] ?? "---",
                    $col['schedule']["shift"]["name"] ?? "---",
                    $col["status"] ?? "---",
                    $col["in"] ?? "---",
                    $col["out"] ?? "---",
                    $col["total_hrs"] ?? "---",
                    $col["ot"] ?? "---",
                    $col["late_coming"] ?? "---",
                    $col["early_going"] ?? "---",
                    $col["device_in"]["short_name"] ?? "---",
                    $col["device_out"]["short_name"] ?? "---"
            ], ",");
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    //weekly report
    public function weekly_summary(Request $request)
    {
        // $type = 'weekly';
        // $end = $request->daily_date ?? date('Y-m-d');
        // $start = date("Y-m-d", strtotime($end . "-7 days"));

        // $model = Attendance::query();

        // $model = $model->whereBetween('date', [$start, $end]);


        // $model = $model->where("employee_id", "<", 5);

        // // return  $data = $model->with('employeeAttendance')->get();
        // $data = $model->get();
        // $data = $data->groupBy(['employee_id', 'date']);
        // $arr = [];

        // foreach ($data as $employee_id => $row) {
        //     $emp = $this->getEmployee($row);

        //     $arr[] = [
        //         'Name' => $emp->first_name ?? '',
        //         'E.ID' => $emp->employee_id ?? '',
        //         'Dept' => $emp->department->name ?? '',
        //         'Date' => $start . ' to ' . $end,
        //         'Total Hrs' => $this->totalHours($row),
        //         'OT' => $this->TotalOtHours($row),
        //         'Present' => 14,
        //         'Absent' => 17,
        //         'Late In' => 2,
        //         'Early Out' => 5,
        //         'record' => $row,
        //     ];
        // }
        // $footer = [
        //     'Device' => "Main Entrance = MED, Back Entrance = BED",
        //     'Shift Type' => "Manual = MA, Auto = AU, NO = NO",
        //     'Shift' => "Morning = Mor, Evening = Eve, Evening2 = Eve2",
        // ];
        // $pdf = App::make('dompdf.wrapper');
        // $arr;


        return Pdf::loadView('pdf.weekly.weekly_summary')->stream();

        // $pdf->loadHTML($this->getHTML($arr, $request, $type));
        // return $pdf->stream();
        // return Pdf::loadView('pdf.monthly_details', compact("arr", "footer"))->stream();
    }

    public function daily_html(Request $request)
    {
        return Pdf::loadView('pdf.html.daily.daily_summary')->stream();
    }
    public function weekly_html(Request $request)
    {
        return Pdf::loadView('pdf.html.weekly.weekly_summary')->stream();
    }
}
