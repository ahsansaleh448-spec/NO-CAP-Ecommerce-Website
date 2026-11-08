<?php
require_once __DIR__ . '/includes/init.php';

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '');
if (is_logged_in()) {
    redirect($next);
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $st = $pdo->prepare('SELECT * FROM users WHERE email=?');
    $st->execute([$email]);
    $u = $st->fetch();

    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$u['id'],
            'name' => $u['name'],
            'email' => $u['email'],
            'role' => $u['role']
        ];
        set_flash('success', 'Welcome back, ' . e($u['name']) . '!');
        redirect($u['role'] === 'admin' && $next === 'index.php' ? 'admin/index.php' : $next);
    }
    $error = 'Invalid email address or password.';
}

$pageTitle = 'Sign In';
require __DIR__ . '/includes/header.php';
?>

<div class="container auth">
    <h1>Sign In to NO CAP</h1>
    <p class="muted">Access your orders, saved cart items, and account details.</p>
    
    <?php if ($error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="form auth-box">
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">
        
        <label>Email Address
            <input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email" placeholder="name@example.com">
        </label>
        
        <label>Password
            <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
        </label>

        <button class="btn lg block" type="submit">Sign In</button>
    </form>

    <div class="auth-footer">
        <p>Don't have an account yet? <a href="<?= url('register.php') ?>">Create an account</a></p>
        <div class="demo-credentials">
            <small><strong>Demo Admin:</strong> admin@nocap.com / admin123</small>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
