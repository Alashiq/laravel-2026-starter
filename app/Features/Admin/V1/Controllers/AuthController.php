<?php

namespace App\Features\Admin\v1\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Features\Admin\v1\Resources\AdminResource;
use App\Features\Admin\v1\Models\Admin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{

    // Login Admin
    public function login(Request $request)
    {
        $admin = Admin::with('role')->where('phone', $request->phone)->first();

        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'رقم الهاتف غير مسجل'], 400);
        }

        if ($admin->locked_until && now()->lessThan($admin->locked_until)) {
            return response()->json([
                'success' => false,
                'message' => 'تم قفل الحساب مؤقتًا بسبب محاولات دخول متكررة. حاول لاحقًا.',
                'locked_until' => $admin->locked_until
            ], 400);
        }

        if (!Hash::check($request->password, $admin->password)) {
            $admin->login_attempts += 1;
            $admin->attempts_at = now();

            if ($admin->login_attempts >= 5) {
                $admin->locked_until = now()->addMinutes(15);
            }

            $admin->save();

            return response()->json(['success' => false, 'message' => 'كلمة المرور غير صحيحة'], 400);
        }

        if ($admin->status === 'not_active') {
            return response()->json(['success' => false, 'message' => 'هذا الحساب غير مفعل. يرجى التواصل مع الإدارة.'], 403);
        } elseif ($admin->status === 'banned') {
            return response()->json(['success' => false, 'message' => 'هذا الحساب محظور ولا يمكن استخدامه مجددًا.'], 403);
        } elseif ($admin->status === 'deleted') {
            return response()->json(['success' => false, 'message' => 'هذا الحساب تم حذفه.'], 403);
        }

        $admin->login_attempts = 0;
        $admin->locked_until = null;
        $admin->attempts_at = null;
        $admin->save();

        $admin->token = $admin->createToken('website', ['role:admin'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'admin' => new AdminResource($admin),
        ]);
    }



    // Auth Admin
    public function profile(Request $request)
    {
        // return $request->user();
        $request->user()->token = $request->bearerToken();
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'admin' => new AdminResource($request->user()),
        ]);
    }


    // Logout
    public function logout(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'انتهت جلسة الدخول مسبقًا أو التوكن غير صالح'
            ], 200);
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }



    // Add Change Passowrd
    public function password(Request $request)
    {
        if ($request->old_password && $request->new_password) {
            if (!Hash::check($request->old_password, $request->user()->password)) {
                return response()->json(['success' => false, 'message' => 'كلمة المرور القديمة غير صحيحة'], 400);
            }
            $request->user()->password = Hash::make($request->new_password);
            $request->user()->save();
            return response()->json(["success" => true, "message" => "تم تغيير كلمة المرور بنجاح"], 200);
        } else {
            return response()->json(["success" => false, "message" => "لم تقم بإرسال اي حقول لتعديلها"], 400);
        }
    }



    // Add Change Name
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



    //  Add Change Photo
    public function photo(Request $request)
    {
        // تحقق من وجود الملف
        if (! $request->hasFile('photo')) {
            return response()->json([
                'success' => false,
                'message' => 'يجب عليك اختيار صورة ليتم رفعها'
            ], 400);
        }

        if (Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ])->fails()) {
            return response()->json(["success" => false, "message" => "الملف الذي اخترته ليس صورة, او يتجاوز 2 ميجا"], 400);
        }

        $file_name = Str::uuid() . '.' . $request->photo->getClientOriginalExtension();
        $file_path = $request->file('photo')->storeAs('admin', $file_name, 'public');


        $oldPath = $request->user()->photo;

        if ($oldPath) {
            $parsedPath = parse_url($oldPath, PHP_URL_PATH);
            $relativePath = Str::after($parsedPath, '/storage/');

            Storage::disk('public')->delete($relativePath);
        }



        $request->user()->photo = $file_path;
        $request->user()->save();



        return response()->json([
            "success" => true,
            "message" => "تم تحديث صورة المستخدم بنجاح",
            "photo" => url(Storage::url($file_path))
        ]);
    }





    // End of Controller
}
