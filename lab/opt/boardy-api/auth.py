import jwt
from fastapi import Header, HTTPException, Depends
import logging

SECRET_KEY = 'your-secret-key-change-me'  # ОБЯЗАТЕЛЬНО СМЕНИТЬ!

logging.basicConfig(level=logging.DEBUG)

async def get_current_user(authorization: str = Header(None)):
    logging.debug(f"Auth header: {authorization}")
    
    if not authorization or not authorization.startswith('Bearer '):
        raise HTTPException(status_code=401, detail='Token required')
    
    token = authorization.split(' ')[1]
    
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=['HS256'])
        logging.debug(f"Payload: {payload}")
        return payload
    except jwt.ExpiredSignatureError:
        raise HTTPException(status_code=401, detail='Token expired')
    except jwt.InvalidTokenError as e:
        logging.error(f"JWT error: {e}")
        raise HTTPException(status_code=401, detail='Invalid token')
