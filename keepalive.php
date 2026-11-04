<?php require_once __DIR__.'/config/auth.php';$u=require_login();header('Content-Type: application/json');echo json_encode(['ok'=>true,'expires_in'=>SESSION_TIMEOUT,'user'=>$u['full_name']]);
