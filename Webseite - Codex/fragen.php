<?php
require_once __DIR__ . '/app/bootstrap.php';

$pdo = humplore_db();

// --- PHP Logik Anfang ---
$creator_id = $_GET['creator_id'] ?? null;

// Creator validieren
$stmtCreator = $pdo->prepare("SELECT * FROM Users WHERE id = ? AND is_creator = 1");
$stmtCreator->execute([$creator_id]);
$creator = $stmtCreator->fetch(PDO::FETCH_ASSOC);

if (!$creator) {
    die("<h1>Creator nicht gefunden</h1>");
}

// Aktueller Benutzer
$currentUser = null;
$askSuccess = '';
if (isset($_SESSION['user_id'])) {
    $stmtUser = $pdo->prepare("SELECT * FROM Users WHERE id = ?");
    $stmtUser->execute([$_SESSION['user_id']]);
    $currentUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
}

// Frage stellen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_question'])) {
    if (!$currentUser || $currentUser['is_creator'] == 1) {
        die("Nicht berechtigt");
    }
    $question_text = htmlspecialchars((string) ($_POST['question_text'] ?? ''));
    $anonymousValue = $_POST['is_anonymous'] ?? null;
    $isAnonymous = is_scalar($anonymousValue) && (string) $anonymousValue === '1' ? 1 : 0;
    $stmt = $pdo->prepare("INSERT INTO Questions (creator_id, author_id, question_text, is_anonymous) VALUES (?, ?, ?, ?)");
    $stmt->execute([$creator_id, $_SESSION['user_id'], $question_text, $isAnonymous]);
    $askSuccess = $isAnonymous === 1 ? 'Anonyme Frage wurde gesendet.' : 'Frage wurde gesendet.';
}

// Frage beantworten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer_question'])) {
    if (!$currentUser || $currentUser['id'] != $creator['id']) {
        die("Nicht berechtigt");
    }
    $answer_text = htmlspecialchars($_POST['answer_text']);
    $question_id = $_POST['question_id'];
    $stmt = $pdo->prepare("UPDATE Questions SET answer_text = ?, answered_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$answer_text, $question_id]);
}

// Fragen holen
$stmtAnswered = $pdo->prepare("
    SELECT q.*, u.username AS author_name 
    FROM Questions q
    JOIN Users u ON q.author_id = u.id
    WHERE q.creator_id = ? AND q.answer_text IS NOT NULL
    ORDER BY q.answered_at DESC
");
$stmtAnswered->execute([$creator_id]);
$answeredQuestions = $stmtAnswered->fetchAll(PDO::FETCH_ASSOC);

$unanswered = [];
if ($currentUser && $currentUser['id'] == $creator['id']) {
    $stmtUnanswered = $pdo->prepare("
        SELECT q.*, u.username 
        FROM Questions q
        JOIN Users u ON q.author_id = u.id
        WHERE q.creator_id = ? AND q.answer_text IS NULL
    ");
    $stmtUnanswered->execute([$creator_id]);
    $unanswered = $stmtUnanswered->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fragen an <?= htmlspecialchars($creator['username']) ?></title>
    <style>
        /* CSS aus profile.php */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f9f9f9;
            font-family: 'Poppins', sans-serif;
        }

        header {
            background-color: #fff;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 70;
            height: 40px;
            /* Optional: Etwas größer für bessere Darstellung */
            display: flex;
            /* Aktiviert Flexbox */
            justify-content: center;
            /* Zentriert horizontal */
            align-items: center;
            /* Zentriert vertikal */
        }

        header h1 {
            font-size: 1.2rem;
            color: #580F41;
        }

        .question-wall {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .question-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .button-34 {
            background: rgb(250, 140, 140);
            border: none;
            border-radius: 10em;
            color: white;
            padding: 10px 20px;
            cursor: pointer;
        }

        .anonymous-control {
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 44px;
            font-weight: 600;
        }

        .anonymous-control input {
            width: 18px;
            height: 18px;
        }

        .anonymous-hint {
            margin-bottom: 10px;
            color: #667160;
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .bottom-nav {
            position: fixed;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 40px);
            max-width: 450px;
            background: #580F41;
            border-radius: 25px;
            padding: 10px;
            display: flex;
            justify-content: space-around;
        }

        .bottom-nav a {
            color: white;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <header>
        <h1>humplore</h1>
    </header>

    <div class="question-wall">
        <h1>Fragen an @<?= htmlspecialchars($creator['username']) ?></h1>

        <?php if (!$currentUser || $currentUser['is_creator'] != 1): ?>
            <div class="ask-form">
                <h2>Stelle eine Frage</h2>
                <?php if ($askSuccess !== ''): ?>
                    <p><?= htmlspecialchars($askSuccess, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <form method="post">
                    <textarea name="question_text" placeholder="Deine Frage..." required></textarea>
                    <label class="anonymous-control">
                        <input type="checkbox" name="is_anonymous" value="1">
                        <span>Anonym fragen</span>
                    </label>
                    <p class="anonymous-hint">Dein Name wird weder dem Creator noch anderen Nutzern angezeigt.</p>
                    <button type="submit" name="new_question" class="button-34">Absenden</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($currentUser && $currentUser['id'] == $creator['id'] && count($unanswered) > 0): ?>
            <div class="unanswered-questions">
                <h2>Offene Fragen</h2>
                <?php foreach ($unanswered as $question): ?>
                    <div class="question-card">
                        <p><strong><?= !empty($question['is_anonymous'])
                            ? 'Anonym'
                            : '@' . htmlspecialchars((string) $question['username'], ENT_QUOTES, 'UTF-8') ?> fragt:</strong></p>
                        <p><?= htmlspecialchars($question['question_text']) ?></p>
                        <form method="post">
                            <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                            <textarea name="answer_text" placeholder="Deine Antwort..." required></textarea>
                            <button type="submit" name="answer_question" class="button-34">Antworten</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="answered-questions">
            <h2>Beantwortete Fragen</h2>
            <?php foreach ($answeredQuestions as $question): ?>
              <div class="question">
                <p class="question-text"><?= htmlspecialchars($question['question_text']) ?></>
                  <?php if (!empty($question['answer_text'])): ?>
                  <p class="answer-text"><?= htmlspecialchars($question['answer_text']) ?></p>
                <?php else: ?>
                  <p class="answer-text text-muted">Noch nicht beantwortet</p>
                <?php endif; ?>

              </div>
            <?php endforeach; ?>
        </div>
    </div>

    <nav class="bottom-nav">
        <a href="platform.php">Home</a>
        <a href="search.php">Suche</a>
        <a href="posten.php">+</a>
        <a href="news.php">News</a>
        <a href="profile.php">Profil</a>
    </nav>
</body>

</html>
