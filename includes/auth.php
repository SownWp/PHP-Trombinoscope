<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Vous devez être connecté pour accéder à cette page.';
    $projectRoot = str_replace('\\', '/', dirname(__DIR__));
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $baseUrl = str_replace($docRoot, '', $projectRoot);
    header('Location: ' . $baseUrl . '/src/Auth/login.php');
    exit;
}
