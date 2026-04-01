<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/csrf.php';

$token = trim($_GET['token'] ?? '');
$flashError = null;
$flash = null;
$demande = null;
$user = null;

if ($token === '') {
    $flashError = 'Lien invalide.';
} else {
    $stmt = $pdo->prepare(
        'SELECT d.*, u.prenom, u.nom, u.email, u.is_banned
         FROM demandes_deban d
         JOIN utilisateurs u ON d.utilisateur_id = u.id
         WHERE d.token = :token LIMIT 1'
    );
    $stmt->execute(['token' => $token]);
    $demande = $stmt->fetch();

    if (!$demande) {
        $flashError = 'Ce lien est invalide ou a expiré.';
    } elseif (!$demande['is_banned']) {
        $flash = 'Votre compte n\'est plus suspendu. Vous pouvez vous connecter.';
    } elseif ($demande['statut'] !== 'en_attente') {
        if ($demande['statut'] === 'acceptee') {
            $flash = 'Votre demande a déjà été acceptée. Vous pouvez vous connecter.';
        } else {
            $flashError = 'Votre demande a déjà été traitée et refusée.';
        }
    } else {
        $user = $demande;
    }
}

$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    if (!verifyCsrfToken()) {
        $flashError = 'Token de sécurité invalide.';
    } else {
        $message = trim($_POST['message'] ?? '');
        if ($message === '') {
            $flashError = 'Veuillez expliquer pourquoi vous souhaitez être débanni.';
        } else {
            $update = $pdo->prepare('UPDATE demandes_deban SET message = :message WHERE id = :id AND statut = :statut');
            $update->execute([
                'message' => $message,
                'id' => $demande['id'],
                'statut' => 'en_attente',
            ]);
            $submitted = true;
            $flash = 'Votre demande a bien été envoyée. Un administrateur l\'examinera prochainement.';
            $user = null;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trombinoscope — Demande de débannissement</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <script src="../../assets/js/script.js" defer></script>
</head>
<body>

  <nav>
    <a href="../../public/index.php" class="nav-logo">trombi<span>.</span></a>
    <button class="nav-toggle" aria-label="Ouvrir le menu">
      <span></span>
      <span></span>
      <span></span>
    </button>
    <ul class="nav-links">
      <li><a href="../../public/index.php">Accueil</a></li>
      <li><a href="login.php" class="btn-nav">Connexion</a></li>
    </ul>
  </nav>

  <div class="container-sm">

    <?php if ($flashError): ?>
      <div class="flash flash-error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <?php if ($flash): ?>
      <div class="flash flash-success"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <?php if ($user && !$submitted): ?>
      <div class="form-card">
        <div class="form-title">Demande de débannissement</div>
        <div class="form-subtitle">
          Bonjour <?= htmlspecialchars($user['prenom']) ?>, expliquez pourquoi votre compte devrait être rétabli.
        </div>

        <form method="POST" action="request-unban.php?token=<?= htmlspecialchars(urlencode($token)) ?>">
          <?= csrfField() ?>

          <div class="form-group">
            <label for="message">Votre message</label>
            <textarea id="message" name="message" rows="5" placeholder="Expliquez la situation..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary">Envoyer ma demande</button>
        </form>

        <div class="form-footer">
          <a href="login.php">Retour à la connexion</a>
        </div>
      </div>
    <?php elseif (!$flashError && !$flash): ?>
      <div class="empty-state">
        <p>Aucune demande à afficher.</p>
      </div>
    <?php endif; ?>

  </div>

  <footer>
    <div class="container">
      <p>Trombinoscope &mdash; Projet PHP &copy; <span class="footer-year"></span></p>
    </div>
  </footer>

</body>
</html>
