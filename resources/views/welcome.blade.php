@extends('layouts.app')

@section('content')
<div class="text-center py-5">
    <h1 class="mb-4">Добро пожаловать в QuestBooking!</h1>
    <p class="lead">Выбирайте, бронируйте и проходите лучшие квесты в городе.</p>
    <a href="{{ route('home') }}" class="btn btn-primary mt-3">Перейти к квестам</a>
</div>
@endsection
