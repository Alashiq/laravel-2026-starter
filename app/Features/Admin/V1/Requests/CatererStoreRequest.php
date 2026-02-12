<?php

namespace App\Features\Admin\v1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CatererStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // قم بتعيينها إلى true للسماح بالطلب. يمكنك إضافة منطق صلاحيات هنا إذا احتجت.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'                      => 'required|string|min:3|max:100|unique:caterers,name',
            'city_id'                   => 'required|integer|exists:cities,id',
            'logo'                      => 'required|image|mimes:jpg,jpeg,png|max:2048', // صورة اختيارية
            'cover_photo'               => 'required|image|mimes:jpg,jpeg,png|max:4096', // صورة اختيارية بحجم أكبر
            'description'               => 'required|string|max:1000',
            'address'                   => 'required|string|max:255',
            'phone'                     => 'required|string|max:20',
            'whatsapp'                  => 'required|string|max:20',
            'latitude'                  => 'required|numeric|between:-90,90',
            'longitude'                 => 'required|numeric|between:-180,180',
            'min_booking_days_before'   => 'required|integer|min:0',
            'min_order_value'           => 'required|numeric|min:0',
            'deposit_percentage'        => 'required|numeric|min:0|max:100',
            'cancellation_policy'       => 'required|string|max:1000',
            'min_guests'                => 'required|integer|min:0',
            'max_guests'                => 'required|integer|min:0|gte:min_guests', // يجب أن يكون أكبر من أو يساوي الحد الأدنى للضيوف
            'offers_tasting_sessions'   => 'required|boolean',
            'offers_tasting_booking'    => 'required|boolean',
            'tasting_policy'            => 'required|string|max:1000',
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
            'name.required' => 'اسم متعهد التموين مطلوب.',
            'name.unique'   => 'هذا الاسم مسجل مسبقًا.',
            'city_id.required' => 'يجب اختيار المدينة.',
            'city_id.exists'   => 'المدينة المختارة غير صحيحة.',
            'logo.image'    => 'يجب أن يكون الشعار ملف صورة.',
            'logo.mimes'    => 'يجب أن يكون امتداد الشعار (jpg, jpeg, png).',
            'logo.max'      => 'يجب ألا يتجاوز حجم الشعار 2MB.',
            'cover_photo.max' => 'يجب ألا يتجاوز حجم صورة الغلاف 4MB.',
            'max_guests.gte' => 'الحد الأقصى للضيوف يجب أن يكون أكبر من أو يساوي الحد الأدنى.',
            'deposit_percentage.max' => 'نسبة العربون لا يمكن أن تتجاوز 100%.',
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
            ], 400)
        );
    }
}
