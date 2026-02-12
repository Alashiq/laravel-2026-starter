<?php

namespace App\Features\Admin\v1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class CityStoreRequest extends FormRequest
{

    public function rules()
    {
        return [
            'name' => 'required|max:40|min:3|unique:cities',
            // Loingitude and Latitude  added 
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'description' => 'required|max:40|min:3',

        ];
    }

    public function messages()
    {

        return [
            'name.required' => 'يجب عليك ادخال الإسم بشكل صحيح',
            'name.max' => 'يجب ان يكون الاسم اقل من 40 حرف',
            'name.min' => 'يجب ان يكون الاسم 3 أحرف على الاقل',
            'name.unique' => 'هذا الإسم محجوز مسبقا',
            'longitude.required' => 'يجب عليك ادخال خط الطول بشكل صحيح',
            'longitude.numeric' => 'يجب ان يكون خط الطول رقما',
            'latitude.required' => 'يجب عليك ادخال خط العرض بشكل صحيح',
            'latitude.numeric' => 'يجب ان يكون خط العرض رقما',
            'description.required' => 'يجب عليك ادخال الوصف بشكل صحيح',
            'description.max' => 'يجب ان يكون الوصف اقل من 40 حرف',
            'description.min' => 'يجب ان يكون الوصف 3 أحرف على الاقل',  
        ];

    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400)
        );



    }


}