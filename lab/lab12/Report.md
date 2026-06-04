# Lab12: Laravel: переезд на фреймворк (MVC + Breeze + Socialite)

## Часть A. Установка и переключение домена

### 1. Composer и PHP-расширения

Установлен Composer глобально и необходимые PHP-расширения для Laravel.

![](screenshots/composer-php.png)

### 2. Переезд папок

Старый проект переименован в `boardy-legacy`, новый Laravel-проект создан в `/var/www/boardy`.

![](screenshots/folders.png)

Версия Laravel — 11.x (или 13.x в моём случае).

![](screenshots/laravel-version.png)

### 3. Структура Laravel

Назначение пяти основных папок:

| Папка | Назначение |
|-------|------------|
| `app/` | Код приложения: модели, контроллеры, провайдеры, middleware |
| `routes/` | Определение маршрутов: web.php (веб-страницы), api.php (API), console.php (консольные команды) |
| `resources/views/` | Blade-шаблоны (HTML с PHP-вставками) |
| `database/` | Миграции, сидеры, фабрики для работы с БД |
| `public/` | **Document root** веб-сервера (единственная папка, видимая извне) |

**Защитный вопрос:** почему document_root nginx должен указывать на `public/`, а не на `/var/www/boardy/`? Что плохого случится, если указать на корень?

> **Ответ:** Если указать document_root на `/var/www/boardy/`, то злоумышленник сможет получить доступ к `.env` (с паролями БД, ключами шифрования), `vendor/` (код всех пакетов) и `storage/` (логи, сессии, кэш). `public/` содержит только `index.php` (точка входа) и статические файлы (CSS, JS, изображения). Все остальные папки изолированы от внешнего мира.

### 4. Nginx-конфиг

Обновлён конфиг nginx: `root` указывает на `public/`, добавлена `try_files` для красивых URL.

![](screenshots/nginx-config.png)

Приветственная страница Laravel открывается по домену.

![](screenshots/laravel-welcome.png)

**Защитный вопрос:** что делает `try_files $uri $uri/ /index.php?$query_string`? Что произойдёт без этой строки при заходе на `/posts/3`?

> **Ответ:** `try_files` проверяет: существует ли файл с таким именем (`$uri`)? Если нет — существует ли папка (`$uri/`)? Если нет — передаёт управление `index.php`. Без этой строки запрос `/posts/3` вернёт `404 Not Found`, потому что физического файла `posts/3` не существует.

---

## Часть B. БД, миграции, сидер

### 5. Создание БД boardy_main

Создана новая БД `boardy_main` с кодировкой `utf8mb4`, пользователю `boardy` выданы права.

![](screenshots/databases.png)

**Защитный вопрос:** зачем мы создаём новую БД, а не подгоняем старую под Laravel? Что в схеме старой БД мешает?

> **Ответ:** В старой БД `boardy` были таблицы, созданные под чистый PHP:
> - В поле `users.password` использовался `password_hash()`, а Laravel ожидает `password` (метод `Hash::make()`)
> - Не было системных таблиц (`migrations`, `sessions`, `cache`, `jobs`)
> - Отсутствовало поле `github_id` (нужно для Socialite)
> - `posts.body` без заголовка `title` (в Lab8-11 не было заголовков)
> 
> Переделывать старую схему дороже, чем создать новую. Старая БД остаётся для legacy-компонентов (Lab9-11).

### 6. Подключение Laravel к БД

Настроен `.env` для подключения к `boardy_main`. Проверка через tinker.

![](screenshots/tinker-pdo.png)

### 7. Миграции posts и comments

Созданы миграции для `posts` и `comments` по шаблону из методички.

![](screenshots/migrate-status.png)

В БД появились таблицы: `users` (Laravel по умолчанию), `posts`, `comments`, а также системные таблицы (`migrations`, `sessions`, `cache` и др.).

![](screenshots/show-tables.png)

### 8. Модели со связями

Созданы модели `Post` и `Comment`, добавлены связи в модель `User`.

Проверка через tinker:
- `Post::first()->author` — возвращает объект `User`
- `Post::first()->comments` — возвращает `Collection` комментариев

![](screenshots/model-relations.png)

### 9. Сидер

Написаны фабрики `PostFactory` и `CommentFactory`, переписан `DatabaseSeeder`. Запущен сидер.

![](screenshots/seed-counts.png)

---

## Часть C. CRUD постов и комментариев

### 10. Маршруты

Настроен `Route::resource('posts', PostController::class)` и маршрут для комментариев.

![](screenshots/route-list.png)

### 11. Лента постов

Реализован `PostController::index` и шаблон `posts/index.blade.php` с пагинацией (10 постов на страницу). У каждого поста указан автор и дата.

![](screenshots/posts-index.png)

### 12. Страница поста с комментариями

Реализован `PostController::show` и `posts/show.blade.php`: сам пост, автор, список комментариев, форма добавления комментария (только для авторизованных).

![](screenshots/post-show.png)

### 13. Создание поста

Реализованы методы `create` и `store` с валидацией (`title` — обязателен, `body` — обязателен).

Форма создания поста.

![](screenshots/post-create.png)

Пост после создания.

![](screenshots/post-after-create.png)

### 14. Policy и редактирование

Создана `PostPolicy`, добавлена авторизация в контроллер (`$this->authorize(...)`), в шаблоне кнопки edit/delete видны только через `@can`.

Кнопки редактирования/удаления под своим постом.

![](screenshots/edit-own.png)

Попытка редактировать чужой пост → 403 Forbidden.

![](screenshots/edit-foreign-403.png)

**Защитный вопрос:** сравните Policy с тем, как авторизация была реализована в Lab10–11 (на чистом PHP). Сколько строк кода ушло на тот же эффект?

> **Ответ:** В Lab10–11 авторизация реализовывалась вручную: на каждой странице проверка `if ($_SESSION['user_id'] !== $post['author_id']) { die('403'); }`. В среднем 5-10 строк на каждую операцию. В Laravel достаточно одного метода в `PostPolicy` (3 строки) и вызова `$this->authorize()` в контроллере (1 строка). Экономия в 5-10 раз.

### 15. Удаление поста

Реализован метод `destroy` с проверкой через Policy.

Пост удалён, в ленте его больше нет.

![](screenshots/post-deleted.png)

### 16. Комментарий через Blade

Реализован `CommentController::store`. Форма на странице поста. После отправки комментарий виден сразу (с указанием автора и времени).

![](screenshots/comment-created.png)

---

## Часть D. Breeze + Socialite

### 17. Установка Breeze

Установлен Breeze (Blade stack), накатаны миграции, собран фронт через `npm run build`.

Страница регистрации.

![](screenshots/register.png)

Страница логина.

![](screenshots/login.png)

### 18. Регистрация и вход

Регистрация через форму, выход, повторный вход — всё работает.

Состояние после регистрации: пользователь залогинен, имя отображается в navbar.

![](screenshots/after-register.png)

### 19. GitHub OAuth-приложение

Создано OAuth App на GitHub. Callback URL: `https://фамилия.ai-info.ru/auth/github/callback`.

![](screenshots/github-app.png)

### 20. Socialite

Установлен Socialite, добавлена миграция `github_id`, настроен `config/services.php` и `.env`, создан `GitHubController`, добавлены маршруты и кнопка на страницу `/login`.

Страница логина с кнопкой «Войти через GitHub».

![](screenshots/login-with-github.png)

### 21. Полный OAuth flow

Полный цикл: кнопка → редирект на GitHub → авторизация → callback → залогинены под GitHub-именем.

Страница авторизации на GitHub.

![](screenshots/github-authorize.png)

Состояние после успешного OAuth-входа: имя GitHub в navbar.

![](screenshots/after-github-login.png)

В БД появилась запись с `github_id`.

![](screenshots/mysql-github-id.png)

**Защитный вопрос:** сравните количество строк кода Lab11 (ручной OAuth на чистом PHP) и Lab12 (Socialite). Что сократилось и за счёт чего?

> **Ответ:** В Lab11 ручная реализация OAuth занимала ~70 строк кода: генерация state, ручной `curl` для обмена `code` на `access_token`, ручной `curl` к `api.github.com/user`, ручная работа с БД для создания/поиска пользователя. В Lab12 с Socialite — ~15 строк кода. Сократилось за счёт:
> - `Socialite::driver('github')->redirect()` — весь redirect формируется автоматически
> - `Socialite::driver('github')->user()` — обмен `code` на токен и запрос профиля за один вызов
> - `User::updateOrCreate()` — объединяет поиск, создание и обновление
> 
> Laravel Socialite — типовое решение для типовой задачи (OAuth). Это и есть выгода фреймворка.

---

## Часть E. Архитектурные вопросы

### 22. Что осталось от прошлых практик

На VPS остались:
- `/var/www/boardy-legacy/` — старый PHP-проект
- БД `boardy` — старые данные

**Зачем не удалили?**

> Старый проект и БД оставлены для Lab13, где FastAPI будет продолжать использовать те же данные через JWT. Если попробовать открыть `https://фамилия.ai-info.ru/login.php` (старый PHP-логин), то:
> - Если nginx настроен на `public/`, он не найдёт `login.php` (он в корне старого проекта) → 404
> - Если переключить обратно — старый PHP не знает о новой БД и миграциях

### 23. FastAPI и React

FastAPI продолжает работать на `api.фамилия.ai-info.ru`, React-файлы лежат в Lab9–11. В Laravel-проекте мы их **не используем**.

**Почему сейчас не используем — что мешает интегрировать?**

> Проекты разделены на разные поддомены:
> - Главный домен (`фамилия.ai-info.ru`) → Laravel (главный сайт)
> - API-домен (`api.фамилия.ai-info.ru`) → FastAPI (API для React)
>
> React-комментарии общаются с FastAPI по API, а не с Laravel. На данный момент нет единого API-шлюза. В Lab13 мы поставим Passport (Laravel станет OAuth-сервером), и FastAPI будет валидировать JWT через публичный ключ Laravel.

**Где они нам пригодятся в Lab13?**

> В Lab13 мы перепишем FastAPI под BFF (Backend For Frontend): он будет принимать Bearer-токены только от Laravel, проверять их через Passport и проксировать запросы в Laravel. React-комментарии будут ходить через FastAPI к Laravel.

### 24. Реалтайм

Сейчас комментарии появляются только после перезагрузки страницы (F5).

**Какое архитектурное решение нужно, чтобы пользователь видел новый комментарий другого пользователя без перезагрузки?**

> Нужно добавить **WebSocket-сервер** (например, Laravel Reverb или Pusher). Принцип работы:
> 1. При создании комментария Laravel отправляет событие (Event) в WebSocket-сервер
> 2. WebSocket-сервер рассылает событие всем клиентам, подписанным на канал `post.{id}`
> 3. Клиент (Blade/React) получает событие и добавляет комментарий в DOM без перезагрузки

**Какие два сервера-кандидата для этого решения и почему именно они?**

> 1. **Laravel Reverb** — официальный WebSocket-сервер Laravel, написан на PHP + Swoole, интегрируется через события и очереди
> 2. **Pusher** — облачный SaaS-сервис (или локальный Soketi), легко подключается через Broadcast-фасад Laravel
>
> Оба поддерживают Laravel Broadcasting, приватные каналы, политики авторизации и горизонтальное масштабирование. В Lab14 мы добавим WebSocket поверх готовой инфраструктуры.

