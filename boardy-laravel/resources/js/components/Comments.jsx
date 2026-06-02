import React, { useEffect, useState } from 'react'

import {
    startLogin,
    handleCallback,
    refreshToken
} from '/js/auth.js'

export default function Comments({
    postId,
    userName
}) {

    const [token, setToken] = useState(null)

    const [comments, setComments] = useState([])

    const [body, setBody] = useState('')

    // =========================
    // CALLBACK
    // =========================

    useEffect(() => {

        handleCallback()
            .then(t => {
                if (t) {
                    setToken(t)
                }
            })

    }, [])

    // =========================
    // LOAD COMMENTS
    // =========================

    useEffect(() => {

        fetch(
            `http://192.168.72.131:8000/api/posts/${postId}/comments`
        )
            .then(r => r.json())
            .then(setComments)

    }, [postId])

    // =========================
    // WS
    // =========================

    useEffect(() => {

        const ws =
            new WebSocket(
                'ws://192.168.72.131:8000/ws'
            )

        ws.onmessage = (event) => {

            const data =
                JSON.parse(event.data)

            console.log(data)

            if (
                data.type === 'new_comment'
            ) {

                if (
                    Number(data.comment.post_id)
                    === Number(postId)
                ) {

                    setComments(prev => [
                        ...prev,
                        data.comment
                    ])
                }
            }
        }

        return () => ws.close()

    }, [postId])

    // =========================
    // AUTH FETCH
    // =========================

    async function authedFetch(
        url,
        options = {}
    ) {

        let response = await fetch(url, {

            ...options,

            headers: {
                ...options.headers,
                Authorization:
                    'Bearer ' + token,
            }
        })

        if (response.status === 401) {

            const newToken =
                await refreshToken()

            if (!newToken) {
                return null
            }

            setToken(newToken)

            response = await fetch(url, {

                ...options,

                headers: {
                    ...options.headers,
                    Authorization:
                        'Bearer ' + newToken,
                }
            })
        }

        return response
    }

    // =========================
    // ADD COMMENT
    // =========================

    async function addComment() {

        if (!token) {
            await startLogin()
            return
        }

        const res = await authedFetch(
            `http://192.168.72.131:8000/api/posts/${postId}/comments`,
            {
                method: 'POST',

                headers: {
                    'Content-Type':
                        'application/json'
                },

                body: JSON.stringify({
                    body,
                    author_name: userName
                })
            }
        )

        if (!res) {
            return
        }

        setBody('')
    }

    return (
        <div>

            <h2>Комментарии</h2>

            <div>

                {comments.map(comment => (

                    <div
                        key={comment.id}
                        style={{
                            border: '1px solid #ccc',
                            padding: '10px',
                            marginBottom: '10px'
                        }}
                    >

                        <b>
                            {comment.author_name}
                        </b>

                        <p>
                            {comment.body}
                        </p>

                    </div>
                ))}

            </div>

            <textarea
                value={body}
                onChange={e =>
                    setBody(e.target.value)
                }
            />

            <br />

            <button onClick={addComment}>
                Отправить
            </button>

        </div>
    )
}
