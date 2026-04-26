<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Загружаем .env.test если есть
if (file_exists(dirname(__DIR__).'/.env.test')) {
    (new Dotenv())->load(dirname(__DIR__).'/.env.test');
}

// Устанавливаем переменные окружения
$_SERVER['KERNEL_CLASS'] = $_ENV['KERNEL_CLASS'] = 'App\Kernel';
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
