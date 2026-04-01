<?php
require_once __DIR__ . '/auth.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash_error'] = 'Accès réservé aux administrateurs.';
    $projectRoot = str_replace('\\', '/', dirname(__DIR__));
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $baseUrl = str_replace($docRoot, '', $projectRoot);
    header('Location: ' . $baseUrl . '/public/index.php');
    exit;
}
