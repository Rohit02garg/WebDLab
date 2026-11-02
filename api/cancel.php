<?php
// Lab 8 Section 2: JSON Response
header("Content-Type: application/json; charset=utf-8");

// Extra Lab Section 6: PDO Connection
require_once __DIR__ . "/../config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "POST method required."]);
    exit;
}

$input = !empty($_POST) ? $_POST : json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    $input = [];
}

$eventId = isset($input["event_id"]) ? (int) $input["event_id"] : 0;
$admissionNo = isset($input["admission_no"]) ? strtoupper(trim($input["admission_no"])) : "";

if ($eventId <= 0 || $admissionNo === "") {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Event ID and Admission Number are required."]);
    exit;
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Q50 EXAM TASK: Session guard (reject unauthenticated requests)
 * HOW TO USE: Add right after header() at top of file (around line 3):
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * session_start();
 * if (!isset($_SESSION["admissionNo"])) {
 *     http_response_code(401);
 *     echo json_encode(["success" => false, "message" => "Unauthorized: Please login first."]);
 *     exit;
 * }
 *
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Q11 EXAM TASK: Block cancellations within 2 hours of event start
 * HOW TO USE: Uncomment/add this block right here:
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * $stmtEv = $pdo->prepare("SELECT e.date_time FROM events e WHERE e.id = ?");
 * $stmtEv->execute([$eventId]);
 * $eventDateTime = $stmtEv->fetchColumn();
 * if ($eventDateTime && (strtotime($eventDateTime) - time()) < (2 * 3600) && strtotime($eventDateTime) > time()) {
 *     http_response_code(400);
 *     echo json_encode(["success" => false, "message" => "Late Cancel Policy: Cannot cancel within 2 hours of event start."]);
 *     exit;
 * }
 */

try {
    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * CHEAT SHEET Q45: Fix IDOR vulnerability (Enforce Session Ownership)
 * HOW TO USE: Override client-provided admission number with authentic session roll:
 * if (!isset($_SESSION["admissionNo"])) { http_response_code(401); exit; }
 * $admissionNo = $_SESSION["admissionNo"]; // IDOR Guard
 *
 * CHEAT SHEET Q4: Implement Soft Deletes (status = 'cancelled')
 * SQL: ALTER TABLE registrations ADD COLUMN status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed';
 * HOW TO USE: In DELETE query below, replace DELETE with:
 * $stmt = $pdo->prepare("UPDATE registrations SET status = 'cancelled' WHERE event_id = :eid AND admission_no = :adm AND status = 'confirmed'");
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 */
    // Delete student registration to release the reserved seat
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE event_id = :eid AND admission_no = :adm");
    $stmt->execute([
        ":eid" => $eventId,
        ":adm" => $admissionNo
    ]);

    if ($stmt->rowCount() > 0) {
        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
         * Q32 EXAM TASK: Change the cancellation success message text
         * HOW TO USE: Replace the "message" string below:
         * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
         * "message" => "Your registration has been removed. The seat is now available to others."
         */
        echo json_encode([
            "success" => true,
            "message" => "Registration cancelled successfully. Your seat has been released!"
        ]);
    } else {
        http_response_code(404);
        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
         * Q45 EXAM TASK: Add XSS protection to not found message
         * HOW TO USE: Wrap $admissionNo with htmlspecialchars():
         * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
         * "message" => "No active registration found for " . htmlspecialchars($admissionNo) . " in this event."
         */
        echo json_encode([
            "success" => false,
            "message" => "No active registration found for " . $admissionNo . " in this event."
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Cancellation failed: " . $e->getMessage()]);
}
?>
