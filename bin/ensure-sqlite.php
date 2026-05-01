<?php

/**
 * Buat file SQLite jika pakai driver sqlite dan file belum ada (container / deploy baru).
 */
declare(strict_types=1);

$base = dirname(__DIR__);

require $base.'/vendor/autoload.php';

$app = require_once $base.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (config('database.default') !== 'sqlite') {
    exit(0);
}

$path = config('database.connections.sqlite.database');

if ($path === ':memory:' || $path === null || $path === '') {
    exit(0);
}

if (is_file($path)) {
    exit(0);
}

$dir = dirname($path);
if ($dir !== '.' && $dir !== '' && ! is_dir($dir)) {
    mkdir($dir, 0775, true);
}

touch($path);

fwrite(STDERR, "[cms] created SQLite file: {$path}\n");
