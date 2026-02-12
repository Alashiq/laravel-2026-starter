<?php

namespace App\Features\Admin\v1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule; // استيراد Rule ضروري

class CatererProductStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true; // الصلاحيات يتم التحقق منها في المسار
    }

// ...
public function rules()
{
    return [
        'caterer_id' => 'required|integer|exists:caterers,id',
        'product_id' => [
            'required',
            'integer',
            'exists:products,id',
            Rule::unique('caterer_products')->where(function ($query) {
                // استخدام caterer_id من الإدخال الحالي
                return $query->where('caterer_id', $this->input('caterer_id'));
            }),
        ],
        'price' => 'required|numeric|min:0',
        'is_available' => 'required|boolean',
    ];
}
// ...


    public function messages()
    {
        return [
            'product_id.required' => 'يجب اختيار المنتج.',
            'product_id.exists'   => 'المنتج المختار غير موجود.',
            'product_id.unique'   => 'هذا المنتج تم إضافته بالفعل لهذا المتعهد.',
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
