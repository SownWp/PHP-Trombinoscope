<?php
require_once __DIR__ . '/auth.php';
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
      <li><a href="profil.php">Mon profil</a></li>
      <li><a href="logout.php">Déconnexion</a></li>
    </ul>
  </nav>

  <div class="container-sm">

    <div class="form-card">
      <div class="form-title">Modifier la publication</div>
      <div class="form-subtitle">Apportez vos corrections puis enregistrez.</div>

      <form action="" method="POST">

        <input type="hidden" name="post_id" value="1">

        <div class="form-group">
          <label for="contenu">Contenu</label>
          <textarea id="contenu" name="contenu" rows="5">Je viens de finir mon projet de BUT1 sur les APIs REST. Si quelqu'un veut un retour croisé, n'hésitez pas !</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>

      </form>

      <div class="form-footer">
        <a href="profil.php">Annuler</a>
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
