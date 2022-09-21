<?php

namespace App\Http\Requests\Company;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateRequest extends FormRequest
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

        if ($this->logo_only == 1) {

            return [
                'logo' => ['image', 'mimes:jpeg,png,jpg,svg', 'max:2048', 'sometimes', 'nullable'],
            ];
        }

        return [
            'member_from' => ['required', 'date'],
            'expiry' => ['required', 'date'],
            'max_branches' => ['required', 'integer'],
            'max_employee' => ['required', 'integer'],
            'max_devices' => ['required', 'integer'],
            'logo' => ['image', 'mimes:jpeg,png,jpg,svg', 'max:2048', 'sometimes', 'nullable'],
            'mol_id' => ['required', 'max:15'], 'min:2',
            'p_o_box_no' => ['required', 'max:15', 'min:2'],

        ];

    }

    public function setContactFields()
    {

        return [
            'name' => $this->contact_name,
            'number' => $this->contact_no,
            'position' => $this->contact_position,
            'whatsapp' => $this->contact_whatsapp,
        ];

    }

    public function setUserFields()
    {
        return [
            'name' => $this->user_name,
            'email' => $this->email,
        ];

    }
}
