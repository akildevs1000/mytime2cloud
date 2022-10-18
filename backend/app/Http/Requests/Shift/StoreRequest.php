<?php

namespace App\Http\Requests\Shift;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'name' => ['required', Rule::unique('shifts')],
            // 'days' => ["required", "array", "min:1"],
            'overtime' => ["required"],
            'shift_type_id' => ["required"],
            'company_id' => ["required"],
            'working_hours' => ['nullable'],

            'on_duty_time' => 'required',
            'off_duty_time' => 'required',
            'late_time' => 'required',
            'early_time' => 'required',
            'beginning_in' => 'required',
            'ending_in' => 'required',
            'beginning_out' => 'required',
            'ending_out' => 'required',
            'absent_min_in' => 'required',
            'absent_min_out' => 'required',
        ];
    }
}
