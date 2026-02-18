Лабораторная работа № 1 "Основы командной строки Linux"

ФИО: Шадрин Константин Дмитриевич 


## №1 Создание виртуальный машины ##

Создана виртуальная машина в VMware Workstation Pro 17

![Параметры VM](screenshots\settings.png)

![Консоль Ubuntu](screenshots\vm-console.png)

## №2 Информация о системе ##

- Ядро: Linux
- OC: Ubuntu Server 24.04 LTS
- CPU: Intel Core i5-12450H
- Память: 3,8 GB
- Диски: ???

![Информация о системе](screenshots\system-info.png)

## №3 Сеть:IP-адрес и открытые порты ##

- IP-адрес: 127.0.0.1
- SSH прослушивается на 22 порту
- Да 53 порт

![ip адрес ПК](screenshots\ip-addr.png)

![Открытые порта](screenshots\ports.png)

## №4 Сервис SSH ##

- SSH сервис неактивен
- PID == 1
- Порт == 22
- Автозапуск выключен 

![Статус SSH](screenshots\ssh-status.png)

![Порты SSH](screenshots\ssh-ports.png)

## №5 Пользователи и группы ##

- В системе 3 пользователя и все они "настоящие"
- пользователь boardy состоит в следующих группах: "boardy, sudo, users" 
- Существуют различные системные пользователи и нужны они для работы с различными службами. Так daemon нужен для запуска фоновых служб и демонов. 

![Пользователи системы](screenshots\users.png)

![Создание нового пользователя](screenshots\new-user.png)

![проверка создания user](screenshots\user-chek.png)

## №6 Дерево каталогов ##

- Основные каталоги: /bin /boot /dev /etc /home /lib /media /mnt /opt /proc /root /run /sbin /srv /sys /tmp /usr /var    
- /etc == Хранит системные конфигурационные файлы
- /var == Хранит изменяемые данные
- /home == Личные директории пользователей 
- /tmp == Временные файлы
- Скрытые каталоги: bash_history, bash_logout, bashrc, cache, lesshst, profile, report, ssh, sudo_as_admin_successful


![Корень](screenshots\root-tree.png)

![Домашний каталог](screenshots\home-tree.png)

## №7 Права доступа ##

- Права на /etc/shadow имеют права 640 так как это необходимо для безопасности чтения паролей.
- /tmp имеет особые права т.к. каждый пользователь может читать и удалять файлы, но только свои файлы. Поэтому /tmp имеет права 1777
- chmod 755:
Владелец:  7 = rwx (чтение + запись + выполнение)
Группа:    5 = r-x (чтение + выполнение)
Остальные: 5 = r-x (чтение + выполнение)
- chmod 600:
Владелец:  6 = rw- (чтение + запись)
Группа:    0 = --- (нет прав)
Остальные: 0 = --- (нет прав)

![права доступа](screenshots\permissions.png)

![Изменения прав доступа](screenshots\chmod.png)

## №8 Установленные пакеты и сервисы ##

- всего установленно 25 пакетов
- установлены следующие ключевые пакеты: curl, git, git-man, nano, vim, openssh-client, openssh-server, openssh-sftp-server, lshw, linux-firmware
- запущенные сервисы и их назначение: 
Системные:
dbus — межпроцессное взаимодействие программ
systemd-journald — сбор системных логов
rsyslog — ведение системного журнала
systemd-udevd — управление устройствами
systemd-logind — управление сеансами пользователей
polkit — управление правами доступа
Сеть:
systemd-networkd — настройка сетевых интерфейсов
systemd-resolved — DNS (разрешение имён)
Время:
systemd-timesyncd — синхронизация времени
Задачи:
cron — планировщик задач
Диски:
udisks2 — управление дисками
multipathd — отказоустойчивость дисков
VMware:
open-vm-tools — интеграция с VMware
vgauth — аутентификация VMware
Связь:
ModemManager — управление модемами
Обновления:
unattended-upgrades — автообновления безопасности
Пользователь:
user@1000 — пользовательский сеанс
getty@tty1 — консольный вход

![установленные пакеты](screenshots\packages.png)

![Запущенные сервисы](screenshots\services.png)

## №9 Конвейер и перенаправления  ##

- Больше всего потребляет процесс /sbin/multipathd 
- Больше всего процессов запускает root
- sudo — запуск от имени root (для доступа ко всем файлам)
du (disk usage) — подсчет занимаемого места
-a — показывать все файлы (не только директории)
-h — human-readable (формат: K, M, G вместо байт)
/var — целевая директория
2>/dev/null — перенаправление ошибок (stderr, поток 2) в /dev/null (игнорирование ошибок доступа)


![Топ-10 процессов по памяти](screenshots\top-processes.png)

![подсчет количества процессов по пользователям](screenshots\process-count.png)

![Большие файлы в /var](screenshots\big-files.png)


## №10 итоговый файл ##

![Содержимое файла report](screenshots\report-files.png)