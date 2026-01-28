<?php

require __DIR__ . '/../src/Bootstrap.php';

use App\Utils\Db;

$pdo = Db::conn();
$sql = file_get_contents(__DIR__ . '/../database/schema.sql');
$pdo->exec($sql);

echo "Migration completed\n";
