@extends('layouts.app')

@section('content')
<div class="container my-5">

    <h2 class="mb-4">Редактирование профиля</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <!-- Редактирование данных -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="mb-3">Личные данные</h4>

                    <form method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Имя</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Телефон</label>
                            <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" required>
                        </div>

                        <button class="btn btn-primary">Сохранить</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Смена пароля -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="mb-3">Смена пароля</h4>

                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Текущий пароль</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Новый пароль</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Подтверждение пароля</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <button class="btn btn-warning">Изменить пароль</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

