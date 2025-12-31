<?php
require_once 'includes/header.php';
redirectIfLoggedIn();

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'الرجاء إدخال البريد الإلكتروني وكلمة المرور';
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            header('Location: index.php');
            exit;
        } else {
            $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
        }
    }
}
?>

<div class="auth-container">
    <div class="card">
        <div class="card-header">
            <h2>تسجيل الدخول</h2>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                تم إنشاء حسابك بنجاح! يمكنك الآن تسجيل الدخول.
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">تسجيل الدخول</button>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
