import sys
import os
from pathlib import Path

# Добавляем родительскую директорию (boardy-api) в путь
sys.path.insert(0, str(Path(__file__).parent.parent))

from fastapi.testclient import TestClient
from main import app

client = TestClient(app)

def test_health_endpoint_returns_ok():
    response = client.get("/health")
    
    assert response.status_code == 200
    assert response.json() == {"ok": True}
