<?php

namespace App\Features\Admin\v1\Controllers;

use App\Features\Admin\Requests\PermissionStoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Features\Admin\v1\Models\Role;
use App\Features\Admin\v1\Requests\PermissionStoreRequest as RequestsPermissionStoreRequest;
use App\Features\Admin\v1\Resources\PermissionResource;

use function PHPUnit\Framework\isEmpty;

class RoleController  extends Controller
{


    // All Roles
    public function index(Request $request)
    {
        if ($request->count)
            $count = $request->count;
        else
            $count = 10;

        $permissions = Role::latest()
            ->notDeleted()
            ->where('name', 'like', '%' . $request->name . '%')->reorder()->orderBy('id', 'asc')
            ->withCount('admins')
            ->paginate($count);
        if ($permissions->isEmpty())
            return $this->empty();
        return response()->json(['success' => true, 'message' => 'تم جلب  الأدوار بنجاح', 'data' => $permissions], 200);
    }

    // Get Role By Id
    public function show($role)
    {
        $permission = Role::withCount('admins')
            ->notDeleted()
            ->find($role);

        if (!$permission)
            return $this->empty();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب الدور بنجاح',
            'data' => PermissionResource::make($permission),
        ], 200);
    }




    // Delete Permission
    public function delete($role)
    {
        $permission = Role::withCount('admins')->notDeleted()->find($role);

        if (!$permission)
            return $this->empty();

        if ($permission->admins_count > 0)
            return $this->badRequest('هذه الدور لا يمكن حذفه لأنه يحتوي على مشرفين');

        $permission->status = 9;

        if ($permission->save())
            return $this->success('تم حذف الدور بنجاح');

        return $this->badRequest('حدث خطأ ما أثناء الحذف');
    }



    // Edit Single Role
    public function edit(Request $request, $role)
    {
        // Check If Role Exist Or Not
        $role = Role::withCount('admins')->notDeleted()->find($role);
        if (!$role)
            return $this->empty();

        // Validate Name
        if (
            Validator::make($request->all(), [
                'name' => 'required',
            ])->fails()
        ) {
            return $this->badRequest("يجب عليك ادخال اسم الدور");
        }

        if (strtolower($request['name']) != strtolower($role->name)) {
            if (
                Validator::make($request->all(), [
                    'name' => 'unique:roles',
                ])->fails()
            ) {
                return $this->badRequest("يوجد دور بهذا الإسم");
            }
        }


        // Validate Permissions 
        if (
            Validator::make($request->all(), [
                'permissions' => 'required|min:1',
            ])->fails()
        ) {
            return $this->badRequest("يجب عليك ادخال صلاحية واحدة على الأقل");
        }
        if (
            Validator::make($request->all(), [
                'permissions' => 'array',
            ])->fails()
        ) {
            return $this->badRequest("نوع الصلاحية غير صحيح");
        }

        $role->name = $request['name'];
        $role->permissions = json_encode($request['permissions']);
        $edit = $role->save();
        if ($edit)
            return $this->success('تم تحديث هذا الدور بنجاح');

        return $this->badRequest('حدث خطأ ما');
    }


    // Get All Permissions
    public function new(Request $request)
    {
        $permissions = [];
        foreach (config('permissions.permissions') as $name => $value) {
            array_push($permissions, ["name" => $name, "description" => $value, "state" => false]);
        }
        return response()->json([
            "success" => true,
            "message" => "تم جلب جميع الصلاحيات بنجاح",
            "data" => [
                "name" => "",
                "permissions" => $permissions,
            ]
        ]);
    }



    // Add New Role
    public function create(RequestsPermissionStoreRequest $request)
    {
        $permission = Role::create([
            'name' => $request['name'],
            'permissions' => json_encode($request['permissions']),

        ]);
        return response()->json(['success' => true, 'message' => 'تم إنشاء الصلاحية بنجاح'], 200);
    }
}
