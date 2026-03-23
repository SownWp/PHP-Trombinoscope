<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $contenu = trim($_POST['contenu'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM publications WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $postId]);
    $post = $stmt->fetch();

    if (!$post || (int) $post['utilisateur_id'] !== (int) $_SESSION['user_id']) {
        $_SESSION['flash_error'] = 'Action non autorisée.';
        header('Location: index.php');
        exit;
    }

    if ($contenu === '') {
        $_SESSION['flash_error'] = 'Le contenu ne peut pas être vide.';
        header('Location: edit-post.php?id=' . $postId);
        exit;
    }

    $update = $pdo->prepare('UPDATE publications SET contenu = :contenu WHERE id = :id');
    $update->execute(['contenu' => $contenu, 'id' => $postId]);

    $_SESSION['flash'] = 'Publication modifiée avec succès.';
    header('Location: profil.php?id=' . $_SESSION['user_id']);
    exit;
}

$postId = (int) ($_GET['id'] ?? 0);
if (!$postId) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM publications WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $postId]);
$post = $stmt->fetch();

if (!$post || (int) $post['utilisateur_id'] !== (int) $_SESSION['user_id']) {
    $_SESSION['flash_error'] = 'Action non autorisée.';
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trombinoscope — Modifier une publication</title>
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

    <div class="form-card">
      <div class="form-title">Modifier la publication</div>
      <div class="form-subtitle">Apportez vos corrections puis enregistrez.</div>

      <form action="edit-post.php" method="POST">
        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">

        <div class="form-group">
          <label for="contenu">Contenu</label>
          <textarea id="contenu" name="contenu" rows="5"><?= htmlspecialchars($post['contenu']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
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
