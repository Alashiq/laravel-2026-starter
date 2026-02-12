<?php

namespace App\Features\App\v1\Controllers;

use App\Features\App\v1\Models\HallBooking;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Features\Admin\v1\Resources\AdminResource;
use App\Features\App\v1\Models\City;
use App\Features\App\v1\Models\Hall;
use App\Features\App\v1\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CityController extends Controller
{

    // List Cities
    public function index(Request $request)
    {
        $name = $request->get('name', '');
        $count = $request->get('count', 4);

        $list = City::latest()
            ->notDeleted()
            ->when($name, function ($query) use ($name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->paginate($count);

        if ($list->count() == 0) {
            return $this->empty();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب المدن بنجاح',
            'data' => $list
        ], 200);
    }



    //  Show City
    public function show(Request $request, $id)
    {

        $city = City::notDeleted()->find($id);
        if (!$city)
            return $this->empty();
        return response()->json(['success' => true, 'message' => 'تم جلب المدينة بنجاح', 'data' => $city], 200);
    }


    //  Show City
    public function list(Request $request)
    {

        $name = $request->get('name', '');
        $city = City::notDeleted()
            ->when($name, function ($query) use ($name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->get();
        if ($city->isEmpty())
            return $this->empty();
        return response()->json(['success' => true, 'message' => 'تم جلب المدينة بنجاح', 'data' => $city], 200);
    }



    // store
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:1|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'فشل التحقق من البيانات',
            ], 400);
        }

        $city = City::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة المدينة بنجاح',
            'data' => $city
        ], 201);
    }


    // End of Controller
}
