<?php

namespace App\Http\Requests\Department;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
{
    use failedValidationWithName;
   
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|min:4|max:50',
            'company_id' => 'required',
        ];
    }
}
