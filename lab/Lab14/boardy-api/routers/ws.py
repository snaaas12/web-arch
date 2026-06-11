from fastapi import APIRouter, WebSocket
from typing import List
import json
import logging

router = APIRouter()
logger = logging.getLogger(__name__)

print('connected')

class ConnectionManager:
    def __init__(self):
        self.active: List[WebSocket] = []

    async def connect(self, ws: WebSocket):
        await ws.accept()
        self.active.append(ws)
        logger.info(f"WebSocket client connected. Total: {len(self.active)}")

    def disconnect(self, ws: WebSocket):
        if ws in self.active:
            self.active.remove(ws)
            logger.info(f"WebSocket client disconnected. Total: {len(self.active)}")

    async def broadcast(self, message: dict):
        """Отправить сообщение всем подключённым клиентам"""
        dead = []
        for ws in self.active:
            try:
                await ws.send_text(json.dumps(message))
            except Exception as e:
                logger.warning(f"Failed to send to client: {e}")
                dead.append(ws)
        
        # Удаляем мёртвые соединения
        for ws in dead:
            self.active.remove(ws)

# Глобальный менеджер соединений
manager = ConnectionManager()

@router.websocket('/ws')
async def websocket_endpoint(ws: WebSocket):
    """WebSocket эндпоинт для клиентов"""
    await manager.connect(ws)
    try:
        # Бесконечно ждём сообщения от клиента
        # (можно использовать для ping/pong или команд от клиента)
        while True:
            # Ждём текст от клиента (можно игнорировать или обработать)
            data = await ws.receive_text()
            logger.debug(f"Received from client: {data}")
            
            # Если нужно отвечать клиенту на его сообщения:
            # await ws.send_text(json.dumps({"echo": data}))
            
    except Exception as e:
        logger.info(f"WebSocket connection closed: {e}")
        manager.disconnect(ws)
