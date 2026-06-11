@extends('layouts.old')

@section('title', 'Все посты - Boardy')

@section('content')
    <h1>Все посты</h1>
    
    <div id="posts-feed">
        @forelse ($posts as $post)
            <div class="post" id="post-{{ $post->id }}">
                <div class="post-author">
                    <a href="{{ route('posts.show', $post) }}" class="link">{{ $post->title }}</a>
                </div>
                <div class="post-body">{{ Str::limit($post->body, 200) }}</div>
                <div class="post-date">
                    Автор: {{ $post->author->name }} | {{ $post->created_at->format('d.m.Y H:i') }}
                </div>
            </div>
        @empty
            <div class="card" id="empty-message">
                <p>Пока нет ни одного поста. Будьте первым!</p>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $posts->links() }}
    </div>

    <script>
        // Делаем переменную глобальной
        window.ws = null;
        let reconnectAttempts = 0;
        
        // Функция подключения
        function connectWebSocket() {
            const wsUrl = 'ws://192.168.72.131:8000/ws';
            console.log('🔄 Подключение к WebSocket:', wsUrl);
            
            try {
                window.ws = new WebSocket(wsUrl);
                
                window.ws.onopen = function() {
                    console.log('✅ WebSocket ПОДКЛЮЧЁН!');
                    console.log('Статус соединения:', window.ws.readyState);
                    reconnectAttempts = 0;
                    
                    // Показываем индикатор
                    showStatus('connected');
                };
                
                window.ws.onmessage = function(event) {
                    console.log('📨 Получено сообщение:', event.data);
                    try {
                        const data = JSON.parse(event.data);
                        console.log('Разобранные данные:', data);
                        
                        if (data.type === 'new_post') {
                            console.log('Новый пост!', data.post);
                            addNewPostToFeed(data.post);
                        }
                    } catch (e) {
                        console.error('Ошибка парсинга:', e);
                    }
                };
                
                window.ws.onerror = function(error) {
                    console.error('❌ WebSocket ошибка:', error);
                    showStatus('error');
                };
                
                window.ws.onclose = function(event) {
                    console.log('🔌 WebSocket отключён. Код:', event.code);
                    showStatus('disconnected');
                    
                    // Переподключение
                    if (reconnectAttempts < 10) {
                        reconnectAttempts++;
                        const delay = 3000;
                        console.log(`🔄 Переподключение через ${delay/1000} сек... (${reconnectAttempts}/10)`);
                        setTimeout(connectWebSocket, delay);
                    }
                };
                
            } catch (e) {
                console.error('Ошибка создания WebSocket:', e);
            }
        }
        
        // Функция добавления поста
        function addNewPostToFeed(post) {
            console.log('Добавляем пост в ленту:', post);
            
            // Удаляем сообщение "нет постов"
            const emptyMessage = document.getElementById('empty-message');
            if (emptyMessage) {
                emptyMessage.remove();
            }
            
            // Форматируем дату
            const postDate = new Date(post.created_at);
            const formattedDate = postDate.toLocaleString('ru-RU');
            
            // Создаём элемент
            const postElement = document.createElement('div');
            postElement.className = 'post';
            postElement.id = `post-${post.id}`;
            postElement.style.border = '2px solid #4CAF50';
            postElement.style.padding = '10px';
            postElement.style.margin = '10px 0';
            postElement.style.borderRadius = '5px';
            postElement.style.backgroundColor = '#f9fff9';
            
            postElement.innerHTML = `
                <div class="post-author">
                    <a href="/posts/${post.id}" class="link">${escapeHtml(post.title)}</a>
                    <span style="color: #4CAF50; font-size: 12px; margin-left: 10px;">🆕 ТОЛЬКО ЧТО!</span>
                </div>
                <div class="post-body">${escapeHtml(post.body.substring(0, 200))}</div>
                <div class="post-date">
                    Автор: ${escapeHtml(post.author)} | ${formattedDate}
                </div>
            `;
            
            // Добавляем в начало ленты
            const feed = document.getElementById('posts-feed');
            if (feed) {
                feed.insertBefore(postElement, feed.firstChild);
                console.log('Пост добавлен в начало ленты');
            } else {
                console.error('Не найден контейнер posts-feed');
            }
        }
        
        // Защита от XSS
        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
        
        // Индикатор статуса
        function showStatus(status) {
            let indicator = document.getElementById('ws-indicator');
            if (!indicator) {
                indicator = document.createElement('div');
                indicator.id = 'ws-indicator';
                indicator.style.cssText = 'position: fixed; bottom: 10px; right: 10px; padding: 5px 10px; border-radius: 5px; font-size: 12px; z-index: 9999; font-family: monospace;';
                document.body.appendChild(indicator);
            }
            
            if (status === 'connected') {
                indicator.innerHTML = '✅ WebSocket: подключён';
                indicator.style.backgroundColor = '#4CAF50';
                indicator.style.color = 'white';
            } else if (status === 'error') {
                indicator.innerHTML = '❌ WebSocket: ошибка';
                indicator.style.backgroundColor = '#f44336';
                indicator.style.color = 'white';
            } else {
                indicator.innerHTML = '🔌 WebSocket: отключён';
                indicator.style.backgroundColor = '#ff9800';
                indicator.style.color = 'white';
            }
        }
        
        // Запускаем при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Страница загружена, запускаем WebSocket...');
            connectWebSocket();
        });
        
        // Для отладки в консоли
        console.log('Скрипт загружен, для проверки введите: window.ws');
    </script>
@endsection
