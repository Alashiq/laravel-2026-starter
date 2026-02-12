<?php

namespace App\Features\Admin\v1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;


class CatererEditRequest extends FormRequest
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
        $catererId = $this->route('caterer'); // أو 'id' حسب اسم البارامتر في ملف الـ routes

        return [
            'name'                      => ['required', 'string', 'min:3', 'max:100', Rule::unique('caterers')->ignore($catererId)],
            'city_id'                   => 'required|integer|exists:cities,id',
            'logo'                      => 'image|mimes:jpg,jpeg,png|max:2048',
            'cover_photo'               => 'image|mimes:jpg,jpeg,png|max:4096',
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
            'max_guests'                => 'required|integer|min:0|gte:min_guests',
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
        // يمكنك استخدام نفس الرسائل من ملف الإنشاء
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
