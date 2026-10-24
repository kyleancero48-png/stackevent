<?php
require_once __DIR__ . '/includes/bootstrap.php';

redirect(is_logged_in() ? url('dashboard.php') : url('login.php'));
