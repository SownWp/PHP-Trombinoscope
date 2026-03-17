<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trombinoscope — Inscription</title>
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
      <li><a href="login.html" class="btn-nav">Connexion</a></li>
    </ul>
  </nav>

  <div class="container-sm">

    <div class="flash flash-error">
      L'adresse email est déjà utilisée. Veuillez en choisir une autre.
    </div>

    <div class="form-card">
      <div class="form-title">Créer un compte</div>
      <div class="form-subtitle">Rejoignez le trombinoscope de votre promotion.</div>

      <form action="" method="POST" enctype="multipart/form-data">

        <div class="avatar-upload">
          <img src="https://api.dicebear.com/7.x/personas/svg?seed=default&backgroundColor=e2ddd6" alt="Avatar par défaut" id="preview-avatar">
          <div>
            <label for="avatar">Photo de profil</label>
            <input type="file" id="avatar" name="avatar" accept="image/*">
            <p class="form-hint">JPG ou PNG, 2 Mo maximum.</p>
          </div>
        </div>

        <hr class="divider">

        <div class="form-group">
          <label for="prenom">Prénom</label>
          <input type="text" id="prenom" name="prenom" placeholder="Alice" required>
        </div>

        <div class="form-group">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" placeholder="Martin" required>
        </div>

        <div class="form-group">
          <label for="email">Adresse email</label>
          <input type="email" id="email" name="email" placeholder="alice@exemple.fr" required>
        </div>

        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" placeholder="8 caractères minimum" required>
          <p class="form-hint">Au moins 8 caractères.</p>
        </div>

        <div class="form-group">
          <label for="promo">Promotion</label>
          <select id="promo" name="promo" required>
            <option value="">Choisissez votre promotion</option>
            <option value="BUT1 2024">BUT1 2024</option>
            <option value="BUT2 2023">BUT2 2023</option>
            <option value="BUT3 2022">BUT3 2022</option>
          </select>
        </div>

        <div class="form-group">
          <label for="specialite">Spécialité</label>
          <input type="text" id="specialite" name="specialite" placeholder="Développeur Web, Designer...">
        </div>

        <div class="form-group">
          <label for="bio">Courte bio</label>
          <textarea id="bio" name="bio" placeholder="Parlez-vous en quelques mots..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Créer mon compte</button>

      </form>

      <div class="form-footer">
        Déjà inscrit ? <a href="login.html">Se connecter</a>
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
