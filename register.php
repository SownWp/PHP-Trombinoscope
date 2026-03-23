<?php
session_start();
require_once __DIR__ . '/config.php';

$flash = $_SESSION['flash'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $promo = trim($_POST['promo'] ?? '');
    $specialite = trim($_POST['specialite'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    if ($prenom === '' || $nom === '' || $email === '' || $password === '' || $promo === '') {
        $_SESSION['flash_error'] = 'Veuillez remplir tous les champs obligatoires.';
        header('Location: register.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = 'Adresse email invalide.';
        header('Location: register.php');
        exit;
    }

    $avatar = 'default.svg';
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];
    $maxSize = 2097152;

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $mime = mime_content_type($_FILES['avatar']['tmp_name']);
        if (!in_array($mime, $allowedMimes)) {
            $_SESSION['flash_error'] = 'Format de fichier non autorisé. Formats acceptés : JPEG, PNG, WebP, AVIF.';
            header('Location: register.php');
            exit;
        }

        if ($_FILES['avatar']['size'] > $maxSize) {
            $_SESSION['flash_error'] = 'Le fichier est trop volumineux (2 Mo maximum).';
            header('Location: register.php');
            exit;
        }

        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatar = uniqid() . '.' . $extension;

        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], __DIR__ . '/uploads/' . $avatar)) {
            $_SESSION['flash_error'] = 'Erreur lors de l\'upload de la photo.';
            header('Location: register.php');
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            $_SESSION['flash_error'] = 'L\'adresse email est déjà utilisée. Veuillez en choisir une autre.';
            header('Location: register.php');
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $insert = $pdo->prepare(
            'INSERT INTO utilisateurs (prenom, nom, email, password, specialite, promo, bio, avatar)
             VALUES (:prenom, :nom, :email, :password, :specialite, :promo, :bio, :avatar)'
        );

        $insert->execute([
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $email,
            'password' => $hashedPassword,
            'specialite' => $specialite !== '' ? $specialite : null,
            'promo' => $promo,
            'bio' => $bio !== '' ? $bio : null,
            'avatar' => $avatar,
        ]);

        $_SESSION['flash'] = 'Inscription réussie. Vous pouvez maintenant vous connecter.';
        header('Location: login.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = 'Une erreur est survenue lors de l\'inscription.';
        header('Location: register.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trombinoscope — Inscription</title>
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
      <div class="form-title">Créer un compte</div>
      <div class="form-subtitle">Rejoignez le trombinoscope de votre promotion.</div>

      <form action="" method="POST" enctype="multipart/form-data">

        <div class="avatar-upload">
          <img src="https://api.dicebear.com/7.x/personas/svg?seed=default&backgroundColor=e2ddd6" alt="Avatar par défaut" id="preview-avatar">
          <div>
            <label for="avatar">Photo de profil</label>
            <input type="file" id="avatar" name="avatar" accept="image/*">
            <p class="form-hint">JPG ou PNG, 2 Mo maximum.</p>
          </div>
        </div>

        <hr class="divider">

        <div class="form-group">
          <label for="prenom">Prénom</label>
          <input type="text" id="prenom" name="prenom" placeholder="Alice" required>
        </div>

        <div class="form-group">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" placeholder="Martin" required>
        </div>

        <div class="form-group">
          <label for="email">Adresse email</label>
          <input type="email" id="email" name="email" placeholder="alice@exemple.fr" required>
        </div>

        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" placeholder="8 caractères minimum" required>
          <p class="form-hint">Au moins 8 caractères.</p>
        </div>

        <div class="form-group">
          <label for="promo">Promotion</label>
          <select id="promo" name="promo" required>
            <option value="">Choisissez votre promotion</option>
            <option value="BUT1 2024">BUT1 2024</option>
            <option value="BUT2 2023">BUT2 2023</option>
            <option value="BUT3 2022">BUT3 2022</option>
          </select>
        </div>

        <div class="form-group">
          <label for="specialite">Spécialité</label>
          <input type="text" id="specialite" name="specialite" placeholder="Développeur Web, Designer...">
        </div>

        <div class="form-group">
          <label for="bio">Courte bio</label>
          <textarea id="bio" name="bio" placeholder="Parlez-vous en quelques mots..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Créer mon compte</button>

      </form>

      <div class="form-footer">
        Déjà inscrit ? <a href="login.php">Se connecter</a>
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
