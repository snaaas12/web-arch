from fastapi import APIRouter, HTTPException, Depends
from pydantic import BaseModel
from database import get_db
from auth import get_current_user
from routers.ws import manager
import aiomysql
import json

router = APIRouter()

class CommentCreate(BaseModel):
    body: str
    author_name: str  # ← ДОБАВЛЕНО: имя автора (денормализация)

class CommentUpdate(BaseModel):
    body: str


# GET - Публичный, без авторизации
@router.get('/posts/{post_id}/comments')
async def get_comments(post_id: int):
    conn = await get_db()
    async with conn.cursor(aiomysql.DictCursor) as cur:
        await cur.execute("""
            SELECT id, body, author_name, author_id, created_at, updated_at
            FROM comments
            WHERE post_id = %s
            ORDER BY created_at
        """, (post_id,))
        items = await cur.fetchall()
    conn.close()

    for item in items:
        if item['created_at']:
            item['created_at'] = str(item['created_at'])
        if item['updated_at']:
            item['updated_at'] = str(item['updated_at'])

    return items


# POST - Требует авторизацию
@router.post('/posts/{post_id}/comments', status_code=201)
async def create_comment(
    post_id: int,
    data: CommentCreate,
    user = Depends(get_current_user)
):
    if not data.body.strip():
        raise HTTPException(status_code=422, detail='Текст комментария не может быть пустым')
    
    if not data.author_name.strip():
        raise HTTPException(status_code=422, detail='Имя автора не может быть пустым')

    # Берём author_id из JWT (поле sub)
    author_id = int(user['sub'])

    conn = await get_db()
    async with conn.cursor() as cur:
        # Проверяем, существует ли пост (опционально)
        await cur.execute('SELECT id FROM posts WHERE id = %s', (post_id,))
        if not await cur.fetchone():
            conn.close()
            raise HTTPException(status_code=404, detail='Пост не найден')

        await cur.execute("""
            INSERT INTO comments (body, post_id, author_id, author_name)
            VALUES (%s, %s, %s, %s)
        """, (data.body, post_id, author_id, data.author_name))

        await conn.commit()
        new_id = cur.lastrowid

    conn.close()
    
    # WebSocket broadcast
    new_comment = {
        'id': new_id,
        'post_id': post_id,
        'author_id': author_id,
        'author_name': data.author_name,
        'body': data.body,
    }
    await manager.broadcast({'type': 'new_comment', 'comment': new_comment})
    
    return {'id': new_id, 'body': data.body, 'author_name': data.author_name, 'status': 'created'}


# PUT - Требует авторизацию (проверяем, что пользователь - автор)
@router.put('/comments/{comment_id}')
async def update_comment(
    comment_id: int,
    data: CommentUpdate,
    user = Depends(get_current_user)
):
    if not data.body.strip():
        raise HTTPException(status_code=422, detail='Текст комментария не может быть пустым')

    author_id = int(user['sub'])

    conn = await get_db()
    async with conn.cursor(aiomysql.DictCursor) as cur:
        # Проверяем, что пользователь - автор комментария
        await cur.execute('SELECT author_id, post_id FROM comments WHERE id = %s', (comment_id,))
        comment = await cur.fetchone()

        if not comment:
            conn.close()
            raise HTTPException(status_code=404, detail='Комментарий не найден')

        # Проверяем права (только автор может редактировать)
        if comment['author_id'] != author_id:
            conn.close()
            raise HTTPException(status_code=403, detail='Нет прав на редактирование')

        # Обновляем комментарий
        await cur.execute("""
            UPDATE comments SET body = %s WHERE id = %s
        """, (data.body, comment_id))

        await conn.commit()

    conn.close()
    
    # WebSocket broadcast
    await manager.broadcast({
        'type': 'update_comment',
        'comment': {'id': comment_id, 'body': data.body}
    })
    
    return {'id': comment_id, 'body': data.body, 'status': 'updated'}


# DELETE - Требует авторизацию (проверяем, что пользователь - автор)
@router.delete('/comments/{comment_id}', status_code=204)
async def delete_comment(
    comment_id: int,
    user = Depends(get_current_user)
):
    author_id = int(user['sub'])

    conn = await get_db()
    async with conn.cursor(aiomysql.DictCursor) as cur:
        # Проверяем, что пользователь - автор комментария
        await cur.execute('SELECT author_id FROM comments WHERE id = %s', (comment_id,))
        comment = await cur.fetchone()

        if not comment:
            conn.close()
            raise HTTPException(status_code=404, detail='Комментарий не найден')

        # Проверяем права (только автор может удалить)
        if comment['author_id'] != author_id:
            conn.close()
            raise HTTPException(status_code=403, detail='Нет прав на удаление')

        await cur.execute('DELETE FROM comments WHERE id = %s', (comment_id,))
        await conn.commit()

    conn.close()
    
    # WebSocket broadcast
    await manager.broadcast({
        'type': 'delete_comment',
        'comment_id': comment_id
    })
