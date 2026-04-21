<?php
session_start();
session_destroy();
header("Location: /facturation/auth/login.php");
exit;
