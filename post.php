<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

$contenu = trim($_POST['contenu'] ?? '');

if ($contenu === '') {
    $_SESSION['flash_error'] = 'Le contenu de la publication ne peut pas être vide.';
    header('Location: profil.php?id=' . $_SESSION['user_id']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO publications (utilisateur_id, contenu) VALUES (:uid, :contenu)');
$stmt->execute([
    'uid' => $_SESSION['user_id'],
    'contenu' => $contenu,
]);

$_SESSION['flash'] = 'Publication ajoutée avec succès.';
header('Location: profil.php?id=' . $_SESSION['user_id']);
exit;
