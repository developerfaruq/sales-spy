<?php
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

echo "Server time: " . date('Y-m-d H:i:s') . "<br>";
echo "Expiry time: " . $expiry . "<br>";
?>