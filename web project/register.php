<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$old = ['name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => strtolower(trim($_POST['email'] ?? '')),
        'phone' => trim($_POST['phone'] ?? '')
    ];
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (strlen($old['name']) < 2) {
        $errors[] = 'Please enter your full name (at least 2 characters).';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }
    if (!preg_match('/^[0-9+\-\s]{7,20}$/', $old['phone'])) {
        $errors[] = 'Please provide a valid contact phone number.';
    }
    if (strlen($pass) < 6) {
        $errors[] = 'Password must be at least 6 characters in length.';
    }
    if ($pass !== $pass2) {
        $errors[] = 'The password confirmation does not match.';
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email=?');
        $check->execute([$old['email']]);
        if ($check->fetch()) {
            $errors[] = 'This email address is already registered. Please sign in instead.';
        }
    }

    if (!$errors) {
        $insert = $pdo->prepare('INSERT INTO users (name, email, phone, password, role) VALUES (?,?,?,?,"customer")');
        $insert->execute([
            $old['name'],
            $old['email'],
            $old['phone'],
            password_hash($pass, PASSWORD_DEFAULT)
        ]);
        
        $newUserId = (int)$pdo->lastInsertId();
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $newUserId,
            'name' => $old['name'],
            'email' => $old['email'],
            'role' => 'customer'
        ];
        
        set_flash('success', 'Your account has been created successfully! Welcome to NO CAP.');
        redirect('index.php');
    }
}

$pageTitle = 'Create Account';
require __DIR__ . '/includes/header.php';
?>

<div class="container auth">
    <h1>Join NO CAP</h1>
    <p class="muted">Create an account to track your orders and enjoy faster checkout.</p>
    
    <?php foreach ($errors as $er): ?>
        <div class="alert error"><?= e($er) ?></div>
    <?php endforeach; ?>

    <form method="post" class="form auth-box">
        <?= csrf_field() ?>
        
        <label>Full Name
            <input name="name" value="<?= e($old['name']) ?>" required placeholder="John Doe">
        </label>
        
        <label>Email Address
            <input type="email" name="email" value="<?= e($old['email']) ?>" required autocomplete="email" placeholder="name@example.com">
        </label>
        
        <label>Phone Number
            <input name="phone" value="<?= e($old['phone']) ?>" required placeholder="+92 300 1234567">
        </label>
        
        <label>Password
            <input type="password" name="password" required autocomplete="new-password" placeholder="Minimum 6 characters">
        </label>
        
        <label>Confirm Password
            <input type="password" name="password2" required autocomplete="new-password" placeholder="Repeat password">
        </label>

        <button class="btn lg block" type="submit">Create Account</button>
    </form>

    <div class="auth-footer">
        <p>Already have an account? <a href="<?= url('login.php') ?>">Sign in here</a></p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
