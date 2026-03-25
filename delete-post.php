<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken()) {
    $_SESSION['flash_error'] = 'Requête invalide.';
    header('Location: index.php');
    exit;
}

$postId = (int) ($_POST['post_id'] ?? 0);
if (!$postId) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM publications WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $postId]);
$post = $stmt->fetch();

if (!$post || (int) $post['utilisateur_id'] !== (int) $_SESSION['user_id']) {
    $_SESSION['flash_error'] = 'Action non autorisée.';
    header('Location: index.php');
    exit;
}

$delete = $pdo->prepare('DELETE FROM publications WHERE id = :id');
$delete->execute(['id' => $postId]);

$_SESSION['flash'] = 'Publication supprimée.';
header('Location: profil.php?id=' . $_SESSION['user_id']);
exit;
