<?php

namespace App\Http\Controllers;

use App\Models\HostCompany;
use App\Models\Notification as NotificationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;


class NotificationsController extends Controller
{
    public static function toSend($model, $notificationClass, $object)
    {
        Notification::send($model, new $notificationClass($object));
    }

    public function test()
    {
        $host = HostCompany::where("id", request("host_company_id"))->with("employee:id,user_id,employee_id")->first();

        return NotificationModel::create([
            "data" => "Test",
            "action" => "Registration",
            "model" => "visitor",
            "user_id" => $host->employee->user_id ?? 0,
            "company_id" => 2,
        ]);

        // Notification::send($model, new $notificationClass($object));
    }



    public function index(Request $request)
    {
        return $this->getDefaultModelSetting($request)->where("read_at", null)->paginate($request->input("per_page", 100));
    }

    public function unread(Request $request)
    {
        return $this->getDefaultModelSetting($request)->where("read_at", null)->get();
    }

    public function read(Request $request)
    {
        return $this->getDefaultModelSetting($request)->whereNot("read_at", null)->get();
    }


    public function getDefaultModelSetting(Request $request)
    {

        $model = NotificationModel::query();

        $model->where("company_id", $request->input("company_id"));

        $model->when($request->filled("user_id"), fn ($q) => $q->where("user_id", $request->user_id));

        $model->orderByDesc("id");

        return $model;
    }
}
