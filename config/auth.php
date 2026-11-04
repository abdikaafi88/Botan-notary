<?php
declare(strict_types=1);
require_once __DIR__.'/db.php';

ini_set('session.use_strict_mode','1');
ini_set('session.cookie_httponly','1');
ini_set('session.cookie_samesite','Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ini_set('session.cookie_secure','1');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function e(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): never { header('Location: '.$url); exit; }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verify_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Invalid CSRF token.'); } }
function flash(string $type, string $message): void { $_SESSION['flash'][] = [$type, $message]; }
function take_flash(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }
function client_ip(): string { return substr((string)($_SERVER['REMOTE_ADDR'] ?? ''),0,45); }
function log_activity(string $action, ?string $entityType=null, ?int $entityId=null, ?string $entityRef=null, ?string $details=null, ?array $actor=null): void {
    try {
        $actor=$actor ?: ($_SESSION['user_snapshot'] ?? null); $userId=$actor['id']??($_SESSION['user_id']??null); $name=$actor['full_name']??($actor['username']??'Guest');
        $s=db()->prepare('INSERT INTO activity_logs(user_id,user_name_snapshot,action,entity_type,entity_id,entity_ref,details,ip_address,user_agent) VALUES(?,?,?,?,?,?,?,?,?)');
        $s->execute([$userId,$name,$action,$entityType,$entityId,$entityRef,$details,client_ip(),substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,500)]);
    } catch(Throwable $ignored) {}
}
function current_user(): ?array {
    static $user = false;
    if ($user !== false) return $user;
    $user = null;
    if (!empty($_SESSION['user_id'])) {
        $s=db()->prepare('SELECT * FROM users WHERE id=? AND status="active"'); $s->execute([$_SESSION['user_id']]); $user=$s->fetch() ?: null;
        if($user) $_SESSION['user_snapshot']=['id'=>$user['id'],'full_name'=>$user['full_name'],'username'=>$user['username'],'role'=>$user['role']];
    }
    return $user;
}
function require_login(): array {
    $u=current_user();
    if (!$u) redirect('login.php');
    if (empty($_SESSION['last_activity']) || time() - (int)$_SESSION['last_activity'] > SESSION_TIMEOUT) {
        log_activity('auto logout','user',(int)$u['id'],$u['username'],'Inactivity timeout',$u);
        logout_user();
        if (str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''),'application/json') || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') { http_response_code(401); header('Content-Type: application/json'); echo json_encode(['error'=>'timeout']); exit; }
        redirect('login.php?timeout=1');
    }
    $_SESSION['last_activity']=time();
    return $u;
}
function require_admin(): array { $u=require_login(); if ($u['role'] !== 'admin') { http_response_code(403); exit('Forbidden'); } return $u; }
function login_user(array $u): void { session_regenerate_id(true); $_SESSION['user_id']=(int)$u['id']; $_SESSION['last_activity']=time(); $_SESSION['user_snapshot']=['id'=>$u['id'],'full_name'=>$u['full_name'],'username'=>$u['username'],'role'=>$u['role']]; log_activity('login','user',(int)$u['id'],$u['username'],'Successful login',$u); }
function logout_user(): void { $_SESSION=[]; if (ini_get('session.use_cookies')) { $p=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']); } if(session_status()===PHP_SESSION_ACTIVE) session_destroy(); }
function upload_file(array $file, string $kind, array $allowed=['pdf','jpg','jpeg','png'], int $maxBytes=MAX_UPLOAD_BYTES): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('A document is missing or could not be uploaded.');
    if (($file['size'] ?? 0) > $maxBytes) throw new RuntimeException('Each document must be 500 MB or smaller.');
    $ext=strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)); if (!in_array($ext,$allowed,true)) throw new RuntimeException('Allowed file types: PDF, JPG, JPEG, PNG.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']); $valid=['pdf'=>'application/pdf','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png'];
    if (($valid[$ext] ?? '') !== $mime) throw new RuntimeException('The uploaded document type is invalid.');
    $dir=__DIR__.'/../uploads/'.$kind; if(!is_dir($dir)) mkdir($dir,0750,true); $name=bin2hex(random_bytes(16)).'.'.$ext; if(!move_uploaded_file($file['tmp_name'],$dir.'/'.$name)) throw new RuntimeException('Could not store the uploaded document.'); return 'uploads/'.$kind.'/'.$name;
}
function remove_file(?string $path): void { if ($path) { $full=__DIR__.'/../'.ltrim($path,'/'); if (is_file($full)) @unlink($full); } }
function old(string $key): string { return e($_POST[$key] ?? ''); }
