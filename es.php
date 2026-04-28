<?php 
$l = []
$hs = password_hash('test', PASSWORD_DEFAULT);
echo "test hasher $hs";
$pass = password_verify('test', "$2y$12$f8tBBzcOR.pnLV7lgRS40OaB6vPA61y3Bdy5UV9YjIauzg0jdkJ8ale");
echo "le passord est : $pass";
var_dump($pass);
?>