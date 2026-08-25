<?php
$c = file_get_contents('app/Controllers/RiwayatController.php');
$c = str_replace("setHeight(100);", "setHeight(80);", $c);
$c = str_replace("setOffsetX(5);", "setOffsetX(40);", $c);
$c = str_replace("setOffsetY(5);", "setOffsetY(15);", $c);
file_put_contents('app/Controllers/RiwayatController.php', $c);
echo 'OK';
