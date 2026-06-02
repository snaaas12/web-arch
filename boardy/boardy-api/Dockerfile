FROM python:3.11-slim

WORKDIR /app

# Системные зависимости для криптографии (нужны для PyJWT)
RUN apt-get update && apt-get install -y \
    build-essential libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Сначала requirements — для кеширования слоёв
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Потом код приложения
COPY . .

EXPOSE 8000
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]


