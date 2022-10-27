<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class WeeklyController extends Controller
{

    public function weekly_details(Request $request)
    {
        $start = $request->from_date ?? date('Y-10-01');
        $end = $request->to_date ?? date('Y-10-07');
        $type = 'weekly';
        $model = Attendance::query();

        // $mo del->whereRaw('extract(month from date) = ?', date("m"));
        // $model->whereMonth("date", date("9"));

        // $model = $model->whereMonth("date", date("m"));

        $model = $model->whereBetween('date', [$start, $end]);

        // $model = $model->where("employee_id", "<", 2);
        $model->with('employeeAttendance');
        $model->orderBy('date', 'asc');
        // $data =   $model->take(10)->get();
        $data =   $model->get();
        $data = $data->groupBy(['employee_id', 'date']);
        $arr = [];

        foreach ($data as $employee_id => $row) {
            $emp = $this->getEmployee($row);

            $arr[] = [
                'Name' => $emp->first_name ?? '',
                'E.ID' => $emp->employee_id ?? '',
                'Dept' => $emp->department->name ?? '',
                'Date' => $start . ' to ' . $end,
                'Total Hrs' => 'totalhrs',
                'OT' => 'ot',
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

        // $this->getHTML($arr, $request, $type);
        // $pdfJobs = new PDFJob($this->getHTML($arr));
        // $this->dispatch($pdfJobs);


        $arr;
        $collection = collect($arr)->take(30);


        return $pdf->loadHTML($this->getHTML($collection, $request, $type))->stream();
        $pdf->stream();
    }
    public function weekly_summary()
    {
        $data = Attendance::whereMonth("date", date("m"))->get()->toArray();
        return Pdf::loadView('pdf.weekly_summary', ["data" => $data])->stream();
    }

    public function weekly_present()
    {
        $data = Attendance::whereMonth("date", date("m"))->get()->toArray();
        return Pdf::loadView('pdf.weekly_present', ["data" => $data])->stream();
    }

    public function weekly_absent()
    {
        $data = Attendance::whereMonth("date", date("m"))->get()->toArray();
        return Pdf::loadView('pdf.weekly_absent', ["data" => $data])->stream();
    }

    public function weekly_late_in()
    {
        $data = Attendance::whereMonth("date", date("m"))->get()->toArray();
        return Pdf::loadView('pdf.weekly_late_in', ["data" => $data])->stream();
    }

    public function weekly_early_out()
    {
        $data = Attendance::whereMonth("date", date("m"))->get()->toArray();
        return Pdf::loadView('pdf.weekly_early_out', ["data" => $data])->stream();
    }

    public function weekly_performance()
    {
        $data = Attendance::whereMonth("date", date("m"))->get()->toArray();
        return Pdf::loadView('pdf.weekly_performance', ["data" => $data])->stream();
    }

    public function getHTML($arr, $request, $type)
    {

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

                th { font-size: 9px; }
                td { font-size: 7px; }

                .page-break { page-break-after: always; }
                .main-table {
                    padding-right: 15px;
                    padding-left: 15px;
                }
                hr {
                    position: relative;
                    border: none;
                    height: 2px;
                    background: #c5c2c2;
                    padding: 0px
                }
                .title-font {
                    font-family: Arial, Helvetica, sans-serif !important;
                    font-size: 14px;
                    font-weight: bold
                }

                .summary-header th {
                    font-size: 10px
                }

                .summary-table td {
                    font-size: 9px
                }
                footer {
                    bottom: 0px;
                    position: absolute;
                    width: 100%;
                }
            </style>
            </head>
            <body>

            <table style="margin-top: -20px !important;backgroundd-color:blue;padding-bottom:0px ">
            <tr>
                <td style="text-align: left;width: 300px; border :none; padding:15px;   backgrozund-color: red">
                    <div style=";">

                            <img src="' . getcwd() . '/upload/app-logo.jpeg" height="70px" width="200">

                    </div>
                </td>
                <td style="text-align: left;width: 333px; border :none; padding:15px; backgrozusnd-color:blue">
                    <div>
                        <table style="text-align: left; border :none;  ">
                            <tr style="text-align: left; border :none;">
                                <td style="text-align: center; border :none">
                                    <span class="title-font">
                                    Weekly Attendance Summary Report
                                    </span>
                                    <hr style="width: 230px">
                                </td>
                            </tr>
                            <tr style="text-align: left; border :none;">
                                <td style="text-align: center; border :none">
                                    <span style="font-size: 11px">
                                    01 Oct 2022 - 30 Oct 2022
                                    </span>
                                    <hr style="width: 230px">
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="text-align: right;width: 300px; border :none; backgrounsd-color: red">


                    <table class="summary-table"
                    style="border:none; padding:0px 50px; margin-left:35px;margin-top:20px;margin-bottom:0px">
                    <tr style="text-align: left; border :none;">
                        <td style="text-align: right; border :none;font-size:10px">
                            <b>
                               Akkil Security & Alarm System LLC
                            </b>
                            <br>
                        </td>
                    </tr>
                    <tr style="text-align: left; border :none;">
                        <td style="text-align: right; border :none;font-size:10px">
                            <span style="margin-right: 3px"> P.O. Box 83481, Dubai </span>
                            <br>
                        </td>
                    </tr>
                    <tr style="text-align: left; border :none;">
                        <td style="text-align: right; border :none;font-size:10px">
                            <span style="margin-right: 3px"> United Arab Emirates </span>
                            <br>
                        </td>
                    </tr>
                    <tr style="text-align: left; border :none;">
                        <td style="text-align: right; border :none;font-size:10px">
                            <span style="margin-right: 3px"> Tel: +97143939562 </span>
                            <br>
                        </td>
                    </tr>
                </table>

                    <br>
                </td>
                </td>
            </tr>
        </table>
        <hr style="margin:0px;padding:0">

            ' . $this->renderTable($arr) .
            '
            <hr style=" bottom: 0px; position: absolute; width: 100%; margin-bottom:20px">
            <footer style="padding-top: 0px!important">
            <table class="main-table">
                <tr style="border :none">
                    <td style="text-align: left;border :none"><b>Device</b>: Main Entrance = MED, Back Entrance = BED</td>
                    <td style="text-align: left;border :none"><b>Shift Type</b>: Manual = MA, Auto = AU, NO = NO</td>
                    <td style="text-align: left;border :none"><b>Shift</b>: Morning = Mor, Evening = Eve, Evening2 = Eve2
                    </td>
                    <td style="text-align: right;border :none;">
                        <b>Powered by</b>: <span style="color:blue"> www.ideahrms.com</span>
                    </td>
                    <td style="text-align: right;border :none">
                        Printed on :  ' . date("d-M-Y ") . '
                    </td>
                </tr>
            </table>
        </footer>
            </body>
            </html>';
    }

    public function renderTable($arr)
    {

        $str_arr = [];
        foreach ($arr as $key => $row) {
            $records = $this->getData($row['record']);
            $str_arr[] = '<div class="page-breaks"><table class="main-table" style="margin-top: 10px !important;">' .
                '<tr style="text-align: left; border :1px solid black; width:120px;">' .
                '<td style="text-align:left;"><b>Name</b>:' . $row["Name"] . '</td>' .
                '<td style="text-align:left;"><b>EID</b>:' . $row["E.ID"] . '</td>' .
                '<td style="text-align:left;"><b>Dept</b>: ' . $row["Dept"] . '</td>' .
                '<td style="text-align:left; width:120px;"><b>Date: </b> ' . $row["Date"] . '</td>' .
                // '<td style="text-align:left; width:120px;"><b>Date: </b> 1 Sep 22 to 30 Sep 22</td>' .
                '<td style="text-align:left;"><b>Total Hrs</b>:' . $row["Total Hrs"] . '</td>' .
                '<td style="text-align:left;"><b>OT</b>:' . $row["OT"] . '</td>' .
                '<td style="text-align:left;color:green"><b>Present</b>:' . $row["Present"] . '</td>' .
                '<td style="text-align:left;color:red"><b>Absent</b>:' . $row["Absent"] . '</td>' .
                '<td style="text-align:left;"><b>Late In</b>:' . $row["Late In"] . '</td>' .
                '<td style="text-align:left;"><b>Early Out</b>:' . $row["Early Out"] . '</td>' .
                '</tr>' .
                '</table>' .
                '<table class="main-table" style="margin-top: 5px !important;  padding-bottom: 5px;">
                    <tr style="background-colorq:#A6A6A6;"><td><b>Dates</b></td>' . $records[0] . '</tr>
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

    public function weekly_html(Request $request)
    {
        return Pdf::loadView('pdf.html.weekly.weekly_summary')->stream();
    }
}