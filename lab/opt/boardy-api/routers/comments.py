from fastapi import APIRouter, HTTPException, Depends
from pydantic import BaseModel
from database import get_db
import aiomysql
from auth import get_current_user  # ← ИМПОРТИРУЕМ НАШУ АВТОРИЗАЦИЮ

router = APIRouter()

class CommentCreate(BaseModel):
    body: str

class CommentUpdate(BaseModel):
    body: str


# GET - Публичный, без авторизации (оставляем как есть)
@router.get('/posts/{post_id}/comments')
async def get_comments(post_id: int):
    conn = await get_db()
    async with conn.cursor(aiomysql.DictCursor) as cur:
        await cur.execute("""
            SELECT c.id, c.body, c.created_at,
                   u.name AS author_name
            FROM comments c
            JOIN users u ON c.author_id = u.id
            WHERE c.post_id = %s
            ORDER BY c.created_at
        """, (post_id,))
        items = await cur.fetchall()
    conn.close()
    
    for item in items:
        if item['created_at']:
            item['created_at'] = str(item['created_at'])
    
    return {'items': items, 'count': len(items)}


# POST - ТРЕБУЕТ АВТОРИЗАЦИЮ
@router.post('/posts/{post_id}/comments', status_code=201)
async def create_comment(
    post_id: int, 
    data: CommentCreate,
    user = Depends(get_current_user)  # ← ДОБАВЛЕНО: получаем user из JWT
):
    if not data.body.strip():
        raise HTTPException(status_code=422, detail='Текст комментария не может быть пустым')
    
    # БЕРЁМ author_id ИЗ JWT, а не хардкод 18
    author_id = user['user_id']  # ← ИЗМЕНЕНО: теперь из токена
    
    conn = await get_db()
    async with conn.cursor() as cur:
        await cur.execute('SELECT id FROM posts WHERE id = %s', (post_id,))
        if not await cur.fetchone():
            conn.close()
            raise HTTPException(status_code=404, detail='Пост не найден')
        
        await cur.execute("""
            INSERT INTO comments (body, post_id, author_id) 
            VALUES (%s, %s, %s)
        """, (data.body, post_id, author_id))  # ← ИСПОЛЬЗУЕМ author_id ИЗ ТОКЕНА
        
        await conn.commit()
        new_id = cur.lastrowid
    
    conn.close()
    return {'id': new_id, 'body': data.body, 'status': 'created'}


# PUT - ТРЕБУЕТ АВТОРИЗАЦИЮ (проверяем, что пользователь - автор)
@router.put('/comments/{comment_id}')
async def update_comment(
    comment_id: int, 
    data: CommentUpdate,
    user = Depends(get_current_user)  # ← ДОБАВЛЕНО
):
    if not data.body.strip():
        raise HTTPException(status_code=422, detail='Текст комментария не может быть пустым')
    
    conn = await get_db()
    async with conn.cursor(aiomysql.DictCursor) as cur:  # ← DictCursor чтобы получить автора
        # Проверяем, что пользователь - автор комментария
        await cur.execute('SELECT author_id FROM comments WHERE id = %s', (comment_id,))
        comment = await cur.fetchone()
        
        if not comment:
            conn.close()
            raise HTTPException(status_code=404, detail='Комментарий не найден')
        
        # Проверяем права (только автор может редактировать)
        if comment['author_id'] != user['user_id']:
            conn.close()
            raise HTTPException(status_code=403, detail='Нет прав на редактирование')
        
        # Обновляем комментарий
        await cur.execute("""
            UPDATE comments SET body = %s WHERE id = %s
        """, (data.body, comment_id))
        
        await conn.commit()
    
    conn.close()
    return {'id': comment_id, 'body': data.body, 'status': 'updated'}


# DELETE - ТРЕБУЕТ АВТОРИЗАЦИЮ (проверяем, что пользователь - автор)
@router.delete('/comments/{comment_id}', status_code=204)
async def delete_comment(
    comment_id: int,
    user = Depends(get_current_user)  # ← ДОБАВЛЕНО
):
    conn = await get_db()
    async with conn.cursor(aiomysql.DictCursor) as cur:
        # Проверяем, что пользователь - автор комментария
        await cur.execute('SELECT author_id FROM comments WHERE id = %s', (comment_id,))
        comment = await cur.fetchone()
        
        if not comment:
            conn.close()
            raise HTTPException(status_code=404, detail='Комментарий не найден')
        
        # Проверяем права (только автор может удалить)
        if comment['author_id'] != user['user_id']:
            conn.close()
            raise HTTPException(status_code=403, detail='Нет прав на удаление')
        
        await cur.execute('DELETE FROM comments WHERE id = %s', (comment_id,))
        await conn.commit()
    
    conn.close()
