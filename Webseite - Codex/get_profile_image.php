<?php
require_once __DIR__ . '/app/bootstrap.php';
humplore_deny_nonproduction_route();

$db = new SQLite3('database.db');

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Bild aus der Datenbank abrufen
$stmt = $db->prepare("SELECT profile_image FROM CreatorDetails WHERE user_id = 3");
$stmt->bindValue(":user_id", $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$data = $result->fetchArray(SQLITE3_ASSOC);

// Wenn ein Bild existiert, ausgeben
if ($data && !empty($data['profile_image'])) {
    header("Content-Type: image/jpeg");
    echo $data['profile_image'];
} else {
    header("Content-Type: image/png");
    readfile("default-profile.png"); // Standardbild anzeigen, falls kein Bild vorhanden ist
}
?>
