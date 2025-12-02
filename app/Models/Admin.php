<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Models\Quest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        // Доступ только админу
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || strtolower(Auth::user()->role ?? '') !== 'admin') {
                abort(403, 'Доступ запрещен');
            }
            return $next($request);
        });
    }

    // ======================================================================
    // СТРАНИЦА АДМИНКИ
    // ======================================================================
    public function index()
    {
        $bookings = Booking::with(['user', 'quest'])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'asc')
            ->get();

        $quests = Quest::all();
        $users  = User::all();

        return view('admin.index', compact('bookings', 'quests', 'users'));
    }

    // ======================================================================
    // ОТМЕНА БРОНИ (с возвратом денег)
    // ======================================================================
    public function cancel($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return back()->with('error', 'Бронирование не найдено.');
        }

        DB::transaction(function () use ($booking) {

            // 1. Отмечаем как отменённое
            $booking->update(['status' => 'canceled']);

            // 2. Возвращаем деньги пользователю
            $booking->user->increment('balance', $booking->total_price);

            // 3. Сохраняем транзакцию
            WalletTransaction::create([
                'user_id'    => $booking->user_id,
                'type'       => 'refund',
                'amount'     => $booking->total_price,
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Бронирование отменено и средства возвращены.');
    }

    // ======================================================================
    // РУЧНОЕ ДОБАВЛЕНИЕ БРОНИ АДМИНОМ
    // ======================================================================
    public function addBooking(Request $request)
    {
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'quest_id'      => 'required|exists:quests,id',
            'date'          => 'required|date',
            'time'          => 'required',
            'players_count' => 'required|integer|min:1',
        ]);

        $quest = Quest::find($validated['quest_id']);

        // Проверка, занят ли слот
        $busy = Booking::where('quest_id', $validated['quest_id'])
            ->where('date', $validated['date'])
            ->where('time', $validated['time'])
            ->where('status', '!=', 'canceled')
            ->exists();

        if ($busy) {
            return back()->with('error', 'Этот слот уже занят.')->withInput();
        }

        // Создание брони
        Booking::create([
            'user_id'       => $validated['user_id'],
            'quest_id'      => $validated['quest_id'],
            'date'          => $validated['date'],
            'time'          => $validated['time'],
            'players_count' => $validated['players_count'],
            'status'        => 'paid',
            'total_price'   => $quest->price * $validated['players_count'],
        ]);

        return back()->with('success', 'Бронирование успешно добавлено.');
    }
}
