<?php
session_start();
require_once __DIR__ . '/config.php';

$flash = $_SESSION['flash'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);

$profilId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$profilId) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, prenom, nom, email, specialite, promo, bio, avatar, created_at FROM utilisateurs WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $profilId]);
$profil = $stmt->fetch();

if (!$profil) {
    header('Location: index.php');
    exit;
}

$isLoggedIn = isset($_SESSION['user_id']);
$isOwner = $isLoggedIn && (int) $_SESSION['user_id'] === (int) $profil['id'];

$stmtPubs = $pdo->prepare(
    'SELECT p.id, p.contenu, p.created_at, p.utilisateur_id, u.prenom, u.nom
     FROM publications p
     JOIN utilisateurs u ON p.utilisateur_id = u.id
     WHERE p.utilisateur_id = :uid
     ORDER BY p.created_at DESC'
);
$stmtPubs->execute(['uid' => $profilId]);
$publications = $stmtPubs->fetchAll();

$commentaires = [];
if (!empty($publications)) {
    $pubIds = array_column($publications, 'id');
    $placeholders = implode(',', array_fill(0, count($pubIds), '?'));
    $stmtCom = $pdo->prepare(
        "SELECT c.id, c.publication_id, c.contenu, c.created_at, c.utilisateur_id,
                u.prenom, u.nom
         FROM commentaires c
         JOIN utilisateurs u ON c.utilisateur_id = u.id
         WHERE c.publication_id IN ($placeholders)
         ORDER BY c.created_at ASC"
    );
    $stmtCom->execute($pubIds);
    foreach ($stmtCom->fetchAll() as $com) {
        $commentaires[$com['publication_id']][] = $com;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trombinoscope — <?= htmlspecialchars($profil['prenom'] . ' ' . $profil['nom']) ?></title>
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
      <?php if ($isLoggedIn): ?>
        <li><a href="profil.php?id=<?= $_SESSION['user_id'] ?>">Mon profil</a></li>
        <li><a href="logout.php">Déconnexion</a></li>
      <?php else: ?>
        <li><a href="register.php">Inscription</a></li>
        <li><a href="login.php" class="btn-nav">Connexion</a></li>
      <?php endif; ?>
    </ul>
  </nav>

  <div class="container">

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

    <div class="profile-header">
      <img
        class="profile-avatar"
        src="uploads/<?= htmlspecialchars($profil['avatar']) ?>"
        alt="<?= htmlspecialchars($profil['prenom'] . ' ' . $profil['nom']) ?>"
      >
      <div class="profile-info">
        <h1><?= htmlspecialchars($profil['prenom'] . ' ' . $profil['nom']) ?></h1>
        <div class="role"><?= htmlspecialchars($profil['specialite'] ?? 'Étudiant') ?> — <?= htmlspecialchars($profil['promo']) ?></div>
        <?php if ($profil['bio']): ?>
          <div class="bio"><?= htmlspecialchars($profil['bio']) ?></div>
        <?php endif; ?>
      </div>
      <?php if ($isOwner): ?>
        <div class="profile-actions">
          <a href="edit-profil.php" class="btn btn-secondary btn-sm">Modifier le profil</a>
          <a href="logout.php" class="btn btn-danger btn-sm">Déconnexion</a>
        </div>
      <?php endif; ?>
    </div>

    <div class="section-title">Publications</div>

    <?php if ($isOwner): ?>
      <div class="form-card form-card-post">
        <form action="post.php" method="POST">
          <div class="form-group">
            <textarea name="contenu" placeholder="Partagez quelque chose avec la promo..." rows="3"></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-inline">Publier</button>
        </form>
      </div>
    <?php endif; ?>

    <div class="post-list">

      <?php if (empty($publications)): ?>
        <p>Aucune publication pour le moment.</p>
      <?php endif; ?>

      <?php foreach ($publications as $pub): ?>
        <div class="post-card">
          <div class="post-meta">
            <?= htmlspecialchars($pub['prenom'] . ' ' . $pub['nom']) ?>
            <?php if ($isLoggedIn && (int) $_SESSION['user_id'] === (int) $pub['utilisateur_id']): ?>
              <span class="badge-owner">Vous</span>
            <?php endif; ?>
            — <?= htmlspecialchars($pub['created_at']) ?>
          </div>
          <div class="post-content">
            <?= nl2br(htmlspecialchars($pub['contenu'])) ?>
          </div>

          <?php if ($isLoggedIn && (int) $_SESSION['user_id'] === (int) $pub['utilisateur_id']): ?>
            <div class="post-actions">
              <a href="edit-post.php?id=<?= $pub['id'] ?>" class="btn btn-secondary btn-sm">Modifier</a>
              <a href="delete-post.php?id=<?= $pub['id'] ?>" class="btn btn-danger btn-sm" data-confirm="Supprimer cette publication ?">Supprimer</a>
            </div>
          <?php endif; ?>

          <div class="comment-list">
            <?php foreach (($commentaires[$pub['id']] ?? []) as $com): ?>
              <div class="comment">
                <div class="comment-author">
                  <a href="profil.php?id=<?= $com['utilisateur_id'] ?>"><?= htmlspecialchars($com['prenom'] . ' ' . $com['nom']) ?></a>
                </div>
                <div class="comment-text"><?= htmlspecialchars($com['contenu']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <?php if ($isLoggedIn): ?>
            <form action="comment.php" method="POST" class="comment-form">
              <input type="hidden" name="post_id" value="<?= $pub['id'] ?>">
              <input type="text" name="contenu" placeholder="Ajouter un commentaire...">
              <button type="submit">Envoyer</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

    </div>
  </div>

  <footer>
    <div class="container">
      <p>Trombinoscope &mdash; Projet PHP &copy; <span class="footer-year"></span></p>
    </div>
  </footer>

</body>
</html>
