<?php
// Set response header to JSON
header("Content-Type: application/json; charset=utf-8");

// Include database connection
require_once __DIR__ . "/../config/db.php";

// Verify request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed. Use POST."
    ]);
    exit;
}

// Read input parameters from POST or JSON body
$input = !empty($_POST) ? $_POST : json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    $input = [];
}

// Extract inputs using professor's ternary isset pattern
$eventId = isset($input["event_id"]) ? trim($input["event_id"]) : (isset($input["eventId"]) ? trim($input["eventId"]) : "");
$admissionNo = isset($input["admission_no"]) ? trim($input["admission_no"]) : (isset($input["admissionNo"]) ? trim($input["admissionNo"]) : "");
$registrationId = isset($input["registration_id"]) ? trim($input["registration_id"]) : (isset($input["registrationId"]) ? trim($input["registrationId"]) : "");

// Validate required fields
if ($eventId === "" || ($admissionNo === "" && $registrationId === "")) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Event ID and Admission Number are required to cancel a registration."
    ]);
    exit;
}

$eventId = intval($eventId);
$admissionNo = strtoupper($admissionNo);
$registrationId = intval($registrationId);

// Execute cancellation inside a database transaction
try {
    // Begin transaction
    $pdo->beginTransaction();

    // 1. Lock the event row
    $stmtEvent = $pdo->prepare("SELECT id, title, capacity FROM events WHERE id = ? FOR UPDATE");
    $stmtEvent->execute([$eventId]);
    $event = $stmtEvent->fetch();

    if (!$event) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Event not found."
        ]);
        exit;
    }

    $capacity = intval($event["capacity"]);

    // 2. Find the registration record
    if ($registrationId > 0 && $admissionNo !== "") {
        $stmtReg = $pdo->prepare("
            SELECT id, student_name, admission_no 
            FROM registrations 
            WHERE id = ? AND event_id = ? AND admission_no = ?
        ");
        $stmtReg->execute([$registrationId, $eventId, $admissionNo]);
    } elseif ($registrationId > 0) {
        $stmtReg = $pdo->prepare("
            SELECT id, student_name, admission_no 
            FROM registrations 
            WHERE id = ? AND event_id = ?
        ");
        $stmtReg->execute([$registrationId, $eventId]);
    } else {
        $stmtReg = $pdo->prepare("
            SELECT id, student_name, admission_no 
            FROM registrations 
            WHERE event_id = ? AND admission_no = ?
        ");
        $stmtReg->execute([$eventId, $admissionNo]);
    }

    $reg = $stmtReg->fetch();

    if (!$reg) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "No active registration found for Admission No '" . $admissionNo . "' in '" . $event["title"] . "'."
        ]);
        exit;
    }

    // 3. Delete the registration to release the reserved seat
    $stmtDel = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
    $stmtDel->execute([$reg["id"]]);

    // 4. Count remaining registrations after cancellation
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ?");
    $stmtCount->execute([$eventId]);
    $newRegisteredCount = intval($stmtCount->fetchColumn());

    // Commit transaction
    $pdo->commit();

    // 5. Calculate dynamically updated remaining seats
    $newRemainingSeats = max(0, $capacity - $newRegisteredCount);
    $percentFilled = ($capacity > 0) ? min(100, round(($newRegisteredCount / $capacity) * 100)) : 0;

    // Return success response with updated seat stats
    echo json_encode([
        "success" => true,
        "message" => "Registration cancelled successfully for " . $reg["student_name"] . " (" . $reg["admission_no"] . "). Seat freed!",
        "data" => [
            "event_id" => $eventId,
            "event_title" => $event["title"],
            "student_name" => $reg["student_name"],
            "admission_no" => $reg["admission_no"],
            "capacity" => $capacity,
            "registered_count" => $newRegisteredCount,
            "remaining_seats" => $newRemainingSeats,
            "percent_filled" => $percentFilled,
            "is_full" => ($newRemainingSeats <= 0)
        ]
    ]);
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Cancellation error: " . $e->getMessage()
    ]);
    exit;
}
?>
