<?php
// GroupUp! - view and create study groups.
// Connect to the database and work with the studygroups table.
require_once __DIR__ . '/database.php';

// Escape database/user text before displaying it in HTML.
function escape_html($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


                // Create a new date object for "now" with a specific timezone
$now = new DateTimeImmutable('now', new DateTimeZone('America/New_York'));

                // Format to: YYYY-MM-DD HH:MM:SS (e.g., 2026-10-13 16:30:00)
$then = $now->modify('+14 days');


$errors = [];
$success = '';
$groups = [];
$selected_group = null;

// Handle a new group submitted by the form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim((string)($_POST['description'] ?? ''));
    $location = trim((string)($_POST['location'] ?? ''));
    $rawday = $_POST['daytime'];
    $tags = trim((string)($_POST['tags'] ?? ''));

    if (!empty($rawday)) {
        // 2. Convert it into a PHP DateTime object safely
        $daytime = new DateTime($rawday);

        // 3. Format it for a standard MySQL DATETIME column (YYYY-MM-DD HH:mm:ss)
        $mysql_dayt = $daytime->format('Y-m-d H:i:s');

        echo "Raw HTML value received: " . htmlspecialchars($rawday) . "<br>";
        echo "Ready for MySQL Database: " . $mysql_dayt;
    } else {
        $errors[] = "No date was selected.";
    }

    if ($description === '' || $location === '' || $tags === '') {
        $errors[] = 'Please fill out all fields.';
    }
    if (mb_strlen($description) > 1000 || mb_strlen($location) > 255 || mb_strlen($tags) > 1000) {
        $errors[] = 'One or more fields are too long.';
    }

    if (!$errors) {
        try {
            // Prepared statements keep submitted text separate from SQL.
            $stmt = mysqli_prepare($conn,
                'INSERT INTO studygroups (DESCRIPT, LOCATION, DAYTME, TAGS) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'ssss', $description, $location, $mysql_dayt, $tags);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Redirect to avoid creating duplicates when the page is refreshed.
            header('Location: index.php?created=1');
            exit;
        } catch (mysqli_sql_exception $e) {
            error_log('GroupUp insert failed: ' . $e->getMessage());
            $errors[] = 'Could not save the study group. Check the table structure and database connection.';
        }
    }
}

if (isset($_GET['created']) && $_GET['created'] === '1') {
    $success = 'Study group created successfully!';
}

try {
    // Get all study groups from the database.
    $result = mysqli_query($conn,
        'SELECT GID, DESCRIPT, LOCATION, DAYTME, TAGS FROM studygroups ORDER BY GID DESC');
    $groups = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);

    // Get one study group by its ID if an ID was provided.
    $group_id = filter_input(INPUT_GET, 'gid', FILTER_VALIDATE_INT);
    if ($group_id !== null && $group_id !== false && $group_id > 0) {
        $stmt = mysqli_prepare($conn,
            'SELECT GID, DESCRIPT, LOCATION, DAYTME, TAGS FROM studygroups WHERE GID = ?');
        mysqli_stmt_bind_param($stmt, 'i', $group_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $selected_group = mysqli_fetch_assoc($result) ?: null;
        mysqli_stmt_close($stmt);
    }
} catch (mysqli_sql_exception $e) {
    error_log('GroupUp select failed: ' . $e->getMessage());
    $errors[] = 'Could not load study groups. Check that the studygroups table exists.';
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GroupUp! - Study Groups</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 850px; margin: 35px auto; padding: 0 18px; line-height: 1.5; }
        label { display: block; margin-top: 12px; font-weight: bold; }
        input, textarea { box-sizing: border-box; width: 100%; padding: 9px; margin-top: 4px; }
        button { margin-top: 15px; padding: 10px 16px; cursor: pointer; }
        .group { border: 1px solid #ccc; border-radius: 8px; padding: 14px; margin: 12px 0; }
        .error { color: #a40000; } .success { color: #176b35; }
    </style>
</head>
<body>
    <h1>GroupUp!</h1>
    <p>Find a study group or create a new one.</p>

    <?php foreach ($errors as $error): ?>
        <p class="error"><?= escape_html($error) ?></p>
    <?php endforeach; ?>
    <?php if ($success): ?>
        <p class="success"><?= escape_html($success) ?></p>
    <?php endif; ?>

    <h2>Create a study group</h2>
    <form method="post" action="index.php">
        <label for="description">Description</label>
        <textarea id="description" name="description" required maxlength="1000"><?= escape_html($_POST['description'] ?? '') ?></textarea>
        <label for="location">Location</label>
        <input id="location" name="location" required maxlength="255" value="<?= escape_html($_POST['location'] ?? '') ?>">
        <label for="daytime">Day and time</label>
        <!-- <input id="daytime" name="daytime" required maxlength="255" placeholder="Wednesday 4 PM" value="<?= escape_html($_POST['daytime'] ?? '') ?>"> -->
         <input
                type="datetime-local"
                id="daytime"
                name="daytime"
                value="<?= $now->format('Y-m-d H:i'); ?>"
                min="<?= $now->format('Y-m-d H:i'); ?>"
                max="<?= $then->format('Y-m-d H:i');?>" />
        <label for="tags">Tags</label>
        <input id="tags" name="tags" required maxlength="1000" placeholder="Math, Calculus" value="<?= escape_html($_POST['tags'] ?? '') ?>">
        <button type="submit">Create Group</button>
    </form>

    <h2>Available study groups (<?= count($groups) ?>)</h2>
    <?php if (!$groups): ?>
        <p>No groups to display yet.</p>
    <?php endif; ?>
    <?php foreach ($groups as $group): ?>
        <div class="group">
            <h3>Group #<?= escape_html($group['GID']) ?></h3>
            <p><strong>Description:</strong> <?= escape_html($group['DESCRIPT']) ?></p>
            <p><strong>Location:</strong> <?= escape_html($group['LOCATION']) ?></p>
            <p><strong>Day/Time:</strong> <?= escape_html($group['DAYTME']) ?></p>
            <p><strong>Tags:</strong> <?= escape_html($group['TAGS']) ?></p>
            <a href="?gid=<?= urlencode((string)$group['GID']) ?>">View group by ID</a>
        </div>
    <?php endforeach; ?>

    <?php if ($selected_group): ?>
        <h2>Selected group #<?= escape_html($selected_group['GID']) ?></h2>
        <p><?= escape_html($selected_group['DESCRIPT']) ?> — <?= escape_html($selected_group['LOCATION']) ?></p>
        <p><?= escape_html($selected_group['DAYTME']) ?> | <?= escape_html($selected_group['TAGS']) ?></p>
    <?php endif; ?>
</body>
</html>