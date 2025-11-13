<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function form($id)
    {
        $quest = DB::table('quests')->where('id', $id)->first();
        abort_if(!$quest, 404);
        return view('booking.form', compact('quest'));
    }

    public function book(Request $request, $id)
    {
        $quest = DB::table('quests')->where('id', $id)->first();
        abort_if(!$quest, 404);

        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'players' => 'required|integer|min:1'
        ]);

        // Полуаем данные
        $user_id = auth()->id(); 
        $date = $request->input('date');
        $time = $request->input('time');
        $players = (int)$request->input('players');

        // Проверка не занят ли слот
        $duration = (int)$quest->duration < 75 ? 60 : 90;
        $buffer = 30;
        $new_start = strtotime("$date $time");
        $new_end = $new_start + ($duration + $buffer) * 60;

        $bookings = DB::table('bookings')
            ->join('quests', 'quests.id', '=', 'bookings.quest_id')
            ->where('bookings.quest_id', $quest->id)
            ->where('bookings.date', $date)
            ->where('bookings.status', '!=', 'canceled')
            ->get();

        foreach ($bookings as $b) {
            $exist_start = strtotime("$date " . $b->time);
            $exist_end = $exist_start + (((int)$b->duration < 75 ? 60 : 90) + 30) * 60;
            if ($new_start < $exist_end && $new_end > $exist_start) {
                return back()->with('error', '❌ Этот слот уже занят.')->withInput();
            }
        }

        // Проверка баланса
        $user = DB::table('users')->where('id', $user_id)->first();
        $total = $quest->price * $players;
        if ($user->balance < $total) {
            return back()->with('error', "❌ Недостаточно средств. Требуется {$total} ₽, на счету {$user->balance} ₽.")->withInput();
        }


        // Запись брони
        DB::transaction(function () use ($user_id, $quest, $date, $time, $players) {
            $total = $quest->price * $players;

            DB::table('bookings')->insert([
                'user_id' => $user_id,
                'quest_id' => $quest->id,
                'date' => $date,
                'time' => $time,
                'players_count' => $players,
                'status' => 'paid',
                'total_price' => $total,
            ]);

            DB::table('users')->where('id', $user_id)->decrement('balance', $total);

            DB::table('wallet_transactions')->insert([
                'user_id' => $user_id,
                'type' => 'spend',
                'amount' => $total,
                'created_at' => now()
            ]);
        });

        return back()->with('success', "✅ Бронирование успешно! С вашего счёта списано {$quest->price} ₽.");
    }
}
