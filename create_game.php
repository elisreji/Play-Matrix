<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get raw POST data for JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // If json_decode failed, check if data was sent as form-data
    if ($data === null) {
        $data = $_POST;
    }

    $name = $data['name'] ?? '';
    $sport = $data['sport'] ?? '';
    $venue = $data['venue'] ?? '';
    $date = $data['date'] ?? '';
    $startTime = $data['startTime'] ?? '';
    $endTime = $data['endTime'] ?? '';
    $type = $data['type'] ?? '';
    $skill = $data['skill'] ?? '';
    $maxPlayers = $data['maxPlayers'] ?? 4;
    $price = $data['price'] ?? 'Free';

    // Basic Validation
    if (empty($name) || empty($sport) || empty($venue) || empty($date) || empty($startTime) || empty($endTime)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO GAMES (host_name, sport, venue, game_date, start_time, end_time, game_type, skill_level, price, max_players) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $result = $stmt->execute([$name, $sport, $venue, $date, $startTime, $endTime, $type, $skill, $price, $maxPlayers]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Game created successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create game.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>