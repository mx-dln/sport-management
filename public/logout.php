<?php
require_once __DIR__ . '/../app/helpers/auth.php';
session_destroy();
redirect(app_url('login.php'));

