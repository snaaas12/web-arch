@extends('layouts.old')

@section('title', 'Регистрация - Boardy')

@section('content')
<div class="card">
    <h1>Регистрация</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label>Имя</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label>Пароль (мин. 6 символов)</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Подтверждение пароля</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <button type="submit">Зарегистрироваться</button>
    </form>

    <hr>

    <p>Уже есть аккаунт? <a href="{{ route('login') }}" class="link">Войдите</a></p>
</div>
@endsection
