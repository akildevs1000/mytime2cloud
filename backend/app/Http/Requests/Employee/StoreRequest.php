<?php

namespace App\Http\Requests\Employee;

use App\Http\Controllers\Controller;
use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    use failedValidationWithName;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $employee = [
            "employee_id" => $this->employee_id,
            "company_id" => $this->company_id
        ];

        $employeeDevice = [
            "system_user_id" => $this->system_user_id,
            "company_id" => $this->company_id
        ];
        $controller = new Controller;

        return [
            'company_id' => ['required'],
            'employee_id' => ['required', $controller->uniqueRecord("employees", $employee)],
            'system_user_id' => ['required', $controller->uniqueRecord("employees", $employeeDevice)],
            'display_name' => ['required', 'min:3', 'max:10'],
            'title' => ['required'],
            'status' => ['nullable'],
            'email' => 'nullable|min:3|max:191|unique:users',
            'profile_picture' => ['image', 'mimes:jpeg,png,jpg,svg', 'max:2048', 'sometimes', 'nullable'],
        ];
    }
}
