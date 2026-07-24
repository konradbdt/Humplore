<?php
require_once __DIR__ . '/app/bootstrap.php';

$errors = $_SESSION['register_error'] ?? [];
$old    = $_SESSION['old_input'] ?? [];

unset($_SESSION['register_error'], $_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Humplore - Registrieren</title>
  <link rel="stylesheet" href="css/styles.css">
  <!-- Google Font: Poppins -->
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
    }
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: rgb(116, 132, 96);
      font-family: 'Poppins', sans-serif;
    }
    .register-window {
      width: 450px;
      padding: 30px;
      border-radius: 12px;
      background-color: #4b573e;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
      text-align: center;
      color: white;
    }
    .register-window h1 {
      margin-bottom: 25px;
      font-size: 2em;
    }
    .register-window label {
      display: block;
      text-align: left;
      margin-bottom: 5px;
      font-weight: 600;
      color: white;
    }
    .register-window input[type="text"],
    .register-window input[type="email"],
    .register-window input[type="password"] {
      display: block;
      width: 90%;
      max-width: 400px;
      margin: 0 auto 20px;
      padding: 12px;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      background-color: #fff;
      color: #333;
      outline: none;
      box-sizing: border-box;
    }

    /* Fehler-Styles */
    .input-error {
      border: 2px solid #ff4d4d !important;
    }
    .error-text {
      color: #ffb3b3;
      font-size: 0.9rem;
      margin: -15px auto 15px;
      width: 90%;
      max-width: 400px;
      text-align: left;
    }
    .error-general {
      background: rgba(255, 77, 77, 0.15);
      border: 1px solid rgba(255, 77, 77, 0.5);
      color: #ffd1d1;
      padding: 10px 12px;
      border-radius: 8px;
      width: 90%;
      max-width: 400px;
      margin: 0 auto 18px;
      text-align: left;
    }

    .register-window button {
      display: block;
      width: 90%;
      max-width: 400px;
      padding: 12px;
      background-color: rgb(67, 77, 56);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      font-size: 1rem;
      transition: background-color 0.3s;
      margin: 0 auto;
    }
    .register-window button:hover {
      background-color: #4B7322;
    }
    .register-window a {
      display: block;
      margin-top: 15px;
      color: white;
      text-decoration: none;
      font-size: 1em;
    }
    .register-window a:hover {
      text-decoration: underline;
    }
    .creator-fields {
      display: none;
      margin-bottom: 15px;
      text-align: left;
    }
  </style>

  <script>
    function toggleCreatorFields() {
      const checkbox = document.getElementById('creator-toggle');
      const fields = document.getElementById('creator-fields');
      fields.style.display = checkbox.checked ? 'block' : 'none';
    }
  </script>
</head>
<body>
  <div class="register-window">
    <h1>Registrieren</h1>

    <?php if (!empty($errors['general'])): ?>
      <div class="error-general"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form action="process_register.php" method="POST">

      <label for="username">Benutzername:</label>
      <input
        type="text"
        id="username"
        name="username"
        placeholder="Dein Benutzername"
        value="<?= htmlspecialchars($old['username'] ?? '') ?>"
        class="<?= isset($errors['username']) ? 'input-error' : '' ?>"
        required
      >
      <?php if (isset($errors['username'])): ?>
        <div class="error-text"><?= htmlspecialchars($errors['username']) ?></div>
      <?php endif; ?>

      <label for="email">E-Mail:</label>
      <input
        type="email"
        id="email"
        name="email"
        placeholder="Deine E-Mail"
        value="<?= htmlspecialchars($old['email'] ?? '') ?>"
        class="<?= isset($errors['email']) ? 'input-error' : '' ?>"
        required
      >
      <?php if (isset($errors['email'])): ?>
        <div class="error-text"><?= htmlspecialchars($errors['email']) ?></div>
      <?php endif; ?>

      <label for="password">Passwort:</label>
      <input
        type="password"
        id="password"
        name="password"
        placeholder="Dein Passwort"
        required
      >

      <!-- Toggle Switch für Creator -->
      <label style="margin-top: 5px;">
        <input
          type="checkbox"
          id="creator-toggle"
          name="is_creator"
          value="1"
          onchange="toggleCreatorFields()"
          <?= !empty($old['is_creator']) ? 'checked' : '' ?>
        >
        Ich bin ein Creator
      </label>

      <!-- Zusätzliche Felder für Creator -->
      <div id="creator-fields" class="creator-fields">
        <label for="hauptthema" style="margin-top: 15px;">Hauptthema:</label>
        <input
          type="text"
          id="hauptthema"
          name="hauptthema"
          placeholder="z.B. Demenz, Anwalt, Hacker"
          value="<?= htmlspecialchars($old['hauptthema'] ?? '') ?>"
        >
      </div>

      <button type="submit">Registrieren</button>
    </form>

    <a href="login.php">Schon ein Konto? Jetzt anmelden</a>
  </div>

  <script>
    // Beim Laden: Creator-Felder korrekt ein-/ausblenden
    toggleCreatorFields();
  </script>
</body>
</html>
