<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => 'required',
            'middle_name' => 'nullable',
            'last_name' => 'required',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'required',
            'age' => 'required|integer|min:20|max:60',
            'sex' => 'required',
            'address' => 'required',
            'job_title' => 'required',
            'department' => 'required',
            'status' => 'required',
            'date_of_service' => 'required|date',
            'salary' => 'required|numeric',
        ];
    }
    
    public function messages()
    {
        return [
            'age.integer' => 'The age must be an integer.',
            'age.min' => 'The age must be at least 20.',
            'age.max' => 'The age may not be greater than 60.',
        ];
    }
}
