<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

$stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

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

    if ($prenom === '' || $nom === '' || $email === '' || $promo === '') {
        $_SESSION['flash_error'] = 'Veuillez remplir tous les champs obligatoires.';
        header('Location: edit-profil.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = 'Adresse email invalide.';
        header('Location: edit-profil.php');
        exit;
    }

    $checkEmail = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email AND id != :id LIMIT 1');
    $checkEmail->execute(['email' => $email, 'id' => $user['id']]);
    if ($checkEmail->fetch()) {
        $_SESSION['flash_error'] = 'Cette adresse email est déjà utilisée par un autre compte.';
        header('Location: edit-profil.php');
        exit;
    }

    $avatar = $user['avatar'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];
    $maxSize = 2097152;

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $mime = mime_content_type($_FILES['avatar']['tmp_name']);
        if (!in_array($mime, $allowedMimes)) {
            $_SESSION['flash_error'] = 'Format de fichier non autorisé. Formats acceptés : JPEG, PNG, WebP, AVIF.';
            header('Location: edit-profil.php');
            exit;
        }

        if ($_FILES['avatar']['size'] > $maxSize) {
            $_SESSION['flash_error'] = 'Le fichier est trop volumineux (2 Mo maximum).';
            header('Location: edit-profil.php');
            exit;
        }

        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $newAvatar = uniqid() . '.' . $extension;

        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], __DIR__ . '/uploads/' . $newAvatar)) {
            $_SESSION['flash_error'] = 'Erreur lors de l\'upload de la photo.';
            header('Location: edit-profil.php');
            exit;
        }

        if ($avatar && $avatar !== 'default.svg') {
            $oldPath = __DIR__ . '/uploads/' . $avatar;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
        $avatar = $newAvatar;
    }

    try {
        $sql = 'UPDATE utilisateurs SET prenom = :prenom, nom = :nom, email = :email,
                specialite = :specialite, promo = :promo, bio = :bio, avatar = :avatar';
        $params = [
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $email,
            'specialite' => $specialite !== '' ? $specialite : null,
            'promo' => $promo,
            'bio' => $bio !== '' ? $bio : null,
            'avatar' => $avatar,
            'id' => $user['id'],
        ];

        if ($password !== '') {
            $sql .= ', password = :password';
            $params['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';

        $update = $pdo->prepare($sql);
        $update->execute($params);

        $_SESSION['flash'] = 'Profil mis à jour avec succès.';
        header('Location: profil.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = 'Une erreur est survenue lors de la mise à jour.';
        header('Location: edit-profil.php');
        exit;
    }
}

$avatarSrc = './uploads/' . htmlspecialchars($user['avatar'] ?? 'default.svg', ENT_QUOTES, 'UTF-8');
$promos = ['BUT1 2024', 'BUT2 2023', 'BUT3 2022'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trombinoscope — Modifier mon profil</title>
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
      <li><a href="profil.php">Mon profil</a></li>
      <li><a href="logout.php">Déconnexion</a></li>
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
      <div class="form-title">Modifier mon profil</div>
      <div class="form-subtitle">Ces informations sont visibles par tous les membres.</div>

      <form action="" method="POST" enctype="multipart/form-data">

        <div class="avatar-upload">
          <img
            src="<?= $avatarSrc ?>"
            alt="Avatar actuel"
            id="preview-avatar"
          >
          <div>
            <label for="avatar">Changer la photo</label>
            <input type="file" id="avatar" name="avatar" accept="image/*">
            <p class="form-hint">Laissez vide pour conserver la photo actuelle.</p>
          </div>
        </div>

        <hr class="divider">

        <div class="form-group">
          <label for="prenom">Prénom</label>
          <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
          <label for="specialite">Spécialité</label>
          <input type="text" id="specialite" name="specialite" value="<?= htmlspecialchars($user['specialite'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
          <label for="promo">Promotion</label>
          <select id="promo" name="promo">
            <?php foreach ($promos as $p): ?>
              <option value="<?= $p ?>" <?= $user['promo'] === $p ? 'selected' : '' ?>><?= $p ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="bio">Bio</label>
          <textarea id="bio" name="bio"><?= htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <hr class="divider">

        <div class="form-group">
          <label for="email">Adresse email</label>
          <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
          <label for="password">Nouveau mot de passe</label>
          <input type="password" id="password" name="password" placeholder="Laissez vide pour ne pas changer">
          <p class="form-hint">Renseignez seulement si vous souhaitez le modifier.</p>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>

      </form>

      <div class="form-footer">
        <a href="profil.php">Annuler et retourner au profil</a>
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
