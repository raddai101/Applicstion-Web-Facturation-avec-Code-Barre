<?php
require_once dirname(__DIR__) . '/config/config.php';
session_unset();
session_destroy();
header('Location: ' . BASE_URL . '/auth/login.php?logout=1');
exit;
