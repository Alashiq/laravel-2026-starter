<?php

namespace App\Features\Admin\v1\Controllers;

use App\Features\Admin\v1\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    // User List
    public function index(Request $request)
    {
        $count = $request->count ?? 10;

        $users = User::with('city:id,name')
            ->latest()
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('phone'), fn($q) => $q->where('phone', 'like', '%' . $request->phone . '%'))
            ->when($request->filled('first_name'), fn($q) => $q->where('first_name', 'like', '%' . $request->first_name . '%'))
            ->when($request->filled('last_name'), fn($q) => $q->where('last_name', 'like', '%' . $request->last_name . '%'))
            ->when($request->filled('city_id'), fn($q) => $q->where('city_id', $request->city_id))
            ->paginate($count);

        if ($users->isEmpty()) {
            return $this->empty();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب المستخدمين بنجاح',
            'data' => $users
        ], 200);
    }

    // Get User By Id
    public function show(Request $request, $user)
    {
        $user = User::with('city:id,name')->find($user);

        if (!$user) {
            return $this->empty('المستخدم غير موجود');
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب المستخدم بنجاح',
            'data' => $user
        ], 200);
    }

    // Create User
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|min:2|max:20',
            'last_name' => 'required|string|min:2|max:20',
            'phone' => 'required|numeric|digits:10|starts_with:09|unique:users,phone',
            'city_id' => 'required|exists:cities,id',
            'status' => 'nullable|integer|in:0,1,2,3',
            'point' => 'nullable|integer',
            'balance' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors()->first());
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'city_id' => $request->city_id,
            'status' => $request->status ?? 2,
            'point' => $request->point ?? 0,
            'balance' => $request->balance ?? 0,
            'otp' => Hash::make('000000'), // Default OTP
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المستخدم بنجاح',
            'data' => $user
        ], 201);
    }

    // Update User
    public function update(Request $request, $user)
    {
        $user = User::find($user);

        if (!$user) {
            return $this->empty('المستخدم غير موجود');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|min:2|max:20',
            'last_name' => 'nullable|string|min:2|max:20',
            'phone' => 'nullable|numeric|digits:10|starts_with:09|unique:users,phone,' . $user->id,
            'city_id' => 'nullable|exists:cities,id',
            'status' => 'nullable|integer|in:0,1,2,3',
            'point' => 'nullable|integer',
            'balance' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors()->first());
        }

        $user->update($request->only([
            'first_name', 'last_name', 'phone', 'city_id', 'status', 'point', 'balance'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المستخدم بنجاح',
            'data' => $user
        ], 200);
    }

    // Delete User
    public function destroy(Request $request, $user)
    {
        $user = User::find($user);

        if (!$user) {
            return $this->empty('المستخدم غير موجود');
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المستخدم بنجاح'
        ], 200);
    }

    // Ban User
    public function ban(Request $request, $user)
    {
        $user = User::find($user);

        if (!$user) {
            return $this->empty('المستخدم غير موجود');
        }

        $validator = Validator::make($request->all(), [
            'ban_expires_at' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors()->first());
        }

        $user->update([
            'status' => 3,
            'ban_expires_at' => $request->ban_expires_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم حظر المستخدم بنجاح'
        ], 200);
    }

    // Unban User
    public function unban(Request $request, $user)
    {
        $user = User::find($user);

        if (!$user) {
            return $this->empty('المستخدم غير موجود');
        }

        $user->update([
            'status' => 2,
            'ban_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء حظر المستخدم بنجاح'
        ], 200);
    }
    // Activate User
    public function activate(Request $request, $user)
    {
        $user = User::find($user);

        if (!$user) {
            return $this->empty('المستخدم غير موجود');
        }

        $user->update(['status' => 2]);

        return response()->json([
            'success' => true,
            'message' => 'تم تفعيل المستخدم بنجاح'
        ], 200);
    }

    // Deactivate User
    public function deactivate(Request $request, $user)
    {
        $user = User::find($user);

        if (!$user) {
            return $this->empty('المستخدم غير موجود');
        }

        $user->update(['status' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء تفعيل المستخدم بنجاح'
        ], 200);
    }}