<?php
$host     = 'localhost';
$db       = 'project';
$user     = 'root';
$password = '123';
$port     = 3306;
$charset  = 'utf8mb4';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($host, $user, $password, $db, $port);
$db->set_charset($charset);
$db->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
