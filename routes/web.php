<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;


// Главная страница
Route::get('/', [QuestController::class, 'index'])->name('home');

// Страница одного квеста
Route::get('/quest/{id}', [QuestController::class, 'show'])->name('quest.show');

// Закрытые маршруты
Route::middleware('auth')->group(function () {
 
    // Бронирование квеста
    Route::get('/booking/{id}', [BookingController::class, 'form'])->name('booking.form');
    Route::post('/booking/{id}', [BookingController::class, 'book'])->name('booking.book');

    // Личный кабинет
    Route::match(['get', 'post'], '/profile', [ProfileController::class, 'index'])->name('profile');
});


Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');


require __DIR__ . '/auth.php';

use App\Http\Controllers\SlotController;

Route::get('/slots/{questId}/{date}', [SlotController::class, 'getSlots'])->name('slots.get');

use App\Http\Controllers\AdminController;

Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/cancel/{id}', [AdminController::class, 'cancel'])->name('admin.cancel');
    Route::post('/admin/add', [AdminController::class, 'addBooking'])->name('admin.add');
});

Route::get('/me', function () {
    if (!auth()->check()) return 'guest';
    return [
        'id'    => auth()->id(),
        'email' => auth()->user()->email,
        'role'  => auth()->user()->role,
    ];
});

