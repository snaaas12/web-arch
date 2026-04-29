import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom';

function CommentsApp({ postId }) {
    const [comments, setComments] = useState([]);
    const [text, setText] = useState('');
    const [jwt, setJwt] = useState(null);
    
    const API_URL = 'https://api.ваша_фамилия.ai-info.ru';
    
    // Получаем JWT при загрузке
    useEffect(() => {
        fetch('/api/me.php', { 
            credentials: 'include'  // ← ВАЖНО: отправляем куку PHPSESSID
        })
            .then(r => {
                if (!r.ok) return null;
                return r.json();
            })
            .then(data => {
                if (data && data.token) {
                    console.log('JWT получен');
                    setJwt(data.token);
                }
            })
            .catch(err => {
                console.error('Ошибка получения JWT:', err);
                setJwt(null);
            });
    }, []);
    
    // Загрузка комментариев (публичная, без токена)
    useEffect(() => {
        fetch(`${API_URL}/api/posts/${postId}/comments`)
            .then(r => r.json())
            .then(setComments)
            .catch(console.error);
    }, [postId]);
    
    // Отправка комментария (с токеном)
    const submitComment = async () => {
        if (!text.trim()) return;
        
        // Формируем headers с токеном если есть
        const headers = {
            'Content-Type': 'application/json',
        };
        
        if (jwt) {
            headers['Authorization'] = `Bearer ${jwt}`;
        }
        
        try {
            const res = await fetch(`${API_URL}/api/posts/${postId}/comments`, {
                method: 'POST',
                headers,
                body: JSON.stringify({ body: text })
            });
            
            if (res.ok) {
                setText('');
                // Перезагружаем комментарии
                const updated = await fetch(`${API_URL}/api/posts/${postId}/comments`).then(r => r.json());
                setComments(updated);
            } else if (res.status === 401) {
                alert('Не авторизован. Войдите в систему.');
            }
        } catch (err) {
            console.error('Ошибка:', err);
        }
    };
    
    return (
        <div>
            <h3>Комментарии</h3>
            {comments.map(c => (
                <div key={c.id}>
                    <strong>{c.author_name || 'Аноним'}</strong>: {c.body}
                </div>
            ))}
            
            {jwt ? (
                <div>
                    <textarea value={text} onChange={e => setText(e.target.value)} />
                    <button onClick={submitComment}>Отправить</button>
                </div>
            ) : (
                <p><a href="/login.php">Войдите</a> чтобы оставить комментарий</p>
            )}
        </div>
    );
}

ReactDOM.render(<CommentsApp postId={1} />, document.getElementById('comments-root'));
