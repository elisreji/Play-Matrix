<?php
require_once 'db_connect.php';
$stmt = $pdo->prepare("SELECT id, venue_name, pic1, pic2, pic3 FROM TRAINER_VENUES");
$stmt->execute();
$v = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($v, JSON_PRETTY_PRINT);
?>
