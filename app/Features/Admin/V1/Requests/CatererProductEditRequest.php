<?php

namespace App\Features\Admin\v1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CatererProductEditRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // الحقول القابلة للتعديل فقط
            'price'        => 'required|numeric|min:0',
            'is_available' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'price.required'      => 'يجب إدخال سعر المنتج.',
            'price.numeric'       => 'يجب أن يكون السعر رقمًا.',
            'is_available.required' => 'يجب تحديد حالة توفر المنتج.',
            'is_available.boolean'  => 'حالة التوفر يجب أن تكون قيمة منطقية (true/false).',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422)
        );
    }
}
