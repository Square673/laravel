<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Quest;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    // Показ формы бронирования
    public function form($id)
    {
        $quest = Quest::findOrFail($id); // 404, если нет квеста
        return view('booking.form', compact('quest'));
    }

    // Создание бронирования
    public function book(Request $request, $id)
    {
        $quest = Quest::findOrFail($id);

        $request->validate([
            'date'    => 'required|date',
            'time'    => 'required',
            'players' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $date    = $request->input('date');
        $time    = $request->input('time');
        $players = (int) $request->input('players');

        // Расчёт длительности и буфера
        $duration = (int)$quest->duration < 75 ? 60 : 90;
        $buffer   = 30;

        $new_start = strtotime("$date $time");
        $new_end   = $new_start + ($duration + $buffer) * 60;

        // Проверка занятости слота
        $bookings = Booking::with('quest')
            ->where('quest_id', $quest->id)
            ->where('date', $date)
            ->where('status', '!=', 'canceled')
            ->get();

        foreach ($bookings as $b) {
            $exist_start    = strtotime("$date " . $b->time);
            $exist_duration = (int)$b->quest->duration < 75 ? 60 : 90;
            $exist_end      = $exist_start + ($exist_duration + $buffer) * 60;

            if ($new_start < $exist_end && $new_end > $exist_start) {
                return back()
                    ->with('error', '❌ Этот слот уже занят.')
                    ->withInput();
            }
        }

        // Проверка баланса
        $total = $quest->price * $players;

        if ($user->balance < $total) {
            return back()
                ->with(
                    'error',
                    "❌ Недостаточно средств. Требуется {$total} ₽, на счету {$user->balance} ₽."
                )
                ->withInput();
        }

        // Запись брони + списание средств + запись транзакции
        DB::transaction(function () use ($user, $quest, $date, $time, $players, $total) {
            Booking::create([
                'user_id'       => $user->id,
                'quest_id'      => $quest->id,
                'date'          => $date,
                'time'          => $time,
                'players_count' => $players,
                'status'        => 'paid',
                'total_price'   => $total,
            ]);

            $user->decrement('balance', $total);

            WalletTransaction::create([
                'user_id'    => $user->id,
                'type'       => 'spend',
                'amount'     => $total,
                'created_at' => now(),
            ]);
        });

        return back()->with(
            'success',
            "✅ Бронирование успешно! С вашего счёта списано {$total} ₽."
        );
    }
}
