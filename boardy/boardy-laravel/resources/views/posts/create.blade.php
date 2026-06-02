@extends('layouts.old')

@section('title', 'Добавить пост - Boardy')

@section('content')
<div class="card">
    <h1>Добавить пост</h1>

    <form method="POST" action="{{ route('posts.store') }}">
        @csrf

        <div class="form-group">
            <label>Заголовок</label>
            <input type="text" name="title" required value="{{ old('title') }}">
        </div>

        <div class="form-group">
            <label>Содержание</label>
            <textarea name="body" rows="10" required>{{ old('body') }}</textarea>
        </div>

        <button type="submit">Опубликовать</button>
    </form>
</div>
@endsection
