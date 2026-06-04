// comments.js - скомпилированная версия без JSX

const { useState, useEffect } = React;

function CommentsApp({ postId }) {
    const [comments, setComments] = useState([]);
    const [text, setText] = useState('');
    const [jwt, setJwt] = useState(null);
    
    const API_URL = 'http://192.168.72.131:8000';
    
    // Получаем JWT при загрузке
    useEffect(() => {
        console.log('1. Запрос к me.php...');
        
        fetch('/api/me.php', { credentials: 'include' })
            .then(r => {
                console.log('2. Ответ получен, status:', r.status);
                if (!r.ok) return null;
                return r.json();
            })
            .then(data => {
                console.log('3. Данные от me.php:', data);
                if (data && data.token) {
                    console.log('4. JWT получен:', data.token);
                    setJwt(data.token);
                } else {
                    console.log('4. JWT не получен - пользователь не залогинен');
                }
            })
            .catch(err => {
                console.error('Ошибка получения JWT:', err);
                setJwt(null);
            });
    }, []);
    
    // Загрузка комментариев
    useEffect(() => {
        console.log('Загрузка комментариев для поста', postId);
        
        fetch(`${API_URL}/api/posts/${postId}/comments`)
            .then(r => r.json())
            .then(data => {
                console.log('Комментарии загружены:', data);
                setComments(data.items || []);
            })
            .catch(err => console.error('Ошибка загрузки комментариев:', err));
    }, [postId]);
    
    // Отправка комментария
    const submitComment = async () => {
        if (!text.trim()) return;
        
        const headers = { 'Content-Type': 'application/json' };
        if (jwt) {
            headers['Authorization'] = `Bearer ${jwt}`;
            console.log('Отправка с JWT');
        } else {
            console.log('Нет JWT, комментарий не отправится');
            alert('Не авторизован. Войдите в систему.');
            return;
        }
        
        try {
            const res = await fetch(`${API_URL}/api/posts/${postId}/comments`, {
                method: 'POST',
                headers,
                body: JSON.stringify({ body: text })
            });
            
            console.log('Ответ от API:', res.status);
            
            if (res.ok) {
                setText('');
                // Перезагружаем комментарии
                const updated = await fetch(`${API_URL}/api/posts/${postId}/comments`).then(r => r.json());
                setComments(updated.items || []);
            } else if (res.status === 401) {
                alert('Сессия истекла. Войдите заново.');
            } else {
                alert('Ошибка при отправке комментария');
            }
        } catch (err) {
            console.error('Ошибка:', err);
            alert('Ошибка соединения с сервером');
        }
    };
    
    // Рендер через React.createElement
    return React.createElement('div', { className: 'comments-section' },
        React.createElement('h3', null, 'Комментарии'),
        React.createElement('div', { className: 'comments-list' },
            comments.length === 0 
                ? React.createElement('p', null, 'Нет комментариев. Будьте первым!')
                : comments.map(c => 
                    React.createElement('div', { key: c.id, className: 'comment' },
                        React.createElement('strong', null, c.author_name || 'Аноним'),
                        ': ',
                        c.body
                    )
                )
        ),
        jwt 
            ? React.createElement('div', { className: 'comment-form' },
                React.createElement('textarea', {
                    value: text,
                    onChange: e => setText(e.target.value),
                    placeholder: 'Напишите комментарий...',
                    rows: 3
                }),
                React.createElement('button', { onClick: submitComment }, 'Отправить')
            )
            : React.createElement('p', null,
                React.createElement('a', { href: '/login.php' }, 'Войдите'),
                ' чтобы оставить комментарий'
            )
    );
}

// Ждём загрузки DOM
document.addEventListener('DOMContentLoaded', function() {
    const rootElement = document.getElementById('comments-root');
    if (rootElement) {
        ReactDOM.render(
            React.createElement(CommentsApp, { postId: 1 }),
            rootElement
        );
    } else {
        console.error('Элемент #comments-root не найден');
    }
});
