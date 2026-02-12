<?php

namespace App\Features\App\v1\Controllers;

use App\Features\App\v1\Models\HallBooking;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Features\Admin\v1\Resources\AdminResource;
use App\Features\App\v1\Models\Hall;
use App\Features\App\v1\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class HallController extends Controller
{

    //  List Halls
    public function index(Request $request)
    {


        if ($request->count)
            $count = $request->count;
        else
            $count = 1;

        $list = Hall::latest()
            ->notDeleted()
            ->isActive()
            ->where('name', 'like', '%' . $request->name . '%')
            ->paginate($count);
        if ($list->isEmpty())
            return $this->empty();
        return response()->json(['success' => true, 'message' => 'تم جلب  القاعات بنجاح', 'data' => $list], 200);
    }


    public function showOld($id)
    {
        if (Validator::make(['id' => $id], [
            'id' => 'required|numeric'
        ])->fails()) {
            return $this->badRequest("يجب عليك ادخال رقم صحيح");
        }

        $item = Hall::notDeleted()->where('id', $id)->first();
        if (!$item)
            return $this->empty();
        return response()->json(['success' => true, 'message' => 'تم جلب القاعة بنجاح', 'data' => $item], 200);
    }

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], ['id' => 'required|numeric']);
        if ($validator->fails()) {
            return $this->badRequest("يجب عليك ادخال رقم صحيح");
        }

        $today = Carbon::today();
        $endDate = Carbon::today()->addMonths(12);

        $hall = Hall::notDeleted()
            ->with(['bookings' => function ($query) use ($today, $endDate) {
                // 1. فلترة الحجوزات حسب النطاق الزمني
                $query->whereBetween('booking_date', [$today, $endDate]);
                // 2. فلترة الحجوزات لتشمل فقط الحالات التي تعتبر "محجوزة"
                $query->whereIn('status', [
                    'confirmed',
                    'fully_paid',
                    'in_progress',
                    'upcoming',
                    'pending_payment',
                    'pending_approval',
                    // لا نضمن 'pending_approval' أو 'pending_payment' لأنها ليست مؤكدة بعد
                ]);

                // 3. اختيار أعمدة محددة فقط لتقليل حجم البيانات المرسلة
                $query->select('hall_id', 'booking_date', 'booking_period');
            }])
            ->find($id);

        if (!$hall) {
            return $this->empty();
        }

        // يمكنك إعادة تسمية مفتاح الحجوزات ليكون أوضح في الـ API
        $hallData = $hall->toArray();
        $hallData['future_bookings'] = $hallData['bookings'];
        unset($hallData['bookings']);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب القاعة بنجاح',
            'data' => $hallData
        ], 200);
    }



    // Make A new Book 
    public function book(Request $request)
    {

        // 1. التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'hall_id'            => 'required|exists:halls,id',
            'booking_date'       => 'required|date|after_or_equal:today',
            'booking_period'     => 'required|in:morning,evening',
            'event_type'         => 'required|string|max:100',
            'event_for'          => 'required|in:men,women,both',
            'event_owner_name'   => 'required|string|max:100',
            'notes'              => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors()->first());
        }


        // 2. جلب بيانات القاعة
        $hall = Hall::find($request->hall_id);

        if (!$hall) {
            return $this->badRequest('القاعة غير موجودة.');
        }

        // تحديد السعر حسب الفترة
        $totalPrice = $request->booking_period === 'morning'
            ? $hall->price_morning
            : $hall->price_evening;

        // قيمة العربون من القاعة
        $deposit = $hall->deposit ?? 0;

        $exists = HallBooking::where('hall_id', $request->hall_id)
            ->where('booking_date', $request->booking_date)
            ->where('booking_period', $request->booking_period)
            ->whereNull('deleted_at')
            ->whereIn('status', [
                'confirmed',
                'fully_paid',
                'in_progress',
                'upcoming'
            ])
            ->exists();

        if ($exists) {
            return $this->badRequest('هذه القاعة محجوزة في نفس التاريخ والفترة.');
        }

        // 3. التأكد من أن المستخدم لا يملك أي حجز في نفس التاريخ والفترة
        $existsForUser = HallBooking::where('hall_id', $request->hall_id)
            ->where('user_id', $request->user()->id)
            ->where('booking_date', $request->booking_date)
            ->where('booking_period', $request->booking_period)
            ->whereNull('deleted_at')
            ->whereIn('status', [
                'pending_approval',
                'pending_payment',
                'confirmed',
                'fully_paid',
                'in_progress',
                'upcoming'
            ])
            ->exists();

        if ($existsForUser) {
            return $this->badRequest('لديك بالفعل حجز آخر في نفس التاريخ والفترة في هذه القاعة.');
        }


        // 3. إنشاء الحجز
        $booking = HallBooking::create([
            'hall_id'             => $request->hall_id,
            'user_id'             => $request->user()->id,
            'booking_date'        => $request->booking_date,
            'booking_period'      => $request->booking_period,
            'status'              => 'pending_approval',
            'event_type'          => $request->event_type,
            'event_for'           => $request->event_for,
            'event_owner_name'    => $request->event_owner_name,
            'total_price'         => $totalPrice,
            'down_payment_amount' => $deposit,
            'remaining_amount'    => $totalPrice,
            'notes'               => $request->notes,
        ]);

        // 4. الرد
        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الحجز بنجاح',
            'data'    => $booking
        ], 201);
    }

    // End of Controller
}
