<?php
require_once 'includes/header.php';
requireLogin();

$pdo = getDBConnection();
$user_id = $_SESSION['user_id'];

$stats = [
    'total' => 0,
    'pending' => 0,
    'completed' => 0,
    'overdue' => 0
];

$stmt = $pdo->prepare('SELECT COUNT(*) as count FROM tasks WHERE user_id = ?');
$stmt->execute([$user_id]);
$stats['total'] = $stmt->fetch()['count'];

$stmt = $pdo->prepare('SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status = "pending"');
$stmt->execute([$user_id]);
$stats['pending'] = $stmt->fetch()['count'];

$stmt = $pdo->prepare('SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status = "completed"');
$stmt->execute([$user_id]);
$stats['completed'] = $stmt->fetch()['count'];

$stmt = $pdo->prepare('SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status = "pending" AND due_date < CURDATE()');
$stmt->execute([$user_id]);
$stats['overdue'] = $stmt->fetch()['count'];

$stmt = $pdo->prepare('
    SELECT * FROM tasks
    WHERE user_id = ? AND status = "pending" AND due_date >= CURDATE() AND due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY due_date ASC
    LIMIT 5
');
$stmt->execute([$user_id]);
$upcoming_tasks = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC, created_at DESC');
$stmt->execute([$user_id]);
$all_tasks = $stmt->fetchAll();

function getDaysRemaining($due_date) {
    $today = new DateTime('today');
    $due = new DateTime($due_date);
    $diff = $today->diff($due);

    if ($due < $today) {
        return -$diff->days;
    }
    return $diff->days;
}

function formatArabicDate($date) {
    $months = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
    ];

    $d = new DateTime($date);
    $day = $d->format('j');
    $month = $months[(int)$d->format('n')];
    $year = $d->format('Y');

    return "$day $month $year";
}
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?php echo $stats['total']; ?></div>
        <div class="stat-label">إجمالي المهام</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #ffc107;"><?php echo $stats['pending']; ?></div>
        <div class="stat-label">قيد الانتظار</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #28a745;"><?php echo $stats['completed']; ?></div>
        <div class="stat-label">مكتملة</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #dc3545;"><?php echo $stats['overdue']; ?></div>
        <div class="stat-label">متأخرة</div>
    </div>
</div>

<?php if (!empty($upcoming_tasks)): ?>
<div class="upcoming-section">
    <h3>المهام القادمة (خلال 7 أيام)</h3>
    <?php foreach ($upcoming_tasks as $task):
        $days = getDaysRemaining($task['due_date']);
    ?>
    <div class="upcoming-task">
        <div class="task-name"><?php echo htmlspecialchars($task['task_name']); ?></div>
        <div class="task-date"><?php echo formatArabicDate($task['due_date']); ?></div>
        <span class="days-remaining">
            <?php
            if ($days === 0) {
                echo 'اليوم!';
            } elseif ($days === 1) {
                echo 'غداً';
            } else {
                echo "بعد $days أيام";
            }
            ?>
        </span>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2>جميع المهام</h2>
        <a href="add_task.php" class="btn btn-primary btn-sm">+ إضافة مهمة</a>
    </div>

    <?php if (empty($all_tasks)): ?>
        <div class="empty-state">
            <h3>لا توجد مهام بعد</h3>
            <p>ابدأ بإضافة مهامك الدراسية لتنظيم وقتك بشكل أفضل</p>
            <a href="add_task.php" class="btn btn-primary" style="margin-top: 15px;">إضافة أول مهمة</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>اسم المهمة</th>
                        <th>تاريخ التسليم</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_tasks as $task):
                        $days = getDaysRemaining($task['due_date']);
                        $is_overdue = $task['status'] === 'pending' && $days < 0;
                        $is_today = $days === 0 && $task['status'] === 'pending';
                    ?>
                    <tr style="<?php echo $is_overdue ? 'background-color: #fff3f3;' : ($is_today ? 'background-color: #fff9e6;' : ''); ?>">
                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                        <td>
                            <?php echo formatArabicDate($task['due_date']); ?>
                            <?php if ($is_today): ?>
                                <br><small style="color: #856404;">اليوم!</small>
                            <?php elseif ($is_overdue): ?>
                                <br><small style="color: #dc3545;">متأخرة بـ <?php echo abs($days); ?> يوم</small>
                            <?php elseif ($task['status'] === 'pending' && $days <= 3): ?>
                                <br><small style="color: #17a2b8;">بعد <?php echo $days; ?> أيام</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($task['status'] === 'completed'): ?>
                                <span class="badge badge-completed">مكتملة</span>
                            <?php elseif ($is_overdue): ?>
                                <span class="badge badge-overdue">متأخرة</span>
                            <?php elseif ($is_today): ?>
                                <span class="badge badge-upcoming">اليوم</span>
                            <?php else: ?>
                                <span class="badge badge-pending">قيد الانتظار</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-warning btn-sm">تعديل</a>
                                <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn btn-danger btn-sm">حذف</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
