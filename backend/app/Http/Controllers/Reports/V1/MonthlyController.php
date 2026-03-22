<?php

namespace App\Http\Controllers\Reports\V1;

use App\Exports\AttendanceExport;
use App\Exports\AttendanceExportGeneral;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateAttendanceReport;
use App\Jobs\GenerateAttendanceReportPDF;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Roster;
use App\Models\Shift;
use App\Models\ShiftType;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MonthlyController extends Controller
{
    public $requestPayload;

    public $employeeId;

    public function startReportGenerationV1(Request $request)
    {
        $requestPayload = [
            'company_id'   => $request->company_id,
            'status'       => "-1",
            'status_slug'  => (new Controller)->getStatusSlug("-1"),
            'from_date'    => $request->from_date,
            'to_date'      => $request->to_date,
            'employee_ids' => $request->input('employee_id', []),
            'template'     => $request->input('report_template'),
        ];

        $companyId    = $requestPayload["company_id"];
        $employeeIds = $requestPayload["employee_ids"];
        $template     = $requestPayload["template"];
        $fromDate     = $requestPayload["from_date"];
        $toDate       = $requestPayload["to_date"];

        // 1. Get the employees
        $employeeIds = Employee::where("company_id", $companyId)
            ->whereIn("system_user_id", $employeeIds)
            ->pluck("id")->toArray();

        if (empty($employeeIds)) {
            return response()->json(['status' => 'error', 'message' => 'No employees found.'], 404);
        }


        $directory = public_path("reports/{$companyId}/{$template}");

        if (!File::isDirectory($directory)) {
            return response()->json(['error' => "Directory not found: {$directory}"], 404);
        }

        $pdfFiles = [];

        try {
            if (!empty($employeeIds)) {
                // Merge specific employees from the array
                foreach ($employeeIds as $id) {
                    $fileName = "Attendance_Report_{$template}_{$fromDate}_{$toDate}_{$id}.pdf";
                    $fullPath = $directory . DIRECTORY_SEPARATOR . $fileName;

                    if (File::exists($fullPath)) {
                        $pdfFiles[] = $fullPath;
                    }
                }
            } else {
                // Merge ALL files in that folder matching the pattern
                $pattern = "Attendance_Report_{$template}_{$fromDate}_{$toDate}_*.pdf";
                $pdfFiles = glob($directory . DIRECTORY_SEPARATOR . $pattern);
            }

            if (empty($pdfFiles)) {
                return response()->json(['error' => "No PDF files found to merge."], 404);
            }

            // Prepare Output Name
            $outputFileName = "Attendance_Report_{$fromDate}_to_{$toDate}.pdf";
            $outputPath = $directory . DIRECTORY_SEPARATOR . $outputFileName;

            // Use "F" to save the file on the server disk
            $this->mergePdfFiles($pdfFiles, "F", $outputPath);

            // Return the URL so the frontend can download it
            return response()->json([
                'status' => 'success',
                'file_name' => $outputFileName,
                'download_url' => asset("reports/{$companyId}/{$template}/{$outputFileName}")
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
