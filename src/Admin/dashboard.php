<?php
require_once __DIR__ . '/../../includes/admin.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/csrf.php';

$flash = $_SESSION['flash'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);

$search = trim($_GET['q'] ?? '');
$filter = $_GET['filter'] ?? 'all';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = 'WHERE role != :currentRole';
$params = ['currentRole' => 'admin'];

if ($filter === 'banned') {
    $where .= ' AND is_banned = 1';
} elseif ($filter === 'active') {
    $where .= ' AND is_banned = 0';
}

if ($search !== '') {
    $where .= ' AND (prenom LIKE :search OR nom LIKE :search OR email LIKE :search)';
    $params['search'] = "%$search%";
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs $where");
$countStmt->execute($params);
$totalUsers = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalUsers / $perPage));

$sql = "SELECT id, prenom, nom, email, promo, is_banned, created_at FROM utilisateurs $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statsTotal = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role != 'admin'")->fetchColumn();
$statsBanned = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE is_banned = 1")->fetchColumn();
$statsActive = $statsTotal - $statsBanned;

$stmtRequests = $pdo->prepare(
    "SELECT d.id, d.message, d.created_at, d.statut, u.id AS user_id, u.prenom, u.nom, u.email
     FROM demandes_deban d
     JOIN utilisateurs u ON d.utilisateur_id = u.id
     WHERE d.message != '' 
     ORDER BY FIELD(d.statut, 'en_attente', 'refusee', 'acceptee'), d.created_at DESC
     LIMIT 20"
);
$stmtRequests->execute();
$debanRequests = $stmtRequests->fetchAll(PDO::FETCH_ASSOC);
$pendingCount = 0;
foreach ($debanRequests as $r) {
    if ($r['statut'] === 'en_attente') $pendingCount++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administration — Trombinoscope</title>
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
      <li><a href="dashboard.php" class="active">Admin</a></li>
      <li><a href="../Profile/profil.php?id=<?= $_SESSION['user_id'] ?>">Mon profil</a></li>
      <li><a href="../Auth/logout.php">Déconnexion</a></li>
    </ul>
  </nav>

  <div class="container">

    <div class="admin-header">
      <h1 class="admin-title">Administration</h1>
      <p class="admin-subtitle">Gérez les utilisateurs de la plateforme.</p>
    </div>

    <?php if ($flash): ?>
      <div class="flash flash-success"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
      <div class="flash flash-error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div class="admin-stats">
      <div class="stat-card">
        <div class="stat-number"><?= $statsTotal ?></div>
        <div class="stat-label">Utilisateurs</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?= $statsActive ?></div>
        <div class="stat-label">Actifs</div>
      </div>
      <div class="stat-card stat-card-danger">
        <div class="stat-number"><?= $statsBanned ?></div>
        <div class="stat-label">Bannis</div>
      </div>
      <?php if ($pendingCount > 0): ?>
        <div class="stat-card stat-card-warning">
          <div class="stat-number"><?= $pendingCount ?></div>
          <div class="stat-label">Demandes</div>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($debanRequests)): ?>
      <div class="section-title">Demandes de débannissement</div>
      <div class="requests-list">
        <?php foreach ($debanRequests as $req): ?>
          <div class="request-card <?= $req['statut'] !== 'en_attente' ? 'request-treated' : '' ?>">
            <div class="request-header">
              <div class="request-user">
                <strong><?= htmlspecialchars($req['prenom'] . ' ' . $req['nom']) ?></strong>
                <span class="request-email"><?= htmlspecialchars($req['email']) ?></span>
              </div>
              <div class="request-meta">
                <?php if ($req['statut'] === 'en_attente'): ?>
                  <span class="badge badge-warning">En attente</span>
                <?php elseif ($req['statut'] === 'acceptee'): ?>
                  <span class="badge badge-active">Acceptée</span>
                <?php else: ?>
                  <span class="badge badge-banned">Refusée</span>
                <?php endif; ?>
                <span class="request-date"><?= date('d/m/Y à H:i', strtotime($req['created_at'])) ?></span>
              </div>
            </div>
            <div class="request-message"><?= nl2br(htmlspecialchars($req['message'])) ?></div>
            <?php if ($req['statut'] === 'en_attente'): ?>
              <div class="request-actions">
                <form method="POST" action="handle-request.php" class="inline-form">
                  <?= csrfField() ?>
                  <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                  <input type="hidden" name="decision" value="acceptee">
                  <button type="submit" class="btn btn-sm btn-success-outline">Accepter et débannir</button>
                </form>
                <form method="POST" action="handle-request.php" class="inline-form">
                  <?= csrfField() ?>
                  <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                  <input type="hidden" name="decision" value="refusee">
                  <button type="submit" class="btn btn-sm btn-danger">Refuser</button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="section-title" style="margin-top: 2rem;">Utilisateurs</div>

    <div class="admin-toolbar">
      <form class="admin-search" method="GET" action="dashboard.php">
        <?php if ($filter !== 'all'): ?>
          <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
        <?php endif; ?>
        <input type="text" name="q" placeholder="Rechercher un utilisateur…" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
      </form>
      <div class="filter-bar">
        <?php
          $filterBase = $search !== '' ? '&q=' . urlencode($search) : '';
        ?>
        <a href="dashboard.php?filter=all<?= $filterBase ?>" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">Tous</a>
        <a href="dashboard.php?filter=active<?= $filterBase ?>" class="filter-btn <?= $filter === 'active' ? 'active' : '' ?>">Actifs</a>
        <a href="dashboard.php?filter=banned<?= $filterBase ?>" class="filter-btn <?= $filter === 'banned' ? 'active' : '' ?>">Bannis</a>
      </div>
    </div>

    <?php if (empty($utilisateurs)): ?>
      <div class="empty-state">
        <p>Aucun utilisateur trouvé.</p>
      </div>
    <?php else: ?>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Utilisateur</th>
              <th>Email</th>
              <th>Promo</th>
              <th>Statut</th>
              <th>Inscrit le</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($utilisateurs as $u): ?>
              <tr class="<?= $u['is_banned'] ? 'row-banned' : '' ?>">
                <td class="cell-user">
                  <a href="../Profile/profil.php?id=<?= $u['id'] ?>">
                    <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['promo'] ?? '—') ?></td>
                <td>
                  <?php if ($u['is_banned']): ?>
                    <span class="badge badge-banned">Banni</span>
                  <?php else: ?>
                    <span class="badge badge-active">Actif</span>
                  <?php endif; ?>
                </td>
                <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                <td>
                  <?php if ($u['is_banned']): ?>
                    <form method="POST" action="ban.php" class="inline-form">
                      <?= csrfField() ?>
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <input type="hidden" name="action" value="unban">
                      <button type="submit" class="btn btn-sm btn-success-outline">Débannir</button>
                    </form>
                  <?php else: ?>
                    <button type="button" class="btn btn-sm btn-danger" onclick="openBanModal(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['prenom'] . ' ' . $u['nom']), ENT_QUOTES) ?>')">Bannir</button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php
            $qp = [];
            if ($filter !== 'all') $qp['filter'] = $filter;
            if ($search !== '') $qp['q'] = $search;
          ?>
          <?php if ($page > 1): ?>
            <a href="dashboard.php?<?= http_build_query(array_merge($qp, ['page' => $page - 1])) ?>" class="pagination-link">&laquo;</a>
          <?php endif; ?>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="dashboard.php?<?= http_build_query(array_merge($qp, ['page' => $i])) ?>" class="pagination-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
          <?php endfor; ?>
          <?php if ($page < $totalPages): ?>
            <a href="dashboard.php?<?= http_build_query(array_merge($qp, ['page' => $page + 1])) ?>" class="pagination-link">&raquo;</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>

  <div class="modal-overlay" id="banModal">
    <div class="modal-card">
      <div class="modal-title">Bannir un utilisateur</div>
      <p class="modal-text">Vous allez bannir <strong id="banUserName"></strong>. Cette personne ne pourra plus se connecter.</p>
      <form method="POST" action="ban.php">
        <?= csrfField() ?>
        <input type="hidden" name="user_id" id="banUserId">
        <input type="hidden" name="action" value="ban">
        <div class="form-group">
          <label for="raison">Raison du bannissement</label>
          <textarea name="raison" id="raison" rows="3" placeholder="Indiquez la raison…"></textarea>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary btn-sm" onclick="closeBanModal()">Annuler</button>
          <button type="submit" class="btn btn-danger">Confirmer le ban</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openBanModal(userId, userName) {
      document.getElementById('banUserId').value = userId;
      document.getElementById('banUserName').textContent = userName;
      document.getElementById('banModal').classList.add('open');
    }
    function closeBanModal() {
      document.getElementById('banModal').classList.remove('open');
    }
    document.getElementById('banModal').addEventListener('click', function(e) {
      if (e.target === this) closeBanModal();
    });
  </script>

  <footer>
    <div class="container">
      <p>Trombinoscope &mdash; Projet PHP &copy; <span class="footer-year"></span></p>
    </div>
  </footer>

</body>
</html>
