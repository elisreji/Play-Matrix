<?php
session_start();
require_once '../db_connect.php';
header('Content-Type: application/json');

$email = $_SESSION['user'] ?? '';
if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $bookings = [];

    // 1. Venue Bookings
    $stmt = $pdo->prepare("
        SELECT 
            vb.id, 
            COALESCE(tv.venue_name, v.venue_name, vb.venue_id) as title, 
            COALESCE(tv.location, v.location, 'Location N/A') as location,
            vb.amount_paid as price, 
            vb.booking_date as date, 
            vb.booking_time as time, 
            'Venue' as type, 
            vb.status, 
            vb.payment_id,
            vb.created_at
        FROM VENUE_BOOKINGS vb
        LEFT JOIN VENUES v ON vb.venue_id = v.venue_id
        LEFT JOIN TRAINER_VENUES tv ON (vb.venue_id = tv.id OR vb.venue_id = CAST(tv.id AS CHAR) OR vb.venue_id = CONCAT('trainer_', tv.id))
        WHERE vb.user_email = ?
    ");
    $stmt->execute([$email]);
    $bookings = array_merge($bookings, $stmt->fetchAll(PDO::FETCH_ASSOC));

    // 2. Trainer/Program Bookings
    $stmt = $pdo->prepare("
        SELECT 
            tb.id, 
            cp.title, 
            cp.type as location,
            tb.amount_paid as price, 
            tb.session_date as date, 
            tb.session_time as time, 
            'Coaching' as type, 
            tb.status, 
            tb.payment_id,
            tb.created_at
        FROM TRAINER_BOOKINGS tb
        JOIN COACHING_PROGRAMS cp ON tb.program_id = cp.id
        WHERE tb.user_email = ?
    ");
    $stmt->execute([$email]);
    $bookings = array_merge($bookings, $stmt->fetchAll(PDO::FETCH_ASSOC));

    // 3. Game Bookings (Participants)
    $stmt = $pdo->prepare("
        SELECT 
            gp.id, 
            CONCAT(g.sport, ' Session') as title, 
            g.venue as location,
            g.price as price, 
            g.game_date as date, 
            g.game_time as time, 
            'Game' as type, 
            'Confirmed' as status, 
            gp.payment_id,
            gp.joined_at as created_at
        FROM GAME_PARTICIPANTS gp
        JOIN GAMES g ON gp.game_id = g.id
        WHERE gp.user_email = ?
    ");
    $stmt->execute([$email]);
    $bookings = array_merge($bookings, $stmt->fetchAll(PDO::FETCH_ASSOC));

    // Sort by created_at desc
    usort($bookings, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // Sync with disputes.json for refund status
    $disputesContent = file_exists('../disputes.json') ? file_get_contents('../disputes.json') : '[]';
    $disputesList = json_decode($disputesContent, true) ?: [];
    
    $refundMap = [];
    foreach ($disputesList as $d) {
        if (!empty($d['payment_id'])) {
            $refundMap[$d['payment_id']] = [
                'status' => $d['status'],
                'resolved_at' => $d['resolved_at'] ?? null
            ];
        }
    }

    foreach ($bookings as &$b) {
        if (!empty($b['payment_id']) && isset($refundMap[$b['payment_id']])) {
            $b['refund_status'] = $refundMap[$b['payment_id']]['status'];
            $b['refund_resolved_at'] = $refundMap[$b['payment_id']]['resolved_at'];
        } else {
            $b['refund_status'] = null;
        }
    }

    echo json_encode(['success' => true, 'bookings' => $bookings]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
