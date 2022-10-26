<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class MonthlyController extends Controller
{
    
    public function monthly_details(Request $request)
    {
        $start = $request->start ?? date('Y-09-1');
        $end = $request->end ?? date('Y-09-30');
        $type = 'monthly';
        $model = Attendance::query();

        // $mo del->whereRaw('extract(month from date) = ?', date("m"));
        // $model->whereMonth("date", date("9"));

        // $model = $model->whereMonth("date", date("m"));

        $model = $model->whereBetween('date', [$start, $end]);

        // $model = $model->where("employee_id", "<", 5);
        $data = $model->with('employeeAttendance')->get();
        $data = $data->groupBy(['employee_id', 'date']);
        $arr = [];

        foreach ($data as $employee_id => $row) {
            $emp = $this->getEmployee($row);

            $arr[] = [
                'Name' => $emp->first_name ?? '',
                'E.ID' => $emp->employee_id ?? '',
                'Dept' => $emp->department->name ?? '',
                'Date' => $start . ' to ' . $end,
                'Total Hrs' => $this->totalHours($row),
                'OT' => $this->TotalOtHours($row),
                'Present' => 14,
                'Absent' => 17,
                'Late In' => 2,
                'Early Out' => 5,
                'record' => $row,
            ];
        }
        $footer = [
            'Device' => "Main Entrance = MED, Back Entrance = BED",
            'Shift Type' => "Manual = MA, Auto = AU, NO = NO",
            'Shift' => "Morning = Mor, Evening = Eve, Evening2 = Eve2",
        ];
        $pdf = App::make('dompdf.wrapper');
        $arr;
        // $this->getHTML($arr);
        // $pdfJobs = new PDFJob($this->getHTML($arr));
        // $this->dispatch($pdfJobs);
        $pdf->loadHTML($this->getHTML($arr, $request, $type));
        return $pdf->stream();
    }
    public function monthly_summary()
    {
        $data = Attendance::whereMonth("date", date("m"))->get()->toArray();
        return Pdf::loadView('pdf.monthly_summary', ["data" => $data])->stream();
    }

    public function monthly_present()
    {
        $data = Attendance::whereMonth("date", date("m"))->get()->toArray();
        return Pdf::loadView('pdf.monthly_present', ["data" => $data])->stream();
    }

    public function monthly_absent()
    {
        $data = Attendance::whereMonth("date", date("m"))->get()->toArray();
        return Pdf::loadView('pdf.monthly_absent', ["data" => $data])->stream();
    }

    public function monthly_late_in()
    {
        $data = Attendance::whereMonth("date", date("m"))->get()->toArray();
        return Pdf::loadView('pdf.monthly_late_in', ["data" => $data])->stream();
    }

    public function monthly_early_out()
    {
        $data = Attendance::whereMonth("date", date("m"))->get()->toArray();
        return Pdf::loadView('pdf.monthly_early_out', ["data" => $data])->stream();
    }

    public function monthly_performance()
    {
        $data = Attendance::whereMonth("date", date("m"))->get()->toArray();
        return Pdf::loadView('pdf.monthly_performance', ["data" => $data])->stream();
    }

    public function getHTML($arr, $request, $type)
    {
        // if ($type = 'monthly'){
        // }

        $companyName = $request->company_name ?? "Sample Company Name";
        $companyAddress = $request->company_address ?? "Street Address,City, State, Zip Code";
        $companyLogo = $request->company_logo ?? "https://backend.ideahrms.com/upload/1664788253.jpeg";
        return '
        <!DOCTYPE html>
            <html>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <head>
            <style>
                table { font-family: arial, sans-serif; border-collapse: collapse; border: none; width: 100%; }
                td, th { border: 1px solid #eeeeee; text-align: left; }
                tr:nth-child(even) { background-color: #eeeeee; }
                th { font-size: 9px; }
                td { font-size: 7px; }
                footer { width: 100%; position: fixed; bottom: 0; }
                .page-break { page-break-after: always; }
            </style>
            </head>
            <body>
                <table style="margin-top: -20px !important;">
                    <tr style="background-color: #5fafa3;">
                        <td style="text-align: left; border :none; padding:15px;">
                            <div>
                                <h3 style="color: #ffffff">' . "$companyName" . '</h3>
                                <h4 style="color: #ffffff">' . $companyAddress . '</h4>
                            </div>

                        </td>
                        <td style="text-align: right; border :none;">
                            <div>
                                <img width="150" src="' . $companyLogo . '" height="70px" width="70">
                            </div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr><td colspan="4" style="text-align: center; border :none;"><div><h2>Monthly Timesheet</h2></div></td></tr>
                    <tr>
                            <td style="text-align: left;"><b>Device</b>: Main Entrance = MED, Back Entrance = BED</td>
                            <td style="text-align: left;"><b>Shift Type</b>: Manual = MA, Auto = AU, NO = NO</td>
                            <td style="text-align: left;"><b>Shift</b>: Morning = Mor, Evening = Eve, Evening2 = Eve2</td>
                            <td style="text-align: right;">
                                Date : ' . date("d/M/Y H:i:s") . '
                            </td>
                        </tr>
                </table>' . $this->renderTable($arr) .
            '
            </body>
            </html>';
    }

    public function renderTable($arr)
    {
        $str_arr = [];
        foreach ($arr as $key => $row) {
            $records = $this->getData($row['record']);
            $str_arr[] = '<div class="page-breaks"><table  style="margin-top: 5px !important;">' .
                '<tr style="text-align: left; border :1px solid black; width:120px;">' .
                '<td style="text-align:left;"><b>Name</b>:' . $row["Name"] . '</td>' .
                '<td style="text-align:left;"><b>EID</b>:' . $row["E.ID"] . '</td>' .
                '<td style="text-align:left;"><b>Dept</b>: ' . $row["Dept"] . '</td>' .
                '<td style="text-align:left; width:120px;"><b>Date: </b> ' . $row["Date"] . '</td>' .
                // '<td style="text-align:left; width:120px;"><b>Date: </b> 1 Sep 22 to 30 Sep 22</td>' .
                '<td style="text-align:left;"><b>Total Hrs</b>:' . $row["Total Hrs"] . '</td>' .
                '<td style="text-align:left;"><b>OT</b>:' . $row["OT"] . '</td>' .
                '<td style="text-align:left;"><b>Present</b>:' . $row["Present"] . '</td>' .
                '<td style="text-align:left;"><b>Absent</b>:' . $row["Absent"] . '</td>' .
                '<td style="text-align:left;"><b>Late In</b>:' . $row["Late In"] . '</td>' .
                '<td style="text-align:left;"><b>Early Out</b>:' . $row["Early Out"] . '</td>' .
                '</tr>' .
                '</table>' .
                '<table style="margin-top: 5px !important;">
                    <tr style="background-color:#A6A6A6;"><td><b>Dates</b></td>' . $records[0] . '</tr>
                    <tr style="background-color:none;"><td><b>Days</b></td>'
                . $records[1] .
                '</tr>
                    <tr><td><b>In</b></td>' . $records[2] . '</tr>
                    <tr><td><b>Out</b></td>' . $records[3] . '</tr>
                    <tr><td><b>Work</b></td>' . $records[4] . '</tr>
                    <tr><td><b>OT</b></td>' . $records[5] . '</tr>
                    <tr><td><b>Late Coming</b></td>' . $records[6] . '</tr>
                    <tr><td><b>Early Going</b></td>' . $records[7] . '</tr>
                    <tr><td><b>Shift</b></td>' . $records[8] . '</tr>
                    <tr><td><b>Shift Type</b> </td>' . $records[9] . '</tr>
                    <tr><td><b>Device In</b></td>' . $records[10] . '</tr>
                    <tr><td><b>Device Out</b></td>' . $records[11] . '</tr>
                    <tr><td><b>Status</b></td>' . $records[12] . '</tr>
                </table></div>';
        }
        return join("", $str_arr);
    }

    public function getData($records)
    {
        $str_arr = [];

        foreach ($records as $key => $record) {
            $status = $record[0]['status'] == 'A' ? 'color:red' : 'color:green';

            $str_arr["dates"][] = '<td style="text-align: center;"> ' . substr($key, 0, 2) . '</td>';
            $str_arr["days"][] = '<td style="text-align: center;"> ' . $record[0]['day'] . '</td>';
            $str_arr["in"][] = '<td style="text-align: center;"> ' . $record[0]['in'] . '</td>';
            $str_arr["out"][] = '<td style="text-align: center;"> ' . $record[0]['out'] . '</td>';
            $str_arr["total_hrs"][] = '<td style="text-align: center;"> ' . $record[0]['total_hrs'] . '</td>';
            $str_arr["ot"][] = '<td style="text-align: center;"> ' . $record[0]['ot'] . '</td>';
            $str_arr["late_coming"][] = '<td style="text-align: center;"> ' . $record[0]['late_coming'] . '</td>';
            $str_arr["early_going"][] = '<td style="text-align: center;"> ' . $record[0]['early_going'] . '</td>';
            $str_arr["shift_id"][] = '<td style="text-align: center;"> ' . $record[0]['shift_id'] . '</td>';
            $str_arr["shift_type_id"][] = '<td style="text-align: center;"> ' . $record[0]['shift_type_id'] . '</td>';
            $str_arr["device_id_in"][] = '<td style="text-align: center;"> ' . $record[0]['device_id_in'] . '</td>';
            $str_arr["device_id_out"][] = '<td style="text-align: center;"> ' . $record[0]['device_id_out'] . '</td>';
            $str_arr["status"][] = '<td style="text-align: center;' . $status . '"> ' . $record[0]['status'] . '</td>';
        }

        return [
            join("", $str_arr["dates"]),
            join("", $str_arr["days"]),
            join("", $str_arr["in"]),
            join("", $str_arr["out"]),
            join("", $str_arr["total_hrs"]),
            join("", $str_arr["ot"]),
            join("", $str_arr["late_coming"]),
            join("", $str_arr["early_going"]),
            join("", $str_arr["shift_id"]),
            join("", $str_arr["shift_type_id"]),
            join("", $str_arr["device_id_in"]),
            join("", $str_arr["device_id_out"]),
            join("", $str_arr["status"]),
        ];
    }

    public function getEmployee($arr)
    {
        foreach ($arr as $a) {
            $data = $a[0]->employeeAttendance;
        }
        return $data;
    }

    public function totalHours($arr)
    {
        $times = [];
        foreach ($arr as $a) {
            $times[] = $a[0]->total_hrs;
        }
        $minutes = 0;
        foreach ($times as $time) {
            if ($time != '---') {
                list($hour, $minute) = explode(':', $time);
                $minutes += $hour * 60;
                $minutes += $minute;
            }
        }

        $hours = floor($minutes / 60);
        $minutes -= $hours * 60;
        return $hours . ':' . $minutes;
    }

    public function TotalOtHours($arr)
    {
        $times = [];
        foreach ($arr as $a) {
            $times[] = $a[0]->ot;
        }
        $minutes = 0;
        foreach ($times as $time) {
            if ($time != '---') {
                list($hour, $minute) = explode(':', $time);
                $minutes += $hour * 60;
                $minutes += $minute;
            }
        }

        $hours = floor($minutes / 60);
        $minutes -= $hours * 60;
        return $hours . ':' . $minutes;
    }
    
    public function monthly_html(Request $request)
    {
        return Pdf::loadView('pdf.html.monthly.monthly_summary')->stream();
    }
}
