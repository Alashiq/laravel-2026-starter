<?php

namespace App\Features\Admin\v1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // نسمح بالطلب، ونتحقق من الصلاحيات في المسار
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'          => 'required|string|min:3|max:150',
            'description'   => 'nullable|string|max:2000',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // صورة اختيارية
            'ingredients'   => 'nullable|string|max:1000',
            'keywords'      => 'nullable|string|max:500',
            'caterer_id'    => 'nullable|integer|exists:caterers,id', // يمكن أن يكون المنتج عامًا
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'name.required' => 'اسم المنتج مطلوب.',
            'name.min'      => 'يجب أن يكون اسم المنتج 3 أحرف على الأقل.',
            'name.max'      => 'يجب ألا يتجاوز اسم المنتج 150 حرفًا.',
            'photo.image'   => 'يجب أن يكون الملف المرفق صورة.',
            'photo.mimes'   => 'يجب أن يكون امتداد الصورة (jpg, jpeg, png, webp).',
            'photo.max'     => 'يجب ألا يتجاوز حجم الصورة 2MB.',
            'caterer_id.exists' => 'متعهد التموين المحدد غير موجود أو غير نشط.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422) // 422 Unprocessable Entity هو الرمز الأنسب لأخطاء التحقق
        );
    }
}
