<?php

namespace App\Http\Requests\CompanyBranch;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    use failedValidationWithName;

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
            'branch_name' => ['required', 'min:3', 'max:20'],

            'licence_expiry' => ['required'],


            'location' => ['required', 'min:3', 'max:20'],
            'address' => ['required', 'min:3', 'max:200'],
            'po_box' => ['required', 'min:3', 'max:20'],

            'licence_issue_by_department' => ['required', 'min:3', 'max:20'],
            'licence_number' => ['required', 'min:3', 'max:20'],

            'telephone' => ['required', 'min:8', 'max:13'],
            'phone' => ['required', 'min:8', 'max:13'],

            'name' => 'required|min:3|max:20',
            'email' => 'required|min:3|max:191|email|unique:users',
            'password' => [
                'required',
                // 'string',
                // 'confirmed',
                'min:6', // must be at least 10 characters in length
                'max:25', // must be maximum 25 characters in length
                // 'regex:/[a-z]/', // must contain at least one lowercase letter
                // 'regex:/[A-Z]/', // must contain at least one uppercase letter
                // 'regex:/[0-9]/', // must contain at least one digit
                // 'regex:/[@$!%*#?&]/', // must contain a special character
            ],

            'profile_picture' => ['image', 'mimes:jpeg,png,jpg,svg', 'max:2048', 'sometimes', 'nullable'],

            'company_id' => ['required'],

        ];
    }
}
