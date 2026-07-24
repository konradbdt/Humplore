<?php
require_once __DIR__ . '/app/bootstrap.php';

$pdo = humplore_db();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $redirect = isset($_POST['redirect']) ? trim((string) $_POST['redirect']) : '';

    // Eingaben prüfen
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Bitte fülle alle Felder aus.";
        header('Location: login.php');
        exit;
    }

    try {
        // Benutzer anhand der E-Mail suchen
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Erfolgreicher Login -> Session-Daten setzen
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // is_creator aus der Datenbank abrufen und in der Session speichern
            humplore_sync_creator_session($pdo, (int) $user['id']);

            // Weiterleitung nach erfolgreichem Login
            $redirect = humplore_normalize_redirect_target($redirect);
            if ($redirect !== '') {
                header('Location: ' . $redirect);
            } else {
                header('Location: platform.php');
            }
            exit;
        } else {
            $_SESSION['error_message'] = "Ungültige E-Mail oder Passwort.";
            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        echo "Fehler: " . $e->getMessage();
        exit;
    }
} else {
    echo "Ungültige Anfrage.";
    exit;
}
