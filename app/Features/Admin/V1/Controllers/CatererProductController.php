<?php

namespace App\Features\Admin\v1\Controllers;

use App\Features\Admin\v1\Models\Caterer;
use App\Features\Admin\v1\Models\CatererProduct;
use App\Features\Admin\v1\Models\Product;
use App\Features\Admin\v1\Requests\CatererProductStoreRequest;
use App\Features\Admin\v1\Requests\CatererProductEditRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CatererProductController extends Controller
{
    // 1. عرض قائمة بجميع منتجات التشاركيات
    public function index(Request $request)
    {
        $count = $request->count ?? 10;

        $query = CatererProduct::with(['caterer:id,name', 'product:id,name,photo']);
        // فلترة باسم المنتج
        if ($request->filled('product_name')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->product_name . '%');
            });
        }

        // فلترة باسم المتعهد
        if ($request->filled('caterer_name')) {
            $query->whereHas('caterer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->caterer_name . '%');
            });
        }

        $catererProducts = $query->latest()->paginate($count);

        if ($catererProducts->isEmpty()) {
            return $this->empty('لا توجد أي منتجات مضافة للمتعهدين حاليًا.');
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب منتجات المتعهدين بنجاح',
            'data'    => $catererProducts
        ], 200);
    }

    // 2. عرض البيانات اللازمة لإنشاء علاقة جديدة
    public function new()
    {
        $caterers = Caterer::notDeleted()->where('status', 1)->select('id', 'name')->get();
        $products = Product::notDeleted()->where('status', 1)->select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب البيانات اللازمة',
            'data'    => [
                'caterers' => $caterers,
                'products' => $products,
            ]
        ], 200);
    }

    // 3. تخزين علاقة جديدة
    public function store(CatererProductStoreRequest $request)
    {
        CatererProduct::create($request->validated());
        return $this->success('تمت إضافة المنتج إلى المتعهد بنجاح');
    }

    // 4. عرض بيانات علاقة معينة للتعديل
    public function editGet($id)
    {
        $catererProduct = CatererProduct::with(['product:id,name', 'caterer:id,name'])->find($id);
        if (!$catererProduct) {
            return $this->empty('هذا السجل غير موجود.');
        }
        return response()->json(['success' => true, 'data' => $catererProduct]);
    }

    // 5. تحديث علاقة معينة
    public function edit(CatererProductEditRequest $request, $id)
    {
        $catererProduct = CatererProduct::find($id);
        if (!$catererProduct) {
            return $this->empty('هذا السجل غير موجود.');
        }
        $catererProduct->update($request->validated());
        return $this->success('تم تحديث بيانات المنتج بنجاح');
    }

    // 6. حذف علاقة معينة
    public function delete($id)
    {
        $catererProduct = CatererProduct::find($id);
        if (!$catererProduct) {
            return $this->empty();
        }
        $catererProduct->delete();
        return $this->success('تم حذف المنتج من قائمة المتعهد بنجاح');
    }
}
