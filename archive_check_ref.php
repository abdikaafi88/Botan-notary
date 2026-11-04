<?php
require_once __DIR__.'/config/auth.php';
require_login();
header('Content-Type: application/json; charset=utf-8');
$ref=trim((string)($_GET['ref']??''));
if($ref===''){echo json_encode(['available'=>false]);exit;}
$s=db()->prepare('SELECT id FROM archives WHERE ref_number=? LIMIT 1');$s->execute([$ref]);
echo json_encode(['available'=>(bool)!$s->fetch()]);
