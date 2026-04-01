<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/cloudinary.php';
require_once __DIR__ . '/../../includes/csrf.php';

$flash = $_SESSION['flash'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);

$profilId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$profilId) {
    header('Location: ../../public/index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, prenom, nom, email, specialite, promo, bio, avatar, created_at FROM utilisateurs WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $profilId]);
$profil = $stmt->fetch();

if (!$profil) {
    header('Location: ../../public/index.php');
    exit;
}

$isLoggedIn = isset($_SESSION['user_id']);
$isOwner = $isLoggedIn && (int) $_SESSION['user_id'] === (int) $profil['id'];
$isAdmin = ($isLoggedIn && ($_SESSION['user_role'] ?? '') === 'admin');

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
$likesCount = [];
$userLikes = [];
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
    $allComments = $stmtCom->fetchAll();

    $comIds = array_column($allComments, 'id');
    if (!empty($comIds)) {
        $comPlaceholders = implode(',', array_fill(0, count($comIds), '?'));
        $stmtLikes = $pdo->prepare(
            "SELECT commentaire_id, COUNT(*) AS total
             FROM likes_commentaires
             WHERE commentaire_id IN ($comPlaceholders)
             GROUP BY commentaire_id"
        );
        $stmtLikes->execute($comIds);
        foreach ($stmtLikes->fetchAll() as $row) {
            $likesCount[$row['commentaire_id']] = (int) $row['total'];
        }

        if ($isLoggedIn) {
            $stmtUserLikes = $pdo->prepare(
                "SELECT commentaire_id
                 FROM likes_commentaires
                 WHERE commentaire_id IN ($comPlaceholders) AND utilisateur_id = ?"
            );
            $stmtUserLikes->execute(array_merge($comIds, [$_SESSION['user_id']]));
            foreach ($stmtUserLikes->fetchAll() as $row) {
                $userLikes[$row['commentaire_id']] = true;
            }
        }
    }

    foreach ($allComments as $com) {
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
      <?php if ($isLoggedIn): ?>
        <?php if ($isAdmin): ?>
          <li><a href="../Admin/dashboard.php">Admin</a></li>
        <?php endif; ?>
        <li><a href="profil.php?id=<?= $_SESSION['user_id'] ?>">Mon profil</a></li>
        <li><a href="../Auth/logout.php">Déconnexion</a></li>
      <?php else: ?>
        <li><a href="../Auth/register.php">Inscription</a></li>
        <li><a href="../Auth/login.php" class="btn-nav">Connexion</a></li>
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
        src="<?= htmlspecialchars(avatarUrl($profil['avatar'])) ?>"
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
          <a href="../Auth/logout.php" class="btn btn-danger btn-sm">Déconnexion</a>
        </div>
      <?php endif; ?>
    </div>

    <div class="section-title">Publications</div>

    <?php if ($isOwner): ?>
      <div class="form-card form-card-post">
        <form action="../Posts/post.php" method="POST">
          <?= csrfField() ?>
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
              <a href="../Posts/edit-post.php?id=<?= $pub['id'] ?>" class="btn btn-secondary btn-sm">Modifier</a>
              <form action="../Posts/delete-post.php" method="POST" style="display:inline;">
                <input type="hidden" name="post_id" value="<?= $pub['id'] ?>">
                <?= csrfField() ?>
                <button type="submit" class="btn btn-danger btn-sm" data-confirm="Supprimer cette publication ?">Supprimer</button>
              </form>
            </div>
          <?php endif; ?>

          <div class="comment-list">
            <?php foreach (($commentaires[$pub['id']] ?? []) as $com): ?>
              <div class="comment">
                <div class="comment-author">
                  <a href="profil.php?id=<?= $com['utilisateur_id'] ?>"><?= htmlspecialchars($com['prenom'] . ' ' . $com['nom']) ?></a>
                  <span class="comment-date"><?= date('d/m/Y à H:i', strtotime($com['created_at'])) ?></span>
                </div>
                <div class="comment-text"><?= htmlspecialchars($com['contenu']) ?></div>
                <div class="comment-footer">
                  <?php if ($isLoggedIn): ?>
                    <button
                      class="like-btn <?= isset($userLikes[$com['id']]) ? 'liked' : '' ?>"
                      data-comment-id="<?= $com['id'] ?>"
                      type="button"
                    >
                      <svg class="like-icon" viewBox="0 0 24 24" width="16" height="16"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                      <span class="like-count"><?= $likesCount[$com['id']] ?? 0 ?></span>
                    </button>
                  <?php else: ?>
                    <span class="like-btn like-btn-static">
                      <svg class="like-icon" viewBox="0 0 24 24" width="16" height="16"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                      <span class="like-count"><?= $likesCount[$com['id']] ?? 0 ?></span>
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <?php if ($isLoggedIn): ?>
            <form action="../Posts/comment.php" method="POST" class="comment-form">
              <?= csrfField() ?>
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
