<?php
echo "PHP: ", PHP_VERSION, "<br>";
$plain = '123456';
$hash  = password_hash($plain, PASSWORD_BCRYPT);
echo "hash: ", htmlspecialchars($hash), "<br>";
echo "verify(123456): ";
var_dump(password_verify('123456', $hash));
