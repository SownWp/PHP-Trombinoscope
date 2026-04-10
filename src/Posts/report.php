<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../public/index.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Vous devez être connecté pour signaler un contenu.';
    header('Location: ../../src/Auth/login.php');
    exit;
}

if (!verifyCsrfToken()) {
    $_SESSION['flash_error'] = 'Token de sécurité invalide.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../../public/index.php'));
    exit;
}

$type     = $_POST['type'] ?? '';
$cibleId  = (int) ($_POST['cible_id'] ?? 0);
$raison   = trim($_POST['raison'] ?? '');
$redirect = $_POST['redirect'] ?? '../../public/index.php';

if (!in_array($type, ['publication', 'commentaire'], true) || $cibleId <= 0) {
    $_SESSION['flash_error'] = 'Signalement invalide.';
    header('Location: ' . $redirect);
    exit;
}

if ($raison === '') {
    $_SESSION['flash_error'] = 'Veuillez indiquer une raison pour le signalement.';
    header('Location: ' . $redirect);
    exit;
}

if ($type === 'publication') {
    $check = $pdo->prepare('SELECT utilisateur_id FROM publications WHERE id = :id LIMIT 1');
    $check->execute(['id' => $cibleId]);
} else {
    $check = $pdo->prepare('SELECT utilisateur_id FROM commentaires WHERE id = :id LIMIT 1');
    $check->execute(['id' => $cibleId]);
}
$target = $check->fetch();

if (!$target) {
    $_SESSION['flash_error'] = 'Le contenu signalé est introuvable.';
    header('Location: ' . $redirect);
    exit;
}

if ((int) $target['utilisateur_id'] === (int) $_SESSION['user_id']) {
    $_SESSION['flash_error'] = 'Vous ne pouvez pas signaler votre propre contenu.';
    header('Location: ' . $redirect);
    exit;
}

$exists = $pdo->prepare(
    'SELECT id FROM signalements WHERE type = :type AND cible_id = :cible_id AND utilisateur_id = :uid LIMIT 1'
);
$exists->execute(['type' => $type, 'cible_id' => $cibleId, 'uid' => $_SESSION['user_id']]);

if ($exists->fetch()) {
    $_SESSION['flash_error'] = 'Vous avez déjà signalé ce contenu.';
    header('Location: ' . $redirect);
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO signalements (type, cible_id, utilisateur_id, raison) VALUES (:type, :cible_id, :uid, :raison)'
);
$stmt->execute([
    'type'     => $type,
    'cible_id' => $cibleId,
    'uid'      => $_SESSION['user_id'],
    'raison'   => $raison,
]);

$_SESSION['flash'] = 'Le contenu a été signalé. Un administrateur examinera votre signalement.';
header('Location: ' . $redirect);
exit;
