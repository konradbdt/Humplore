<?php
require_once __DIR__ . '/app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Daten aus dem Formular auslesen + trim
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $plainPass  = $_POST['password'] ?? '';
    $isCreator  = isset($_POST['is_creator']) ? 1 : 0;
    $hauptthema = $_POST['hauptthema'] ?? null;

    // Pflichtfelder prüfen
    if ($username === '' || $email === '' || $plainPass === '') {
        $_SESSION['register_error'] = ['general' => 'Bitte fülle alle Pflichtfelder aus.'];
        $_SESSION['old_input'] = [
            'username'   => $username,
            'email'      => $email,
            'is_creator' => $isCreator,
            'hauptthema' => $hauptthema
        ];
        header("Location: register.php");
        exit;
    }

    // ✅ E-Mail allgemein validieren (ALLE Domains erlaubt)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = [
            'email' => 'Bitte gib eine gültige E-Mail-Adresse ein.'
        ];
        $_SESSION['old_input'] = [
            'username'   => $username,
            'email'      => $email,
            'is_creator' => $isCreator,
            'hauptthema' => $hauptthema
        ];
        header("Location: register.php");
        exit;
    }

    // Passwort hashen
    $password = password_hash($plainPass, PASSWORD_BCRYPT);

    // DB-Verbindung
    $pdo = humplore_db();

    try {
        // Prüfen ob Username oder Email schon existiert
        $check = $pdo->prepare(
            "SELECT username, email FROM Users WHERE username = ? OR email = ? LIMIT 1"
        );
        $check->execute([$username, $email]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $_SESSION['register_error'] = [];

            if (strcasecmp($existing['username'], $username) === 0) {
                $_SESSION['register_error']['username'] =
                    'Dieser Benutzername ist bereits vergeben.';
            }

            if (strcasecmp($existing['email'], $email) === 0) {
                $_SESSION['register_error']['email'] =
                    'Diese E-Mail ist bereits registriert.';
            }

            $_SESSION['old_input'] = [
                'username'   => $username,
                'email'      => $email,
                'is_creator' => $isCreator,
                'hauptthema' => $hauptthema
            ];
            header("Location: register.php");
            exit;
        }

        // Transaktion starten
        $pdo->beginTransaction();

        // User speichern
        $stmt = $pdo->prepare(
            "INSERT INTO Users (username, email, password_hash, is_creator)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$username, $email, $password, $isCreator]);

        // CreatorDetails speichern (falls Creator)
        if ($isCreator) {
            $userId = (int)$pdo->lastInsertId();
            $stmt = $pdo->prepare(
                "INSERT INTO CreatorDetails (user_id, main_topic, bio)
                 VALUES (?, ?, ?)"
            );
            $stmt->execute([$userId, $hauptthema, null]);
        }

        // Commit
        $pdo->commit();

        // Erfolg → Login
        header("Location: login.php?success=1");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['register_error'] = [
            'general' => 'Fehler bei der Registrierung. Bitte versuche es erneut.'
        ];
        $_SESSION['old_input'] = [
            'username'   => $username,
            'email'      => $email,
            'is_creator' => $isCreator,
            'hauptthema' => $hauptthema
        ];
        header("Location: register.php");
        exit;
    }
} else {
    http_response_code(405);
    header('Allow: POST');
    echo "Ungültige Anfrage.";
}
?>
