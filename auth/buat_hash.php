<?php
$password_untuk_dihash = 'admin123';

$hash = password_hash($password_untuk_dihash, PASSWORD_DEFAULT);

echo "Password Anda: " . $password_untuk_dihash . "<br>";
echo "Hash yang dihasilkan (copy ini): <br>";
echo $hash;
?>