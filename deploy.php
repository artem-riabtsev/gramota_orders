<?php

namespace Deployer;

require 'recipe/symfony.php';

// Config

set('repository', 'git@github.com:artem-riabtsev/gramota_orders.git');
set('branch', 'main');
set('http_user', 'j97027527');
set('writable_mode', 'chown');
set('bin/php', '/opt/alt/php83/usr/bin/php'); // Путь до интерпритатора PHP

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('')
    ->set('hostname', '8ae3587f5143.hosting.myjino.ru')
    ->set('remote_user', 'j97027527')
    ->set('deploy_path', '~/Projects/test/orders');

// Hooks

after('deploy:failed', 'deploy:unlock');
