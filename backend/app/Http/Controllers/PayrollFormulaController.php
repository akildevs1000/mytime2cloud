<?php

namespace App\Http\Controllers;

use App\Models\PayrollFormula;
use Illuminate\Http\Request;

use App\Http\Requests\PayrollFormula\StoreRequest;
use App\Http\Requests\PayrollFormula\UpdateRequest;


class PayrollFormulaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request, PayrollFormula $model)
    {
        $data = $request->validated();
        $where = ["company_id" => $data['company_id']];

        try {
            $record = $model->updateOrCreate($where, $data);

            if ($record) {
                return $this->response('Payroll formula successfully added.', $record, true);
            } else {
                return $this->response('Payroll formula cannot add.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PayrollFormula  $payrollFormula
     * @return \Illuminate\Http\Response
     */
    
    public function show(PayrollFormula $model, Request $request,$id)
    {
        $where = ["company_id" => $request->company_id, "employee_id" => $id];

        return $model->where($where)->first();
    }
}
