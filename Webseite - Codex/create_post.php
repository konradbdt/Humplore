<?php
session_start();

// Zugriff nur für eingeloggte Creator erlauben
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_creator']) || (int)$_SESSION['is_creator'] !== 1) {
    http_response_code(403);
    exit('Kein Zugriff.');
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Humplore - Post erstellen</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background-color: rgb(116, 132, 96);
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
    }
    .top-nav {
      width: 100%;
      background-color: #4b573e;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: white;
      font-size: 1.5rem;
    }
    .top-nav .logo {
      text-decoration: none;
      color: white;
      font-weight: bold;
      font-size: 1.8rem;
    }
    .top-nav .logout-btn {
      background-color: white;
      color: #4b573e;
      border: none;
      padding: 8px 15px;
      border-radius: 5px;
      font-size: 1rem;
      cursor: pointer;
    }
    .top-nav .logout-btn:hover {
      background-color: #f0f0f0;
    }
    .post-container {
      width: 500px;
      padding: 30px;
      background-color: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      text-align: center;
      margin-top: 40px;
    }
    .post-container h1 {
      font-size: 1.8rem;
      margin-bottom: 20px;
      color: #4b573e;
    }
    form {
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    label {
      font-weight: 500;
      font-size: 1rem;
      color: #4b573e;
      margin-bottom: 8px;
    }
    input[type="text"],
    textarea,
    input[type="file"] {
      width: 100%;
      max-width: 450px;
      padding: 12px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
      font-family: 'Poppins', sans-serif;
      text-align: center;
    }
    textarea { resize: none; }
    button {
      width: 100%;
      max-width: 450px;
      padding: 12px;
      background-color: #4b573e;
      color: #ffffff;
      font-size: 1rem;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover { background-color: #3a4a32; }
    .cancel-link {
      margin-top: 15px;
      font-size: 0.9rem;
      color: #999;
      text-decoration: none;
    }
    .cancel-link:hover { text-decoration: underline; }
  </style>
</head>

<body>
  <div class="post-container">
    <h1>Neuen Post erstellen</h1>

    <form action="process_post.php" method="POST" enctype="multipart/form-data">
      <label for="title">Titel:</label>
      <!-- Wichtig: KEIN pattern/Filter, damit ? ! usw. erlaubt sind -->
      <input
        type="text"
        id="title"
        name="title"
        placeholder="Titel des Beitrags"
        required
        maxlength="255">

      <label for="content">Inhalt:</label>
      <textarea id="content" name="content" placeholder="Schreibe etwas..." rows="6"></textarea>

      <label for="media">Medien (optional):</label>
      <input type="file" id="media" name="media" accept="image/*,video/*">

      <button type="submit">Posten</button>
    </form>

    <a href="profile.php" class="cancel-link">Abbrechen und zum Profil zurückkehren</a>
  </div>
</body>
</html>
