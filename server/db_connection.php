<?php

$pdo = new PDO("mysql:host=localhost;dbname=travhub-us-apply", "root", "");
// $pdo = new PDO("mysql:host=localhost;dbname=sazummec_travhub_us_apply", "sazummec_travhub_uk_apply", "!Qa@Ws3eD4rF");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
