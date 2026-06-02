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

                <form action="{{ route('posts.destroy', $post) }}"
                      method="POST"
                      style="display: inline;">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="delete-btn"
                            onclick="return confirm('Удалить пост?')">
                        Удалить
                    </button>
                </form>
            </div>
        @endcan
    </div>

    {{-- React comments root --}}
    <div class="card" style="margin-top: 1rem;">
        <div
            id="comments-root"
            data-post-id="{{ $post->id }}"
            data-user-name="{{ auth()->user()?->name }}"
            data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
        ></div>
    </div>
@endsection

@viteReactRefresh
@vite('resources/js/comments.jsx')