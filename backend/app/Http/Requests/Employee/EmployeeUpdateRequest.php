<?php

namespace App\Http\Requests\Employee;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class EmployeeUpdateRequest extends FormRequest
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
            'first_name' => ['required', 'min:3', 'max:100'],
            'last_name' => ['required', 'min:3', 'max:100'],
            'email' => 'required|min:3|max:191'/*|unique:companies,email,'*//*.$this->company->id*/,
            // 'profile_picture' => ['image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
            'password' => [
                'confirmed',
                'string',
                'min:6',             // must be at least 10 characters in length
                'max:25',             // must be maximum 25 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],

        ];
    }

    public function messages()
    {
        return [
            'password.regex' => "You password must contain numeric, special(!@#$%) characters",
        ];
    }

    public function setContactFields()
    {

        return [
            'name'  => $this->contact_name,
            'number' => $this->contact_no,
            'position' => $this->contact_position,
            'whatsapp' => $this->contact_whatsapp
        ];
    }

    public function setUserFields()
    {
        return [
            'name'  => $this->user_name,
            'email' => $this->email,
        ];
    }
}
