<?php
declare(strict_types=1);

// Hosting can point CHORES_CONFIG_FILE at a PHP file outside the public web root.
// Environment variables are supported for development and managed hosting.
$configFile = getenv('CHORES_CONFIG_FILE');
$config = [];
if ($configFile !== false && $configFile !== '' && is_file($configFile)) {
    $loadedConfig = require $configFile;
    if (is_array($loadedConfig)) {
        $config = $loadedConfig;
    }
}

$dbhostname = (string)($config['db_host'] ?? getenv('CHORES_DB_HOST') ?: 'localhost');
$dbusername = (string)($config['db_user'] ?? getenv('CHORES_DB_USER') ?: '');
$dbpassword = (string)($config['db_password'] ?? getenv('CHORES_DB_PASSWORD') ?: '');
$dbdatabase = (string)($config['db_name'] ?? getenv('CHORES_DB_NAME') ?: '');

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && is_string($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function require_database_config(): void
{
    global $dbusername, $dbpassword, $dbdatabase;
    if ($dbusername === '' || $dbdatabase === '') {
        http_response_code(500);
        exit('Application configuration is incomplete.');
    }
}
