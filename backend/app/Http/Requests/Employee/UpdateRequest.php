<?php

namespace App\Http\Requests\Employee;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        return [
            'company_id' => ['required'],
            'employee_id' => ['required'],
            'system_user_id' => ['required'],
            'display_name' => ['required', 'min:3', 'max:10'],
            'first_name' => ['nullable', 'min:3', 'max:10'],
            'last_name' => ['nullable', 'min:3', 'max:10'],
            'title' => ['required'],
            'status' => ['nullable'],
            'department_id' => ['nullable'],
            'sub_department_id' => ['nullable'],
            'designation_id' => ['nullable'],
            'role_id' => ['nullable'],
            'employee_id' => ['required'],

            'profile_picture' => ['image', 'mimes:jpeg,png,jpg,svg', 'max:2048', 'sometimes', 'nullable'],
        ];
    }
}