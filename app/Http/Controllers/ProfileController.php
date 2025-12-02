<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // ===============================
        // БРОНИРОВАНИЯ ПОЛЬЗОВАТЕЛЯ
        // ===============================
        $bookings = Booking::with('quest')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        // ===============================
        // ПОПОЛНЕНИЕ БАЛАНСА
        // ===============================
        if ($request->isMethod('post') && $request->has('topup')) {

            DB::transaction(function () use ($user) {

                $user->increment('balance', 500);

                WalletTransaction::create([
                    'user_id'    => $user->id,
                    'type'       => 'topup',
                    'amount'     => 500,
                    'created_at' => now(),
                ]);
            });

            return redirect()->back()->with('success', 'Баланс пополнен на 500 ₽');
        }

        // ===============================
        // ОТМЕНА БРОНИ
        // ===============================
        if ($request->isMethod('post') && $request->has('cancel_id')) {

            $bookingId = $request->input('cancel_id');

            /** @var Booking|null $booking */
            $booking = Booking::where('id', $bookingId)
                ->where('user_id', $user->id)
                ->where('status', 'paid')
                ->first();

            if (!$booking) {
                return back()->with('error', '❌ Это бронирование нельзя отменить.');
            }

            DB::transaction(function () use ($booking, $user) {

                // Меняем статус
                $booking->update(['status' => 'canceled']);

                // Возвращаем деньги
                $user->increment('balance', $booking->total_price);

                // Логируем транзакцию
                WalletTransaction::create([
                    'user_id'    => $user->id,
                    'type'       => 'refund',
                    'amount'     => $booking->total_price,
                    'created_at' => now(),
                ]);
            });

            return back()->with(
                'success',
                "✅ Бронирование отменено. Возвращено {$booking->total_price} ₽."
            );
        }

        return view('profile.index', compact('user', 'bookings'));
    }
}
