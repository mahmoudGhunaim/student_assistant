<?php
require_once 'includes/header.php';
requireLogin();

$errors = [];
$success = '';

// Get task ID
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($task_id <= 0) {
    header('Location: index.php');
    exit;
}

$pdo = getDBConnection();

// Fetch task
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$task_id, $_SESSION['user_id']]);
$task = $stmt->fetch();

if (!$task) {
    header('Location: index.php');
    exit;
}

$task_name = $task['task_name'];
$due_date = $task['due_date'];
$status = $task['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name = trim($_POST['task_name'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $status = $_POST['status'] ?? 'pending';

    // Validate task name
    if (empty($task_name)) {
        $errors[] = 'اسم المهمة مطلوب';
    } elseif (strlen($task_name) < 3) {
        $errors[] = 'اسم المهمة يجب أن يكون 3 أحرف على الأقل';
    }

    // Validate due date
    if (empty($due_date)) {
        $errors[] = 'تاريخ التسليم مطلوب';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $due_date);
        if (!$date) {
            $errors[] = 'تاريخ التسليم غير صالح';
        }
    }

    // Validate status
    if (!in_array($status, ['pending', 'completed'])) {
        $status = 'pending';
    }

    // Update task
    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE tasks SET task_name = ?, due_date = ?, status = ? WHERE id = ? AND user_id = ?');

        if ($stmt->execute([$task_name, $due_date, $status, $task_id, $_SESSION['user_id']])) {
            $success = 'تم تحديث المهمة بنجاح!';
        } else {
            $errors[] = 'حدث خطأ أثناء تحديث المهمة';
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <h2>تعديل المهمة</h2>
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
            <input type="text" id="task_name" name="task_name" value="<?php echo htmlspecialchars($task_name); ?>" required>
        </div>

        <div class="form-group">
            <label for="due_date">تاريخ التسليم</label>
            <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($due_date); ?>" required>
        </div>

        <div class="form-group">
            <label for="status">الحالة</label>
            <select id="status" name="status">
                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>مكتملة</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
            <a href="index.php" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
