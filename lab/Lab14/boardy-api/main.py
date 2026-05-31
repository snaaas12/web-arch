from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from datetime import datetime
from contextlib import asynccontextmanager

from routers import comments
from routers import ws

from database import get_db

import aiomysql
import asyncio
import json
import logging

import redis.asyncio as redis

logger = logging.getLogger(__name__)

# =========================
# REDIS SUBSCRIBER
# =========================

async def redis_subscriber():
    print("REDIS SUBCRIVER STARTED")
    r = redis.from_url("redis://127.0.0.1:6379")

    pubsub = r.pubsub()

    await pubsub.subscribe(
        "new_post",
        "user.renamed"
    )

    logger.info("Redis subscriber started")

    async for message in pubsub.listen():
        print("Message:", message)
        if message["type"] != "message":
            continue

        try:
            channel = message["channel"].decode()
            data = json.loads(message["data"])

            logger.info(f"Redis message: {channel}")

            if channel == "new_post":

                await ws.manager.broadcast({
                    "type": "new_post",
                    "post": data
                })

            elif channel == "user.renamed":

                conn = await get_db()

                async with conn.cursor() as cur:
                    await cur.execute(
                        """
                        UPDATE comments
                        SET author_name=%s
                        WHERE author_id=%s
                        """,
                        (data["new_name"], data["id"])
                    )

                    await conn.commit()

                conn.close()

                await ws.manager.broadcast({
                    "type": "user_renamed",
                    "user_id": data["id"],
                    "new_name": data["new_name"]
                })

        except Exception as e:
            logger.error(f"Redis subscriber error: {e}")

# =========================
# FASTAPI LIFESPAN
# =========================

@asynccontextmanager
async def lifespan(app: FastAPI):

    task = asyncio.create_task(redis_subscriber())

    yield

    task.cancel()

# =========================
# APP
# =========================

app = FastAPI(
    title='Boardy API',
    version='0.5.0',
    lifespan=lifespan
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://192.168.72.131"
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# =========================
# ROUTERS
# =========================

app.include_router(comments.router, prefix="/api")
app.include_router(ws.router)

# =========================
# STATUS
# =========================

@app.get('/api/status')
async def status():
    return {
        'status': 'ok',
        'time': str(datetime.now())
    }

# =========================
# USERS
# =========================

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

        return {
            'users': users,
            'count': len(users)
        }

    except aiomysql.Error as e:

        logger.error(f"Database error: {e}")

        raise HTTPException(
            status_code=500,
            detail="Database error"
        )

    finally:

        if conn:
            conn.close()
