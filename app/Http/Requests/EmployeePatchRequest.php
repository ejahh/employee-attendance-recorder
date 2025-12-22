<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeePatchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => 'sometimes|required',
            'middle_name' => 'sometimes|nullable',
            'last_name' => 'sometimes|required',
            'date_of_birth' => 'sometimes|required|date',
            'place_of_birth' => 'sometimes|required',
            'age' => 'sometimes|required|integer',
            'sex' => 'sometimes|required',
            'address' => 'sometimes|required',
            'job_title' => 'sometimes|required',
            'department' => 'sometimes|required',
            'status' => 'sometimes|required',
            'date_of_service' => 'sometimes|required|date',
            'salary' => 'sometimes|required|numeric',
        ];
    }
}
