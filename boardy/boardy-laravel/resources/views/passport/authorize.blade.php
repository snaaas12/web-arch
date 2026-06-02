<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth авторизация</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="w-full max-w-md bg-white shadow-xl rounded-2xl p-8">

    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Авторизация приложения
        </h1>

        <p class="text-gray-500 mt-2">
            Приложение хочет получить доступ к вашему аккаунту
        </p>
    </div>

    <div class="bg-gray-50 rounded-xl p-4 mb-6 border">
        <div class="text-sm text-gray-500 mb-1">
            Клиент
        </div>

        <div class="font-semibold text-gray-800">
            {{ $client->name }}
        </div>

        <div class="text-xs text-gray-400 mt-2 break-all">
            redirect_uri:
            {{ request('redirect_uri') }}
        </div>
    </div>

    <div class="flex gap-3">

        <form
            method="POST"
            action="{{ route('passport.authorizations.approve') }}"
            class="flex-1"
        >
            @csrf

            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">

            <button
                type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-xl transition"
            >
                Разрешить
            </button>
        </form>

        <form
            method="POST"
            action="{{ route('passport.authorizations.deny') }}"
            class="flex-1"
        >
            @csrf
            @method('DELETE')

            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">

            <button
                type="submit"
                class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-xl transition"
            >
                Отмена
            </button>
        </form>

    </div>

</div>

</body>
</html>

