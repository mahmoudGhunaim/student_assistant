<?php
require_once 'includes/header.php';
redirectIfLoggedIn();

$errors = [];
$success = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name)) {
        $errors[] = 'الاسم مطلوب';
    } elseif (strlen($name) < 2) {
        $errors[] = 'الاسم يجب أن يكون حرفين على الأقل';
    }

    if (empty($email)) {
        $errors[] = 'البريد الإلكتروني مطلوب';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'البريد الإلكتروني غير صالح';
    }

    if (empty($password)) {
        $errors[] = 'كلمة المرور مطلوبة';
    } elseif (strlen($password) < 6) {
        $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'كلمتا المرور غير متطابقتين';
    }

    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'البريد الإلكتروني مستخدم بالفعل';
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');

        if ($stmt->execute([$name, $email, $hashed_password])) {
            $success = 'تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.';
            $name = '';
            $email = '';
        } else {
            $errors[] = 'حدث خطأ أثناء إنشاء الحساب';
        }
    }
}
?>

<div class="auth-container">
    <div class="card">
        <div class="card-header">
            <h2>إنشاء حساب جديد</h2>
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
                <br><a href="login.php">تسجيل الدخول الآن</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">الاسم</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>

            <div class="form-group">
                <label for="confirm_password">تأكيد كلمة المرور</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">إنشاء الحساب</button>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
