<?php

namespace App\Features\Admin\v1\Controllers;

use App\Features\Admin\v1\Models\Caterer;
use App\Features\Admin\v1\Models\Product;
use App\Features\Admin\v1\Requests\ProductStoreRequest; // ستحتاج لإنشاء هذا الملف
use App\Features\Admin\v1\Requests\ProductEditRequest;  // ستحتاج لإنشاء هذا الملف
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     * عرض قائمة المنتجات
     */
    public function index(Request $request)
    {
        $count = $request->count ?? 10;

        $products = Product::with('caterer:id,name') // جلب العلاقة مع متعهد التموين
            ->when($request->filled('name'), fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
            ->notDeleted()
            ->latest()
            ->paginate($count);

        if ($products->isEmpty()) {
            return $this->empty();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب المنتجات بنجاح',
            'data'    => $products
        ], 200);
    }

    /**
     * Display the specified product.
     * عرض منتج معين
     */
    public function show($id)
    {
        $product = Product::with('caterer:id,name')
            ->notDeleted()
            ->find($id);

        if (!$product) {
            return $this->empty();
        }


        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات المنتج بنجاح',
            'data'    => $product
        ], 200);
    }

    /**
     * Soft delete the specified product.
     * حذف ناعم للمنتج
     */
    public function delete($id)
    {
        $product = Product::notDeleted()->find($id);
        if (!$product) {
            return $this->empty();
        }

        $product->status = 9;
        if ($product->save()) {
            return $this->success('تم حذف المنتج بنجاح');
        }

        return $this->badRequest('حدث خطأ ما');
    }

    /**
     * Activate the specified product.
     * تفعيل المنتج
     */
    public function active($id)
    {
        $product = Product::notDeleted()->find($id);
        if (!$product) {
            return $this->empty();
        }

        if ($product->status == 1) {
            return $this->badRequest('هذا المنتج مفعل مسبقًا');
        }

        $product->status = 1;
        if ($product->save()) {
            return $this->success('تم تفعيل المنتج بنجاح');
        }

        return $this->badRequest('حدث خطأ ما');
    }

    /**
     * Deactivate the specified product.
     * إلغاء تفعيل المنتج
     */
    public function disActive($id)
    {
        $product = Product::notDeleted()->find($id);
        if (!$product) {
            return $this->empty();
        }

        if ($product->status == 0) {
            return $this->badRequest('هذا المنتج غير مفعل بالفعل');
        }

        $product->status = 0;
        if ($product->save()) {
            return $this->success('تم إلغاء تفعيل المنتج بنجاح');
        }

        return $this->badRequest('حدث خطأ ما');
    }

    /**
     * Show the form for creating a new product.
     * عرض البيانات اللازمة لإنشاء منتج جديد
     */
    public function new()
    {
        // جلب قائمة متعهدي التموين النشطين فقط لربط المنتج بهم
        $caterers = Caterer::notDeleted()->where('status', 1)->select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب البيانات اللازمة لإنشاء منتج جديد',
            'data'    => [
                'caterers' => $caterers,
            ]
        ], 200);
    }

    /**
     * Store a newly created product in storage.
     * تخزين منتج جديد
     */
    public function store(ProductStoreRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $file_name = Str::uuid() . '.' . $request->photo->getClientOriginalExtension();
            $data['photo'] = $request->file('photo')->storeAs('products', $file_name, 'public');
        }
        
        $data['status'] = 0;

        Product::create($data);

        return $this->success('تم إنشاء المنتج بنجاح');
    }

    /**
     * Show the form for editing the specified product.
     * عرض بيانات المنتج للتعديل
     */
    public function editGet($id)
    {
        $product = Product::notDeleted()->find($id);
        if (!$product) {
            return $this->empty();
        }

        $caterers = Caterer::notDeleted()->where('status', 1)->select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات المنتج للتعديل',
            'data'    => $product,
            'caterers'  => $caterers
        ], 200);
    }

    /**
     * Update the specified product in storage.
     * تحديث المنتج
     */
    public function edit(ProductEditRequest $request, $id)
    {
        $product = Product::notDeleted()->find($id);
        if (!$product) {
            return $this->empty();
        }

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($product->getRawOriginal('photo')) {
                 Storage::disk('public')->delete($product->getRawOriginal('photo'));
            }
            $file_name = Str::uuid() . '.' . $request->photo->getClientOriginalExtension();
            $data['photo'] = $request->file('photo')->storeAs('products', $file_name, 'public');
        }

        $product->update($data);

        return $this->success('تم تحديث بيانات المنتج بنجاح');
    }
}
