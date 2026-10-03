<?php
declare(strict_types=1);

function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }
function url(string $path = ''): string { return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/'); }
function is_post(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
function redirect(string $path): never { header('Location: ' . url($path)); exit; }
function csrf_token(): string { if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
function verify_csrf(): void { $token = $_POST['csrf_token'] ?? ''; if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) { http_response_code(419); exit('Invalid CSRF token.'); } }
function flash(string $key, ?string $message = null): ?string { if ($message !== null) { $_SESSION['flash'][$key] = $message; return null; } $v = $_SESSION['flash'][$key] ?? null; unset($_SESSION['flash'][$key]); return $v; }
function lang(): string { $l = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en'; if (!in_array($l, ['en','am'], true)) $l='en'; $_SESSION['lang']=$l; return $l; }
function t(string $en, string $am): string { return lang() === 'am' ? $am : $en; }
function text_lang(array $row, string $field): string { $l=lang(); $v=$row[$field.'_'.$l] ?? ''; if ($v==='') $v=$row[$field.'_en'] ?? ''; return (string)$v; }
function lang_url(string $path): string { return url($path) . (str_contains($path,'?') ? '&' : '?') . 'lang=' . rawurlencode(lang()); }
function other_lang_url(): string { $new=lang()==='en'?'am':'en'; $path=parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'; $query=[]; parse_str($_SERVER['QUERY_STRING'] ?? '', $query); $query['lang']=$new; return $path.'?'.http_build_query($query); }
function service_url(): string { return url('register-service.php'); }
?>
