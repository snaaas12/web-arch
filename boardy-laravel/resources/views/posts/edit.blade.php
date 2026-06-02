@extends('layouts.app-old-style')

@section('title', 'Редактировать пост')

@section('content')
    <div class="card">
        <h1>Редактировать пост</h1>

        <form action="{{ route('posts.update', $post) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">Заголовок</label>
                <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}" required>
                @error('title')
                    <div class="error" style="margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="body">Содержание</label>
                <textarea name="body" id="body" rows="10" required>{{ old('body', $post->body) }}</textarea>
                @error('body')
                    <div class="error" style="margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit">Сохранить</button>
                <a href="{{ route('posts.show', $post) }}" class="link">Отмена</a>
            </div>
        </form>
    </div>
@endsection
