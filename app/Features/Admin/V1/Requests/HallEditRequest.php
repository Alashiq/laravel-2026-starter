<?php

namespace App\Features\Admin\v1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class HallEditRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name'        => 'required|string|min:3|max:50',
            'city_id'     => 'required|integer|exists:cities,id',
            'address'     => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'whatsapp'    => 'nullable|string|max:20',
            'supervisor_phone' => 'nullable|string|max:20',
            'tables'      => 'nullable|integer|min:0',
            'chairs'      => 'nullable|integer|min:0',
            'capacity'    => 'nullable|integer|min:0',
            'price_morning' => 'nullable|numeric|min:0',
            'price_evening' => 'nullable|numeric|min:0',
            'price_full_day' => 'nullable|numeric|min:0',
            'deposit'     => 'nullable|numeric|min:0',
            'cancellation_policy' => 'nullable|string|max:500',
            'services_text' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:500',
            'final_payment_days' => 'nullable|integer|min:0',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'اسم القاعة مطلوب',
            'name.min' => 'يجب أن يكون اسم القاعة 3 أحرف على الأقل',
            'name.max' => 'اسم القاعة يجب أن يكون أقل من 50 حرف',
            'name.unique' => 'اسم القاعة مسجل مسبقًا',

            'city_id.required' => 'يجب اختيار المدينة',
            'city_id.exists'   => 'المدينة المختارة غير موجودة',

            'logo.required' => 'شعار القاعة مطلوب',
            'logo.mimes' => 'صيغة الشعار يجب أن تكون JPG أو JPEG أو PNG',
            'logo.max' => 'حجم الشعار يجب ألا يتجاوز 2MB',

            'tables.integer' => 'عدد الطاولات يجب أن يكون رقمًا صحيحًا',
            'chairs.integer' => 'عدد الكراسي يجب أن يكون رقمًا صحيحًا',
            'capacity.integer' => 'السعة الكلية يجب أن تكون رقمًا صحيحًا',

            'price_morning.numeric' => 'سعر الصباح يجب أن يكون رقمًا',
            'price_evening.numeric' => 'سعر المساء يجب أن يكون رقمًا',
            'price_full_day.numeric' => 'سعر اليوم الكامل يجب أن يكون رقمًا',

            'deposit.numeric' => 'قيمة العربون يجب أن تكون رقمًا',

            'latitude.numeric' => 'خط العرض يجب أن يكون رقمًا',
            'longitude.numeric' => 'خط الطول يجب أن يكون رقمًا',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400)
        );
    }
}
