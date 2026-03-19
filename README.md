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
git clone https://github.com/yourusername/gramota-orders.git
cd gramota-orders

# 2. Постройте контейнеры
docker compose build --pull --no-cache

# 3. Запустите проект
docker compose up --wait

# 4. Выполните миграции Doctrine
docker compose exec php bin/console doctrine:migrations:migrate

# 5. Сборка фронтенд-ассетов (React & Webpack)
# Установите JS-зависимости и скомпилируйте файлы
docker compose exec php npm install
docker compose exec php npm run dev

# 6. Подпишите сертификаты (WSL2, при первом запуске) 
docker cp $(docker compose ps -q frankenphp):/data/caddy/pki/authorities/local/root.crt /mnt/c/Users/Public/root.crt
powershell.exe -Command 'Start-Process certutil.exe -ArgumentList "-addstore", "ROOT", "C:\Users\Public\root.crt" -Verb RunAs'

# 7. Откройте приложение
https://localhost
