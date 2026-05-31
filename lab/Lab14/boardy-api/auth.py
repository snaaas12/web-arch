import jwt
from fastapi import Header, HTTPException

# Загружаем публичный ключ Passport
with open('/opt/boardy-api/oauth-public.key', 'r') as f:
    PUBLIC_KEY = f.read()

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
