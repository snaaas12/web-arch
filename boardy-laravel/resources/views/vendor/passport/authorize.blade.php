<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подтверждение авторизации</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
            text-align: center;
        }
        button {
            background: #1A5276;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        button.decline {
            background: #dc3545;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Подтверждение авторизации</h1>
        <p>Приложение <strong>{{ $client->name }}</strong> запрашивает доступ к вашему аккаунту.</p>
        
        <form method="post" action="{{ route('passport.authorizations.approve') }}" style="display: inline;">
            @csrf
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <button type="submit">Разрешить</button>
        </form>
        
        <form method="post" action="{{ route('passport.authorizations.deny') }}" style="display: inline;">
            @csrf
            @method('DELETE')
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <button type="submit" class="decline">Запретить</button>
        </form>
    </div>
</body>
</html>
