<?php

namespace App\Http\Requests\Master\Invoice;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class EmailRequest extends FormRequest
{
    use failedValidationWithName;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email_message' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
