# 📦 Gramota Orders — Система управления заказами

**Gramota Orders** — это веб-приложение для учёта заказчиков, заказов и оплат, разработанное на фреймворке [Symfony 7](https://symfony.com/) с использованием [Doctrine ORM](https://www.doctrine-project.org/), шаблонов [Twig](https://twig.symfony.com/) и адаптивного дизайна на базе [Bootstrap 5](https://getbootstrap.com/).

Приложение запускается в окружении [Docker](https://www.docker.com/) с [FrankenPHP](https://frankenphp.dev) и [Caddy](https://caddyserver.com/).

---

## 🚀 Возможности

- 👥 Управление заказчиками (создание, редактирование, удаление)
- 📑 Учёт заказов и оплат
- 🔒 Аутентификация пользователей
- 💡 Простой и современный интерфейс
- 🐳 Docker-окружение без лишних зависимостей

---

## 📦 Требования

- Docker Compose v2.10+
- Порт 443 (для HTTPS)

---

## ⚙️ Установка и запуск

```bash
# 1. Клонируйте репозиторий
git clone https://github.com/artem-riabtsev/gramota_orders
cd gramota-orders

# 2. Постройте контейнеры
docker compose build --pull --no-cache

# 3. Запустите проект
docker compose up --wait

# 4. Подписать сертификаты (WSL2, при первом запуске) 
docker cp $(docker compose ps -q frankenphp):/data/caddy/pki/authorities/local/root.crt /mnt/c/Users/Public/root.crt
powershell.exe -Command 'Start-Process certutil.exe -ArgumentList "-addstore", "ROOT", "C:\Users\Public\root.crt" -Verb RunAs'

# 5. Откройте приложение
https://localhost
