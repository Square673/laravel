@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="card-title mb-3">{{ $quest->title }}</h2>
                <p class="text-muted mb-2">
                    Сложность: {{ $quest->difficulty }} ·
                    Длительность: {{ $quest->duration }} мин
                </p>
                <p>{{ $quest->description }}</p>
                <p class="fs-5 fw-bold mt-3">Стоимость: {{ $quest->price }} ₽</p>

                <a href="{{ route('booking.form', $quest->id) }}" class="btn btn-primary mt-3">Забронировать</a>
            </div>
        </div>

        <a href="{{ route('home') }}" class="btn btn-outline-secondary">← Назад к списку</a>
    </div>
</div>
@endsection
