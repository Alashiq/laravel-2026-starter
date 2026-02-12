<?php

namespace App\Features\Admin\v1\Controllers;

use App\Features\Admin\v1\Models\Admin;
use App\Features\Admin\v1\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{

    // Admin List

    public function index(Request $request)
    {
        $count = $request->count ?? 10;

        $admins = Admin::latest()
            ->where('id', '<>', $request->user()->id)
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('phone'), fn($q) => $q->where('phone', 'like', '%' . $request->phone . '%'))
            ->when($request->filled('first_name'), fn($q) => $q->where('first_name', 'like', '%' . $request->first_name . '%'))
            ->when($request->filled('last_name'), fn($q) => $q->where('last_name', 'like', '%' . $request->last_name . '%'))
            ->notDeleted()
            ->paginate($count);

        if ($admins->isEmpty()) {
            return $this->empty();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب المشرفين بنجاح',
            'data' => $admins
        ], 200);
    }




    // Get Admin By Id
    public function show(Request $request, $admin)
    {
        $admin = Admin::with('role:id,name')
            ->where('id', '<>', $request->user()->id)
            ->notDeleted()
            ->find($admin);

        if (!$admin)
            return $this->empty();
        return response()->json(['success' => true, 'message' => 'تم جلب المشرف بنجاح', 'data' => $admin], 200);
    }




    // Data For New Admin
    public function new()
    {
        $roles = Role::select('id', 'name')
            ->latest()
            ->notDeleted()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب البيانات بنجاح',
            'roles'   => $roles
        ], 200);
    }




    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone'      => 'required|unique:admins',
            'first_name' => 'required|string|min:2|max:25',
            'last_name'  => 'required|string|min:2|max:25',
            'password'   => 'required|string|min:6|max:25',
            'role_id'    => 'required|integer|exists:roles,id'
        ], [
            'phone.required'      => 'يجب عليك إدخال رقم الهاتف',
            'phone.unique'        => 'رقم الهاتف محجوز مسبقا',
            'first_name.required' => 'يجب عليك إدخال الإسم',
            'last_name.required'  => 'يجب عليك إدخال اللقب',
            'password.required'   => 'يجب عليك إدخال كلمة مرور صحيحة',
            'role_id.required'    => 'يجب اختيار دور للمشرف',
            'role_id.exists'      => 'دور المشرف غير متاح'
        ]);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors()->first());
        }

        // التأكد من أن الدور غير محذوف
        $role = Role::notDeleted()->find($request->role_id);
        if (!$role) {
            return $this->badRequest('هذا الدور غير متاح');
        }

        Admin::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'phone'      => $request->phone,
            'role_id'    => $request->role_id,
            'password'   => Hash::make($request->password),
        ]);

        return $this->success('تم إنشاء هذا الحساب بنجاح');
    }



    // Get Admin By Id With Permseeions
    public function editGet($admin)
    {
        $admin = Admin::with('role:id,name')
            ->notDeleted()
            ->find($admin);
        if (!$admin)
            return $this->empty();

        $roles = Role::select('id', 'name')->notDeleted()->get();

        if ($roles->isEmpty()) {
            return $this->empty();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات المشرف بنجاح',
            'data' => $admin,
            'roles' => $roles
        ], 200);
    }


    //  Change Admin Role
    public function edit($admin, Request $request)
    {
        $admin = Admin::notDeleted()->find($admin);

        if (!$admin)
            return $this->badRequest('هذه الحساب غير موجود');

        $validator = Validator::make($request->all(), [
            'role_id' => 'required|integer|exists:roles,id'
        ], [
            'role_id.required' => 'يجب عليك إرسال رقم الدور',
        ]);

        if ($validator->fails())
            return $this->badRequest($validator->errors()->first());


        $role = Role::notDeleted()->find($request->role_id);
        if (!$role)
            return response()->json(['success' => false, 'message' => 'هذا الدور لم يعد متاح قم بإختيار دور اخر'], 400);

        $admin->role_id = $request->role_id;
        if ($admin->save())
            return $this->success('تم تحديث دور الحساب بنجاح');


        return $this->badRequest('حدث خطأ ما');
    }



    // Delete Admin
    public function delete($id, Request $request)
    {
        $admin = Admin::notDeleted()->find($id);
        if (!$admin)
            return $this->empty();


        if ($request->user()->id == $admin->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك حذف حسابك الحالي'
            ], 403);
        }

        $admin->phone = 'old-' . $admin->phone;
        $admin->status = 'deleted';

        if ($admin->save())
            return $this->success('تم حذف هذا الحساب بنجاح');

        return $this->badRequest('حدث خطأ ما');
    }


    // Activate Admin
    public function active($id)
    {
        $admin = Admin::notDeleted()->find($id);

        if (!$admin)
            return $this->empty();

        if ($admin->status === 'active')
            return $this->badRequest('هذا الحساب مفعل مسبقًا');

        if ($admin->status === 'banned')
            return $this->badRequest('هذا الحساب محظور ولا يمكن تفعيله');

        $admin->status = 'active';
        if ($admin->save())
            return $this->success('تم تفعيل هذا الحساب بنجاح');

        return $this->badRequest('حدث خطأ ما');
    }


    // DisActivate Admin
    public function disActive($id)
    {
        $admin = Admin::notDeleted()->find($id);
        if (!$admin)
            return $this->empty();

        if ($admin->status === 'not_active')
            return $this->badRequest('هذا الحساب غير مفعل مسبقًا');


        if ($admin->status === 'banned')
            return $this->badRequest('هذا الحساب محظور ولا يمكن إيقاف تفعيله');

        $admin->status = 'not_active';
        if ($admin->save())
            return $this->success('تم إلغاء تفعيل هذا الحساب بنجاح');

        return $this->badRequest('حدث خطأ ما');
    }


    // Banned Admin
    public function banned($id)
    {
        $admin = Admin::notDeleted()->find($id);
        if (!$admin)
            return $this->empty();

        if ($admin->status === 'banned')
            return $this->badRequest('هذا الحساب محظور مسبقًا');

        $admin->status = 'banned';
        $edit = $admin->save();
        if ($admin->save())
            return $this->success('تم حظر هذا الحساب بنجاح');

        return $this->badRequest('حدث خطأ ما');
    }

    // Banned Admin
    public function resetPassword($id)
    {
        $admin = Admin::notDeleted()->find($id);
        if (!$admin)
            return $this->empty();

        if ($admin->status === 'banned')
            return $this->badRequest('هذا الحساب محظور مسبقًا');

        $admin->password = Hash::make("123456");
        $edit = $admin->save();
        if ($admin->save())
            return $this->success('تم تغيير كلمة المرور إلى 123456 , يجب عليك تغيير كلمة المرور بمجرد تسجيل دخول إلى الحساب');

        return $this->badRequest('حدث خطأ ما');
    }


    // End of Controller
}
