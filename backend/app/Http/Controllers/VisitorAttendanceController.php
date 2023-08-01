<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\VisitorAttendance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class VisitorAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return (new VisitorAttendance)->processVisitorModel($request)->paginate($request->per_page ?? 100);
    }

    public function monthly_pdf(Request $request)
    {

        $model = (new VisitorAttendance)->processVisitorModel($request)->get();

        $data = $model->groupBy(['visitor_id', 'date']);

        $final_data = ["data" => $data, "info" => $this->prepareInfoData($request)];

        return Pdf::loadView('pdf.visitor.general', $final_data)->stream();
    }

    public function monthly_download_pdf(Request $request)
    {
        $model = (new VisitorAttendance)->processVisitorModel($request)->get();

        $data = $model->groupBy(['visitor_id', 'date']);

        $final_data = ["data" => $data, "info" => $this->prepareInfoData($request)];

        return Pdf::loadView('pdf.visitor.general', $final_data)->download();
    }

    public function monthly_download_csv(Request $request)
    {
        $data = (new VisitorAttendance)->processVisitorModel($request)->get();

        $fileName = 'report.csv';

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        );

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            $i = 0;

            fputcsv($file, ["#", "Date", "V.ID", "Full Name", "Status", "In", "Out", "Total Hrs", "D.In", "D.Out"]);
            foreach ($data as $col) {
                fputcsv($file, [
                    ++$i,
                    $col['date'],
                    $col['visitor_id'] ?? "---",
                    $col['visitor']["full_name"] ?? "---",
                    $col["status"] ?? "---",
                    $col["in"] ?? "---",
                    $col["out"] ?? "---",
                    $col["total_hrs"] ?? "---",
                    $col["device_in"]["short_name"] ?? "---",
                    $col["device_out"]["short_name"] ?? "---",
                ], ",");
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function prepareInfoData($request)
    {
        $data = [];
        $data['from_date'] = date('d-M-Y', strtotime($request->from_date));
        $data['to_date'] = date('d-M-Y', strtotime($request->to_date));
        $data['report_type'] = $request->report_type ?? "";
        $data['status'] = $request->status ?? "";
        return $data;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VisitorAttendance  $visitorAttendance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VisitorAttendance $visitorAttendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VisitorAttendance  $visitorAttendance
     * @return \Illuminate\Http\Response
     */
    public function destroy(VisitorAttendance $visitorAttendance)
    {
        //
    }
}
