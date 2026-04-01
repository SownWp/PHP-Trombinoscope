<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/cloudinary.php';

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = ($isLoggedIn && ($_SESSION['user_role'] ?? '') === 'admin');

$promoFilter = $_GET['promo'] ?? null;
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

if ($promoFilter) {
  $countSql = "SELECT COUNT(*) FROM utilisateurs WHERE promo = :promo";
  $countStmt = $pdo->prepare($countSql);
  $countStmt->execute(['promo' => $promoFilter]);
  $totalUsers = (int) $countStmt->fetchColumn();

  $sql = "SELECT id, prenom, nom, specialite, promo, bio, avatar, created_at FROM utilisateurs WHERE promo = :promo LIMIT :limit OFFSET :offset";
  $query = $pdo->prepare($sql);
  $query->bindValue('promo', $promoFilter);
  $query->bindValue('limit', $perPage, PDO::PARAM_INT);
  $query->bindValue('offset', $offset, PDO::PARAM_INT);
  $query->execute();
} else {
  $countSql = "SELECT COUNT(*) FROM utilisateurs";
  $totalUsers = (int) $pdo->query($countSql)->fetchColumn();

  $sql = "SELECT id, prenom, nom, specialite, promo, bio, avatar FROM utilisateurs LIMIT :limit OFFSET :offset";
  $query = $pdo->prepare($sql);
  $query->bindValue('limit', $perPage, PDO::PARAM_INT);
  $query->bindValue('offset', $offset, PDO::PARAM_INT);
  $query->execute();
}

$utilisateurs = $query->fetchAll(PDO::FETCH_ASSOC);
$totalPages = max(1, (int) ceil($totalUsers / $perPage));

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trombinoscope — Accueil</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <script src="../assets/js/script.js" defer></script>
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
        <?php if ($isAdmin): ?>
          <li><a href="../src/Admin/dashboard.php">Admin</a></li>
        <?php endif; ?>
        <li><a href="../src/Profile/profil.php?id=<?= $_SESSION['user_id'] ?>">Mon profil</a></li>
        <li><a href="../src/Auth/logout.php">Déconnexion</a></li>
      <?php else: ?>
        <li><a href="../src/Auth/register.php">Inscription</a></li>
        <li><a href="../src/Auth/login.php" class="btn-nav">Connexion</a></li>
      <?php endif; ?>
    </ul>
  </nav>

  <div class="container">

    <div class="hero">
      <h1>Le trombinoscope<br>de <em>votre promo <u>B1</u></em></h1>
      <p>Retrouvez tous vos camarades, partagez vos publications et échangez des commentaires.</p>
      <a href="../src/Auth/register.php" class="btn btn-primary btn-inline">Rejoindre la promo</a>
    </div>

    <div class="flash flash-success">
      Bienvenue sur le trombinoscope ! Inscrivez-vous pour rejoindre la promo.
    </div>

    <div class="filter-bar">
      <a href="index.php" class="filter-btn <?= !$promoFilter ? 'active' : '' ?>">Tous</a>
      <a href="index.php?promo=BUT1 2024" class="filter-btn <?= $promoFilter == 'BUT1 2024' ? 'active' : '' ?>"> BUT1 2024</a>
      <a href="index.php?promo=BUT2 2023" class="filter-btn <?= $promoFilter == 'BUT2 2023' ? 'active' : '' ?>">BUT2 2023</a>
      <a href="index.php?promo=BUT3 2022" class="filter-btn <?= $promoFilter == 'BUT3 2022' ? 'active' : '' ?>">BUT3 2022</a>
    </div>

    <div class="trombi-grid">
      <?php if (empty($utilisateurs)): ?>
        <p>Aucun membre trouvé pour le moment.</p>
      <?php else: ?>
        <?php foreach ($utilisateurs as $user): ?>
          <div class="trombi-card card">
            <a href="../src/Profile/profil.php?id=<?= $user['id'] ?>">
              <img class="card-img" src="<?= htmlspecialchars(avatarUrl($user['avatar'])) ?>" alt="<?= htmlspecialchars($user['prenom']) ?>">
              <div class="card-body">
                <div class="card-name"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></div>
                <div class="card-role"><?= htmlspecialchars($user['specialite'] ?? 'Étudiant') ?></div>
                <span class="card-promo"><?= htmlspecialchars($user['promo']) ?></span>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php
          $queryParams = $promoFilter ? ['promo' => $promoFilter] : [];
        ?>
        <?php if ($page > 1): ?>
          <a href="index.php?<?= http_build_query(array_merge($queryParams, ['page' => $page - 1])) ?>" class="pagination-link">&laquo; Précédent</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="index.php?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>" class="pagination-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <a href="index.php?<?= http_build_query(array_merge($queryParams, ['page' => $page + 1])) ?>" class="pagination-link">Suivant &raquo;</a>
        <?php endif; ?>
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
