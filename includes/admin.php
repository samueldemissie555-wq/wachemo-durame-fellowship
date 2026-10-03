<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/images.php';
require_admin();
function admin_page(string $title, string $active='dashboard', string $subtitle=''): void {
    $nav=[
      'dashboard'=>['dashboard.php','⌂','Dashboard'],
      'content'=>['content.php','▣','Announcements'],
      'events'=>['events.php','◷','Events'],
      'sermons'=>['sermons.php','▶','Sermons'],
      'resources'=>['resources.php','▤','Resources'],
      'gallery'=>['gallery.php','▧','Gallery'],
      'media'=>['media.php','▦','Media Manager'],
      'ministries'=>['ministries.php','✦','Ministries'],
      'prayer'=>['prayer.php','♡','Prayer Requests'],
      'registrations'=>['registrations.php','☑','Service Registrations'],
      'forms'=>['forms.php','▤','Form Builder'],
      'messages'=>['messages.php','✉','Messages'],
      'social'=>['social.php','◉','Social Links'],
      'users'=>['users.php','♙','Users'],
      'settings'=>['settings.php','⚙','Settings'],
    ];
    ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#061a2b"><title><?=e($title)?> • Fellowship Admin</title><link rel="stylesheet" href="../assets/css/style.css?v=20260914"><link rel="stylesheet" href="../assets/css/deployment-fix.css?v=20260914"></head><body class="admin-body"><div class="admin-shell"><aside class="admin-side"><div class="admin-brand"><div class="admin-brand-logo"><img src="../<?=e(current_logo())?>" alt="Logo"></div><div><strong>DURAME FELLOWSHIP</strong><small>ADMIN CONTROL CENTER</small></div></div><nav class="admin-nav"><div class="admin-nav-section">Management</div><?php foreach($nav as $key=>$n): ?><a class="<?= $active===$key?'active':'' ?>" href="<?=e($n[0])?>"><span><?=e($n[1])?></span><?=e($n[2])?></a><?php endforeach; ?><div class="admin-nav-section">Website</div><a href="../index.php" target="_blank"><span>↗</span>View Website</a><a href="<?=e(service_url())?>" target="_blank" rel="noopener"><span>↗</span>Service Form</a><a href="logout.php"><span>⇥</span>Logout</a></nav></aside><main class="admin-main"><div class="admin-top"><div><span class="eyebrow">DURAME CAMPUS FELLOWSHIP</span><h1><?=e($title)?></h1><?php if($subtitle): ?><p class="muted"><?=e($subtitle)?></p><?php else: ?><p class="muted">Welcome, <?=e($_SESSION['admin_username']??'Administrator')?> · <?=e($_SESSION['admin_role']??'admin')?></p><?php endif; ?></div><div class="admin-actions"><a class="btn outline" href="../index.php" target="_blank">View Website ↗</a></div></div><?php
}
function admin_end(): void { echo '</main></div></body></html>'; }
function admin_flash(): void { if($m=flash('admin_success')) echo '<div class="alert">'.e($m).'</div>'; if($m=flash('admin_error')) echo '<div class="alert error">'.e($m).'</div>'; }
function log_admin(string $action): void { try{ $st=db()->prepare('INSERT INTO activity_logs(user_id,action,ip_address) VALUES(?,?,?)');$st->execute([$_SESSION['admin_id']??null,$action,$_SERVER['REMOTE_ADDR']??null]); }catch(Throwable $e){} }
function require_role(array $roles=['super_admin','admin','editor']): void { if(!in_array($_SESSION['admin_role']??'', $roles, true)){http_response_code(403);exit('Access denied.');} }
?>
