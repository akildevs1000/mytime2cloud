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
        if ($company) {
            $whatsapp_number = "971552205149"; //$company->contact['whatsapp'] ?? '971552205149';

            $employee = Employee::where("company_id", $company_id)
                ->where("employee_id", $attendace["employee_id"])
                ->first();

            $shift = Shift::where("id", $attendace["shift_id"])

                ->first();



            if ($employee) {
                $status = $attendace["out"] === '---' ? 'In' : 'Out';
                $device_id = $status === 'In' ? $attendace["device_id_in"] : $attendace["device_id_out"];

                // Fetch device details
                $device = Device::where("serial_number", $device_id)->first();

                // Compose the message
                $message = sprintf(
                    "Company: %s\n\n%s %s\n\nID: %s\nStatus: %s\nTime: %s %s\nDevice: %s\nShift: %s\nShiftTime: %s",
                    $company->name,
                    $employee->first_name,
                    $employee->last_name, // Assuming you meant last name instead of repeating first name
                    $employee->employee_id,
                    $status,
                    $attendace["date"],
                    $status === 'In' ? $attendace["in"] : $attendace["out"],
                    $device->name ?? 'Unknown Device',
                    $shift ? $shift["name"] : '---',
                    $shift ? $shift["on_duty_time"] . '-' . $shift["off_duty_time"] : '---',
                );

                // Send WhatsApp message
                return $this->addMessage($company_id, $whatsapp_number, $message);
            } else {
                return "Employee Details are not exist";
            }
        }
    }
}
