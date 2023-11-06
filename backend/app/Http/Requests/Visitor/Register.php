<?php

namespace App\Http\Requests\Visitor;

use Illuminate\Foundation\Http\FormRequest;

class Register extends FormRequest
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
        $validations = [];

        // if ($this->logo) {
        // $validations['logo'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
        // }

        $validations['visit_from'] = 'required|date';
        $validations['visit_to'] = 'required|date';
        $validations['purpose_id'] = 'required';
        $validations['first_name'] = 'required|string|max:255';
        $validations['last_name'] = 'required|string|max:255';
        $validations['gender'] = 'required|in:Male,Female';
        $validations['phone_number'] = 'required|string|max:255';
        $validations['email'] = 'nullable|email|max:255';
        $validations['visitor_company_name'] = 'required|string|max:255';
        $validations['id_type'] = 'required';
        $validations['id_number'] = 'required|string|max:255';
        $validations['id_copy'] = 'required';
        $validations['host_company_id'] = 'required';
        $validations['company_id'] = 'required';
        $validations['logo'] = 'required';
        $validations['date'] = 'required|date';

        return $validations;
    }

    public function messages()
    {
        return [
            'logo.required' => 'The Photo field is required',
        ];
    }
}
