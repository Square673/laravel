<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Получение бронирования пользователя
        $bookings = DB::table('bookings')
            ->join('quests', 'quests.id', '=', 'bookings.quest_id')
            ->where('user_id', $user->id)
            ->select(
                'bookings.*',
                'quests.title as quest_title',
                'quests.duration'
            )
            ->orderByDesc('bookings.id')
            ->get();

        // Обработка пополнения
        if ($request->isMethod('post') && $request->has('topup')) {
            DB::table('users')->where('id', $user->id)->increment('balance', 500);

            DB::table('wallet_transactions')->insert([
                'user_id' => $user->id,
                'type' => 'topup',
                'amount' => 500,
                'created_at' => now()
            ]);

            return redirect()->back()->with('success', 'Баланс пополнен на 500 ₽');
        }

        // Обработка отмены брони
        if ($request->isMethod('post') && $request->has('cancel_id')) {
    $bookingId = $request->input('cancel_id');

    $booking = DB::table('bookings')
        ->where('id', $bookingId)
        ->where('user_id', $user->id)
        ->where('status', 'paid')
        ->first();

    if (!$booking) {
        return redirect()->back()->with('error', '❌ Это бронирование нельзя отменить.');
    }
    // Изменение в таблице статуса и возвра денег
    DB::transaction(function () use ($booking, $user) {
        DB::table('bookings')->where('id', $booking->id)->update(['status' => 'canceled']);

        DB::table('users')->where('id', $user->id)->increment('balance', $booking->total_price);

        DB::table('wallet_transactions')->insert([
            'user_id' => $user->id,
            'type' => 'refund',
            'amount' => $booking->total_price,
            'created_at' => now(),
        ]);
    });

    return redirect()->back()->with('success', "✅ Бронирование отменено. Возвращено {$booking->total_price} ₽.");
}


        return view('profile.index', compact('user', 'bookings'));
    }
}
