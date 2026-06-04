<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Boardy - Доска объявлений')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
        }
        
        nav {
            background: #1A5276;
            padding: 1rem 2rem;
            display: flex;
            gap: 1.5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        nav a, nav span {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        nav .brand {
            font-size: 1.5rem;
            font-weight: bold;
            margin-right: 1rem;
        }
        
        nav a:hover {
            text-decoration: underline;
        }
        
        nav span {
            margin-left: auto;
        }
        
        main {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        h1 {
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }
        
        input, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        button {
            background: #1A5276;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }
        
        button:hover {
            background: #2c6e9e;
        }
        
        .error {
            background: #fee;
            color: #c00;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .success {
            background: #efe;
            color: #2c6e2c;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .post {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .post-author {
            color: #1A5276;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .post-body {
            color: #333;
            line-height: 1.4;
        }
        
        .post-date {
            color: #999;
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
        
        .comment {
            border-top: 1px solid #eee;
            padding: 0.75rem 0;
        }
        
        .comment-author {
            color: #1A5276;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .comment-body {
            color: #333;
        }
        
        .comment-date {
            color: #999;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }
        
        .link {
            color: #1A5276;
            text-decoration: none;
        }
        
        .link:hover {
            text-decoration: underline;
        }
        
        hr {
            margin: 1rem 0;
            border: none;
            border-top: 1px solid #eee;
        }
        
        .post-actions {
            margin-top: 0.5rem;
        }
        
        .post-actions a, .post-actions button {
            font-size: 0.85rem;
            padding: 0.25rem 0.5rem;
            margin-right: 0.5rem;
            background: #e0e0e0;
            color: #333;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        
        .post-actions .delete-btn {
            background: #dc3545;
            color: white;
        }
        
        .comment-form {
            margin-top: 1rem;
        }
        
        .comment-form textarea {
            margin-bottom: 0.5rem;
        }
        
        .github-btn {
            display: block;
            width: 100%;
            background: #24292e;
            color: white;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            border: none;
            box-sizing: border-box;
            margin-top: 1rem;
        }
        
        .github-btn:hover {
            background: #3a4045;
        }
        
        .or-divider {
            text-align: center;
            margin-bottom: 1.5rem;
            position: relative;
            border-top: 1px solid #dee2e6;
        }
        
        .or-divider span {
            background: white;
            padding: 0 10px;
            position: relative;
            top: -11px;
            color: #6c757d;
            font-size: 14px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 1rem;
        }
        
        .mb-3 {
            margin-bottom: 1rem;
        }
        
        .inline {
            display: inline;
        }
        
        .max-w-400 {
            max-width: 400px;
            margin: 40px auto;
        }
    </style>
</head>
<body>

<nav>
    <a href="{{ route('posts.index') }}" class="brand">Boardy</a>
    <a href="{{ route('posts.index') }}">Все посты</a>
    
    @auth
        <a href="{{ route('posts.create') }}">Добавить пост</a>
    @endauth

    <div style="margin-left: auto; display: flex; gap: 1.5rem; align-items: center;">
        @auth
            <span>Привет, {{ Auth::user()->name }}!</span>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" style="background: transparent; color: white; padding: 0; font-weight: 500;">
                    Выйти
                </button>
            </form>
        @else
            <a href="{{ route('login') }}">Вход</a>
            <a href="{{ route('register') }}">Регистрация</a>
        @endauth
    </div>
</nav>

<main>
    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @yield('content')
</main>

</body>
</html>
