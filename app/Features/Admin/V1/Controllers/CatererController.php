<?php

namespace App\Features\Admin\v1\Controllers;

use App\Features\Admin\Requests\PermissionStoreRequest;
use App\Features\Admin\v1\Models\Caterer;
use App\Features\Admin\v1\Models\City;
use App\Features\Admin\v1\Models\Hall;
use App\Features\Admin\v1\Requests\CatererEditRequest;
use App\Features\Admin\v1\Requests\CatererStoreRequest;
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

class CatererController  extends Controller
{


        // Caterer List
    public function index(Request $request)
    {
        $count = $request->count ?? 10;

        $caterers = Caterer::with('city:id,name')
            ->when($request->filled('name'), fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
            ->notDeleted()
            ->latest()
            ->paginate($count);

        if ($caterers->isEmpty()) {
            return $this->empty();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب متعهدي التموين بنجاح',
            'data'    => $caterers
        ], 200);
    }



    // Get Caterer By Id
    public function show($id)
    {
        $caterer = Caterer::with('city:id,name')
            ->notDeleted()
            ->find($id);

        if (!$caterer) {
            return $this->empty();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات متعهد التموين بنجاح',
            'data'    => $caterer
        ], 200);
    }




    // Delete Caterer (Soft Delete via status=9)
    public function delete($id)
    {
        $caterer = Caterer::notDeleted()->find($id);
        if (!$caterer) {
            return $this->empty();
        }

        $caterer->status = 9;
        if ($caterer->save()) {
            return $this->success('تم حذف متعهد التموين بنجاح');
        }

        return $this->badRequest('حدث خطأ ما');
    }



    // Activate Caterer
    public function active($id)
    {
        $caterer = Caterer::notDeleted()->find($id);
        if (!$caterer) {
            return $this->empty();
        }

        if ($caterer->status == 1) {
            return $this->badRequest('هذه التشاركية مفعلة مسبقًا');
        }

        $caterer->status = 1;
        if ($caterer->save()) {
            return $this->success('تم تفعيل حساب متعهد التموين بنجاح');
        }

        return $this->badRequest('حدث خطأ ما');
    }


        // DisActivate Caterer
    public function disActive($id)
    {
        $caterer = Caterer::notDeleted()->find($id);
        if (!$caterer) {
            return $this->empty();
        }

        if ($caterer->status == 0) {
            return $this->badRequest('هذا الحساب غير مفعل بالفعل');
        }

        $caterer->status = 0;
        if ($caterer->save()) {
            return $this->success('تم إلغاء تفعيل حساب متعهد التموين بنجاح');
        }

        return $this->badRequest('حدث خطأ ما');
    }

        // Data For New Caterer
    public function new()
    {
        $cities = City::select('id', 'name')->notDeleted()->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب البيانات اللازمة لإنشاء متعهد جديد',
            'data'    => [
                'cities' => $cities,
            ]
        ], 200);
    }

        // Store New Caterer
    public function store(CatererStoreRequest $request) // استخدم FormRequest للتحقق
    {
        $data = $request->validated(); // احصل على البيانات التي تم التحقق منها

        if ($request->hasFile('logo')) {
            $file_name = Str::uuid() . '.' . $request->logo->getClientOriginalExtension();
            $data['logo'] = $request->file('logo')->storeAs('caterers/logos', $file_name, 'public');
        }

        if ($request->hasFile('cover_photo')) {
            $file_name = Str::uuid() . '.' . $request->cover_photo->getClientOriginalExtension();
            $data['cover_photo'] = $request->file('cover_photo')->storeAs('caterers/covers', $file_name, 'public');
        }
        
        $data['status'] = 0; // الحالة الافتراضية عند الإنشاء

        Caterer::create($data);

        return $this->success('تم إنشاء حساب متعهد التموين بنجاح');
    }




    // Get Caterer By Id For Edit
    public function editGet($id)
    {
        $caterer = Caterer::notDeleted()->find($id);
        if (!$caterer) {
            return $this->empty();
        }

        $cities = City::select('id', 'name')->notDeleted()->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات متعهد التموين للتعديل',
            'data'    => $caterer,
            'cities'  => $cities
        ], 200);
    }

    // Edit Caterer
    public function edit(CatererEditRequest $request, $id) // استخدم FormRequest للتحقق
    {
        $caterer = Caterer::notDeleted()->find($id);
        if (!$caterer) {
            return $this->empty();
        }

        $data = $request->validated();

        if ($request->hasFile('logo')) {
            // يمكنك إضافة كود لحذف الصورة القديمة إذا أردت
            // if ($caterer->logo) { Storage::disk('public')->delete($caterer->logo); }
            $file_name = Str::uuid() . '.' . $request->logo->getClientOriginalExtension();
            $data['logo'] = $request->file('logo')->storeAs('caterers/logos', $file_name, 'public');
        }

        if ($request->hasFile('cover_photo')) {
            // if ($caterer->cover_photo) { Storage::disk('public')->delete($caterer->cover_photo); }
            $file_name = Str::uuid() . '.' . $request->cover_photo->getClientOriginalExtension();
            $data['cover_photo'] = $request->file('cover_photo')->storeAs('caterers/covers', $file_name, 'public');
        }

        $caterer->update($data);

        return $this->success('تم تحديث بيانات متعهد التموين بنجاح');
    }

    // End of HallController
}
