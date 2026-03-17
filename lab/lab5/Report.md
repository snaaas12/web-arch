Лабораторная работа № 1 "HTTPS для Boardy"

ФИО: Шадрин Константин Дмитриевич 

## №1 установка certbot

![](screenshots/certbot-installed.png)

## №2 получения сертификата

![](screenshots/certbot-success.png)

## №3 проверка в браузере

![](screenshots/browser-lock.png)

![](screenshots/certificate-info.png)

## №4 Редирект 

код 301 - 301 Moved Permanently (перемещен постоянно)

Location - https://snaaas.ai-info.ru/

![](screenshots/redirect-config.png)

## №5 конфиг после certbot

listen 443 ssl - прослушивание HTTPS порта
ssl_certificate -  путь к сертификату
ssl_certificate_key - путь к приватному ключу

![](screenshots/nginx-ssl-config.png)

## №6 сертификат для api-поддомена

![](screenshots/api-certbot.png)

## №7 проверка обоих доменов 

![](screenshots/both-https.png)

## №8 TSL handshake

ver TLS - 1.3

шифрование - TLS_AES_256_GCM_SHA384

subject - snaaas.ai-info.ru

Issuer - C=US; O=Let's Encrypt; CN=E8 (Let's Encrypt)

Срок действия - Expires: June 15, 2026 

![](screenshots/tls-handshake.png)

## №9 цепочка доверия

Сертификат сайта подписан Let's Encrypt. Let's Encrypt подписан ISRG Root X1. ISRG Root встроен в ОС.

Сервер отправляет браузеру несколько сертификатов: Сертификат сайта, промежуточный сертификат 

![](screenshots/chain.png)

## №10

У сертификатов одинаковый Issuer - C = US, O = Let's Encrypt, CN = E8
И различные subjects у основной страницы - CN = snaaas.ai-info.ru , а у api - CN = api.snaaas.ai-info.ru


![](screenshots/compare-certs.png)

## №11 HSTS

HSTS - механизм, который принудительно активирует соединение через HTTPS. Предотвращает атаки типа "человек посередине" (MITM)

![](screenshots/hsts.png)

## №12 Кэширование и gzip

![](screenshots/cache-gzip.png)

## №13 автообновление

![](screenshots/renew.png)

## №14 PR

