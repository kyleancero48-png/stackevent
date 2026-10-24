<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/classes/users.php';

require_guest();

if (isset($_GET['logout'])) {
    flash('success', 'You have been logged out.');
    redirect(url('login.php'));
}

$errors = [];
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(url('login.php'));

    $identifier = trim((string) ($_POST['login'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($identifier === '' || $password === '') {
        $errors[] = 'Enter your username or email and your password.';
    } else {
        $users = new Users($conn);
        $user = $users->findByLogin($identifier);

        if ($user && password_verify($password, $user->password_hash)) {
            login_user((array) $user);
            flash('success', 'Welcome back, ' . $user->full_name . '.');
            redirect(url('dashboard.php'));
        }

        $errors[] = 'Invalid credentials. Check your username/email and password.';
    }
}

render_head('Sign in', 'auth-body');
render_termbar('/login');
?>
<main class="auth-wrap">
    <section class="auth-card">
        <div class="auth-card-head">
            <div class="auth-brandline">
                <span class="brand-mark">SE</span>
                <span class="brand">StackEvent</span>
            </div>
            <div class="auth-eyebrow">auth --login</div>
            <h1 class="auth-title">Sign in<span class="cursor"></span></h1>
            <p class="auth-sub">Authenticate to open the event operations console.</p>
        </div>
        <div class="auth-card-body">
            <?php render_flash(); ?>

            <?php if ($errors !== []): ?>
                <div class="alert-terminal alert-error" role="alert">
                    <span class="alert-prompt">[err]</span>
                    <div>
                        <ul class="error-list">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e(url('login.php')) ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="login">Username or email</label>
                    <input type="text" class="form-control" id="login" name="login" value="<?= e($identifier) ?>" autocomplete="username" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn-add">
                    Authenticate <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>
        <div class="auth-card-foot">
            No account yet? <a href="<?= e(url('register.php')) ?>">Create account</a>
        </div>
    </section>
    <p class="auth-hint">STACKEVENT <span class="dot-sep">/</span> EVENT OPERATIONS CONSOLE <span class="dot-sep">/</span> AUTHORIZED ACCESS ONLY</p>
</main>
<?php render_footer(); ?>
