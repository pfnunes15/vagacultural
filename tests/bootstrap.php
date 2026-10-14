<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

/*
 * Docker Compose injects the running app's environment (SCOUT_DRIVER=meilisearch,
 * DB_DATABASE=vaga, cache/session/queue=redis, …) into $_SERVER. Laravel's Env
 * reads $_SERVER before $_ENV, so those production-ish values would override the
 * test settings in phpunit.xml — whose <env force> only reaches $_ENV/putenv,
 * not $_SERVER. Pin the test-critical variables across every layer here, before
 * the framework boots, so the suite stays hermetic (in-memory DB, synchronous
 * in-process search, array cache) regardless of the container's env.
 *
 * Keep this list in sync with the <php><env> block in phpunit.xml.
 */
$testEnv = [
    'APP_ENV' => 'testing',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'SCOUT_DRIVER' => 'collection',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array',
];

foreach ($testEnv as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}
