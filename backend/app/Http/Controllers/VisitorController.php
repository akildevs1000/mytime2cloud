<?php

namespace App\Http\Controllers;

use App\Http\Requests\Visitor\Register;
use App\Http\Requests\Visitor\Store;
use App\Http\Requests\Visitor\Update;
use App\Jobs\ProcessSDKCommand;
use App\Models\Company;
use App\Models\HostCompany;
use App\Models\Notification;
use App\Models\Visitor;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class VisitorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getVisitorStatusList()
    {
        return (new  Visitor)->getVisitorStatusIds();
    }
    public function visitors_with_type(Request $request)
    {
        $model = Visitor::query();

        $model->where("company_id", $request->input("company_id"));

        $model->when($request->filled('branch_id'), function ($q) use ($request) {
            $q->Where('branch_id',   $request->branch_id);
        });

        return $model->get();
    }
    public function timeTOSeconds($str_time)
    {
        // $str_time = "2:50";

        // sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);

        // return  $time_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;

        return  $seconds = strtotime($str_time) - strtotime('TODAY');
    }
    public function index(Request $request)
    {
        $model = Visitor::query();

        $model->where("company_id", $request->input("company_id"));
        $model->when($request->filled('branch_id'), function ($q) use ($request) {
            $q->Where('branch_id',   $request->branch_id);
        });

        $fields = ['id', 'company_name', 'system_user_id', 'manager_name', 'phone', 'email', 'zone_id', 'phone_number', 'email', 'time_in'];

        $model = $this->process_ilike_filter($model, $request, $fields);
        $model->when($request->filled('first_name'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->Where('first_name', 'ILIKE', "$request->first_name%");
                $q->orWhere('last_name', 'ILIKE', "$request->first_name%");
            });
        });

        $model->when($request->filled("from_date"), fn ($q) => $q->whereDate("visit_from", '>=', $request->from_date));
        $model->when($request->filled("to_date"), fn ($q) => $q->whereDate("visit_to", '<=', $request->to_date));

        // $startDate = Carbon::parse($request->from_date);
        // $endDate = Carbon::parse($request->to_date);

        // $model = $model->where(function ($query) use ($startDate, $endDate) {
        //     $query->whereBetween('visit_from', [$startDate, $endDate])
        //         ->orWhereBetween('visit_to', [$startDate, $endDate])
        //         ->orWhere(function ($query) use ($startDate, $endDate) {
        //             $query->where('visit_from', '<', $startDate)
        //                 ->where('visit_to', '>', $endDate);
        //         });
        // });

        $fields1 = ['host_company_id', 'purpose_id', 'status_id'];
        $model = $this->process_column_filter($model, $request, $fields1);



        $model->when($request->filled('statsFilterValue'), function ($q) use ($request) {
            if ($request->statsFilterValue == 'Expected')
                $q->WhereIn('status_id',  [2, 4, 5]);

            else if ($request->statsFilterValue == 'Checked In')
                $q->Where('status_id', 6);

            else  if ($request->statsFilterValue == 'Checked Out')
                $q->Where('status_id', 7);

            else  if ($request->statsFilterValue == 'Pending')
                $q->Where('status_id', 1);
            else  if ($request->statsFilterValue == 'Approved')
                $q->WhereIn('status_id',  [2, 4, 5, 6, 7]);
            else  if ($request->statsFilterValue == 'Rejected')
                $q->Where('status_id', 3);
        });



        $model->when($request->filled('sortBy'), function ($q) use ($request) {
            $sortDesc = $request->input('sortDesc');
            if (strpos($request->sortBy, '.')) {
            } else {
                $q->orderBy($request->sortBy . "", $sortDesc == 'true' ? 'desc' : 'asc'); {
                }
            }
        });




        if (!$request->sortBy)
            $model->orderBy("visit_from", "DESC");

        $results = $model->with(["zone", "host", "timezone:id,timezone_id,timezone_name", "purpose:id,name"])->paginate($request->input("per_page", 100));

        $overstayedVisitors = [];
        if ($request->statsFilterValue == 'Over Stayed') {

            $data = $results->getCollection();;


            foreach ($data  as $pending) {


                $actucalCheckOutTime = $this->timeTOSeconds($pending->time_out);
                if ($pending->checked_out_datetime) {
                    // $visitorCheckoutTime = $this->timeTOSeconds(date('H:i', strtotime($pending->checked_out_datetime)));
                } else {
                    $visitorCheckoutTime = $this->timeTOSeconds(date("H:i"));

                    $pending["actucalCheckOutTime"] = $actucalCheckOutTime;
                    $pending["visitorCheckoutTime"] = $visitorCheckoutTime;
                    $pending["over_stay"] = gmdate("H:i", $visitorCheckoutTime - $actucalCheckOutTime);
                    $pending["over_stay"] = explode(":", $pending["over_stay"])[0] . 'h:' . explode(":", $pending["over_stay"])[1] . 'm';
                    if ($visitorCheckoutTime > $actucalCheckOutTime) {

                        $overstayedVisitors[] = $pending;
                    }
                }
            }

            $overstayedVisitors = new Collection($overstayedVisitors);
            return $data = $results->setCollection($overstayedVisitors);;
        } else {

            return $results;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Store $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $ext;
            $request->logo->move(public_path('media/visitor/logo/'), $fileName);
            $data['logo'] = $fileName;
        }

        try {

            $visitor = Visitor::create($data);

            if (!$visitor) {
                return $this->response('Visitor cannot add.', null, false);
            }

            $preparedJson = $this->prepareJsonForSDK($data);

            // $this->SDKCommand(env('SDK_URL') . "/Person/AddRange", $preparedJson);
            ProcessSDKCommand::dispatch(env('SDK_URL') . "/Person/AddRange", $preparedJson);

            return $this->response('Visitor successfully created.', null, true);
        } catch (\Throwable $th) {
            return $this->response($th, null, true);
        }
    }

    public function register(Register $request)
    {
        $data = $request->validated();

        $data['logo'] = $this->processImage("media/visitor/logo");
        $data['date'] = date("Y-m-d");
        $data['visitor_filled_datetime'] = date("Y-m-d H:i:s");

        try {

            if (!Visitor::create($data)) {
                return $this->response('Form is not submitted.', null, false);
            }

            // $preparedJson = $this->prepareJsonForSDK([
            //     "first_name" => "first_name",
            //     "last_name" => "last_name",
            //     "system_user_id" => "system_user_id",
            //     "timezone_id" => "timezone_id",
            //     "logo" => "logo",
            //     "zone_id" => "zone_id",
            // ]);
            // ProcessSDKCommand::dispatch(env('SDK_URL') . "/Person/AddRange", $preparedJson);


            $message = "👥 *New Visitor Registered* 👥\n\n";
            $message .= "*Dear, User*\n\n";
            $message .= "New visitor has been registered.\n\n";
            $message .= "*Visitor Details*\n\n";
            $message .= "*Name* " . $data['first_name'] . " " .  $data['last_name'] . ".\n";
            $message .= "*Visit Date* " . $data['visit_from'] . " To " .  $data['visit_to'] . ".\n";
            $message .= "*Phone Number* " . $data['phone_number'] . ".\n";
            $message .= "*Visitor Company* " . $data['visitor_company_name'] . ".\n";
            $message .= "*Date:* " . date("d-M-y") . "\n";
            $message .= "*App Link:* " . "https://mobile.mytime2cloud.com/login" . "\n\n";
            $message .= "Best regards\n";
            $message .= "*MyTime2Cloud*";
            $company = Company::where("id", $request->company_id)->first();


            if ($data['host_company_id'] ?? false) {

                $host = HostCompany::where("id", $data['host_company_id'])->with("employee:id,user_id,employee_id")->first();

                (new WhatsappController)->sendWhatsappNotification($company, $message, $host->number ?? 971554501483);

                Notification::create([
                    "data" => "New visitor has been registered",
                    "action" => "Registration",
                    "model" => "Visitor",
                    "user_id" => $host->employee->user_id ?? 0,
                    "company_id" => $request->company_id,
                    "redirect_url" => "visitor_requests"
                ]);
            }

            $data['url'] = "https://backend.mytime2cloud.com/media/visitor/logo/" . $data['logo'];


            return $this->response('Form has been submitted successfully.', $data, true);
        } catch (\Throwable $th) {

            return $th;
            // return $this->response('Server Error.', null, true);
        }
    }

    public function visitorStatusUpdate(Request $request, $id)
    {
        // $company = Company::where("id", $request->company_id)->first();

        // $message = "👥 *New Visitor Registered* 👥\n\n";
        // $message .= "*Dear, User*\n\n";
        // $message .= "New visitor has been registered.\n\n";
        // $message .= "*Date:* " . date("d-M-y") . "\n\n";
        // $message .= "Best regards\n";
        // $message .= "*MyTime2Cloud*";

        // return (new WhatsappController)->sendWhatsappNotification($company, $message, '971554501483');

        try {
            $visitor = Visitor::whereId($id)->update([
                "status_id" => $request->status_id,
                "host_changed_status_datetime" => date("Y-m-d H:i:s")
            ]);
            if (!$visitor) {
                return $this->response('Visitor cannot update.', null, false);
            }

            $statusText = $request->status_id == 1 ? 'Approved' : 'Rejected';

            return $this->response("Visitor status has been {$statusText}.", null, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function prepareJsonForSDK($data)
    {
        $personList = [];

        $personList["name"] = $data["first_name"] . " " . $data["last_name"];
        $personList["userCode"] = $data["system_user_id"];
        $personList["timeGroup"] = $data["timezone_id"];


        if (env("APP_ENV") == "local") {
            $personList["faceImage"] =  "https://stagingbackend.ideahrms.com/media/employee/profile_picture/1686330253.jpg";
        } else {
            $personList["faceImage"] =  asset('media/visitor/logo/' . $data['logo']);
        }

        $zoneDevices = Zone::with(["devices"])->find($data['zone_id']);

        return [
            "snList" => collect($zoneDevices->devices)->pluck("device_id"),
            "personList" => [$personList],
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Visitor  $visitor
     * @return \Illuminate\Http\Response
     */
    public function update(Update $request, $id)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $ext;
            $request->logo->move(public_path('media/visitor/logo/'), $fileName);
            $data['logo'] = $fileName;
        }

        try {

            $visitor = Visitor::whereId($id)->update($data);
            if (!$visitor) {
                return $this->response('Visitor cannot update.', null, false);
            }

            return $this->response('Visitor successfully updated.', null, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Visitor  $visitor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Visitor $visitor)
    {
        return $visitor->delete();
    }
}
