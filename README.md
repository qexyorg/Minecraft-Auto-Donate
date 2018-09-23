# Minecraft Auto Donate
OnePage Auto Donate for Minecraft

Install:
1. Import file import.sql via MySQL
2. Change file Applications/MyApp/MyApp.php on section mysqli for your database
3. Upload all files (without import.sql) on your web server

Other settings can be changed in Applications/MyApp/MyApp.php

-------------------

Функционал:
- Продажа групп привилегий
- Оплата через платежный шлюз Unitpay
- Полный Ajax
- Выдача через плагин Shopping Cart Reloaded
- Вывод онлайн статистики с сервера (Мониторинг)
- Адаптация для мобильных устройств
- Настройки через файл конфигурации

Установка:
- Извлечь все файлы и папки из архива
- Импортировать файл import.sql в базу
- В файле Applications/MyApp/MyApp.php изменить раздел mysqli на свои данные
- Залить все файлы и папки, кроме import.sql, в корень вашего сайта

Настройки Unitpay:
- Добавить новый проект на сайте Unitpay
- В графе "Обработчик платежей" укажите ссылку http://ваш-сайт.ком/donate/status/
- Скопируйте публичный и приватный ключи и вставьте их в соответствующих полях в файле Applications/MyApp/MyApp.php раздела unitpay
