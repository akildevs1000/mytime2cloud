<?php

namespace App\Http\Requests\TradeLicense;

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
        return [
            "license_no" => "required|min:3|max:20",
            "license_type" => "required|min:1|max:20",
            "emirate" => "required|min:3|max:20",
            "makeem_no" => "required",
            "manager" => "required",
            "issue_date" => "required",
            "expiry_date" => "required",

            // "company_id" => "required",

        ];
    }
}
