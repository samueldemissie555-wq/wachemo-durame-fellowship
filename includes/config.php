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
define('DB_HOST', 'sql304.infinityfree.com');
define('DB_NAME', 'if0_42696741_Durame');
define('DB_USER', 'if0_42696741');
/* Put the MySQL password shown/created in InfinityFree here. */
define('DB_PASS', 'Same0909Same');

define('SERVICE_FORM_URL', 'register-service.php');
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
date_default_timezone_set('Africa/Addis_Ababa');
?>
