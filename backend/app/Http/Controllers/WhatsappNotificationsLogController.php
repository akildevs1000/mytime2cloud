<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Device;
use App\Models\Employee;
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
        $company = Company::with(["contact"])->where("id", $company_id)->first();

        if ($whatsapp_number == '') {
            $whatsapp_number = $company->contact['whatsapp'];
        }

        $employee = Employee::where("company_id", $company_id)->where("user_id", $attendace["employee_id"])->first();
        $device = null;
        $status = "";

        if ($attendace["out"] == '---') {
            $device = Device::where("serial_number", $attendace["device_id_in"]);
            $status = "In";
        } else {
            $device = Device::where("serial_number", $attendace["device_id_out"]);
            $status = "Out";
        }
        $message = $company["name"] . "\n\n";
        $message .= $employee["first_name"] . " " . $employee["first_name"] . "\n\n";
        $message .= "ID: " . $company["user_id"] . "\n\n";
        $message .=   $status . "\n\n";
        $message .=   "Time: " . $attendace["logDate"] . "\n\n";


        $this->addMessage($company_id, $whatsapp_number, $message);
    }
}
