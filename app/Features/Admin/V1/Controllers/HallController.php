<?php

namespace App\Features\Admin\v1\Controllers;

use App\Features\Admin\Requests\PermissionStoreRequest;
use App\Features\Admin\v1\Models\City;
use App\Features\Admin\v1\Models\Hall;
use App\Features\Admin\v1\Requests\HallEditRequest;
use App\Features\Admin\v1\Requests\HallStoreRequest;
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
use Illuminate\Support\Str;


use function PHPUnit\Framework\isEmpty;

class HallController  extends Controller
{


    // Hall List
    public function index(Request $request)
    {
        $count = $request->count ?? 10;

        $halls = Hall::with('city:id,name')
            ->when($request->filled('name'), fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
            ->notDeleted()
            ->latest()
            ->paginate($count);

        if ($halls->isEmpty())
            return $this->empty();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب القاعات بنجاح',
            'data'    => $halls
        ], 200);
    }



    // Get Hall By Id
    public function show($id)
    {
        $hall = Hall::with('city:id,name')
            ->notDeleted()
            ->find($id);

        if (!$hall)
            return $this->empty();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب القاعة بنجاح',
            'data'    => $hall
        ], 200);
    }






    // Delete Hall (Soft Delete via status=9)
    public function delete($id)
    {
        $hall = Hall::notDeleted()->find($id);
        if (!$hall)
            return $this->empty();

        $hall->status = 9;
        if ($hall->save())
            return $this->success('تم حذف القاعة بنجاح');

        return $this->badRequest('حدث خطأ ما');
    }

    // Activate Hall
    public function active($id)
    {
        $hall = Hall::notDeleted()->find($id);
        if (!$hall)
            return $this->empty();

        if ($hall->status == 1)
            return $this->badRequest('هذه القاعة مفعلة مسبقًا');

        $hall->status = 1;
        if ($hall->save())
            return $this->success('تم تفعيل القاعة بنجاح');

        return $this->badRequest('حدث خطأ ما');
    }

    // DisActivate Hall
    public function disActive($id)
    {
        $hall = Hall::notDeleted()->find($id);
        if (!$hall)
            return $this->empty();

        if ($hall->status == 0)
            return $this->badRequest('هذه القاعة غير مفعلة مسبقًا');

        $hall->status = 0;
        if ($hall->save())
            return $this->success('تم إلغاء تفعيل القاعة بنجاح');

        return $this->badRequest('حدث خطأ ما');
    }



    // Get Hall By Id For Edit
    public function editGet($id)
    {
        $hall = Hall::notDeleted()->find($id);
        if (!$hall)
            return $this->empty();

        $cities = City::select('id', 'name')->notDeleted()->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات القاعة بنجاح',
            'data'    => $hall,
            'cities'  => $cities
        ], 200);
    }



    // Edit Hall
    public function edit(HallEditRequest $request, $id)
    {
        $hall = Hall::notDeleted()->find($id);
        if (!$hall)
            return $this->empty();

        $hall->fill($request->only([
            'name',
            'city_id',
            'address',
            'phone',
            'whatsapp',
            'supervisor_phone',
            'tables',
            'chairs',
            'capacity',
            'price_morning',
            'price_evening',
            'price_full_day',
            'deposit',
            'cancellation_policy',
            'services_text',
            'description',
            'final_payment_days',
            'latitude',
            'longitude',
            // 👇 الحقول البوليانية اللي نسيتها
            'drinks_service',
            'buffet',
            'decoration',
            'sound_system',
            'bride_room',
            'photography',
            'parking',
            'air_conditioning',
        ]));

        if ($request->hasFile('logo')) {
            $file_name = Str::uuid() . '.' . $request->logo->getClientOriginalExtension();
            $file_path = $request->file('logo')->storeAs('halls_logo', $file_name, 'public');
            $hall->logo = $file_path;
        }

        if ($hall->save())
            return $this->success('تم تحديث بيانات القاعة بنجاح');

        return $this->badRequest('حدث خطأ ما');
    }




    // Data For New Hall
    public function new()
    {
        $cities = City::select('id', 'name')->notDeleted()->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب البيانات بنجاح',
            'data'    => [
                'cities' => $cities,
                'name'   => '',
                'logo'   => ''
            ]
        ], 200);
    }





    // Store New Hall
    public function store(HallStoreRequest $request)
    {
        $file_path = null;
        if ($request->hasFile('logo')) {
            $file_name = Str::uuid() . '.' . $request->logo->getClientOriginalExtension();
            $file_path = $request->file('logo')->storeAs('halls_logo', $file_name, 'public');
        }


        Hall::create(array_merge($request->only([
            'name',
            'city_id',
            'address',
            'phone',
            'whatsapp',
            'supervisor_phone',
            'tables',
            'chairs',
            'capacity',
            'price_morning',
            'price_evening',
            'price_full_day',
            'deposit',
            'cancellation_policy',
            'services_text',
            'description',
            'final_payment_days',
            'latitude',
            'longitude',
            // 👇 الحقول البوليانية اللي نسيتها
            'drinks_service',
            'buffet',
            'decoration',
            'sound_system',
            'bride_room',
            'photography',
            'parking',
            'air_conditioning',
        ]), ['logo' => $file_path, 'status' => 0]));

        return $this->success('تم إنشاء القاعة بنجاح');
    }

    // End of HallController
}
