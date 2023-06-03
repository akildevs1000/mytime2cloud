<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollFormula;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PayslipController extends Controller
{
    public function index($department_id)
    {
        $employees = Employee::where("department_id", $department_id)
            ->withOut("schedule")
            ->with("payroll")
            ->get(["id", "employee_id"]);

        $data = [];

        foreach ($employees as $employee) {
            $data[] = $this->renderPayslip($employee);
        }

        return $data;
    }

    public function renderPayslip($employee, $present = 20, $absent = 10)
    {
        $conditions = ["company_id" => $employee->company_id, "employee_id" => $employee->employee_id];

        $attendances = Attendance::where($conditions)
            ->whereMonth('date', '=', date('m'))
            ->whereIn('status', ['P', 'A'])
            ->get();

        $present = $attendances->where('status', 'P')->count();
        $absent = $attendances->where('status', 'A')->count();

        $payroll = $employee->payroll;

        $salary_type = $payroll->payroll_formula->salary_type;
        $payroll->SELECTEDSALARY = $salary_type == "basic_salary" ? $payroll->basic_salary  : $payroll->net_salary;

        $payroll->perDaySalary = $this->getPerDaySalary($payroll->SELECTEDSALARY ?? 0);
        $payroll->perHourSalary = $this->getPerHourSalary($payroll->perDaySalary ?? 0);

        $payroll->earnedSalary = $present * $payroll->perDaySalary;
        $payroll->deductedSalary = $absent * $payroll->perDaySalary;
        $payroll->earningsCount = $payroll->net_salary  - $payroll->basic_salary;

        $extraEarnings = [
            "label" => "Basic",
            "value" =>  $payroll->SELECTEDSALARY
        ];

        $payroll->earnings = array_merge([$extraEarnings], $payroll->earnings);

        $payroll->deductions = [
            [
                "label" => "Abents",
                "value" => $payroll->deductedSalary
            ]
        ];

        $payroll->earnedSubTotal = ($payroll->earningsCount) + ($payroll->earnedSalary);
        $payroll->salary_and_earnings = ($payroll->earningsCount) + ($payroll->SELECTEDSALARY);

        $payroll->finalSalary = ($payroll->salary_and_earnings) - $payroll->deductedSalary;


        return $payroll;
    }

    public function show(Request $request, $id)
    {
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

        $extraEarnings = [
            "label" => "Basic",
            "value" =>  $Payroll->SELECTEDSALARY
        ];
        $Payroll->earnings = array_merge([$extraEarnings], $Payroll->earnings);

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
