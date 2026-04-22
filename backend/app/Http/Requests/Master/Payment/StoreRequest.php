<?php

namespace App\Http\Requests\Master\Payment;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    use failedValidationWithName;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'company_id'    => ['required', 'integer', 'exists:companies,id'],
            'amount'        => ['required', 'numeric', 'gt:0'],
            'description'   => ['required', 'string', 'min:3', 'max:500'],
            'tax_percent'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_date'  => ['required', 'date'],
            'method'        => ['required', 'in:cash,bank,card,cheque,online,other'],
            'reference_no'  => ['nullable', 'string', 'max:64'],
            'notes'         => ['nullable', 'string', 'max:1000'],
            'email_now'     => ['sometimes', 'boolean'],
            'email_message' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
