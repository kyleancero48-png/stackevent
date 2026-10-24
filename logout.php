<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(is_logged_in() ? url('dashboard.php') : url('login.php'));
}

verify_csrf(is_logged_in() ? url('dashboard.php') : url('login.php'));

logout_user();
redirect(url('login.php?logout=1'));
