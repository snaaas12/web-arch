import React, { useEffect, useState } from 'react'
import ReactDOM from 'react-dom/client'

import {
    startLogin,
    handleCallback,
    refreshToken
} from '../../public/js/auth.js'

function Comments({ postId, userName, authenticated }) {
    const [token, setToken] = useState(null)
    const [comments, setComments] = useState([])
    const [body, setBody] = useState('')
    const [loading, setLoading] = useState(true)

    const API_BASE = 'http://192.168.72.131:8000'

    // =========================
    // OAuth callback
    // =========================
    useEffect(() => {
        handleCallback()
           .then(t => {
                if (t) {
                    console.log('✅ access_token получен')
                    setToken(t)
                }
            })
            .catch(err => {
                console.error('OAuth callback error:', err)
            })
    }, [])

    // =========================
    // Загрузка комментариев
    // =========================
    useEffect(() => {
        loadComments()
    }, [])

    async function loadComments() {
        try {
            const res = await fetch(
                `${API_BASE}/api/posts/${postId}/comments`
            )

            const data = await res.json()

            console.log('comments api:',data)

            setComments(data.items || [])
        } catch (e) {
             console.error('Ошибка загрузки комментариев:', e)
        } finally {
            setLoading(false)
        }
		
    }

    // =========================
    // WebSocket realtime
    // =========================
    useEffect(() => {
        const protocol =
            window.location.protocol === 'https:' ? 'wss:' : 'ws:'

        const ws = new WebSocket(`ws://192.168.72.131:8000/ws`);

        ws.onopen = () => {
            console.log('✅ WS connected')
        }

        ws.onmessage = event => {
            try {
                const data = JSON.parse(event.data)

                console.log('📨 WS message:', data)

                // Новый комментарий
                if (data.type === 'new_comment') {
                    if (data.comment.post_id == postId) {
                        setComments(prev => [...prev, data.comment])
                    }
                }

                // Обновление комментария
                if (data.type === 'update_comment') {
                    setComments(prev =>
                        prev.map(c =>
                            c.id === data.comment.id
                                ? {
                                      ...c,
                                      body: data.comment.body
                                  }
                                : c
                        )
                    )
                }

                // Удаление комментария
                if (data.type === 'delete_comment') {
                    setComments(prev =>
                        prev.filter(
                            c => c.id !== data.comment_id
                        )
                    )
                }

                // Переименование пользователя
                if (data.type === 'user_renamed') {
                    setComments(prev =>
                        prev.map(c =>
                            c.author_id === data.user_id
                                ? {
                                      ...c,
                                      author_name:
                                          data.new_name
                                  }
                                : c
                        )
                    )
                }
            } catch (e) {
                console.error('WS parse error:', e)
            }
        }

        ws.onerror = err => {
            console.error('WS error:', err)
        }

        ws.onclose = () => {
            console.log('WS disconnected')
        }

        return () => ws.close()
    }, [])

    // =========================
    // fetch с refresh
    // =========================
    async function authedFetch(url, options = {}) {
        let response = await fetch(url, {
            ...options,
            headers: {
                ...(options.headers || {}),
                Authorization: 'Bearer ' + token,
            },
        })

        if (response.status === 401) {
            console.log('🔄 token expired')

            const newToken = await refreshToken()

            if (!newToken) return null

            setToken(newToken)

            response = await fetch(url, {
                ...options,
                headers: {
                    ...(options.headers || {}),
                    Authorization:
                        'Bearer ' + newToken,
                },
            })
        }

        return response
    }

    // =========================
    // Добавление комментария
    // =========================
    async function addComment(e) {
        e.preventDefault()

        if (!body.trim()) return

        if (!token) {
            alert('Сначала войдите')
            return
        }

        try {
            const res = await authedFetch(
                `${API_BASE}/api/posts/${postId}/comments`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type':
                            'application/json',
                    },
                    body: JSON.stringify({
                        body,
                        author_name: userName,
                    }),
                }
            )

            if (!res) return

            if (!res.ok) {
                const txt = await res.text()
                console.error(txt)
                alert('Ошибка создания комментария')
                return
            }

            setBody('')
        } catch (e) {
            console.error(e)
        }
    }

    // =========================
    // Удаление комментария
    // =========================
    async function deleteComment(id) {
        if (!confirm('Удалить комментарий?')) {
            return
        }

        try {
            const res = await authedFetch(
                `${API_BASE}/api/comments/${id}`,
                {
                    method: 'DELETE',
                }
            )

            if (!res?.ok) {
                alert('Ошибка удаления')
            }
        } catch (e) {
            console.error(e)
        }
    }

    // =========================
    // Render
    // =========================
    return (
        <div>
            <h3>
                Комментарии ({comments.length})
            </h3>

            {!authenticated ? (
                <div
        style={{
            marginBottom: '1rem',
        }}
    >
        <a
            href="/login"
            style={{
                display: 'inline-block',
                padding: '10px 16px',
                background: '#2563eb',
                color: '#fff',
                borderRadius: '8px',
                textDecoration: 'none',
            }}
        >
            Войти
        </a>
    </div>
) : !token ? (
    <div
        style={{
            marginBottom: '1rem',
        }}
    >
        <button onClick={startLogin}>
            Войти через OAuth
        </button>
    </div>
            ) : (
                <form
                    onSubmit={addComment}
                    style={{ marginBottom: '1rem' }}
                >
                    <textarea
                        rows="3"
                        value={body}
                        onChange={e =>
                            setBody(e.target.value)
                        }
                        placeholder="Напишите комментарий..."
                        required
                        style={{
                            width: '100%',
                            marginBottom: '0.5rem',
                        }}
                    />

                    <button type="submit">
                        Отправить
                    </button>
                </form>
            )}

            {loading ? (
                <p>Загрузка...</p>
            ) : comments.length === 0 ? (
                <p
                    style={{
                        color: '#999',
                    }}
                >
                    Нет комментариев
                </p>
            ) : (
                
                    Array.isArray(comments) &&
                    comments.map(comment => (
                    <div
                        key={comment.id}
                        className="comment"
                        style={{
                            borderBottom:
                                '1px solid #ddd',
                            padding: '10px 0',
                        }}
                    >
                        <div
                            style={{
                                fontWeight: 'bold',
                            }}
                        >
                            {comment.author_name}
                        </div>

                        <div>{comment.body}</div>

                        {token &&
                            Number(comment.author_id) > 0 &&
                            userName ===
                                comment.author_name && (
                                <button
                                    onClick={() =>
                                        deleteComment(
                                            comment.id
                                        )
                                    }
                                    style={{
                                        marginTop: '5px',
                                    }}
                                >
                                    Удалить
                                </button>
                            )}
                    </div>
                ))
            )}
        </div>
    )
}

// =========================
// Mount
// =========================
const rootElement =
    document.getElementById('comments-root')

if (rootElement) {
    const postId =
        rootElement.dataset.postId

    sessionStorage.setItem(
        'return_post_id',
        postId
    )

    const userName =
        rootElement.dataset.userName

    const authenticated =
        rootElement.dataset.authenticated ===
        'true'

    ReactDOM.createRoot(rootElement).render(
        <Comments
            postId={postId}
            userName={userName}
            authenticated={authenticated}
        />
    )
}
