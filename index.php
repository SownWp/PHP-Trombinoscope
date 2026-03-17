<?php
require_once 'config.php';

try {
    // On demande à la base de données de lister toutes ses tables
    $query = $pdo->query("SHOW TABLES");
    $tables = $query->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "La connexion est OK, mais aucune table n'a été trouvée dans 'defaultdb'.";
    } else {
        echo "✅ Connexion réussie ! Voici les tables trouvées sur Aiven :<br>";
        foreach ($tables as $table) {
            echo "- $table <br>";
        }
    }
} catch (PDOException $e) {
    die("❌ Erreur de connexion : " . $e->getMessage());
}

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
    <a href="index.html" class="nav-logo">trombi<span>.</span></a>
    <button class="nav-toggle" aria-label="Ouvrir le menu">
      <span></span>
      <span></span>
      <span></span>
    </button>
    <ul class="nav-links">
      <li><a href="index.html">Accueil</a></li>
      <li><a href="register.html">Inscription</a></li>
      <li><a href="login.html" class="btn-nav">Connexion</a></li>
    </ul>
  </nav>

  <div class="container">

    <div class="hero">
      <h1>Le trombinoscope<br>de <em>votre promo <u>B1</u></em></h1>
      <p>Retrouvez tous vos camarades, partagez vos publications et échangez des commentaires.</p>
      <a href="register.html" class="btn btn-primary btn-inline">Rejoindre la promo</a>
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
        <a href="profil.html">
          <img class="card-img" src="https://api.dicebear.com/7.x/personas/svg?seed=Alice&backgroundColor=b6e3f4" alt="Alice Martin">
          <div class="card-body">
            <div class="card-name">Alice Martin</div>
            <div class="card-role">Développeuse Web</div>
            <span class="card-promo">BUT1 2024</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.html">
          <img class="card-img" src="https://api.dicebear.com/7.x/personas/svg?seed=Lucas&backgroundColor=ffdfbf" alt="Lucas Bernard">
          <div class="card-body">
            <div class="card-name">Lucas Bernard</div>
            <div class="card-role">Designer UI</div>
            <span class="card-promo">BUT1 2024</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.html">
          <img class="card-img" src="https://api.dicebear.com/7.x/personas/svg?seed=Sofia&backgroundColor=d1f4d1" alt="Sofia Dupont">
          <div class="card-body">
            <div class="card-name">Sofia Dupont</div>
            <div class="card-role">Data Analyst</div>
            <span class="card-promo">BUT2 2023</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.html">
          <img class="card-img" src="https://api.dicebear.com/7.x/personas/svg?seed=Karim&backgroundColor=ffd5dc" alt="Karim Ndiaye">
          <div class="card-body">
            <div class="card-name">Karim Ndiaye</div>
            <div class="card-role">DevOps</div>
            <span class="card-promo">BUT2 2023</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.html">
          <img class="card-img" src="https://api.dicebear.com/7.x/personas/svg?seed=Emma&backgroundColor=e8d5ff" alt="Emma Leroy">
          <div class="card-body">
            <div class="card-name">Emma Leroy</div>
            <div class="card-role">Product Manager</div>
            <span class="card-promo">BUT3 2022</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.html">
          <img class="card-img" src="https://api.dicebear.com/7.x/personas/svg?seed=Noah&backgroundColor=fff3b0" alt="Noah Girard">
          <div class="card-body">
            <div class="card-name">Noah Girard</div>
            <div class="card-role">Sécurité Réseau</div>
            <span class="card-promo">BUT3 2022</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.html">
          <img class="card-img" src="https://api.dicebear.com/7.x/personas/svg?seed=Yasmine&backgroundColor=c0f0f0" alt="Yasmine Benali">
          <div class="card-body">
            <div class="card-name">Yasmine Benali</div>
            <div class="card-role">Développeuse Mobile</div>
            <span class="card-promo">BUT1 2024</span>
          </div>
        </a>
      </div>

      <div class="trombi-card card">
        <a href="profil.html">
          <img class="card-img" src="https://api.dicebear.com/7.x/personas/svg?seed=Tom&backgroundColor=ffd5b0" alt="Tom Faure">
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
