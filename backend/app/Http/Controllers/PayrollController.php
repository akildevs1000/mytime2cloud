<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use Illuminate\Http\Request;
use App\Http\Requests\Payroll\StoreRequest;
use App\Http\Requests\Payroll\UpdateRequest;

class PayrollController extends Controller
{
    public function store(StoreRequest $request, Payroll $model)
    {   
        $data = $request->validated();
        
        $where = ["company_id" => $data['company_id'], "employee_id" => $data['employee_id']];

        try {
            $record = $model->updateOrCreate($where, $data);

            if ($record) {
                return $this->response('Payroll successfully added.', $record, true);
            } else {
                return $this->response('Payroll cannot add.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show(Payroll $model, Request $request,$id)
    {
        $where = ["company_id" => $request->company_id, "employee_id" => $id];

        return $model->where($where)->first();
    }
}
