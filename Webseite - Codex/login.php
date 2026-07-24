<?php
require_once __DIR__ . '/app/bootstrap.php';

// Fehlernachricht initialisieren
$error_message = '';
$redirect_target = isset($_GET['redirect']) ? (string) $_GET['redirect'] : '';

// Überprüfen, ob eine Fehlernachricht übergeben wurde
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Fehlernachricht zurücksetzen
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Humplore - Anmelden</title>
    <style>
        /* Allgemeines Styling */
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #6a743a;
            /* Hellgrauer Hintergrund */
            font-family: Arial, sans-serif;
        }

        /* Container für das Fenster */
        .login-window {
            width: 450px;
            /* Fenster verbreitert */
            padding: 30px;
            /* Mehr Platz im Inneren */
            border-radius: 12px;
            background-color: #879251ff;
            /* Dein Grün aus index.html */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            /* Größerer Schatten für mehr Präsenz */
            text-align: center;
        }

        /* Überschrift im Fenster */
        .login-window h1 {
            color: white;
            font-size: 2em;
            /* Größerer Titel */
            margin-bottom: 25px;
        }

        /* Formular Styling */
        .login-window form {
            display: flex;
            flex-direction: column;
        }

        .login-window label {
            color: white;
            font-weight: bold;
            margin-bottom: 8px;
            text-align: left;
        }

        .login-window input {
            padding: 12px;
            /* Größere Eingabefelder */
            margin-bottom: 20px;
            border: none;
            border-radius: 8px;
        }

        .login-window button {
            padding: 12px;
            /* Größerer Button */
            background-color: #6a743a;
            /* Dunkleres Grün für den Button */
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .login-window button:hover {
            background-color: #4B7322;
            /* Leicht abweichende Button-Farbe bei Hover */
        }

        .login-window a {
            color: white;
            text-decoration: none;
            font-size: 1em;
            /* Größerer Text für den Link */
            margin-top: 15px;
            display: inline-block;
        }

        .login-window a:hover {
            text-decoration: underline;
        }

        /* Fehlernachricht Styling */
        .error-message {
            color: #fff;
            /* Weißer Text für bessere Lesbarkeit */
            background-color: rgba(255, 0, 0, 0.8);
            /* Roter Hintergrund mit Transparenz */
            padding: 10px;
            /* Innenabstand */
            border-radius: 5px;
            /* Abgerundete Ecken */
            margin-top: -15px;
            /* Negative Marge, um es näher an das Eingabefeld zu bringen */
            margin-bottom: 20px;
            /* Abstand zum nächsten Element */
            text-align: left;
            /* Links ausrichten */
        }
    </style>
</head>

<body>
    <!-- Login-Fenster -->
    <div class="login-window">
        <h1>Anmelden</h1>
        <form action="process_login.php" method="POST">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_target, ENT_QUOTES, 'UTF-8'); ?>">
            <label for="email">E-Mail:</label>
            <input type="email" id="email" name="email" placeholder="Deine E-Mail" required>
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <label for="password">Passwort:</label>
            <input type="password" id="password" name="password" placeholder="Dein Passwort" required>
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <button type="submit">Anmelden</button>
        </form>
        <a href="register.php">Neu hier? Jetzt registrieren</a>
    </div>
</body>

</html>
