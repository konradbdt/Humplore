<?php
require_once __DIR__ . '/app/bootstrap.php';
humplore_deny_nonproduction_route();

$pdo = humplore_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sicherstellen, dass der Nutzer Creator ist
    if (!isset($_SESSION['user_id']) || !humplore_current_user_is_creator($pdo)) {
        die("Zugriff verweigert");
    }

    $bio = $_POST['bio'] ?? '';
    $profileImage = $_FILES['profile_image']['tmp_name'] ?? null;
    $imageData = null;

    // Bildvalidierung
    if ($profileImage && is_uploaded_file($profileImage)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $profileImage);
        finfo_close($finfo);

        if (strpos($mime, 'image/') === 0) {
            $imageData = file_get_contents($profileImage);
        }
    }

    try {
        // Verwenden Sie REPLACE um vorhandene Einträge zu aktualisieren
        $stmt = $pdo->prepare("
            INSERT OR REPLACE INTO CreatorDetails 
            (user_id, bio, profile_image) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $bio,
            $imageData ?: null // NULL falls kein Bild hochgeladen
        ]);

        header("Location: profile.php");
        exit;

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("Ein Datenbankfehler ist aufgetreten");
    }
}
