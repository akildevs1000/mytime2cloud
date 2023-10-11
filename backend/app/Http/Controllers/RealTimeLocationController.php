<?php

namespace App\Http\Controllers;

use App\Models\RealTimeLocation;
use Illuminate\Http\Request;
use App\Http\Requests\RealTimeLocation\StoreRequest;

class RealTimeLocationController extends Controller
{
    public function index(Request $request)
    {
        $model = RealTimeLocation::query();
        $model->where("company_id", $request->company_id);
        $model->where("UserID", $request->UserID);
        $model->where("date", $request->date ?? date("Y-m-d"));
        return $model->paginate($request->per_page ?? 100);
    }

    public function store(StoreRequest $request)
    {
        try {
            $data = $request->validated();
            $data["date"] = date("Y-m-d");
            $data["datetime"] = date("Y-m-d H:i:s");
            RealTimeLocation::insert($data);
            return $this->response('Realtime location added.', null, true);
        } catch (\Throwable $th) {
            return $this->response('Realtime location cannot add.', null, false);
        }
    }
}
