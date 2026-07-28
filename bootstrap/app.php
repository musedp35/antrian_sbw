<?php

/*
|--------------------------------------------------------------------------
| Create The Application Instance
|--------------------------------------------------------------------------
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind config SERVICE EARLY - Fixes ReflectionException
|--------------------------------------------------------------------------
*/

// Load .env variables first
if (file_exists(__DIR__.'/../.env')) {
    $lines = file(__DIR__.'/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (trim($line)[0] === '#') continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Create initial config repository with minimum needed items
$configInstance = new \Illuminate\Config\Repository([
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'Sistem Antrian SBW',
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => (bool)($_ENV['APP_DEBUG'] ?? false),
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'timezone' => 'UTC',
        'locale' => 'en',
        'fallback_locale' => 'en',
        'providers' => [],
    ],
]);

// Bind config to container IMMEDIATELY (before loading config files)
$app->instance('config', $configInstance);
$app->alias('config', \Illuminate\Config\Repository::class);
$app->alias('config', \Illuminate\Contracts\Config\Repository::class);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Load and merge all config files into the bound config repository
|--------------------------------------------------------------------------
*/

$configFiles = glob(__DIR__.'/../config/*.php');
foreach ($configFiles as $filename) {
    $key = basename($filename, '.php');
    $data = require $filename;
    if (is_array($data)) {
        $configInstance->set($key, $data);
    }
}

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(App\Providers\RouteServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
*/

return $app;
