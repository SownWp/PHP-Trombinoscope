<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

$flash = $_SESSION['flash'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);

$stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int) ($_POST['user_id'] ?? 0);

    if ($userId !== (int) $_SESSION['user_id']) {
        $_SESSION['flash_error'] = 'Action non autorisée.';
        header('Location: index.php');
        exit;
    }

    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $specialite = trim($_POST['specialite'] ?? '');
    $promo = trim($_POST['promo'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($prenom === '' || $nom === '' || $email === '') {
        $_SESSION['flash_error'] = 'Veuillez remplir tous les champs obligatoires.';
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
        $avatar = uniqid() . '.' . $extension;
        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], __DIR__ . '/uploads/' . $avatar)) {
            $_SESSION['flash_error'] = 'Erreur lors de l\'upload de la photo.';
            header('Location: edit-profil.php');
            exit;
        }
    }

    $fields = [
        'prenom' => $prenom,
        'nom' => $nom,
        'email' => $email,
        'specialite' => $specialite !== '' ? $specialite : null,
        'promo' => $promo,
        'bio' => $bio !== '' ? $bio : null,
        'avatar' => $avatar,
    ];

    if ($password !== '') {
        $fields['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    $setParts = [];
    $params = [];
    foreach ($fields as $key => $value) {
        $setParts[] = "$key = :$key";
        $params[$key] = $value;
    }
    $params['id'] = $_SESSION['user_id'];

    $sql = 'UPDATE utilisateurs SET ' . implode(', ', $setParts) . ' WHERE id = :id';
    $pdo->prepare($sql)->execute($params);

    $_SESSION['user_prenom'] = $prenom;

    $_SESSION['flash'] = 'Profil modifié avec succès.';
    header('Location: profil.php?id=' . $_SESSION['user_id']);
    exit;
}
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
      <li><a href="profil.php?id=<?= $_SESSION['user_id'] ?>">Mon profil</a></li>
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
      <div class="form-subtitle">Mettez à jour vos informations personnelles.</div>

      <form action="edit-profil.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

        <div class="avatar-upload">
          <img src="uploads/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar actuel" id="preview-avatar">
          <div>
            <label for="avatar">Photo de profil</label>
            <input type="file" id="avatar" name="avatar" accept="image/*">
            <p class="form-hint">JPG, PNG, WebP ou AVIF, 2 Mo maximum.</p>
          </div>
        </div>

        <hr class="divider">

        <div class="form-group">
          <label for="prenom">Prénom</label>
          <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
        </div>

        <div class="form-group">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
        </div>

        <div class="form-group">
          <label for="email">Adresse email</label>
          <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="form-group">
          <label for="password">Nouveau mot de passe</label>
          <input type="password" id="password" name="password" placeholder="Laisser vide pour ne pas changer">
          <p class="form-hint">Au moins 8 caractères. Laisser vide pour conserver le mot de passe actuel.</p>
        </div>

        <div class="form-group">
          <label for="promo">Promotion</label>
          <select id="promo" name="promo" required>
            <option value="">Choisissez votre promotion</option>
            <option value="BUT1 2024" <?= $user['promo'] === 'BUT1 2024' ? 'selected' : '' ?>>BUT1 2024</option>
            <option value="BUT2 2023" <?= $user['promo'] === 'BUT2 2023' ? 'selected' : '' ?>>BUT2 2023</option>
            <option value="BUT3 2022" <?= $user['promo'] === 'BUT3 2022' ? 'selected' : '' ?>>BUT3 2022</option>
          </select>
        </div>

        <div class="form-group">
          <label for="specialite">Spécialité</label>
          <input type="text" id="specialite" name="specialite" value="<?= htmlspecialchars($user['specialite'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="bio">Courte bio</label>
          <textarea id="bio" name="bio"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
      </form>

      <div class="form-footer">
        <a href="profil.php?id=<?= $_SESSION['user_id'] ?>">Annuler</a>
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
