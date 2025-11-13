<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

    // Список брони
    public function index()
    {
        $bookings = DB::table('bookings')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->join('quests', 'bookings.quest_id', '=', 'quests.id')
            ->select('bookings.*', 'users.name as user_name', 'quests.title as quest_title')
            ->orderBy('bookings.date', 'desc')
            ->orderBy('bookings.time', 'asc')
            ->get();

        $quests = DB::table('quests')->get();
        $users  = DB::table('users')->get();

        return view('admin.index', compact('bookings', 'quests', 'users'));
    }

    // Отмена брони с возвратом средств
    public function cancel($id)
    {
        $booking = DB::table('bookings')->where('id', $id)->first();
        if (!$booking) {
            return back()->with('error', 'Бронирование не найдено.');
        }

        DB::transaction(function () use ($booking) {
            DB::table('bookings')->where('id', $booking->id)->update(['status' => 'canceled']);
            DB::table('users')->where('id', $booking->user_id)->increment('balance', $booking->total_price);
            DB::table('wallet_transactions')->insert([
                'user_id'    => $booking->user_id,
                'type'       => 'refund',
                'amount'     => $booking->total_price,
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Бронирование отменено и средства возвращены.');
    }

    // Ручное добавление брони админом
    public function addBooking(Request $request)
    {
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'quest_id'      => 'required|exists:quests,id',
            'date'          => 'required|date',
            'time'          => 'required',
            'players_count' => 'required|integer|min:1',
        ]);

        $quest = DB::table('quests')->where('id', $validated['quest_id'])->first();
        if (!$quest) {
            return back()->with('error', 'Квест не найден.')->withInput();
        }

        $busy = DB::table('bookings')
            ->where('quest_id', $validated['quest_id'])
            ->where('date',     $validated['date'])
            ->where('time',     $validated['time'])
            ->where('status', '!=', 'canceled')
            ->exists();

        if ($busy) {
            return back()->with('error', 'Этот слот уже занят.')->withInput();
        }

        DB::table('bookings')->insert([
            'user_id'       => $validated['user_id'],
            'quest_id'      => $validated['quest_id'],
            'date'          => $validated['date'],
            'time'          => $validated['time'],
            'players_count' => $validated['players_count'],
            'status'        => 'paid',
            'total_price'   => (int)$quest->price * (int)$validated['players_count'],
        ]);

        return back()->with('success', 'Бронирование успешно добавлено.');
    }
}
