<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/csrf.php';

$flash = null;
$flashError = null;
$tokenValid = false;
$token = $_GET['token'] ?? '';

if ($token !== '') {
    $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() LIMIT 1');
    $stmt->execute(['token' => $token]);
    $reset = $stmt->fetch();
    $tokenValid = (bool)$reset;
}

if (!$tokenValid && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $flashError = 'Ce lien de réinitialisation est invalide ou a expiré.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $flashError = 'Token de sécurité invalide. Veuillez réessayer.';
    } else {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() LIMIT 1');
        $stmt->execute(['token' => $token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            $flashError = 'Ce lien de réinitialisation est invalide ou a expiré.';
        } elseif (strlen($password) < 8) {
            $flashError = 'Le mot de passe doit contenir au moins 8 caractères.';
            $tokenValid = true;
        } elseif ($password !== $passwordConfirm) {
            $flashError = 'Les mots de passe ne correspondent pas.';
            $tokenValid = true;
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare('UPDATE utilisateurs SET password = :password WHERE email = :email');
            $update->execute(['password' => $hashed, 'email' => $reset['email']]);

            $pdo->prepare('DELETE FROM password_resets WHERE email = :email')->execute(['email' => $reset['email']]);

            $_SESSION['flash'] = 'Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.';
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trombinoscope — Nouveau mot de passe</title>
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
      <div class="flash flash-error">
        <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <?php if ($flash): ?>
      <div class="flash flash-success">
        <?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <?php if ($tokenValid): ?>
      <div class="form-card">
        <div class="form-title">Nouveau mot de passe</div>
        <div class="form-subtitle">Choisissez un nouveau mot de passe pour votre compte.</div>

        <form action="" method="POST">
          <?= csrfField() ?>
          <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

          <div class="form-group">
            <label for="password">Nouveau mot de passe</label>
            <input type="password" id="password" name="password" placeholder="8 caractères minimum" required>
            <p class="form-hint">Au moins 8 caractères.</p>
          </div>

          <div class="form-group">
            <label for="password_confirm">Confirmer le mot de passe</label>
            <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirmez votre mot de passe" required>
          </div>

          <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
        </form>

        <div class="form-footer">
          <a href="login.php">Retour à la connexion</a>
        </div>
      </div>
    <?php else: ?>
      <div class="form-card" style="text-align:center;">
        <div class="form-title">Lien expiré</div>
        <div class="form-subtitle">Ce lien n'est plus valide. Veuillez refaire une demande.</div>
        <a href="forgot-password.php" class="btn btn-primary btn-inline" style="margin-top:1rem;">Mot de passe oublié</a>
        <div class="form-footer">
          <a href="login.php">Retour à la connexion</a>
        </div>
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
