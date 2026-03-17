<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trombinoscope — Connexion</title>
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
      <li><a href="register.html" class="btn-nav">Inscription</a></li>
    </ul>
  </nav>

  <div class="container-sm">

    <div class="flash flash-error">
      Email ou mot de passe incorrect.
    </div>

    <div class="form-card">
      <div class="form-title">Se connecter</div>
      <div class="form-subtitle">Bon retour parmi nous.</div>

      <form action="" method="POST">

        <div class="form-group">
          <label for="email">Adresse email</label>
          <input type="email" id="email" name="email" placeholder="alice@exemple.fr" required>
        </div>

        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
        </div>

        <div class="form-check">
          <input type="checkbox" id="remember" name="remember" value="1">
          <label for="remember">Se souvenir de moi</label>
        </div>

        <button type="submit" class="btn btn-primary">Se connecter</button>

      </form>

      <div class="form-footer">
        Pas encore de compte ? <a href="register.html">S'inscrire</a>
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
