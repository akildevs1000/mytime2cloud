<?php

namespace App\Http\Controllers;

use App\Models\Roster;
use Illuminate\Http\Request;
use App\Http\Requests\Roster\StoreRequest;
use App\Http\Requests\Roster\UpdateRequest;
use App\Models\ScheduleEmployee;
use App\Models\Shift;

class RosterController extends Controller
{
    public function index(Request $request, Roster $model)
    {
        try {
            return $model
                ->where('company_id', $request->company_id)
                ->orderBy('id', 'desc')
                ->paginate($request->per_page ?? 20);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function store(StoreRequest $request)
    {
        try {
            $json = [];
            $days = $request->days;
            $shift_ids = $request->shift_ids;
            $shift_names = $request->shift_names;

            for ($i = 0; $i < count($days); $i++) {
                $shift =  Shift::find($shift_ids[$i]);
                $json[] = [
                    "day" => $days[$i],
                    "shift_id" => $shift_ids[$i],
                    "shift_name" => $shift_names[$i],
                    "time" => isset($shift) ? ($shift->on_duty_time . " - " . $shift->off_duty_time) : "---",
                ];
            }

            $created =   Roster::create([
                "json" => $json,
                "name" => $request->name,
                "company_id" => $request->company_id
            ]);

            if ($created) {
                return $this->response('Schedule successfully added.', $created, true);
            } else {
                return $this->response('Schedule cannot add.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function storeScheduleArrange(Request $request)
    {
        try {
            $arr = [];
            $empIds = $request->employee_ids;
            $schedules = $request->schedules;

            foreach ($empIds as $empId) {
                foreach ($schedules as $schedule) {
                    $arr = [
                        'roster_id' => $schedule['schedule_id'],
                        'employee_id' => $empId,
                        'from_date' => $schedule['from_date'],
                        'to_date' => $schedule['to_date'],
                        'isOverTime' => $schedule['is_over_time'],
                        'company_id' => $request->company_id,
                    ];
                    $created =   ScheduleEmployee::create($arr);
                }
            }
            // $created =   ScheduleEmployee::insert($arr);

            if ($created) {
                return $this->response('Schedule successfully added.', $created, true);
            } else {
                return $this->response('Schedule cannot add.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update_old(UpdateRequest $request, Roster $roster)
    {
        $json = [];
        $days = $request->days;
        $shift_ids = $request->shift_ids;
        $shift_names = $request->shift_names;

        for ($i = 0; $i < count($days); $i++) {
            $shift =  Shift::find($shift_ids[$i]);
            $json[] = [
                "day" => $days[$i],
                "shift_id" => $shift_ids[$i],
                "shift_name" => $shift_names[$i],
                "time" => isset($shift) ? ($shift->on_duty_time . " - " . $shift->off_duty_time) : "---",
            ];
        }

        $update = $roster->update([
            "json" => $json,
            "name" => $request->name,
            "company_id" => $request->company_id
        ]);

        if ($update) {
            return $this->response('Schedule successfully update.', $update, true);
        } else {
            return $this->response('Schedule cannot add.', null, false);
        }
    }

    public function update(UpdateRequest $request, Roster $roster)
    {
        $data = $request->json;
        $arr = [];
        foreach ($data as $data) {
            $shift =  Shift::find($data['shift_id']);
            $arr[] = [
                "day" => $data['day'],
                "shift_id" => $data['shift_id'],
                "shift_name" => $shift->name ?? '',
                "time" => isset($shift) ? ($shift->on_duty_time . " - " . $shift->off_duty_time) : "---",
            ];
        }
        $update = $roster->update([
            "json" => $arr,
            "name" => $request->name,
            "company_id" => $request->company_id
        ]);

        if ($update) {
            return $this->response('Schedule successfully update.', $update, true);
        } else {
            return $this->response('Schedule cannot add.', null, false);
        }
    }

    public function destroy(Roster $roster)
    {
        try {
            $record = $roster->delete();
            if ($record) {
                return $this->response('Schedule successfully deleted.', null, true);
            } else {
                return $this->response('Schedule cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getRosterList(Request $request)
    {
        try {
            $model =  Roster::query();
            return $model
                ->where('company_id', $request->company_id)
                ->orderBy('id', 'ASC')
                ->get(['id as schedule_id', 'name']);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getRosterByEmployee(Request $request, $id)
    {
        try {
            $model =  ScheduleEmployee::query();
            $data = $model
                ->whereCompanyId($request->company_id)
                ->whereEmployeeId($id)
                ->withOut(["shift", "shift_type", "logs", "first_log", "last_log"])
                // ->with('roster')
                ->get(['id', 'employee_id', 'isOverTime as is_over_time', 'roster_id as schedule_id', 'from_date', 'to_date'])
                ->makeHidden(['employee_id', 'show_from_date', 'show_to_date'])
                ->groupBy('employee_id');
            return $data[$id];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function scheduleUpdateByEmployee(Request $request, $id)
    {
        try {
            $schedules =   $request->schedules;
            foreach ($schedules as $schedule) {
                $update = ScheduleEmployee::find($schedule['id'])->update([
                    "isOverTime" => $schedule['is_over_time'],
                    "roster_id" => $schedule['schedule_id'],
                    "from_date" => $schedule['from_date'],
                    "to_date" => $schedule['to_date']
                ]);
            }
            return $this->response('Schedule successfully Updated.', null, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}