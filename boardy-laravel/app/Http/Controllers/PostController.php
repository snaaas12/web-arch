<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('author')->latest()->paginate(10);
        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body'  => 'required|string|min:10',
        ]);

        $post = $request->user()->posts()->create($data);

        $post->load('author');

        Redis::publish('new_post', json_encode([
            'id'         => $post->id,
            'title'      => $post->title,
            'body'       => $post->body,
            'author'     => $post->author->name,
            'author_id'  => $post->user_id,
            'created_at' => $post->created_at->toISOString(),
        ]));

        return redirect()->route('posts.index')
            ->with('success', 'Пост успешно создан!');
    }
    public function show(Post $post)
    {
        $post->load('author');
        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        Gate::authorize('update', $post);
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        Gate::authorize('update', $post);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body'  => 'required|string|min:10',
        ]);

        $post->update($data);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Пост обновлён!');
    }

    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Пост удалён!');
    }
}
