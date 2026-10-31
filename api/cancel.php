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

try {
    // Delete student registration to release the reserved seat
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE event_id = :eid AND admission_no = :adm");
    $stmt->execute([
        ":eid" => $eventId,
        ":adm" => $admissionNo
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Registration cancelled successfully. Your seat has been released!"
        ]);
    } else {
        http_response_code(404);
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
