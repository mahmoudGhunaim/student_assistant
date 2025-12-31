<?php
require_once 'includes/header.php';
requireLogin();

$errors = [];
$success = '';
$task_name = '';
$due_date = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name = trim($_POST['task_name'] ?? '');
    $due_date = $_POST['due_date'] ?? '';

    if (empty($task_name)) {
        $errors[] = 'اسم المهمة مطلوب';
    } elseif (strlen($task_name) < 3) {
        $errors[] = 'اسم المهمة يجب أن يكون 3 أحرف على الأقل';
    }

    if (empty($due_date)) {
        $errors[] = 'تاريخ التسليم مطلوب';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $due_date);
        if (!$date) {
            $errors[] = 'تاريخ التسليم غير صالح';
        }
    }

    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('INSERT INTO tasks (user_id, task_name, due_date) VALUES (?, ?, ?)');

        if ($stmt->execute([$_SESSION['user_id'], $task_name, $due_date])) {
            $success = 'تمت إضافة المهمة بنجاح!';
            $task_name = '';
            $due_date = '';
        } else {
            $errors[] = 'حدث خطأ أثناء إضافة المهمة';
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <h2>إضافة مهمة جديدة</h2>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-right: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
            <br><a href="index.php">العودة إلى قائمة المهام</a>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="task_name">اسم المهمة</label>
            <input type="text" id="task_name" name="task_name" value="<?php echo htmlspecialchars($task_name); ?>" placeholder="مثال: تسليم واجب الرياضيات" required>
        </div>

        <div class="form-group">
            <label for="due_date">تاريخ التسليم</label>
            <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($due_date); ?>" min="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">إضافة المهمة</button>
            <a href="index.php" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
