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
    public function index(Request $request)
    {
        $search = $request->get('search');  // Получаем запрос от пользователя

        $bookings = Booking::with(['user', 'quest'])
            ->orderByRaw('strftime("%Y-%m-%d %H:%M", date || " " || time) DESC'); // Сортировка по дате и времени

        // Поиск по номеру телефона
        if ($search) {
            $bookings = $bookings->whereHas('user', function ($query) use ($search) {
                $query->where('phone', 'like', '%'.$search.'%'); // Поиск по полю phone
            });
        }

        $bookings = $bookings->get();

        $quests = Quest::all();
        $users  = User::all();

        return view('admin.index', compact('bookings', 'quests', 'users', 'search'));
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
            'user_phone'  => 'required|string',
            'quest_id'    => 'required|exists:quests,id',
            'date'        => 'required|date',
            'time'        => 'required',
            'players_count'=> 'required|integer|min:1',
        ]);

        // Проверяем, есть ли пользователь с таким номером телефона
        $user = User::where('phone', $validated['user_phone'])->first();

        // Если пользователя нет, создаём нового пользователя
        if (!$user) {
            // Создаем нового пользователя с базовым паролем
            $user = User::create([
                'name'     => 'Пользователь ' . $validated['user_phone'], // Имя по умолчанию
                'phone'    => $validated['user_phone'], // Номер телефона
                'password' => bcrypt('password123'), // Базовый пароль
            ]);
        }

        // Создаем бронь с привязкой к найденному или новому пользователю
        $booking = Booking::create([
            'quest_id'    => $validated['quest_id'],
            'date'        => $validated['date'],
            'time'        => $validated['time'],
            'players_count'=> $validated['players_count'],
            'status'      => 'pending', // Статус можно поставить в ожидание
            'total_price' => Quest::find($validated['quest_id'])->price * $validated['players_count'],
            'phone'       => $validated['user_phone'], // Сохраняем номер телефона
            'user_id'     => $user->id, // Привязываем к пользователю
        ]);

        return redirect()->back()->with('success', 'Бронь добавлена!');
    }

    // ======================================================================
    // ПОИСК ПОЛЬЗОВАТЕЛЕЙ ПО НОМЕРУ ТЕЛЕФОНА (для автозаполнения)
    // ======================================================================
    public function searchUser(Request $request)
    {
        // Получаем запрос для поиска
        $search = $request->get('q');

        // Ищем пользователей по номеру телефона
        $users = User::where('phone', 'like', "%$search%")->get();

        // Возвращаем данные в формате JSON
        return response()->json($users);
    }
}
