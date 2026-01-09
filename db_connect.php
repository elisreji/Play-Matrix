<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'playmatrix';

$pdo = null;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If database doesn't exist, we fallback to NULL.
    // The application scripts check if $pdo exists.
    $pdo = null;
}
?>