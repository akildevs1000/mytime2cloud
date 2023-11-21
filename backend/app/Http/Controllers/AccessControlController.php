<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;


class AccessControlController extends Controller
{
    public function index(AttendanceLog $model, Request $request)
    {
        return $model->filter($request)->paginate($request->per_page);
    }

    public function access_control_report_print_pdf(AttendanceLog $model, Request $request)
    {
        $data = $model->filter($request)->get()->toArray();

        if ($request->debug) return $data;

        return Pdf::loadView("pdf.access_control_reports.custom", [
            "data" => $data,
            "company" => Company::whereId(request("company_id") ?? 0)->first(),
            "params" => $request->all()
        ])->stream();
    }

    public function access_control_report_download_pdf(AttendanceLog $model, Request $request)
    {
        $data = $model->filter($request)->get()->toArray();

        if ($request->debug) return $data;

        return Pdf::loadView("pdf.access_control_reports.custom", [
            "data" => $data,
            "company" => Company::whereId(request("company_id") ?? 0)->first(),
            "params" => $request->all()
        ])->download();
    }
}
