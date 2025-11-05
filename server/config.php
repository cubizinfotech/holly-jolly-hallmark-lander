<?php
// server/config.php

// MySQL example
$dbHost = '127.0.0.1';
$dbName = 'holly_jolly_hallmark_lander';
$dbPort = 3306;
$dbUser = 'root';
$dbPass = '';

// Email configuration gmail
$emailHost = 'smtp.gmail.com';
$emailPort = 587;
$emailFrom = '';
$emailTo = '';
$emailUsername = '';
$emailPassword = '';

/*
// Email configuration hostinger
$emailHost = 'smtp.hostinger.com';
$emailPort = 587;
$emailFrom = '';
$emailTo = '';
$emailUsername = '';
$emailPassword = '';
*/

// DSN and options
$dbDsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
$dbOptions = [
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
