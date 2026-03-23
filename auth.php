<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Vous devez être connecté pour accéder à cette page.';
    header('Location: login.php');
    exit;
}
