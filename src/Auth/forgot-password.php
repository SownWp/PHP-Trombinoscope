<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../config/mailtrap.php';

$pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$flash = null;
$flashError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $flashError = 'Token de sécurité invalide. Veuillez réessayer.';
    } else {
        $email = trim($_POST['email'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $flashError = 'Veuillez entrer une adresse email valide.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                $pdo->prepare('DELETE FROM password_resets WHERE email = :email')->execute(['email' => $email]);

                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $insert = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)');
                $insert->execute(['email' => $email, 'token' => $token, 'expires_at' => $expiresAt]);

                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $path = dirname($_SERVER['SCRIPT_NAME']);
                $resetUrl = "$scheme://$host$path/reset-password.php?token=$token";

                $html = '
                <div style="font-family:Arial,sans-serif;max-width:500px;margin:0 auto;padding:2rem;">
                    <h2 style="color:#1A1714;">Réinitialisation de mot de passe</h2>
                    <p style="color:#8C8379;">Vous avez demandé la réinitialisation de votre mot de passe sur le Trombinoscope.</p>
                    <p><a href="' . $resetUrl . '" style="display:inline-block;padding:0.7rem 1.5rem;background:#C94B2C;color:white;text-decoration:none;border-radius:8px;font-weight:600;">Réinitialiser mon mot de passe</a></p>
                    <p style="color:#8C8379;font-size:0.85rem;">Ce lien expire dans 1 heure. Si vous n\'avez pas fait cette demande, ignorez cet email.</p>
                </div>';

                sendMail($email, 'Réinitialisation de votre mot de passe — Trombinoscope', $html);
            }

            $flash = 'Si cette adresse existe dans notre base, un email de réinitialisation a été envoyé.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trombinoscope — Mot de passe oublié</title>
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

    <div class="form-card">
      <div class="form-title">Mot de passe oublié</div>
      <div class="form-subtitle">Entrez votre adresse email pour recevoir un lien de réinitialisation.</div>

      <form action="" method="POST">
        <?= csrfField() ?>

        <div class="form-group">
          <label for="email">Adresse email</label>
          <input type="email" id="email" name="email" placeholder="alice@exemple.fr" required>
        </div>

        <button type="submit" class="btn btn-primary">Envoyer le lien</button>
      </form>

      <div class="form-footer">
        <a href="login.php">Retour à la connexion</a>
      </div>
    </div>
  </div>

  <footer>
    <div class="container">
      <p>Trombinoscope &mdash; Projet PHP &copy; <span class="footer-year"></span></p>
    </div>
  </footer>

</body>
</html>
