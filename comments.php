<?php
/**
 * Small helper to safely display text in HTML
 */
function h($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, "UTF-8");
}

/**
 * Database connection settings
 */
$DB_HOST = "127.0.0.1";
$DB_PORT = "3307";
$DB_NAME = "twin_cities";
$DB_USER = "root";
$DB_PASS = "";

/**
 * Connect to MySQL using PDO
 */
try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("DB Connection failed: " . h($e->getMessage()));
}

/**
 * Create comment tables if they do not already exist
 */
$pdo->exec("
    CREATE TABLE IF NOT EXISTS comments_swindon (
        comment_id   INT AUTO_INCREMENT PRIMARY KEY,
        username     VARCHAR(80)  NOT NULL,
        email        VARCHAR(150) NULL,
        comment_text TEXT         NOT NULL
    )
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS comments_salzgitter (
        comment_id   INT AUTO_INCREMENT PRIMARY KEY,
        username     VARCHAR(80)  NOT NULL,
        email        VARCHAR(150) NULL,
        comment_text TEXT         NOT NULL
    )
");

/**
 * Handle comment form submission
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_personal_comment') {

    // Get submitted form values
    $city         = trim($_POST['city'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $comment_text = trim($_POST['comment_text'] ?? '');

    // Choose the correct table based on city
    $table = '';
    if ($city === 'Swindon') {
        $table = 'comments_swindon';
    } elseif ($city === 'Salzgitter') {
        $table = 'comments_salzgitter';
    }

    // Only insert if required fields are filled
    if ($table !== '' && $username !== '' && $comment_text !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO $table (username, email, comment_text)
            VALUES (:username, :email, :comment_text)
        ");

        $stmt->execute([
            ':username'     => $username,
            ':email'        => ($email === '' ? null : $email),
            ':comment_text' => $comment_text
        ]);
    }

    // Refresh page after posting to avoid duplicate resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/**
 * Load saved comments for each city
 */
$swindon_comments = $pdo->query("
    SELECT comment_id, username, email, comment_text
    FROM comments_swindon
    ORDER BY comment_id DESC
    LIMIT 50
")->fetchAll();

$salzgitter_comments = $pdo->query("
    SELECT comment_id, username, email, comment_text
    FROM comments_salzgitter
    ORDER BY comment_id DESC
    LIMIT 50
")->fetchAll();
?>

<div class="section" id="personal-comments">

    <a href="index3.php"><button type="button">Back to Maps</button></a>

    <h2>Twin Cities (Adielgreat Olobayo: Comments section created)</h2>
    <p class="muted">This comment section is my personal contribution.</p>

    <div class="card">
        <h3 style="margin-top:0;">Leave a Comment</h3>

        <!-- Comment form -->
        <form method="POST">
            <input type="hidden" name="action" value="add_personal_comment">

            <label><strong>Select City</strong></label>
            <select name="city" required>
                <option value="">-- Choose a city --</option>
                <option value="Swindon">Swindon</option>
                <option value="Salzgitter">Salzgitter</option>
            </select>

            <label style="margin-top:10px; display:block;"><strong>Username</strong></label>
            <input type="text" name="username" required maxlength="80">

            <label style="margin-top:10px; display:block;"><strong>Email (optional)</strong></label>
            <input type="email" name="email" maxlength="150">

            <label style="margin-top:10px; display:block;"><strong>Comment text</strong></label>
            <textarea name="comment_text" required maxlength="2000"></textarea>

            <button type="submit" style="margin-top:10px;">Post Comment</button>
        </form>
    </div>

    <!-- Swindon comments list -->
    <div class="card">
        <h3 style="margin-top:0;">Previous Comments - Swindon</h3>

        <?php if (!$swindon_comments): ?>
            <p class="muted">No Swindon comments recorded yet.</p>
        <?php else: ?>
            <?php foreach ($swindon_comments as $c): ?>
                <div class="card" style="margin:10px 0; background:#fcfcff;">
                    <p class="muted" style="margin:0;">
                        <strong>comment_id:</strong> <?php echo (int)$c['comment_id']; ?>
                    </p>

                    <p style="margin:6px 0 0 0;">
                        <strong>username:</strong> <?php echo h($c['username']); ?>
                    </p>

                    <p style="margin:6px 0 0 0;">
                        <strong>email:</strong> <?php echo h($c['email'] ?? ''); ?>
                    </p>

                    <p style="margin:8px 0 0 0;">
                        <strong>comment text:</strong><br>
                        <?php echo nl2br(h($c['comment_text'])); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Salzgitter comments list -->
    <div class="card">
        <h3 style="margin-top:0;">Previous Comments - Salzgitter</h3>

        <?php if (!$salzgitter_comments): ?>
            <p class="muted">No Salzgitter comments recorded yet.</p>
        <?php else: ?>
            <?php foreach ($salzgitter_comments as $c): ?>
                <div class="card" style="margin:10px 0; background:#fcfcff;">
                    <p class="muted" style="margin:0;">
                        <strong>comment_id:</strong> <?php echo (int)$c['comment_id']; ?>
                    </p>

                    <p style="margin:6px 0 0 0;">
                        <strong>username:</strong> <?php echo h($c['username']); ?>
                    </p>

                    <p style="margin:6px 0 0 0;">
                        <strong>email:</strong> <?php echo h($c['email'] ?? ''); ?>
                    </p>

                    <p style="margin:8px 0 0 0;">
                        <strong>comment text:</strong><br>
                        <?php echo nl2br(h($c['comment_text'])); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
