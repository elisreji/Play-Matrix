<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['user'];
$type = $_GET['type'] ?? 'trainers';

try {
    $results = [];

    if ($type === 'trainers') {
        // Fetch Trainers from approved or accepted requests
        $stmt = $pdo->prepare("
            SELECT u.full_name as name, u.email as id, tp.specializations
            FROM TRAINER_REQUESTS tr
            JOIN USERS u ON tr.trainer_email = u.email
            LEFT JOIN TRAINER_PROFILES tp ON u.email = tp.user_email
            WHERE tr.user_email = ? AND tr.status IN ('Approved', 'Accepted')
        ");
        $stmt->execute([$email]);
        $results = $stmt->fetchAll();
    } elseif ($type === 'venues') {
        // Fetch Venues from verified bookings
        $stmt = $pdo->prepare("
            SELECT vb.venue_id, tv.venue_name as name, tv.location, tv.id as real_id
            FROM VENUE_BOOKINGS vb
            JOIN TRAINER_VENUES tv ON (vb.venue_id = tv.id OR vb.venue_id = CONCAT('trainer_', tv.id))
            WHERE vb.user_email = ?
            GROUP BY tv.id
        ");
        $stmt->execute([$email]);
        $rows = $stmt->fetchAll();
        foreach($rows as $r) {
            $results[] = [
                'id' => $r['venue_id'],
                'name' => $r['name'],
                'location' => $r['location']
            ];
        }

        // ADDED: Fetch Venues visited via Joined Games
        $stmt = $pdo->prepare("
            SELECT DISTINCT g.venue as name, 'Local Arena' as location, CONCAT('venue_game_', g.id) as id
            FROM GAME_PARTICIPANTS gp
            JOIN GAMES g ON gp.game_id = g.id
            WHERE gp.user_email = ?
        ");
        $stmt->execute([$email]);
        $gameVenues = $stmt->fetchAll();
        foreach($gameVenues as $gv) {
            $results[] = [
                'id' => $gv['id'],
                'name' => $gv['name'],
                'location' => $gv['location']
            ];
        }
    } elseif ($type === 'events') {
        // Fetch Coaching Programs (Events) Dino might be attending
        $stmt = $pdo->prepare("
            SELECT cp.id, cp.title as name, cp.type, b.trainer_email
            FROM TRAINER_BOOKINGS b
            JOIN COACHING_PROGRAMS cp ON b.program_id = cp.id
            WHERE b.user_email = ? AND b.program_id IS NOT NULL
            GROUP BY b.program_id
        ");
        $stmt->execute([$email]);
        $results = $stmt->fetchAll();

        // ADDED: Fetch Games Dino has joined (User-curated events)
        $stmt = $pdo->prepare("
            SELECT g.id, g.sport as name, 'Community Game' as type, g.host_name as trainer_email
            FROM GAME_PARTICIPANTS gp
            JOIN GAMES g ON gp.game_id = g.id
            WHERE gp.user_email = ?
        ");
        $stmt->execute([$email]);
        $gameEvents = $stmt->fetchAll();
        foreach($gameEvents as $ge) {
            $results[] = [
                'id' => 'game_' . $ge['id'],
                'name' => $ge['name'] . ' Session',
                'type' => $ge['type'],
                'trainer_email' => $ge['trainer_email']
            ];
        }
    }

    echo json_encode(['success' => true, 'data' => $results]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
