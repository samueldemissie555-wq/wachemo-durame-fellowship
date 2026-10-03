<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

function admin_logged_in(): bool {
    return isset($_SESSION['admin_id']);
}

function require_admin(): void {
    if (!admin_logged_in()) {
        redirect('admin/login.php');
    }
}
?>