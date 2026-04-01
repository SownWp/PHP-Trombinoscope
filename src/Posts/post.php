<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/csrf.php';

if (!verifyCsrfToken()) {
    $_SESSION['flash_error'] = 'Token de sécurité invalide.';
    header('Location: ../Profile/profil.php?id=' . $_SESSION['user_id']);
    exit;
}

$contenu = trim($_POST['contenu'] ?? '');

if ($contenu === '') {
    $_SESSION['flash_error'] = 'Le contenu de la publication ne peut pas être vide.';
    header('Location: ../Profile/profil.php?id=' . $_SESSION['user_id']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO publications (utilisateur_id, contenu) VALUES (:uid, :contenu)');
$stmt->execute([
    'uid' => $_SESSION['user_id'],
    'contenu' => $contenu,
]);

$_SESSION['flash'] = 'Publication ajoutée avec succès.';
header('Location: ../Profile/profil.php?id=' . $_SESSION['user_id']);
exit;
