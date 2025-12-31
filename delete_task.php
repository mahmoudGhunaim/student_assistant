<?php
require_once 'includes/header.php';
requireLogin();

// Get task ID
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($task_id <= 0) {
    header('Location: index.php');
    exit;
}

$pdo = getDBConnection();

$stmt = $pdo->prepare('SELECT id FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$task_id, $_SESSION['user_id']]);
$task = $stmt->fetch();

if (!$task) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
        $stmt->execute([$task_id, $_SESSION['user_id']]);
    }
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$task_id, $_SESSION['user_id']]);
$task = $stmt->fetch();
?>

<div class="card" style="max-width: 500px; margin: 0 auto;">
    <div class="card-header">
        <h2>تأكيد الحذف</h2>
    </div>

    <div class="alert alert-warning">
        هل أنت متأكد من حذف المهمة التالية؟
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <strong>اسم المهمة:</strong> <?php echo htmlspecialchars($task['task_name']); ?><br>
        <strong>تاريخ التسليم:</strong> <?php echo htmlspecialchars($task['due_date']); ?>
    </div>

    <form method="POST" action="">
        <div style="display: flex; gap: 10px;">
            <button type="submit" name="confirm" value="yes" class="btn btn-danger">نعم، احذف المهمة</button>
            <a href="index.php" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
