<?php
$host     = '92.33.164.11';
$db       = 'project';
$user     = 'DI4020';
$password = 'F3gTdVKnBjYm72wcyF7X';
$port     = 8459;
$charset  = 'utf8mb4';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($host, $user, $password, $db, $port);
$db->set_charset($charset);
$db->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
