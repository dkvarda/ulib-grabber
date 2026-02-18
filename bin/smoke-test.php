#!/usr/bin/env php
<?php

declare(strict_types=1);

use Ulib\Grabber\UlibPhoneDirectory;

$projectRoot = dirname(__DIR__);

requireAutoload($projectRoot);

$cases = [
    'default' => [],
    'filter_firstname' => ['firstname' => 'Ján', 'sort' => 1, 'column' => 0],
    'page_2' => ['page' => 2],
];

foreach ($cases as $name => $query) {
    echo "=== CASE: {$name} ===\n";

    try {
        $grabber = new UlibPhoneDirectory($query);
        $users = $grabber->getUsers();
        $pageResult = $grabber->getPageResult();
        $paginator = $grabber->getPaginator();

        echo 'users_count=' . count($users) . PHP_EOL;
        echo 'page_result=' . ($pageResult ?? 'null') . PHP_EOL;
        echo 'active_page=' . ($paginator['activePage'] ?? 'null') . PHP_EOL;
        echo 'pages=' . (isset($paginator['pages']) ? implode(',', $paginator['pages']) : 'null') . PHP_EOL;

        if ($users !== []) {
            $first = $users[0];
            echo 'first_user_clean_name=' . $first->getCleanName() . PHP_EOL;
            echo 'first_user_mail=' . ($first->getMail() ?? 'null') . PHP_EOL;
            echo 'first_user_phones=' . implode(',', $first->getPhoneNumbers()) . PHP_EOL;
        }

        echo "status=OK\n\n";
    } catch (Throwable $e) {
        echo "status=ERROR\n";
        echo 'error_class=' . $e::class . PHP_EOL;
        echo 'error_message=' . $e->getMessage() . PHP_EOL . PHP_EOL;
    }
}

function requireAutoload(string $projectRoot): void
{
    $autoload = $projectRoot . '/vendor/autoload.php';
    if (is_file($autoload)) {
        try {
            require $autoload;

            return;
        } catch (Throwable) {
            // Fallback for environments where composer platform check fails.
        }
    }

    spl_autoload_register(static function (string $class) use ($projectRoot): void {
        $prefix = 'Ulib\\Grabber\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $file = $projectRoot . '/src/' . str_replace('\\', '/', $relative) . '.php';

        if (is_file($file)) {
            require $file;
        }
    });
}
