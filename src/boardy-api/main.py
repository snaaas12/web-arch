from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware  # ← добавить импорт
from datetime import datetime
from routers import comments
from database import get_db
import aiomysql
import logging

app = FastAPI(title='Boardy API', version='0.3.0')

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(comments.router, prefix="/api")
logger = logging.getLogger(__name__)


@app.get('/api/status')
async def status():
    return {'status': 'ok', 'time': str(datetime.now())}

@app.get('/api/messages')
async def get_messages():
    conn = await get_db()
    async with conn.cursor(aiomysql.DictCursor) as cur:
        await cur.execute(
            'SELECT posts.body AS message, users.name, '
            'posts.created_at FROM posts '
            'JOIN users ON posts.author_id = users.id '
            'ORDER BY posts.created_at DESC'
        )
        messages = await cur.fetchall()
    conn.close()
    for m in messages:
        m['created_at'] = str(m['created_at'])
    return {'messages': messages, 'count': len(messages)}

@app.get('/api/users')
async def get_users():
    conn = None
    try:
        conn = await get_db()
        async with conn.cursor(aiomysql.DictCursor) as cur:
            await cur.execute(
                'SELECT id, name, email, created_at FROM users'
            )
            users = await cur.fetchall()
        
        for u in users:
            if u['created_at']:
                u['created_at'] = str(u['created_at'])
        
        return {'users': users, 'count': len(users)}
    
    except aiomysql.Error as e:
        logger.error(f"Database error: {e}")
        raise HTTPException(status_code=500, detail="Database error")
    
    finally:
        if conn:
            await conn.close()
