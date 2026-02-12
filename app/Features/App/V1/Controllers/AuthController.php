<?php

namespace App\Features\App\v1\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Features\Admin\v1\Resources\AdminResource;
use App\Features\App\v1\Models\City;
use App\Features\App\v1\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{

    // Login User
    public function login(Request $request)
    {


        // Validation
        if (
            Validator::make($request->all(), [
                'phone' => 'required|numeric|digits:10|starts_with:09',
            ])->fails()
        ) {
            return $this->badRequest("رقم الهاتف يجب ان يكون عبارة عن رقم ومكون من 10 أرقام");
        }


        //  Create OTP
        $user = User::where('phone', $request->phone)->first();
        if ($request->phone == "0926503011")
            $smsCode = "123456";
        else if ($request->phone == "0900000000")
            $smsCode = "654321";
        else
            $smsCode = rand(100000, 999999);


        if (!$user) {
            $lastNotificationId = 0;
            // $lastNotificationId = UserNotification::orderBy('id', 'desc')->value('id');


            User::create([
                'first_name' => 'الإسم',
                'last_name' => 'اللقب',
                'phone' => $request->phone,
                'city_id' => 1,
                'last_notification' => $lastNotificationId ?? 1,
                'otp' => Hash::make($smsCode),
                'status' => 0,
            ]);


            // $this->smsService->sendOtp($request->phone, $smsCode);

            return response()->json(['success' => true, 'message' => 'تم إرسال رمز التفعيل إلى هاتفك'], 200);
        } else {
            if ($user->status == 3)
                return $this->badRequest('رقم الهاتف محظور ولا يمكن استخدامه');

            if ($user->ban_expires_at >= Carbon::now() && $user->ban_expires_at != null)
                return $this->badRequest('لا يمكنك الدخول الأن حاول في وقت اخر');


            if ($user->login_attempts == 1 && Carbon::parse($user->attempts_at)->addMinutes(1) > now()) {
                return $this->badRequest('حاول بعد دقيقة من الأن');
            }

            if ($user->login_attempts == 2 && Carbon::parse($user->attempts_at)->addMinutes(5) > now()) {
                return $this->badRequest('حاول بعد 5 دقائق من الأن');
            }


            if ($user->login_attempts == 3 && Carbon::parse($user->attempts_at)->addMinutes(30) > now()) {
                return $this->badRequest('حاول بعد 30 دقيقة من الأن');
            }


            if ($user->login_attempts >= 4 && Carbon::parse($user->attempts_at)->addMinutes(240) > now()) {
                return $this->badRequest('لقد قمت بالعديد من محاولات الدخول');
            }

            if (Carbon::parse($user->attempts_at)->addMinutes(240) > now())
                $attemp_count = $user->login_attempts + 1;
            else
                $attemp_count = 1;

            $user->login_attempts = $attemp_count;
            $user->attempts_at = Carbon::now();
            $user->otp = Hash::make($smsCode);
            $user->save();

            // if ($request->phone != "0926503011")
            //     $this->smsService->sendOtp($request->phone, $smsCode);

            return response()->json(['success' => true, 'message' => 'تم إرسال رمز التفعيل إلى هاتفك'], 200);
        }
    }





    // Activate User
    public function activate(Request $request)
    {
        if (
            Validator::make($request->all(), [
                'phone' => 'required|numeric|digits:10|starts_with:09',
            ])->fails()
        ) {
            return $this->badRequest("رقم الهاتف يجب ان يكون عبارة عن رقم ومكون من 10 أرقام");
        }
        if (
            Validator::make($request->all(), [
                'otp' => 'required|numeric|digits:6',
            ])->fails()
        ) {
            return $this->badRequest("يجب عليك ادخال رمز تحقق صحيح من 6 أرقام");
        }


        $user = User::where('phone', $request->phone)->with('city')->first();
        if (!$user)
            return $this->badRequest('بيانات دخول غير صحيحة');

        if ($user->status == 3 || $user->ban_expires_at >= Carbon::now())
            return $this->badRequest('رقم الهاتف محظور');



        if ($user->otp_attempts == 4 && Carbon::parse($user->otp_attempts_at)->addMinutes(1) > now()) {
            return $this->badRequest('حاول بعد دقيقة من الأن');
        }

        if ($user->otp_attempts == 5 && Carbon::parse($user->otp_attempts_at)->addMinutes(5) > now()) {
            return $this->badRequest('حاول بعد 5 دقائق من الأن');
        }


        if ($user->otp_attempts == 6 && Carbon::parse($user->otp_attempts_at)->addMinutes(30) > now()) {
            return $this->badRequest('حاول بعد 30 دقيقة من الأن');
        }


        if ($user->otp_attempts >= 7 && Carbon::parse($user->otp_attempts_at)->addMinutes(240) > now()) {
            return $this->badRequest('لقد قمت بالعديد من محاولات الدخول');
        }


        if (Carbon::parse($user->otp_attempts_at)->addMinutes(240) > now())
            $otp_attemp_count = $user->otp_attempts + 1;
        else
            $otp_attemp_count = 1;

        // if ($user->otp != $request->otp) {

        if (!Hash::check($request->otp, $user->otp)) {
            $user->otp_attempts = $otp_attemp_count;
            $user->otp_attempts_at = Carbon::now();
            $user->save();
            return $this->unauthorized('رمز التفعيل الذي ادختله غير صحيح');
        }


        // Create OTP
        $smsCode = rand(100000, 999999);

        // Update OTP AND DeviceToken
        if ($user->status == 0)
            $user->status = 1;

        $user->otp_attempts = 0;
        $user->otp = Hash::make($smsCode);
        $user->save();

        $notificationCount = 0;


        $cities = City::notDeleted()
            ->select('id', 'name') // خذ الحقول اللي تحتاجها فقط
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'مرحبا بك',
            'user' => [
                'firstname' => $user->first_name,
                'lastname' => $user->last_name,
                'phone' => $user->phone,
                'photo' => $user->photo,
                'token' => $user->createToken('app', ['role:user'])->plainTextToken,
                'point' => $user->point,
                'balance' => $user->balance,
                'notifications' => $notificationCount,

                'city' => $user->city->name,
                'city_id' => $user->city_id,

                'status' => $user->status,
            ],
            'cities' => $cities,

        ], 200);
    }


    //  Signup User

    public function signup(Request $request)
    {
        //  Vlidation
        if (
            Validator::make($request->all(), [
                'first_name' => 'required|string|min:2|max:20',
                'last_name' => 'required|string|min:2|max:20',
                'city_id' => 'required|exists:cities,id', // تأكد من وجود المدينة
            ])->fails()
        ) {
            return $this->badRequest("يجب عليك إدخال الإسم واللقب واختيار المدينة بشكل صحيح");
        }
        //  Load User
        $user = User::where('id', $request->user()->id)->first();
        if (!$user)
            return $this->badRequest('بيانات دخول غير صحيحة');


        // Check Status
        if ($user->status != 1)
            return $this->badRequest('هذا الحساب مسجل مسبقا او غير مفعل');

        // Update User
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->city_id = $request->city_id;
        $user->status = 2;
        $user->save();

        // جلب اسم المدينة
        $cityName = $user->city ? $user->city->name : null;


        // Return Response
        return response()->json([
            'success' => true,
            'message' => 'مرحبا بك',
            'user' => [
                'firstname' => $user->first_name,
                'lastname' => $user->last_name,
                'phone' => $user->phone,
                'photo' => $user->photo,
                'token' => $user->createToken('app', ['role:user'])->plainTextToken,
                'point' => $user->point,
                'balance' => $user->balance,

                'notifications' => 0,

                'city' => $cityName, // اسم المدينة هنا
                'city_id' => $user->city_id,

                'status' => $user->status,
            ]
        ], 200);
    }

    // Profile User

    public function profile(Request $request)
    {


        $user = User::with('city')->find($request->user()->id);

        if ($user->status == 3 || $user->ban_expires_at >= Carbon::now())
            return $this->unauthorized('هذا الحساب تم حظره');

        if ($user->status != 2 && $user->status != 1)
            return $this->unauthorized('هذا الحساب غير مفعل او تم حذفه');



        $user->updated_at = Carbon::now();

        $user->update();
        $user->touch();


        $user_id = $request->user()->id;
        $notificationCount = 0;

        if ($user->status == 1) {
            $cities = City::notDeleted()
                ->select('id', 'name') // خذ الحقول اللي تحتاجها فقط
                ->get();
        }


        return response()->json([
            'success' => true,
            'message' => 'مرحبا بك',
            'user' => [
                'firstname' => $user->first_name,
                'lastname' => $user->last_name,
                'phone' => $user->phone,
                'photo' => $user->photo,
                'token' => $request->bearerToken(),
                'point' => $user->point,
                'balance' => $user->balance,
                'notifications' => $notificationCount,

                'city' => $user->city->name,
                'city_id' => $user->city_id,

                'status' => $user->status,
            ],
            'cities' => $user->status == 1 ? $cities : [],

        ], 200);
    }



    // Logout User
    public function logout(Request $request)
    {
        $user = $request->user();
        if (!$user)
            return $this->unauthorized('إنتهت جلسة الدخول مسبقا');

        $user->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل خروجك بنجاح',
        ], 200);
    }

    // Change User Name

    public function name(Request $request)
    {

        if ($request->first_name || $request->last_name) {
            $request->user()->Update(
                request()->only(
                    "first_name",
                    "last_name"
                )
            );
            return response()->json([
                "success" => true,
                "message" => "تم تحديث الإسم بنجاح",
                "user" => $request->user(),
            ], 200);
        } else {
            return $this->badRequest('يجب عليك إدخال الإسم أو اللقب');
        }
    }


    // Change User City

    public function city(Request $request)
    {

        if ($request->city_id) {

            // تحقق أن المدينة موجودة فعلاً في جدول المدن
            $city = City::latest()
                ->notDeleted()
                ->where('id', $request->city_id)
                ->first();

            if (!$city) {
                return $this->badRequest('المدينة المختارة غير موجودة');
            }

            $request->user()->Update(
                request()->only(
                    "city_id",
                )
            );
            return $this->success('تم تحديث المدينة بنجاح');
        } else {
            return $this->badRequest('يجب عليك اختيار مدينة');
        }
    }


    // End of Controller
}
