<?php

namespace App\Http\Requests\Tanent;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
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
            "full_name" => "required|min:3|max:20",
            "phone_number" => "required|min:10|max:20",
            "floor_id" => "required",
            "room_id" => "required",
            "start_date" => "required",
            "end_date" => "required",
            "profile_picture" => "nullable",
            "attachment" => "nullable",
            "company_id" => "nullable",
        ];
    }
}
