<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: /facturation/auth/login.php");
    exit;
}
