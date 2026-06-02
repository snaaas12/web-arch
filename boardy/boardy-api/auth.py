import jwt
from fastapi import Header, HTTPException
from pathlib import Path

# Путь к папке, где лежит auth.py
BASE_DIR = Path(__file__).resolve().parent
# Путь к ключу в той же папке
PUBLIC_KEY_PATH = BASE_DIR / "oauth-public.key"

with open(PUBLIC_KEY_PATH, 'r') as f:
    public_key = f.read()

async def get_current_user(authorization: str = Header(None)):
    if not authorization or not authorization.startswith('Bearer '):
        raise HTTPException(status_code=401, detail='Token required')

    token = authorization.split(' ')[1]

    try:
        payload = jwt.decode(
            token, 
            PUBLIC_KEY, 
            algorithms=['RS256'],
            options={'verify_aud': False}
        )
        return payload
    except jwt.ExpiredSignatureError:
        raise HTTPException(status_code=401, detail='Token expired')
    except jwt.InvalidTokenError as e:
        raise HTTPException(status_code=401, detail=f'Invalid token: {e}')
