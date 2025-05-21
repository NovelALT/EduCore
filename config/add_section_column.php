<?php
require_once('db_connect.php');

try {
    $pdo->exec("ALTER TABLE lessons ADD COLUMN section VARCHAR(100) DEFAULT 'บทเรียนทั่วไป'");
    echo "Successfully added section column";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "Column already exists";
    } else {
        throw $e;
    }
}
