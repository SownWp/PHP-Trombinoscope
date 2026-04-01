<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non connecté']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$commentId = (int) ($input['comment_id'] ?? 0);

if (!$commentId) {
    http_response_code(400);
    echo json_encode(['error' => 'Commentaire invalide']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

$check = $pdo->prepare('SELECT id FROM likes_commentaires WHERE commentaire_id = :cid AND utilisateur_id = :uid');
$check->execute(['cid' => $commentId, 'uid' => $userId]);

if ($check->fetch()) {
    $pdo->prepare('DELETE FROM likes_commentaires WHERE commentaire_id = :cid AND utilisateur_id = :uid')
        ->execute(['cid' => $commentId, 'uid' => $userId]);
    $liked = false;
} else {
    $pdo->prepare('INSERT INTO likes_commentaires (commentaire_id, utilisateur_id) VALUES (:cid, :uid)')
        ->execute(['cid' => $commentId, 'uid' => $userId]);
    $liked = true;
}

$count = $pdo->prepare('SELECT COUNT(*) FROM likes_commentaires WHERE commentaire_id = :cid');
$count->execute(['cid' => $commentId]);
$totalLikes = (int) $count->fetchColumn();

echo json_encode(['liked' => $liked, 'count' => $totalLikes]);
