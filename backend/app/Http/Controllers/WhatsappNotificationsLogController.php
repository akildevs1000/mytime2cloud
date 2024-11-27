<?php

namespace App\Http\Controllers;

use App\Models\Company;
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


        $company = Company::with(["contact"])->where("id", $request->company_id)->first();


        if ($company->enable_desktop_whatsapp == true) {

            if ($request->filled("whatsapp_number") && $request->filled("message"))
                WhatsappNotificationsLog::create([
                    "company_id" =>  $request->company_id,
                    "whatsapp_number" => $request->whatsapp_number,
                    "message" => $request->message
                ]);

            return $this->response("Whatsapp Request Created Successfully", null, true);
        } else {
            return $this->response("Desktop Whatsapp is not enabled", null, false);
        }
    }
}
