<?php
require_once __DIR__ . '/app/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = humplore_db();

// Daten aus dem Formular abrufen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $bio = trim($_POST['bio']);
    $user_id = $_SESSION['user_id'];
    

    // Validierung der Eingaben
    if (!empty($username) && !empty($bio)) {
        try {
            // Benutzername und Bio in der Datenbank aktualisieren
            $stmt = $pdo->prepare("UPDATE Users SET username = :username WHERE id = :id");
            $stmt->execute([':username' => $username, ':id' => $user_id]);

            if ($_SESSION['is_creator'] == 1) {
                $stmt = $pdo->prepare("UPDATE CreatorDetails SET bio = :bio WHERE user_id = :user_id");
                $stmt->execute([':bio' => $bio, ':user_id' => $user_id]);
            }

            // Nach erfolgreicher Aktualisierung zurück zum Profil
            header("Location: profile.php");
            exit;
        } catch (PDOException $e) {
            echo "Fehler bei der Aktualisierung: " . $e->getMessage();
            exit;
        }
    } else {
        echo "Bitte fülle alle Felder aus.";
        exit;
    }
} else {
    echo "Ungültige Anfrage.";
    exit;
}
?>
