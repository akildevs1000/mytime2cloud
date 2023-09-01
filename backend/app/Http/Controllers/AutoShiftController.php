<?php

namespace App\Http\Controllers;

use App\Http\Requests\AutoShift\StoreRequest;
use App\Models\AutoShift;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutoShiftController extends Controller
{
    public function index(Request $request)
    {
        $company_id = $request->company_id;

        return Shift::where("company_id", $company_id)->whereHas("autoshift", function ($q) use ($company_id) {
            $q->where('company_id', $company_id);
        })->get();
    }

    public function get_shifts_by_autoshift(Request $request)
    {
        $company_id = $request->company_id;
        return Shift::where("company_id", $request->company_id)->whereHas("autoshift", function ($q) use ($company_id) {
            $q->where('company_id', $company_id);
        })->get();
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        $company_id = $data["company_id"];

        $arr = [];

        foreach ($data["shift_ids"] as $shift_id) {
            $arr[] = ['shift_id' => $shift_id, 'company_id' => $company_id];
        }

        try {
            $model = AutoShift::query();
            $model->where("company_id", $request->company_id);
            $model->delete();
            $record = $model->insert($arr);
            if ($record) {
                return $this->response('Shifts has been assign to assigned to Auto Shift', null, true);
            } else {
                return $this->response('Shifts cannot assigned to Auto Shift', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
