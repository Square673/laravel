@extends('layouts.app')

@section('content')
<div class="container my-5">
    <h2 class="text-center mb-4">Личный кабинет</h2>

    {{-- Сообщения --}}
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif

    {{-- Информация о пользователе --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5>👤 {{ $user->name }}</h5>

            <p><strong>Телефон:</strong> {{ $user->phone }}</p>
            <p><strong>Баланс:</strong> <span class="text-success fw-bold">{{ $user->balance }} ₽</span></p>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
                    ✏️ Редактировать профиль
                </a>

                <form method="post">
                    @csrf
                    <button type="submit" name="topup" class="btn btn-success">
                        Пополнить баланс +500 ₽
                    </button>
                </form>

                {{-- Видим кнопку только для админов --}}
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.index') }}" class="btn btn-warning">Перейти в админ панель</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Таблица бронирований --}}
    <h4 class="mb-3">Мои бронирования</h4>

    @if($bookings->isEmpty())
        <div class="alert alert-info">У вас пока нет бронирований.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Квест</th>
                        <th>Дата</th>
                        <th>Время</th>
                        <th>Игроков</th>
                        <th>Статус</th>
                        <th>Стоимость</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $b)
                    <tr>
                        <td>{{ $b->quest->title ?? 'Квест удалён' }}</td>
                        <td>{{ $b->date }}</td>
                        <td>{{ $b->time }}</td>
                        <td>{{ $b->players_count }}</td>
                        <td>
                            @if($b->status === 'paid')
                                <span class="badge bg-success">Оплачено</span>
                            @elseif($b->status === 'canceled')
                                <span class="badge bg-secondary">Отменено</span>
                            @else
                                <span class="badge bg-warning text-dark">{{ $b->status }}</span>
                            @endif
                        </td>
                        <td>{{ $b->total_price }} ₽</td>
                        <td>
                            @if($b->status === 'paid')
                                <form method="post" onsubmit="return confirm('Отменить бронь?');">
                                    @csrf
                                    <input type="hidden" name="cancel_id" value="{{ $b->id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Отменить</button>
                                </form>
                            @else
                                <span class="text-muted">–</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
