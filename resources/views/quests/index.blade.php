@extends('layouts.app')

@section('content')
<div class="text-center mb-5">
    <h1 class="fw-bold mb-2">Выберите квест</h1>
    <p class="text-muted">Оцените сложность и выберите приключение по душе</p>
</div>

<div class="row g-4">
    @foreach($quests as $quest)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-semibold">{{ $quest->title }}</h5>
                    <p class="card-text small text-muted mb-2">
                        Сложность: {{ $quest->difficulty }} · Длительность: {{ $quest->duration }} мин
                    </p>
                    <p class="card-text flex-grow-1">{{ Str::limit($quest->description, 120) }}</p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <a href="{{ route('quest.show', $quest->id) }}" class="btn btn-primary">Подробнее</a>
                        <span class="fw-bold fs-5">{{ $quest->price }} ₽</span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
