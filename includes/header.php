<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/images.php';
$currentLang = lang();
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!doctype html>
<html lang="<?= $currentLang === 'am' ? 'am' : 'en' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#071b2b">
<meta name="description" content="<?=e(APP_NAME)?> — Wachemo University, Durame Campus">
<title><?=e($pageTitle)?> | <?=e(APP_NAME)?></title>
<link rel="stylesheet" href="<?=url('assets/css/style.css')?>?v=20260907">
<link rel="stylesheet" href="<?=url('assets/css/deployment-fix.css')?>?v=20260907">
</head>
<body>
<div class="topbar"><div class="container topbar-in"><span>✦ <?=e(t('Wachemo University · Durame Campus','ዋቸሞ ዩኒቨርሲቲ · ዱራሜ ካምፓስ'))?></span><span><?=e(t('Seeking God • Growing Together • Serving Others','እግዚአብሔርን መፈለግ • በአንድነት ማደግ • ሌሎችን ማገልገል'))?></span></div></div>
<header class="site-header" id="siteHeader">
  <div class="container nav-wrap">
    <a class="brand" href="<?=lang_url('index.php')?>">
      <span class="brand-logo-wrap"><img class="brand-logo" src="<?=e(current_logo())?>" alt="Durame Campus Fellowship"></span><span><strong>DURAME</strong><small><?=e(t('CAMPUS FELLOWSHIP','ካምፓስ ፌሎሽፕ'))?></small></span>
    </a>
    <button class="menu-toggle" id="menuToggle" aria-label="Open menu" aria-expanded="false">☰</button>
    <nav class="main-nav" id="mainNav">
      <a href="<?=lang_url('index.php')?>"><?=e(t('Home','መነሻ'))?></a>
      <a href="<?=lang_url('about.php')?>"><?=e(t('About','ስለ እኛ'))?></a>
      <a href="<?=lang_url('ministries.php')?>"><?=e(t('Ministries','አገልግሎቶች'))?></a>
      <a href="<?=lang_url('events.php')?>"><?=e(t('Events','ዝግጅቶች'))?></a>
      <a href="<?=lang_url('sermons.php')?>"><?=e(t('Sermons','ስብከቶች'))?></a>
      <a href="<?=lang_url('resources.php')?>"><?=e(t('Resources','ምንጮች'))?></a>
      <a href="<?=lang_url('gallery.php')?>"><?=e(t('Gallery','ጋለሪ'))?></a>
      <a href="<?=lang_url('prayer.php')?>"><?=e(t('Prayer','ጸሎት'))?></a>
      <a href="<?=lang_url('contact.php')?>"><?=e(t('Contact','እውቂያ'))?></a>
      <a class="nav-register" href="<?=e(service_url())?>" target="_blank" rel="noopener"><?=e(t('Register for Service','ለአገልግሎት ይመዝገቡ'))?> ↗</a>
    </nav>
    <div class="header-tools"><a class="lang-switch" href="<?=e(other_lang_url())?>"><?=$currentLang==='en'?'አማ':'EN'?></a><span>⌕</span><span>◔</span></div>
  </div>
</header>
<main>
<script defer src="<?=url('assets/js/app.js')?>?v=20260823"></script>
