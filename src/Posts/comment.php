<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/csrf.php';

if (!verifyCsrfToken()) {
    $_SESSION['flash_error'] = 'Token de sécurité invalide.';
    header('Location: ../../public/index.php');
    exit;
}

$postId = (int) ($_POST['post_id'] ?? 0);
$contenu = trim($_POST['contenu'] ?? '');

if (!$postId || $contenu === '') {
    header('Location: ../../public/index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT utilisateur_id FROM publications WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $postId]);
$pub = $stmt->fetch();

if (!$pub) {
    header('Location: ../../public/index.php');
    exit;
}

$insert = $pdo->prepare('INSERT INTO commentaires (publication_id, utilisateur_id, contenu) VALUES (:pid, :uid, :contenu)');
$insert->execute([
    'pid' => $postId,
    'uid' => $_SESSION['user_id'],
    'contenu' => $contenu,
]);

header('Location: ../Profile/profil.php?id=' . $pub['utilisateur_id']);
exit;
