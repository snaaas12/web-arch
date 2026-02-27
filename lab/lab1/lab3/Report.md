# Лабораторная работа №3 "Nginx, DNS"

## №1 Установка Nginx

Вывод systemctl status nginx

![](screenshots/nginx-status.png)


## №2 Страница по IP 

Страница в браузере 

![](screenshots/browser-ip.png)


## №3 curl

![](screenshots/curl.png)


## №4 Директория и права

смена прав доступа

![](screenshots/permission.png)

## №5 Конфигурация Nginx

директива - значение -- описание

listen - 80 -- Показывает какой порт прослушивается

root - /var/www/example.com -- Путь к папке, откуда сервер будет брать файлы для отправки клиенту 

server_name - example.com -- Имя Сервера 

index - index.html -- Имена файлов, которые будут использоваться по умолчанию, при запросе к содержимому директории