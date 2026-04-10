<?php
require_once __DIR__ . '/../../includes/admin.php';
require_once __DIR__ . '/../../config/config.php';
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

$reportId = (int) ($_POST['report_id'] ?? 0);
$decision = $_POST['decision'] ?? '';
$action   = $_POST['content_action'] ?? '';

if ($reportId <= 0 || !in_array($decision, ['validee', 'rejetee'], true)) {
    $_SESSION['flash_error'] = 'Requête invalide.';
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM signalements WHERE id = :id AND statut = :statut LIMIT 1');
$stmt->execute(['id' => $reportId, 'statut' => 'en_attente']);
$report = $stmt->fetch();

if (!$report) {
    $_SESSION['flash_error'] = 'Signalement introuvable ou déjà traité.';
    header('Location: dashboard.php');
    exit;
}

$pdo->beginTransaction();
try {
    $update = $pdo->prepare(
        'UPDATE signalements SET statut = :statut, admin_id = :admin_id, treated_at = NOW() WHERE id = :id'
    );
    $update->execute([
        'statut'   => $decision,
        'admin_id' => $_SESSION['user_id'],
        'id'       => $reportId,
    ]);

    if ($decision === 'validee' && $action === 'supprimer') {
        if ($report['type'] === 'publication') {
            $pdo->prepare('DELETE FROM publications WHERE id = :id')->execute(['id' => $report['cible_id']]);
        } else {
            $pdo->prepare('DELETE FROM commentaires WHERE id = :id')->execute(['id' => $report['cible_id']]);
        }

        $pdo->prepare(
            'UPDATE signalements SET statut = :statut, admin_id = :admin_id, treated_at = NOW()
             WHERE type = :type AND cible_id = :cible_id AND statut = :pending'
        )->execute([
            'statut'   => 'validee',
            'admin_id' => $_SESSION['user_id'],
            'type'     => $report['type'],
            'cible_id' => $report['cible_id'],
            'pending'  => 'en_attente',
        ]);
    }

    $pdo->commit();

    if ($decision === 'validee') {
        $_SESSION['flash'] = $action === 'supprimer'
            ? 'Signalement validé et contenu supprimé.'
            : 'Signalement validé.';
    } else {
        $_SESSION['flash'] = 'Signalement rejeté.';
    }
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash_error'] = 'Une erreur est survenue.';
}

header('Location: dashboard.php');
exit;
