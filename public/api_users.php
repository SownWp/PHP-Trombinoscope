<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/cloudinary.php';

header('Content-Type: application/json');

$promo   = $_GET['promo'] ?? null;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

if ($promo) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE promo = :promo");
    $countStmt->execute(['promo' => $promo]);
    $total = (int) $countStmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT id, prenom, nom, specialite, promo, bio, avatar FROM utilisateurs WHERE promo = :promo LIMIT :limit OFFSET :offset");
    $stmt->bindValue('promo', $promo);
} else {
    $total = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();

    $stmt = $pdo->prepare("SELECT id, prenom, nom, specialite, promo, bio, avatar FROM utilisateurs LIMIT :limit OFFSET :offset");
}

$stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$html = '';
foreach ($users as $user) {
    $avatarSrc = htmlspecialchars(avatarUrl($user['avatar']));
    $prenom    = htmlspecialchars($user['prenom']);
    $nom       = htmlspecialchars($user['nom']);
    $spec      = htmlspecialchars($user['specialite'] ?? 'Étudiant');
    $promoText = htmlspecialchars($user['promo']);
    $id        = (int) $user['id'];

    $html .= '<div class="trombi-card card">'
           .   '<a href="../src/Profile/profil.php?id=' . $id . '">'
           .     '<img class="card-img" src="' . $avatarSrc . '" alt="' . $prenom . '">'
           .     '<div class="card-body">'
           .       '<div class="card-name">' . $prenom . ' ' . $nom . '</div>'
           .       '<div class="card-role">' . $spec . '</div>'
           .       '<span class="card-promo">' . $promoText . '</span>'
           .     '</div>'
           .   '</a>'
           . '</div>';
}

$totalPages = max(1, (int) ceil($total / $perPage));

echo json_encode([
    'html'     => $html,
    'page'     => $page,
    'hasMore'  => $page < $totalPages,
]);
