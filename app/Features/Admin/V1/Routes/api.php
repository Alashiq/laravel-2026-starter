<?php

use App\Features\Admin\v1\Controllers\AdminController;
use App\Features\Admin\v1\Controllers\AuthController;
use App\Features\Admin\v1\Controllers\CatererController;
use App\Features\Admin\v1\Controllers\CatererProductController;
use App\Features\Admin\v1\Controllers\CityController;
use App\Features\Admin\v1\Controllers\HallController;
use App\Features\Admin\v1\Controllers\HomeController;
use App\Features\Admin\v1\Controllers\ProductController;
use App\Features\Admin\v1\Controllers\RoleController;
use App\Features\Admin\v1\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);



Route::middleware(['auth:sanctum', 'type.admin'])->group(function () {


    # # # # # # # # # # # # # # # # #  Home  # # # # # # # # # # # # # # # # #
    Route::controller(HomeController::class)->prefix('home')->group(
        function () {
            Route::get('/', [HomeController::class, 'index'])->middleware('check.role:HomeChart');
        }
    );
    # # # # # # # # # # # # # # # # # End Home  # # # # # # # # # # # # # # # 



    # # # # # # # # # # # # # # # Admin Auth # # # # # # # # # # # # # # # 
    Route::group(
        ['prefix' => 'auth'],
        function () {
            Route::get('/profile', [AuthController::class, 'profile']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/password', [AuthController::class, 'password']);
            Route::post('/name', [AuthController::class, 'name']);
            Route::post('/photo', [AuthController::class, 'photo']);
        }
    );
    # # # # # # # # # # # # # # # End Admin Auth # # # # # # # # # # # # # # # 



    # # # # # # # # # # # # # # # # #  Admin Role  # # # # # # # # # # # # # # # # #
    Route::controller(RoleController::class)->prefix('role')->group(
        function () {
            Route::get('/', action: [RoleController::class, 'index'])->middleware('check.role:ReadRole');
            Route::get('/new', [RoleController::class, 'new'])->middleware('check.role:CreateRole');
            Route::delete('/{role}', [RoleController::class, 'delete'])->middleware('check.role:DeleteRole');
            Route::get('/{role}', [RoleController::class, 'show'])->middleware('check.role:ReadRole');
            Route::put('/{role}', [RoleController::class, 'edit'])->middleware('check.role:EditRole');
            Route::post('/', [RoleController::class, 'create'])->middleware('check.role:CreateRole');
        }
    );
    # # # # # # # # # # # # # # # # # End Admin Role  # # # # # # # # # # # # # # # 



    # # # # # # # # # # # # # # # # #  Admin  # # # # # # # # # # # # # # # # #
    Route::controller(AdminController::class)->prefix('admin')->group(
        function () {
            Route::get('/', [AdminController::class, 'index'])->middleware('check.role:ReadAdmin');

            Route::get('/new', [AdminController::class, 'new'])->middleware('check.role:CreateAdmin');
            Route::post('/', [AdminController::class, 'store'])->middleware('check.role:CreateAdmin');

            Route::get('/{admin}', [AdminController::class, 'show'])->middleware('check.role:ReadAdmin');

            Route::get('/{admin}/edit', [AdminController::class, 'editGet'])->middleware('check.role:EditRoleAdmin');
            Route::put('/{admin}', [AdminController::class, 'edit'])->middleware('check.role:EditRoleAdmin');

            Route::delete('/{admin}', [AdminController::class, 'delete'])->middleware('check.role:DeleteAdmin');

            Route::put('/{admin}/active', [AdminController::class, 'active'])->middleware('check.role:ActiveAdmin');
            Route::put('/{admin}/disActive', [AdminController::class, 'disActive'])->middleware('check.role:DisActiveAdmin');
            Route::put('/{admin}/banned', [AdminController::class, 'banned'])->middleware('check.role:BannedAdmin');
            Route::put('/{admin}/reset', [AdminController::class, 'resetPassword'])->middleware('check.role:ResetPasswordAdmin');
        }
    );
    # # # # # # # # # # # # # # # # # End Admin  # # # # # # # # # # # # # # # 



    # # # # # # # # # # # # # # # # #  Admin City  # # # # # # # # # # # # # # # # #
    Route::controller(CityController::class)->prefix('city')->group(
        function () {
            Route::get('/', action: [CityController::class, 'index'])->middleware('check.role:ReadCity');
            Route::get('/new', [CityController::class, 'new'])->middleware('check.role:CreateCity');

            Route::delete('/{city}', [CityController::class, 'delete'])->middleware('check.role:DeleteCity');
            Route::get('/{city}', [CityController::class, 'show'])->middleware('check.role:ReadCity');
            Route::get('/{city}/edit', [CityController::class, 'editGet'])->middleware('check.role:EditCity');

            Route::put('/{city}', [CityController::class, 'edit'])->middleware('check.role:EditCity');
            Route::post('/', [CityController::class, 'create'])->middleware('check.role:CreateCity');
        }
    );
    # # # # # # # # # # # # # # # # # End Admin City  # # # # # # # # # # # # # # # 


    # # # # # # # # # # # # # # # # #  Admin Hall  # # # # # # # # # # # # # # # # #
    Route::controller(HallController::class)->prefix('hall')->group(
        function () {
            Route::get('/', [HallController::class, 'index'])->middleware('check.role:ReadHall');

            Route::get('/new', [HallController::class, 'new'])->middleware('check.role:CreateHall');
            Route::post('/', [HallController::class, 'store'])->middleware('check.role:CreateHall');

            Route::get('/{hall}', [HallController::class, 'show'])->middleware('check.role:ReadHall');

            Route::get('/{hall}/edit', [HallController::class, 'editGet'])->middleware('check.role:EditHall');
            Route::put('/{hall}', [HallController::class, 'edit'])->middleware('check.role:EditHall');

            Route::delete('/{hall}', [HallController::class, 'delete'])->middleware('check.role:DeleteHall');

            Route::put('/{hall}/active', [HallController::class, 'active'])->middleware('check.role:ActiveHall');
            Route::put('/{hall}/disActive', [HallController::class, 'disActive'])->middleware('check.role:DisActiveHall');
        }
    );
    # # # # # # # # # # # # # # # # # End Admin Hall  # # # # # # # # # # # # # # # #

    # # # # # # # # # # # # # # # # #  Admin Caterer  # # # # # # # # # # # # # # # # #
    Route::controller(CatererController::class)->prefix('caterer')->group(
        function () {
            // جلب قائمة متعهدي التموين
            Route::get('/', [CatererController::class, 'index'])->middleware('check.role:ReadCaterer');

            // جلب البيانات اللازمة لإنشاء متعهد جديد (مثل المدن)
            Route::get('/new', [CatererController::class, 'new'])->middleware('check.role:CreateCaterer');
            // تخزين متعهد تموين جديد
            Route::post('/', [CatererController::class, 'store'])->middleware('check.role:CreateCaterer');

            // جلب بيانات متعهد تموين معين بواسطة ID
            Route::get('/{caterer}', [CatererController::class, 'show'])->middleware('check.role:ReadCaterer');

            // جلب بيانات متعهد التموين لغرض التعديل
            Route::get('/{caterer}/edit', [CatererController::class, 'editGet'])->middleware('check.role:EditCaterer');
            // تحديث بيانات متعهد التموين (استخدمنا post هنا للتوافق مع HTML forms التي لا تدعم PUT للملفات)
            Route::post('/{caterer}', [CatererController::class, 'edit'])->middleware('check.role:EditCaterer');

            // حذف متعهد التموين (حذف ناعم)
            Route::delete('/{caterer}', [CatererController::class, 'delete'])->middleware('check.role:DeleteCaterer');

            // تفعيل حساب متعهد التموين
            Route::put('/{caterer}/active', [CatererController::class, 'active'])->middleware('check.role:ActiveCaterer');
            // إلغاء تفعيل حساب متعهد التموين
            Route::put('/{caterer}/disActive', [CatererController::class, 'disActive'])->middleware('check.role:DisActiveCaterer');
        }
    );
    # # # # # # # # # # # # # # # # # End Admin Caterer  # # # # # # # # # # # # # # # #


    # # # # # # # # # # # # # # # # #  Admin Product  # # # # # # # # # # # # # # # # #
    Route::controller(ProductController::class)->prefix('product')->group(
        function () {
            // 1. جلب قائمة المنتجات (مع إمكانية البحث والفلترة)
            Route::get('/', [ProductController::class, 'index'])->middleware('check.role:ReadProduct');

            // 2. جلب البيانات اللازمة لإنشاء منتج جديد (مثل قائمة متعهدي التموين)
            Route::get('/new', [ProductController::class, 'new'])->middleware('check.role:CreateProduct');

            // 3. تخزين منتج جديد في قاعدة البيانات
            Route::post('/', [ProductController::class, 'store'])->middleware('check.role:CreateProduct');

            // 4. جلب بيانات منتج معين بواسطة ID
            Route::get('/{product}', [ProductController::class, 'show'])->middleware('check.role:ReadProduct');

            // 5. جلب بيانات المنتج لغرض التعديل
            Route::get('/{product}/edit', [ProductController::class, 'editGet'])->middleware('check.role:EditProduct');

            // 6. تحديث بيانات المنتج (استخدام POST للتوافق مع رفع الملفات)
            Route::post('/{product}', [ProductController::class, 'edit'])->middleware('check.role:EditProduct');

            // 7. حذف المنتج (حذف ناعم بتغيير الحالة إلى 9)
            Route::delete('/{product}', [ProductController::class, 'delete'])->middleware('check.role:DeleteProduct');

            // 8. تفعيل المنتج (تغيير الحالة إلى 1)
            Route::put('/{product}/active', [ProductController::class, 'active'])->middleware('check.role:ActiveProduct');

            // 9. إلغاء تفعيل المنتج (تغيير الحالة إلى 0)
            Route::put('/{product}/disActive', [ProductController::class, 'disActive'])->middleware('check.role:DisActiveProduct');
        }
    );
    # # # # # # # # # # # # # # # # # End Admin Product  # # # # # # # # # # # # # # # #


    # # # # # # # # # # # # # # # # #  Admin Caterer's Products (Global)  # # # # # # # # # # # # # # # # #
    Route::prefix('caterer-product')->controller(CatererProductController::class)->group(
        function () {
            // 1. جلب قائمة بجميع منتجات التشاركيات (مع إمكانية البحث والفلترة)
            Route::get('/', [CatererProductController::class, 'index'])->middleware('check.role:ReadCatererProduct');

            // 2. جلب البيانات اللازمة لإنشاء علاقة جديدة (قائمة التشاركيات والمنتجات)
            Route::get('/new', [CatererProductController::class, 'new'])->middleware('check.role:CreateCatererProduct');


            // 3. تخزين علاقة جديدة
            Route::post('/', [CatererProductController::class, 'store'])->middleware('check.role:CreateCatererProduct');

            // 4. جلب بيانات علاقة معينة للتعديل
            Route::get('/{catererProduct}/edit', [CatererProductController::class, 'editGet'])->middleware('check.role:EditCatererProduct');

            // 5. تحديث بيانات علاقة معينة
            Route::put('/{catererProduct}', [CatererProductController::class, 'edit'])->middleware('check.role:EditCatererProduct');

            // 6. حذف علاقة معينة
            Route::delete('/{catererProduct}', [CatererProductController::class, 'delete'])->middleware('check.role:DeleteCatererProduct');
        }
    );
    # # # # # # # # # # # # # # # # # End Admin Caterer's Products (Global) # # # # # # # # # # # # # # # #



    # # # # # # # # # # # # # # # # #  Admin Users  # # # # # # # # # # # # # # # # #
    Route::controller(UserController::class)->prefix('user')->group(
        function () {
            // 1. جلب جميع المستخدمين
            Route::get('/', [UserController::class, 'index'])->middleware('check.role:ReadUser');

            // 2. جلب مستخدم معين
            Route::get('/{user}', [UserController::class, 'show'])->middleware('check.role:ReadUser');

            // 3. إنشاء مستخدم جديد
            Route::post('/', [UserController::class, 'store'])->middleware('check.role:CreateUser');

            // 4. تحديث مستخدم معين
            Route::put('/{user}', [UserController::class, 'update'])->middleware('check.role:EditUser');

            // 5. حذف مستخدم معين
            Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('check.role:DeleteUser');

            // 6. تفعيل مستخدم
            Route::post('/{user}/activate', [UserController::class, 'activate'])->middleware('check.role:ActiveUser');

            // 7. إلغاء تفعيل مستخدم
            Route::post('/{user}/deactivate', [UserController::class, 'deactivate'])->middleware('check.role:DisActiveUser');

            // 8. حظر مستخدم
            Route::post('/{user}/ban', [UserController::class, 'ban'])->middleware('check.role:BanUser');

            // 9. إلغاء حظر مستخدم
            Route::post('/{user}/unban', [UserController::class, 'unban'])->middleware('check.role:UnbanUser');
        }
    );
    # # # # # # # # # # # # # # # # # End Admin Users # # # # # # # # # # # # # # # #



});
