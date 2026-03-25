<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php';

if (!verifyCsrfToken()) {
    $_SESSION['flash_error'] = 'Token de sécurité invalide.';
    header('Location: index.php');
    exit;
}

$postId = (int) ($_POST['post_id'] ?? 0);
$contenu = trim($_POST['contenu'] ?? '');

if (!$postId || $contenu === '') {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT utilisateur_id FROM publications WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $postId]);
$pub = $stmt->fetch();

if (!$pub) {
    header('Location: index.php');
    exit;
}

$insert = $pdo->prepare('INSERT INTO commentaires (publication_id, utilisateur_id, contenu) VALUES (:pid, :uid, :contenu)');
$insert->execute([
    'pid' => $postId,
    'uid' => $_SESSION['user_id'],
    'contenu' => $contenu,
]);

header('Location: profil.php?id=' . $pub['utilisateur_id']);
exit;
