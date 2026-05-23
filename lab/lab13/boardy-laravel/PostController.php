<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        // Создаём пост
        $post = $request->user()->posts()->create($data);
        
        // Загружаем автора для broadcast
        $post->load('author');

        // Отправляем broadcast в FastAPI через WebSocket
        try {
            $fastapiUrl = config('app.fastapi_url', 'http://localhost:8000');
            $response = Http::timeout(2)->post($fastapiUrl . '/internal/broadcast', [
                'id'         => $post->id,
                'title'      => $post->title,
                'body'       => $post->body,
                'author'     => $post->author->name,
                'author_id'  => $post->author_id,
                'created_at' => $post->created_at->toISOString(),
            ]);
            
            Log::info('Broadcast sent to FastAPI', ['post_id' => $post->id, 'response' => $response->status()]);
            
        } catch (\Exception $e) {
            // Пост всё равно создаётся, даже если broadcast не удался
            Log::warning('WebSocket broadcast failed: ' . $e->getMessage());
        }

        return redirect()->route('posts.index')
            ->with('success', 'Пост успешно создан!');
    }

    public function show(Post $post)
    {
        $post->load('author', 'comments.author');
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