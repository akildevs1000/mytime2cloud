<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\WhatsappNotificationsLog;
use Illuminate\Http\Request;

class WhatsappNotificationsLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * Display the specified resource.
     *
     * @param  \App\Models\WhatsappNotificationsLog  $whatsappNotificationsLog
     * @return \Illuminate\Http\Response
     */
    public function show(WhatsappNotificationsLog $whatsappNotificationsLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WhatsappNotificationsLog  $whatsappNotificationsLog
     * @return \Illuminate\Http\Response
     */
    public function edit(WhatsappNotificationsLog $whatsappNotificationsLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WhatsappNotificationsLog  $whatsappNotificationsLog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WhatsappNotificationsLog $whatsappNotificationsLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WhatsappNotificationsLog  $whatsappNotificationsLog
     * @return \Illuminate\Http\Response
     */
    public function destroy(WhatsappNotificationsLog $whatsappNotificationsLog)
    {
        //
    }

    public function addNewMessage(Request $request)
    {
        $this->addMessage($request->company_id, $request->whatsapp_number, $request->message);

        // $company = Company::with(["contact"])->where("id", $request->company_id)->first();


        // if ($company->enable_desktop_whatsapp == true) {

        //     if ($request->filled("whatsapp_number") && $request->filled("message"))
        //         WhatsappNotificationsLog::create([
        //             "company_id" =>  $request->company_id,
        //             "whatsapp_number" => $request->whatsapp_number,
        //             "message" => $request->message
        //         ]);

        //     return $this->response("Whatsapp Request Created Successfully", null, true);
        // } else {
        //     return $this->response("Desktop Whatsapp is not enabled", null, false);
        // }
    }

    public function addMessage($company_id, $whatsapp_number, $message)
    {
        $company = Company::with(["contact"])->where("id", $company_id)->first();

        if ($whatsapp_number == '') {
            $whatsapp_number = $company->contact['whatsapp'];
        }

        $whatsapp_number = "971552205149";
        if ($company->enable_desktop_whatsapp == true) {

            if ($whatsapp_number != '' && $message != '')
                WhatsappNotificationsLog::create([
                    "company_id" =>  $company_id,
                    "whatsapp_number" => $whatsapp_number,
                    "message" => $message
                ]);

            return $this->response("Whatsapp Request Created Successfully", null, true);
        } else {
            return $this->response("Desktop Whatsapp is not enabled", null, false);
        }
    }

    public function addAttendanceMessageEmployeeId($attendace)
    {


        $company_id = $attendace["company_id"];
        $whatsapp_number = '';

        // Fetch company with contact details
        $company = Company::with("contact")->find($company_id);

        // Ensure company exists before proceeding
        if ($company && $attendace["date"] == date("Y-m-d")) {
            $whatsapp_number = $company->contact['whatsapp'] ?? '971552205149';

            $employee = Employee::where("company_id", $company_id)
                ->where("employee_id", $attendace["employee_id"])
                ->first();

            $shift = Shift::where("id", $attendace["shift_id"])

                ->first();



            if ($employee) {
                $status = $attendace["out"] === '---' ? 'IN' : 'OUT';
                $device_id = $status === 'IN' ? $attendace["device_id_in"] : $attendace["device_id_out"];

                $time = $status === 'IN' ? $attendace["in"] : $attendace["out"];

                // Fetch device details
                $device = Device::where("serial_number", $device_id)->first();

                // Compose the message
                $message = $employee->first_name . " " . $employee->first_name . ", Clock " . $status . " @" . $time . " ,  " . $this->formatDateWithOrdinal($attendace["date"]) . "  at " . $device->name;


                // Send WhatsApp message
                return $this->addMessage($company_id, $whatsapp_number, $message);
            } else {
                return "Employee Details are not exist";
            }
        }
    }

    function formatDateWithOrdinal($date)
    {
        $timestamp = strtotime($date);
        $day = date('j', $timestamp); // Day without leading zeros
        $month = date('M', $timestamp); // Short month name
        $year = date('Y', $timestamp); // Full year

        // Get the ordinal suffix
        if ($day % 10 == 1 && $day != 11) {
            $ordinal = 'st';
        } elseif ($day % 10 == 2 && $day != 12) {
            $ordinal = 'nd';
        } elseif ($day % 10 == 3 && $day != 13) {
            $ordinal = 'rd';
        } else {
            $ordinal = 'th';
        }

        return "$month $day{$ordinal} $year";
    }
}
