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

# 2. Запустить проект
make setup

# 3. Откройте приложение
https://localhost

# Пересобрать фронт (dev)
docker compose exec php bash -c "cd /app && npm run build"
