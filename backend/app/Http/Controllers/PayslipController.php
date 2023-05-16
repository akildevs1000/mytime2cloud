<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\PayrollFormula;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PayslipController extends Controller
{
    public function show(Request $request, $id)
    {
        // $this->renderFakeData($request->company_id, $id);

        $Payroll = Payroll::where(["employee_id" => $id])->first(["basic_salary", "net_salary", "earnings", "company_id"]);

        $salary_type = $Payroll->payroll_formula->salary_type;
        $Payroll->SELECTEDSALARY = $salary_type == "basic_salary" ? $Payroll->basic_salary  : $Payroll->net_salary;

        $Payroll->perDaySalary = $this->getPerDaySalary($Payroll->SELECTEDSALARY ?? 0);
        $Payroll->perHourSalary = $this->getPerHourSalary($Payroll->perDaySalary ?? 0);

        $conditions = ["company_id" => $request->company_id, "employee_id" => $request->employee_id];

        $attendances = Attendance::where($conditions)
            ->whereMonth('date', '=', date('m'))
            ->whereIn('status', ['P', 'A'])
            ->get();

        $Payroll->present = $attendances->where('status', 'P')->count();
        $Payroll->absent = $attendances->where('status', 'A')->count();

        $Payroll->present = 20;
        $Payroll->absent = 10;

        $Payroll->earnedSalary = $Payroll->present * $Payroll->perDaySalary;
        $Payroll->deductedSalary = $Payroll->absent * $Payroll->perDaySalary;
        $Payroll->earningsCount = $Payroll->net_salary  - $Payroll->basic_salary;

        // {{ data.present }} Presents x {{ data.perDaySalary }} AED =
        // {{ data.earnedSalary }}
        $extraEarnings = [
            "label" => $Payroll->present . " Present x " . $Payroll->perDaySalary . " AED = ",
            "value" => $Payroll->earnedSalary
        ];

        $Payroll->earnings = array_merge([$extraEarnings], $Payroll->earnings);

        // array_push($Payroll->earnings,"s");

        $Payroll->deductions = [
            [
                "label" => "Abents",
                "value" => $Payroll->deductedSalary
            ]
        ];


        $Payroll->earnedSubTotal = ($Payroll->earningsCount) + ($Payroll->earnedSalary);
        $Payroll->salary_and_earnings = ($Payroll->earningsCount) + ($Payroll->SELECTEDSALARY);

        $Payroll->finalSalary = ($Payroll->salary_and_earnings) - $Payroll->deductedSalary;


        return $Payroll;
    }
    public function getPerHourSalary($perDaySalary)
    {
        return number_format($perDaySalary / 8, 2);
    }
    public function getPerDaySalary($salary)
    {
        return number_format($salary / 30, 2);
    }

    public function renderFakeData($company_id, $id)
    {
        $arr = [
            [
                "date" => date("Y-m-01"),
                "status" => "P",
                "employee_id" => $id,
                "company_id" => $company_id,
            ],
            [
                "date" => date("Y-m-02"),
                "status" => "P",
                "employee_id" => $id,
                "company_id" => $company_id,
            ],
            [
                "date" => date("Y-m-03"),
                "status" => "A",
                "employee_id" => $id,
                "company_id" => $company_id,
            ],
            [
                "date" => date("Y-m-04"),
                "status" => "P",
                "employee_id" => $id,
                "company_id" => $company_id,
            ],

        ];

        return Attendance::insert($arr);
    }
}
