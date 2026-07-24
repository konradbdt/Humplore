<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/app/bootstrap.php';
// Zugriff nur für Creator
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_creator']) || $_SESSION['is_creator'] != 1) {
    header("Location: login.php");
    exit;
}

$pdo = humplore_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: create_post.php");
    exit;
}

// Daten aus dem Formular auslesen
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$creator_id = $_SESSION['user_id'];

// Überprüfen, ob Thema und Inhalt gesetzt sind
if (empty($title) || empty($content)) {
    die("Bitte geben Sie einen Titel und Inhalt ein.");
}

// Optionaler Dateiupload
$mediaUrl = null; // Standardwert
$mediaType = 'text'; // Standardmedientyp für reine Inhalte ohne Datei

if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['media']['tmp_name'];
    $fileName = $_FILES['media']['name'];
    $fileType = $_FILES['media']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Erlaubte Dateitypen prüfen
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov'];
    if (in_array($fileExtension, $allowedExtensions)) {
        // Generiere neuen Dateinamen und Zielpfad
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadDir = "uploads/";
        $destPath = $uploadDir . $newFileName;

        // Datei verschieben
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $mediaUrl = $destPath;
            $mediaType = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'video';
        } else {
            die("Fehler beim Hochladen der Datei.");
        }
    } else {
        die("Dateityp wird nicht unterstützt.");
    }
}

try {
    // Beginne Transaktion
    $pdo->beginTransaction();

    // Beitrag in die Posts-Tabelle einfügen
    $stmt = $pdo->prepare("
        INSERT INTO Posts (creator_id, title, content, media_url, media_type, created_at)
        VALUES (?, ?, ?, ?, ?, datetime('now'))
    ");
    $stmt->execute([$creator_id, $title, $content, $mediaUrl, $mediaType]);

    $pdo->commit();
    
    // Erfolgreiche Registrierung des Posts, weiterleiten auf die Profilseite
    header("Location: profile.php?post=success");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Fehler beim Erstellen des Posts: " . $e->getMessage());
}
?>
