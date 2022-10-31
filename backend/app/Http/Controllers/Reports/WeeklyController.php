<?php

namespace App\Http\Controllers\Reports;

use App\Models\Company;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\Employee;

class WeeklyController extends Controller
{

    public function weekly_details(Request $request)
    {
        $start = $request->from_date ?? date('Y-10-01');
        $end = $request->to_date ?? date('Y-10-07');

        $model = Attendance::query();
        $model = $model->whereBetween('date', [$start, $end]);
        $model->orderBy('date', 'asc');

        $model->when($request->department_id && $request->department_id != -1, function ($q) use ($request) {
            $ids = Employee::where("department_id", $request->department_id)->pluck("employee_id");
            $q->whereIn('employee_id', $ids);
        });

        $data = $model->get()->groupBy(['employee_id', 'date']);
        
        $pdf = App::make('dompdf.wrapper');

        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first(["logo", "name", "company_code", "location", "p_o_box_no", "id"]);
        $company['report_type'] = $this->getStatusText($request->status);
        $company['start'] = $start;
        $company['end'] = $end;


        return $pdf->loadHTML($this->getHTML($data, (object)$company))->stream();
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

    public function getHTML($data, $company)
    {
        $mob = $company->contact->number ?? '';
        $companyLogo = $company->logo ?? '';

        // <img src="' . $companyLogo . '" height="100px" width="100">

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
                    <div style="img">


                    </div>
                </td>
                <td style="text-align: left;width: 333px; border :none; padding:15px; backgrozusnd-color:blue">
                    <div>
                        <table style="text-align: left; border :none;  ">
                            <tr style="text-align: left; border :none;">
                                <td style="text-align: center; border :none">
                                    <span class="title-font">
                                    Weekly Attendance ' . $company->report_type . ' Report
                                    </span>
                                    <hr style="width: 230px">
                                </td>
                            </tr>
                            <tr style="text-align: left; border :none;">
                                <td style="text-align: center; border :none">
                                    <span style="font-size: 11px">
                                    ' . date('d M Y', strtotime($company->start))  . ' - ' .  date('d M Y', strtotime($company->end))  . '
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
                            ' . $company->name . '
                            </b>
                            <br>
                        </td>
                    </tr>
                    <tr style="text-align: left; border :none;">
                        <td style="text-align: right; border :none;font-size:10px">
                            <span style="margin-right: 3px"> P.O.Box ' . $company->p_o_box_no . ' </span>
                            <br>
                        </td>
                    </tr>
                    <tr style="text-align: left; border :none;">
                        <td style="text-align: right; border :none;font-size:10px">
                            <span style="margin-right: 3px">' . $company->location . '</span>
                            <br>
                        </td>
                    </tr>
                    <tr style="text-align: left; border :none;">
                        <td style="text-align: right; border :none;font-size:10px">
                            <span style="margin-right: 3px"> Tel: ' .  $mob . ' </span>
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

            ' . $this->renderTable($data, $company) .
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

    public function renderTable($data, $company)
    {
        $start = date('d M Y', strtotime($company->start));
        $end = date('d M Y', strtotime($company->enc));

        $str_arr = [];
        foreach ($data as $key => $row) {
            $emp = Employee::where("employee_id", $key)->first();

            $str_arr[] = '<div class="page-breaks"><table  style="margin-top: 5px !important;">' .
                '<tr style="text-align: left; border :1px solid black; width:120px;">' .
                '<td style="text-align:left;"><b>Name</b>:' . $emp->first_name ?? '' . '</td>' .
                '<td style="text-align:left;"><b>EID</b>:' . $emp->employee_id ?? '' . '</td>' .
                '<td style="text-align:left;"><b>Dept</b>: ' . $emp->department->name ?? '' . '</td>' .
                '<td style="text-align:left; width:120px;"><b>Date: </b> ' . $start . ' to ' . $end . '</td>' .
                // '<td style="text-align:left; width:120px;"><b>Date: </b> 1 Sep 22 to 30 Sep 22</td>' .
                '<td style="text-align:left;"><b>Total Hrs</b>:' . $this->totalHours($row) . '</td>' .
                '<td style="text-align:left;"><b>OT</b>:' . $this->totalOtHours($row) . '</td>' .
                '<td style="text-align:left;color:green"><b>Present</b>:' . $row["Present"] . '</td>' .
                '<td style="text-align:left;color:red"><b>Absent</b>:' . $row["Absent"] . '</td>' .
                '<td style="text-align:left;"><b>Late In</b>:' . $row["Late In"] . '</td>' .
                '<td style="text-align:left;"><b>Early Out</b>:' . $row["Early Out"] . '</td>' .
                '</tr>' .
                '</table>' .
                $this->getData($row) .
                '</div>';
        }
        return join("", $str_arr);
    }

    public function getData($records)
    {

        $str = '<table class="main-table" style="margin-top: 15px !important;  padding-bottom: 5px;">';

        $dates = '<tr style="background-colorq:#A6A6A6;"><td><b>Dates</b></td>';
        $days = '<tr style="background-colorq:#A6A6A6;"><td><b>Days</b></td>';
        $in = '<tr style="background-colorq:#A6A6A6;"><td><b>In</b></td>';
        $out= '<tr style="background-colorq:#A6A6A6;"><td><b>Out</b></td>';
        $work = '<tr style="background-colorq:#A6A6A6;"><td><b>Work</b></td>';
        $ot = '<tr style="background-colorq:#A6A6A6;"><td><b>OT</b></td>';
        $shift = '<tr style="background-colorq:#A6A6A6;"><td><b>Shift</b></td>';
        $shift_type = '<tr style="background-colorq:#A6A6A6;"><td><b>Shift Type</b></td>';
        $din = '<tr style="background-colorq:#A6A6A6;"><td><b>Device In</b></td>';
        $dout = '<tr style="background-colorq:#A6A6A6;"><td><b>Device Out</b></td>';
        $status_tr = '<tr style="background-colorq:#A6A6A6;"><td><b>Status</b></td>';


        foreach ($records as $key => $record) {


            $dates .= '<td style="text-align: center;"> ' . substr($key, 0, 2) .' </td>';
            $days .= '<td style="text-align: center;"> ' . $record[0]['day'] .' </td>';

            $in .= '<td style="text-align: center;"> ' . $record[0]['in'] .' </td>';
            $out .= '<td style="text-align: center;"> ' . $record[0]['out'] .' </td>';

            $work .= '<td style="text-align: center;"> ' . $record[0]['total_hrs'] .' </td>';
            $ot .= '<td style="text-align: center;"> ' . $record[0]['ot'] .' </td>';

            $shift .= '<td style="text-align: center;"> ' . $record[0]['shift_id'] .' </td>';
            $shift_type .= '<td style="text-align: center;"> ' . $record[0]['shift_type_id'] .' </td>';
            $din .= '<td style="text-align: center;"> ' . $record[0]['device_id_in'] .' </td>';
            $dout .= '<td style="text-align: center;"> ' . $record[0]['device_id_out'] .' </td>';

            $status = $record[0]['status'] == 'A' ? 'color:red' : 'color:green';

            $status_tr .= '<td style="text-align: center;"> ' . $status .' </td>';
        }


        $dates .= '</tr>';
        $days .= '</tr>';
        $in .= '</tr>';
        $out.= '</tr>';
        $work .= '</tr>';
        $ot .= '</tr>';
        $shift .= '</tr>';
        $shift_type .= '</tr>';
        $din .= '</tr>';
        $dout .= '</tr>';
        $status_tr .= '</tr>';

        $str = $str. $dates. $days. $in. $out. $work. $ot. $shift. $shift_type. $din. $dout. $status_tr;

        $str .= '</table>';


        return $str;
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

    public function totalOtHours($arr)
    {
        $times = [];
        foreach ($arr as $a) {
            $times[] = $a[0]->ot;
        }
        $minutes = 0;

        foreach ($times as $time) {
            if ($time != '---') {
                $hour = '00';
                $minute = '00';
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
