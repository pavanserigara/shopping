<?php
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();

if (!is_dir(__DIR__ . '/export')) {
    mkdir(__DIR__ . '/export', 0755, true);
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

$sqlOutput  = "-- LocalShopOS Production Database Dump with Demo Data\n";
$sqlOutput .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$sqlOutput .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $table) {
    $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
    if ($createStmt) {
        $sqlOutput .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $sqlOutput .= $createStmt['Create Table'] . ";\n\n";

        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $colsEscaped = array_map(function($c) { return "`{$c}`"; }, array_keys($row));
                $valsEscaped = array_map(function($v) use ($pdo) {
                    if ($v === null) return 'NULL';
                    return $pdo->quote($v);
                }, array_values($row));

                $sqlOutput .= "INSERT INTO `{$table}` (" . implode(', ', $colsEscaped) . ") VALUES (" . implode(', ', $valsEscaped) . ");\n";
            }
            $sqlOutput .= "\n";
        }
    }
}

$sqlOutput .= "SET FOREIGN_KEY_CHECKS=1;\n";

file_put_contents(__DIR__ . '/export/database_demo.sql', $sqlOutput);
echo "Successfully generated export/database_demo.sql (" . strlen($sqlOutput) . " bytes)\n";
