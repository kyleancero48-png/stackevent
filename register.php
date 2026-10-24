<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/classes/users.php';

require_guest();

$errors = [];
$old = [
    'full_name' => '',
    'username' => '',
    'email' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(url('register.php'));

    $old['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $old['username'] = trim((string) ($_POST['username'] ?? ''));
    $old['email'] = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (text_length($old['full_name']) < 2 || text_length($old['full_name']) > 100) {
        $errors[] = 'Full name must be between 2 and 100 characters.';
    }
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $old['username'])) {
        $errors[] = 'Username must be 3-30 characters using letters, numbers, or underscores only.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || text_length($old['email']) > 150) {
        $errors[] = 'Enter a valid email address (maximum 150 characters).';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if (strlen($password) > 72) {
        $errors[] = 'Password must not be longer than 72 characters.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'The password confirmation does not match.';
    }

    $users = new Users($conn);

    if ($errors === []) {
        if ($users->usernameExists($old['username'])) {
            $errors[] = 'That username is already taken.';
        }
        if ($users->emailExists($old['email'])) {
            $errors[] = 'That email address is already registered.';
        }
    }

    if ($errors === []) {
        try {
            $userId = $users->create(
                $old['full_name'],
                $old['username'],
                $old['email'],
                password_hash($password, PASSWORD_DEFAULT)
            );

            $user = $users->findById($userId);
            if ($user === null) {
                throw new RuntimeException('Newly created user could not be loaded.');
            }

            login_user((array) $user);
            flash('success', 'Account created. Welcome to StackEvent, ' . $user->full_name . '.');
            redirect(url('dashboard.php'));
        } catch (PDOException $e) {
            error_log('Registration failed: ' . $e->getMessage());
            $errors[] = $e->getCode() === '23000'
                ? 'That username or email is already registered.'
                : 'Could not create your account. Please try again.';
        } catch (Throwable $e) {
            error_log('Registration failed: ' . $e->getMessage());
            $errors[] = 'Could not create your account. Please try again.';
        }
    }
}

render_head('Create account', 'auth-body');
render_termbar('/register');
?>
<main class="auth-wrap">
    <section class="auth-card">
        <div class="auth-card-head">
            <div class="auth-brandline">
                <span class="brand-mark">SE</span>
                <span class="brand">StackEvent</span>
            </div>
            <div class="auth-eyebrow">auth --create-account</div>
            <h1 class="auth-title">Create account<span class="cursor"></span></h1>
            <p class="auth-sub">Register an operator account for the event console.</p>
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

            <form method="post" action="<?= e(url('register.php')) ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="full_name">Full name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" maxlength="100" autocomplete="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= e($old['username']) ?>" maxlength="30" pattern="[A-Za-z0-9_]{3,30}" autocomplete="username" required>
                    <div class="form-hint">3-30 characters: letters, numbers, underscores.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= e($old['email']) ?>" maxlength="150" autocomplete="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                    <div class="form-hint">At least 8 characters. Stored as a secure hash.</div>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="confirm_password">Confirm password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" maxlength="72" autocomplete="new-password" required>
                </div>
                <button type="submit" class="btn-add">
                    Create account <i class="bi bi-person-plus"></i>
                </button>
            </form>
        </div>
        <div class="auth-card-foot">
            Already have an account? <a href="<?= e(url('login.php')) ?>">Sign in</a>
        </div>
    </section>
    <p class="auth-hint">STACKEVENT <span class="dot-sep">/</span> EVENT OPERATIONS CONSOLE <span class="dot-sep">/</span> AUTHORIZED ACCESS ONLY</p>
</main>
<?php render_footer(); ?>
