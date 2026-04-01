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

$requestId = (int) ($_POST['request_id'] ?? 0);
$decision = $_POST['decision'] ?? '';

if ($requestId <= 0 || !in_array($decision, ['acceptee', 'refusee'], true)) {
    $_SESSION['flash_error'] = 'Requête invalide.';
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT d.*, u.prenom, u.nom, u.email
     FROM demandes_deban d
     JOIN utilisateurs u ON d.utilisateur_id = u.id
     WHERE d.id = :id AND d.statut = :statut LIMIT 1'
);
$stmt->execute(['id' => $requestId, 'statut' => 'en_attente']);
$request = $stmt->fetch();

if (!$request) {
    $_SESSION['flash_error'] = 'Demande introuvable ou déjà traitée.';
    header('Location: dashboard.php');
    exit;
}

$adminId = $_SESSION['user_id'];

$pdo->beginTransaction();
try {
    $update = $pdo->prepare(
        'UPDATE demandes_deban SET statut = :statut, admin_id = :admin_id, treated_at = NOW() WHERE id = :id'
    );
    $update->execute([
        'statut' => $decision,
        'admin_id' => $adminId,
        'id' => $requestId,
    ]);

    if ($decision === 'acceptee') {
        $pdo->prepare('UPDATE utilisateurs SET is_banned = 0 WHERE id = :id')
            ->execute(['id' => $request['utilisateur_id']]);

        $pdo->prepare(
            'INSERT INTO bannissements (utilisateur_id, admin_id, action, raison) VALUES (:uid, :aid, :action, :raison)'
        )->execute([
            'uid' => $request['utilisateur_id'],
            'aid' => $adminId,
            'action' => 'unban',
            'raison' => 'Demande de débannissement acceptée',
        ]);

        $html = '
        <div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;padding:2rem;">
            <h2 style="color:#2D6A4F;">Votre compte a été rétabli</h2>
            <p>Bonjour <strong>' . htmlspecialchars($request['prenom']) . '</strong>,</p>
            <p>Votre demande de débannissement a été <strong>acceptée</strong>. Votre compte est de nouveau actif.</p>
            <p>Vous pouvez vous reconnecter dès maintenant.</p>
            <p style="text-align:center;margin:2rem 0;">
                <a href="' . ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/src/Auth/login.php" style="background:#2D6A4F;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;">Se connecter</a>
            </p>
        </div>';

        sendMail($request['email'], 'Votre compte a été rétabli — Trombinoscope', $html);

        $_SESSION['flash'] = htmlspecialchars($request['prenom'] . ' ' . $request['nom']) . ' a été débanni.';
    } else {
        $html = '
        <div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;padding:2rem;">
            <h2 style="color:#C94B2C;">Demande de débannissement refusée</h2>
            <p>Bonjour <strong>' . htmlspecialchars($request['prenom']) . '</strong>,</p>
            <p>Votre demande de débannissement a été <strong>refusée</strong> par un administrateur.</p>
            <p>Votre compte reste suspendu. Si vous avez des questions, contactez l\'administration.</p>
        </div>';

        sendMail($request['email'], 'Demande de débannissement refusée — Trombinoscope', $html);

        $_SESSION['flash'] = 'La demande de ' . htmlspecialchars($request['prenom'] . ' ' . $request['nom']) . ' a été refusée.';
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash_error'] = 'Une erreur est survenue.';
}

header('Location: dashboard.php');
exit;
