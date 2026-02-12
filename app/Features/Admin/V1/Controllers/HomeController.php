<?php

namespace App\Features\Admin\v1\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Features\Admin\v1\Resources\AdminResource;
use App\Features\Admin\v1\Models\Admin;
use App\Features\Admin\v1\Models\MoamalatDeposit;
use App\Features\Admin\v1\Models\Transaction;
use App\Features\Admin\v1\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{


    // Load Home Statisic
    public function index()
{
    // التواريخ الأساسية
    $todayStart = Carbon::today();
    $yesterdayStart = Carbon::yesterday();
    $yesterdayEnd = Carbon::yesterday()->endOfDay();
    $lastWeekStart = Carbon::now()->subDays(6)->startOfDay();
    $lastMonthStart = Carbon::now()->subDays(29)->startOfDay();
    $prevMonthStart = Carbon::now()->subDays(59)->startOfDay();
    $prevMonthEnd = Carbon::now()->subDays(30)->endOfDay();




    return response()->json([
        'success' => true,
        'message' => 'تم جلب البيانات بنجاح',
        'data' => [
                'admins' => '4',
                'roles' => '2',
                'today' => 'الجمعة',
        ]
    ], 200);
}

    // End of Controller
}
