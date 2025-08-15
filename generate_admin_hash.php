<?php
$plainPassword = "mrunal@05"; // Change to your desired password
$hash = password_hash($plainPassword, PASSWORD_DEFAULT);
echo "Hashed Password: " . $hash;
?>
