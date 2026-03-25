<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php';

$flash = $_SESSION['flash'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);

$emailValue = $_COOKIE['remember_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $flashError = 'Token de sécurité invalide. Veuillez réessayer.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if ($email === '' || $password === '') {
            $flashError = 'Veuillez remplir tous les champs.';
            $emailValue = $email;
        } else {
            $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $flashError = 'Email ou mot de passe incorrect.';
                $emailValue = $email;
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_prenom'] = $user['prenom'];

                if ($remember) {
                    setcookie('remember_email', $email, time() + (30 * 24 * 3600), '/');
                } else {
                    setcookie('remember_email', '', time() - 3600, '/');
                }

                header('Location: index.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trombinoscope — Connexion</title>
  <link rel="stylesheet" href="./assets/css/style.css">
  <script src="./assets/js/script.js" defer></script>
</head>
<body>

  <nav>
    <a href="index.php" class="nav-logo">trombi<span>.</span></a>
    <button class="nav-toggle" aria-label="Ouvrir le menu">
      <span></span>
      <span></span>
      <span></span>
    </button>
    <ul class="nav-links">
      <li><a href="index.php">Accueil</a></li>
      <li><a href="register.php" class="btn-nav">Inscription</a></li>
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
      <div class="form-title">Se connecter</div>
      <div class="form-subtitle">Bon retour parmi nous.</div>

      <form action="" method="POST">
        <?= csrfField() ?>

        <div class="form-group">
          <label for="email">Adresse email</label>
          <input type="email" id="email" name="email" placeholder="alice@exemple.fr" value="<?= htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
        </div>

        <div class="form-check">
          <input type="checkbox" id="remember" name="remember" value="1" <?= !empty($_COOKIE['remember_email']) ? 'checked' : '' ?>>
          <label for="remember">Se souvenir de moi</label>
        </div>

        <button type="submit" class="btn btn-primary">Se connecter</button>

      </form>

      <div class="form-footer">
        Pas encore de compte ? <a href="register.php">S'inscrire</a>
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
