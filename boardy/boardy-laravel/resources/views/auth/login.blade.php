<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в Boardy</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f4f6fb;
            font-family: Arial, sans-serif;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 24px;
            text-align: center;
            color: #1e293b;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 14px;
            border: 1px solid #dbe1ea;
            border-radius: 10px;
            font-size: 14px;
        }

        button,
        .oauth-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
            transition: 0.2s;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .login-btn {
            background: #4f46e5;
            color: white;
        }

        .login-btn:hover {
            background: #4338ca;
        }

        .oauth-btn {
            background: #0f172a;
            color: white;
            margin-top: 12px;
        }

        .oauth-btn:hover {
            background: #1e293b;
        }

        .links {
            margin-top: 24px;
            text-align: center;
            font-size: 14px;
        }

        .links a {
            color: #4f46e5;
            text-decoration: none;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        hr {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 24px 0;
        }
    </style>
</head>
<body>

<div class="card">

    <h1>Вход в Boardy</h1>

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <input
            type="email"
            name="email"
            placeholder="Email"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Пароль"
            required
        >

        @if(request()->has('client_id'))
            <input type="hidden" name="client_id" value="{{ request()->client_id }}">
            <input type="hidden" name="redirect_uri" value="{{ request()->redirect_uri }}">
            <input type="hidden" name="response_type" value="{{ request()->response_type }}">
            <input type="hidden" name="state" value="{{ request()->state }}">
            <input type="hidden" name="scope" value="{{ request()->scope }}">
        @endif

        <button class="login-btn" type="submit">
            Войти
        </button>
    </form>

    <hr>

    <a
        class="oauth-btn"
        href="/oauth/redirect"
    >
        Войти через OAuth
    </a>

    <div class="links">
        <a href="{{ route('register') }}">
            Регистрация
        </a>

        |
        
        <a href="{{ route('auth.github') }}">
            GitHub
        </a>
    </div>

</div>

</body>
</html>