@extends('layouts.old')

@section('title', 'Все посты - Boardy')

@section('content')
    <h1>Все посты</h1>
    
    @forelse ($posts as $post)
        <div class="post">
            <div class="post-author">
                <a href="{{ route('posts.show', $post) }}" class="link">{{ $post->title }}</a>
            </div>
            <div class="post-body">{{ Str::limit($post->body, 200) }}</div>
            <div class="post-date">
                Автор: {{ $post->author->name }} | {{ $post->created_at->format('d.m.Y H:i') }}
            </div>
        </div>
    @empty
        <div class="card">
            <p>Пока нет ни одного поста. Будьте первым!</p>
        </div>
    @endforelse

    <div class="mt-3">
        {{ $posts->links() }}
    </div>
@endsection
