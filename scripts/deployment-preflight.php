<?php

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Fail before migrations if production protections or the recovery copy are missing.
if (! $app->environment('production') || config('app.debug') || ! str_starts_with(config('app.url'), 'https://')
    || ! config('session.secure') || ! config('session.http_only') || ! config('app.key')) {
    fwrite(STDERR, "Deployment stopped: production environment, HTTPS, secure cookies and debug-off are required.\n");
    exit(1);
}

$connection = $app['db']->connection();
if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
    fwrite(STDERR, "Deployment stopped: expected the production MySQL/MariaDB connection.\n");
    exit(1);
}
$finder = new ExecutableFinder;
$binary = $finder->find('mariadb-dump') ?? $finder->find('mysqldump');
if (! $binary) {
    fwrite(STDERR, "Deployment stopped: database dump executable is unavailable.\n");
    exit(1);
}

$directory = storage_path('app/private/deploy-backups');
if (is_link($directory) || (! is_dir($directory) && ! mkdir($directory, 0700, true))) {
    throw new RuntimeException('Cannot create private backup directory.');
}
chmod($directory, 0700);
$basename = $directory.'/'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4));
$credentials = tempnam($directory, 'db-client-');
chmod($credentials, 0600);
$dump = $basename.'.sql.partial';
$handle = fopen($dump, 'xb');
chmod($dump, 0600);
$complete = false;
try {
    $quote = static fn ($value) => '"'.str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '\\n', '\\r'], (string) $value).'"';
    $client = "[client]\n";
    foreach (['host', 'port', 'username', 'password', 'unix_socket'] as $key) {
        $value = $connection->getConfig($key);
        if ($value !== null && $value !== '') {
            $option = ['username' => 'user', 'unix_socket' => 'socket'][$key] ?? $key;
            $client .= $option.'='.$quote($value)."\n";
        }
    }
    if (file_put_contents($credentials, $client) === false) {
        throw new RuntimeException('Cannot prepare database backup credentials.');
    }
    $process = new Process([$binary, '--defaults-extra-file='.$credentials, '--single-transaction', '--quick', '--skip-lock-tables', '--no-tablespaces', '--hex-blob', '--', $connection->getDatabaseName()]);
    $process->setTimeout(180);
    $process->run(function ($type, $buffer) use ($handle, $process) {
        if ($type === Process::OUT) {
            if (fwrite($handle, $buffer) !== strlen($buffer)) {
                throw new RuntimeException('Database backup write failed.');
            }
            $process->clearOutput();
        }
    });
    if (! $process->isSuccessful() || fstat($handle)['size'] === 0) {
        // Do not print connection details or SQL from the database client.
        throw new RuntimeException('Database backup failed. No migrations have been run.');
    }
    fclose($handle);
    $handle = null;
    if (! rename($dump, $basename.'.sql')) {
        throw new RuntimeException('Cannot finalize the database backup.');
    }
    $complete = true;
    echo "Production security preflight passed; private database backup completed.\n";
} finally {
    if (is_resource($handle)) {
        fclose($handle);
    }
    if (is_file($credentials)) {
        unlink($credentials);
    }
    if (! $complete && is_file($dump)) {
        unlink($dump);
    }
}
