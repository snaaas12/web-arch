@extends('layouts.old')

@section('title', $post->title . ' - Boardy')

@section('content')
    <div class="card">
        <h1>{{ $post->title }}</h1>
        
        <div class="post-author" style="margin-bottom: 0.5rem;">
            Автор: {{ $post->author->name }}
        </div>
        
        <div class="post-body" style="margin: 1rem 0;">
            {{ $post->body }}
        </div>
        
        <div class="post-date">
            {{ $post->created_at->format('d.m.Y H:i') }}
        </div>
        
        @can('update', $post)
            <div class="post-actions" style="margin-top: 1rem;">
                <a href="{{ route('posts.edit', $post) }}" class="link">Редактировать</a>
                <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="delete-btn" onclick="return confirm('Удалить пост?')">Удалить</button>
                </form>
            </div>
        @endcan
    </div>

    <!-- Комментарии -->
    <div class="card" style="margin-top: 1rem;">
        <h3>Комментарии ({{ $post->comments->count() }})</h3>
        
        @auth
            <form action="{{ route('comments.store') }}" method="POST" class="comment-form">
                @csrf
                <input type="hidden" name="post_id" value="{{ $post->id }}">
                <textarea name="body" rows="3" placeholder="Напишите комментарий..." required></textarea>
                <button type="submit">Отправить</button>
            </form>
        @else
            <p>
                <a href="{{ route('login') }}" class="link">Войдите</a>, чтобы оставить комментарий.
            </p>
        @endauth
        
        @forelse ($post->comments as $comment)
            <div class="comment">
                <div class="comment-author">{{ $comment->author->name }}</div>
                <div class="comment-body">{{ $comment->body }}</div>
                <div class="comment-date">{{ $comment->created_at->diffForHumans() }}</div>
            </div>
        @empty
            <p class="text-center" style="color: #999;">Нет комментариев. Будьте первым!</p>
        @endforelse
    </div>
@endsection
