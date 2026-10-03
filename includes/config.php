<?php
declare(strict_types=1);
session_start();

define('APP_NAME', 'Durame Campus Fellowship');
/* Automatically detect the installation folder so CSS, JS, images and links work both at the domain root and in a subfolder. */
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if (str_ends_with($basePath, '/admin')) { $basePath = rtrim(dirname($basePath), '/'); }
if ($basePath === '.' || $basePath === '/') { $basePath = ''; }
define('BASE_URL', $basePath);

/* InfinityFree details supplied by the site owner. */
define('DB_HOST', 'YOUR_DATABASE_HOST');
define('DB_NAME', 'YOUR_DATABASE_NAME');
define('DB_USER', 'YOUR_DATABASE_USER');
define('DB_PASS', 'YOUR_DATABASE_PASSWORD');

define('SERVICE_FORM_URL', 'register-service.php');
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
date_default_timezone_set('Africa/Addis_Ababa');
?>
