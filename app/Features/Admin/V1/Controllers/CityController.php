<?php

namespace App\Features\Admin\v1\Controllers;

use App\Features\Admin\Requests\PermissionStoreRequest;
use App\Features\Admin\v1\Models\City;
use App\Http\Controllers\Controller;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Features\Admin\v1\Models\Role;
use App\Features\Admin\v1\Requests\CityStoreRequest;
use App\Features\Admin\v1\Requests\PermissionStoreRequest as RequestsPermissionStoreRequest;
use App\Features\Admin\v1\Resources\PermissionResource;

use function PHPUnit\Framework\isEmpty;

class CityController  extends Controller
{


    // All Cities
    public function index(Request $request)
    {
        $count = $request->count ?? 10;

        $cities = City::latest()
            ->where('name', 'like', '%' . $request->name . '%')
            ->where('status', '!=', 9)
            ->paginate($count);

        if ($cities->isEmpty())
            return $this->empty();

        return response()->json(['success' => true, 'message' => 'تم جلب المدن بنجاح', 'data' => $cities], 200);
    }


    // Get City By Id
    public function show($id)
    {
        $city = City::notDeleted()
            ->find($id);

        if (!$city)
            return $this->empty();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب المدينة بنجاح',
            'data' => $city,
        ], 200);
    }




    // Delete Permission
    public function delete($id)
    {
        $city = City::notDeleted()->find($id);

        if (!$city)
            return $this->empty();


        $city->status = 9;

        if ($city->save())
            return $this->success('تم حذف المدينة بنجاح');

        return $this->badRequest('حدث خطأ ما أثناء الحذف');
    }



    // Get Admin By Id With Permseeions
    public function editGet($id)
    {
        $city = City::notDeleted()->find($id);
        if (!$city)
            return $this->empty();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات المدينة بنجاح',
            'data' => $city,
        ], 200);
    }

    // Edit Single Role
    public function edit(CityStoreRequest $request, $id)
    {
        // Check If Role Exist Or Not
        $city = City::notDeleted()->find($id);
        if (!$city)
            return $this->empty();


        $city->fill($request->only(['name', 'longitude', 'latitude', 'description']));
      $edit =   $city->save();

        if ($edit)
            return $this->success('تم تحديث هذا الدور بنجاح');

        return $this->badRequest('حدث خطأ ما');
    }


    // Get Data For New City
    public function new(Request $request)
    {
        $permissions = [];
        return response()->json([
            "success" => true,
            "message" => "تم جلب جميع البيانات بنجاح",
            "data" => [
                "name" => "",
                "permissions" => $permissions,
            ]
        ]);
    }



    // Add New Role
    public function create(CityStoreRequest $request)
    {
        $city = City::create([
            'name' => $request['name'],
            'longitude' => $request['longitude'],
            'latitude' => $request['latitude'],
            'description' => $request['description'],
        ]);
        return response()->json(['success' => true, 'message' => 'تم إضافة المدينة بنجاح'], 200);
    }
}
