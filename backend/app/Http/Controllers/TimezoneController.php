<?php

namespace App\Http\Controllers;

use App\Http\Requests\Timezone\StoreRequest;
use App\Http\Requests\Timezone\UpdateRequest;
use App\Models\Timezone;
use App\Models\TimezoneDefaultJson;
use Illuminate\Http\Request;

class TimezoneController extends Controller
{
    public function index(Request $request)
    {
        return Timezone::where('company_id', $request->company_id)->where("is_default", false)->paginate($request->per_page ?? 100);
    }

    public function getTimezoneJson(Request $request)
    {
        return Timezone::where('company_id', $request->company_id)->pluck("json");
    }

    public function store(StoreRequest $request)
    {

        $data = $request->validated();
        $data["scheduled_days"] = $this->processSchedule($data["scheduled_days"], false);
        $data["json"] = $this->processJson($request->timezone_id, $data["interval"], false);

        try {
            $record = Timezone::create($data);
            return $this->response('Timezone Successfully created.', $record, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show(Timezone $timezone)
    {
        return $timezone->find();
    }

    public function update(UpdateRequest $request, Timezone $timezone)
    {
        try {

            $record = $timezone->update($request->validated());

            if ($record) {
                return $this->response('Timezone Successfully updated.', $record, true);
            } else {
                return $this->response('Timezone cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(Timezone $timezone)
    {
        try {

            $record = $timezone->delete();

            if ($record) {
                return $this->response('Timezone Successfully deleted.', $record, true);
            } else {
                return $this->response('Timezone cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function processIntervals($intervals, $isDefault)
    {
        $arr = [];

        foreach ($intervals as $key => $interval) {
            $arr[] =  [
                "dayWeek" => $key,
                "timeSegmentList" => $this->processTimeFrames($interval, $isDefault),
            ];
        }
        return $arr;
    }
    public function processTimeFrames($interval, $isDefault = false)
    {
        $arr = [];

        for ($i = 1; $i <= 8; $i++) {
            if (isset($interval['interval' . $i]) && count($interval['interval' . $i]) > 0 && !$isDefault) {
                $arr[] = $interval['interval' . $i];
            } else {
                $arr[] = ["begin" => "00:00", "end" => "00:00"];
            }
        }
        return $arr;
    }
    public function storeTimezoneDefaultJson(Request $request)
    {
        foreach (range(1, 64) as $iteration) {
            TimezoneDefaultJson::create([
                "index" => $iteration,
                "dayTimeList" => $request->dayTimeList
            ]);
        }
        return TimezoneDefaultJson::count();
    }

    public function processSchedule($schedules, $isDefault)
    {
        $arr = [];
        foreach ($schedules as $d) {
            $arr[] =  [
                "day" => $d["day"],
                "isScheduled" => $isDefault ? false : $d["isScheduled"],
                "dayWeek" => $d["dayWeek"],
            ];
        }
        return  $arr;
    }
    public function processJson($timezone_id, $interval, $isDefault)
    {
        return [
            "index" => $timezone_id,
            "dayTimeList" => $this->processIntervals($interval, $isDefault),
        ];
    }
}
