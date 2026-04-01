<?php
require_once __DIR__ . '/../../includes/admin.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/mailtrap.php';
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

if (!verifyCsrfToken()) {
    $_SESSION['flash_error'] = 'Token de sécurité invalide.';
    header('Location: dashboard.php');
    exit;
}

$userId = (int) ($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';
$raison = trim($_POST['raison'] ?? '');

if ($userId <= 0 || !in_array($action, ['ban', 'unban'], true)) {
    $_SESSION['flash_error'] = 'Requête invalide.';
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, prenom, nom, email, role FROM utilisateurs WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $userId]);
$target = $stmt->fetch();

if (!$target) {
    $_SESSION['flash_error'] = 'Utilisateur introuvable.';
    header('Location: dashboard.php');
    exit;
}

if ($target['role'] === 'admin') {
    $_SESSION['flash_error'] = 'Impossible de bannir un administrateur.';
    header('Location: dashboard.php');
    exit;
}

$adminId = $_SESSION['user_id'];
$isBanned = $action === 'ban' ? 1 : 0;

$pdo->beginTransaction();
try {
    $update = $pdo->prepare('UPDATE utilisateurs SET is_banned = :banned WHERE id = :id');
    $update->execute(['banned' => $isBanned, 'id' => $userId]);

    $log = $pdo->prepare(
        'INSERT INTO bannissements (utilisateur_id, admin_id, action, raison) VALUES (:uid, :aid, :action, :raison)'
    );
    $log->execute([
        'uid' => $userId,
        'aid' => $adminId,
        'action' => $action,
        'raison' => $raison !== '' ? $raison : null,
    ]);

    if ($action === 'ban') {
        $token = bin2hex(random_bytes(32));
        $insertToken = $pdo->prepare(
            'INSERT INTO demandes_deban (utilisateur_id, token, message, statut) VALUES (:uid, :token, :message, :statut)'
        );
        $insertToken->execute([
            'uid' => $userId,
            'token' => $token,
            'message' => '',
            'statut' => 'en_attente',
        ]);
    }

    $pdo->commit();

    $name = $target['prenom'] . ' ' . $target['nom'];
    if ($action === 'ban') {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $unbanUrl = $protocol . '://' . $host . '/src/Auth/request-unban.php?token=' . $token;

        $raisonHtml = $raison !== '' ? htmlspecialchars($raison) : 'Aucune raison spécifiée';
        $html = '
        <div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;padding:2rem;">
            <h2 style="color:#C94B2C;">Votre compte a été suspendu</h2>
            <p>Bonjour <strong>' . htmlspecialchars($target['prenom']) . '</strong>,</p>
            <p>Votre compte sur le Trombinoscope a été suspendu par un administrateur.</p>
            <p><strong>Raison :</strong> ' . $raisonHtml . '</p>
            <p>Si vous pensez qu\'il s\'agit d\'une erreur, vous pouvez soumettre une demande de débannissement en cliquant sur le bouton ci-dessous :</p>
            <p style="text-align:center;margin:2rem 0;">
                <a href="' . $unbanUrl . '" style="background:#C94B2C;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;">Demander un débannissement</a>
            </p>
            <p style="color:#8C8379;font-size:0.85rem;">Ce lien est unique et personnel. Ne le partagez pas.</p>
        </div>';

        sendMail($target['email'], 'Votre compte a été suspendu — Trombinoscope', $html);

        $_SESSION['flash'] = htmlspecialchars($name) . ' a été banni. Un email lui a été envoyé.';
    } else {
        $_SESSION['flash'] = htmlspecialchars($name) . ' a été débanni.';
    }
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash_error'] = 'Une erreur est survenue.';
}

header('Location: dashboard.php');
exit;
