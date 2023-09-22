<?php

namespace App\Http\Controllers\Reports;

use App\Models\Shift;
use App\Models\Device;
use App\Models\Company;
use App\Models\Employee;
use App\Models\ShiftType;
use App\Models\Attendance;
use App\Models\Department;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;

class PDFController extends Controller
{

    public function daily_summary(Request $request)
    {
        return Pdf::loadView('pdf.html.daily.daily_summary')->stream();
    }
    public function weekly_summary(Request $request)
    {
        return Pdf::loadView('pdf.html.weekly.weekly_summary_v1')->stream();
    }
    public function monthly_summary()
    {
        return Pdf::loadView('pdf.html.monthly.monthly_summary_v1')->stream();
    }

    public function dailyAccessControl()
    {
        return Pdf::loadView('pdf.html.daily.access_control')->stream();
    }
    public function weeklyAccessControl()
    {
        return Pdf::loadView('pdf.html.weekly.access_control')->stream();
    }
    public function monthlyAccessControl()
    {
        return Pdf::loadView('pdf.html.monthly.access_control')->stream();
    }
    public function monthlyAccessControlV1()
    {
        return Pdf::loadView('pdf.html.monthly.access_control_v1')->stream();
    }
    public function monthlyAccessControlCount()
    {
        return Pdf::loadView('pdf.html.monthly.access_control_count')->stream();
    }
    public function monthlyAccessControlByDevice()
    {
        return Pdf::loadView('pdf.html.monthly.access_control_by_device')->stream();
    }
}
