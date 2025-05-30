<?php

namespace App\Http\Requests\ReportNotification;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\failedValidationWithName;


class StoreRequest extends FormRequest
{
    use failedValidationWithName; // gives response when validation failed

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $arr = [
            'automation' => 'nullable',
            'subject' => 'required',
            // 'body' => 'nullable',
            'day' => 'nullable',
            'date' => 'nullable',
            'company_id' => 'required',
            'branch_id' => 'required',
            'frequency' => 'required',
            'time' => 'required',
            'reports' => 'array|min:1|max:5',
            'mediums' => 'array|min:1',
            'managers' => 'array|min:1',
            // 'tos' => 'array|min:1',
            // 'ccs' => 'array|nullable',
            // 'bccs' => 'array|nullable',
        ];

        // if weekly or monthly
        if ($this->frequency == "Weekly") {

            if ($this->type !== "access_control") {
                $arr['day'] = "required";
            } else {
                $arr['days'] = "required";
                $arr['from_time'] = "required";
                $arr['to_time'] = "required";
                $arr['time'] = "nullable";
            }
        }

        if ($this->frequency == "Monthly") {
            $arr['date'] = "required";
        }

        return $arr;
    }

    public function messages()
    {
        return [
            'company_id.required' => 'The company field is required',
            'reports.min' => 'Atleast 1 Report must be selected',
            'mediums.min' => 'Atleast 1 Medium must be selected',
            'tos.min' => 'Atleast 1 Email must be selected',
            'managers.min' => 'Atleast 1 Manager must be selected',
        ];
    }
}
