<?php
require_once 'config.php';



?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trombinoscope — Accueil</title>
  <link rel="stylesheet" href="./assets/css/style.css">
  <script src="./assets/js/script.js" defer></script>
</head>
<body>

  <nav>
    <a href="index.php" class="nav-logo">trombi<span>.</span></a>
    <button class="nav-toggle" aria-label="Ouvrir le menu">
      <span></span>i
      <span></span>
      <span></span>
    </button>
    <ul class="nav-links">
      <li><a href="index.php">Accueil</a></li>
      <li><a href="register.php">Inscription</a></li>
      <li><a href="login.php" class="btn-nav">Connexion</a></li>
    </ul>
  </nav>

  <div class="container">

    <div class="hero">
      <h1>Le trombinoscope<br>de <em>votre promo <u>B1</u></em></h1>
      <p>Retrouvez tous vos camarades, partagez vos publications et échangez des commentaires.</p>
      <a href="register.php" class="btn btn-primary btn-inline">Rejoindre la promo</a>
    </div>

    <div class="flash flash-success">
      Bienvenue sur le trombinoscope ! Inscrivez-vous pour rejoindre la promo.
    </div>

    <div class="filter-bar">
      <a href="#" class="filter-btn active">Tous</a>
      <a href="#" class="filter-btn">BUT1 2024</a>
      <a href="#" class="filter-btn">BUT2 2023</a>
      <a href="#" class="filter-btn">BUT3 2022</a>
    </div>

    <div class="trombi-grid">

      <div class="trombi-card card">
        <a href="profil.php">
          <img class="card-img" src="./img/wp15865200.webp" alt="Alice Martin">
          <div class="card-body">
            <div class="card-name">Alice Martin</div>
            <div class="card-role">Développeuse Web</div>
            <span class="card-promo">BUT1 2024</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.php">
          <img class="card-img" src="./img/illustration-tigre-portant-lunettes-soleil-veste-jaune_95549-8236.webp" alt="Lucas Bernard">
          <div class="card-body">
            <div class="card-name">Lucas Bernard</div>
            <div class="card-role">Designer UI</div>
            <span class="card-promo">BUT1 2024</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.php">
          <img class="card-img" src="./img/Tralalero_Tralalala.webp" alt="Sofia Dupont">
          <div class="card-body">
            <div class="card-name">Sofia Dupont</div>
            <div class="card-role">Data Analyst</div>
            <span class="card-promo">BUT2 2023</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.php">
          <img class="card-img" src="./img/pork-john-image.webp" alt="Karim Ndiaye">
          <div class="card-body">
            <div class="card-name">Karim Ndiaye</div>
            <div class="card-role">DevOps</div>
            <span class="card-promo">BUT2 2023</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.php">
          <img class="card-img" src="./img/81fd16bc-2edf-4add-b221-ffdc1bd93bea-1761943332015-thumbnailM.webp" alt="Emma Leroy">
          <div class="card-body">
            <div class="card-name">Emma Leroy</div>
            <div class="card-role">Product Manager</div>
            <span class="card-promo">BUT3 2022</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.php">
          <img class="card-img" src="./img/washington-charlie-kirk-is-seen-in-the-fiserv-forum-on-the-third-night-of-the-republican.webp" alt="Noah Girard">
          <div class="card-body">
            <div class="card-name">Noah Girard</div>
            <div class="card-role">Sécurité Réseau</div>
            <span class="card-promo">BUT3 2022</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.php">
          <img class="card-img" src="./img/oI7peF3IEIdIA7fcO8fzK8xAj51TSDoWDBMQCN~tplv-tiktokx-origin (1).webp" alt="Yasmine Benali">
          <div class="card-body">
            <div class="card-name">Yasmine Benali</div>
            <div class="card-role">Développeuse Mobile</div>
            <span class="card-promo">BUT1 2024</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.php">
          <img class="card-img" src="./img/Tralalero_Tralalala.webp" alt="Tom Faure">
          <div class="card-body">
            <div class="card-name">Tom Faure</div>
            <div class="card-role">Administrateur Sys.</div>
            <span class="card-promo">BUT2 2023</span>
          </div>
        </a>
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
