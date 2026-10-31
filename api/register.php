<?php
// Lab 8 Section 2: JSON Response
header("Content-Type: application/json; charset=utf-8");

// Extra Lab Section 6: PDO Connection
require_once __DIR__ . "/../config/db.php";

// Verify request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "POST method required."]);
    exit;
}

// Read input parameters from POST or JSON body
$input = !empty($_POST) ? $_POST : json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    $input = [];
}

// Extract inputs using professor's ternary isset pattern (Lab 10 Section 5.1)
$eventId = isset($input["event_id"]) ? (int) $input["event_id"] : 0;
$studentName = isset($input["student_name"]) ? trim($input["student_name"]) : "";
$admissionNo = isset($input["admission_no"]) ? strtoupper(trim($input["admission_no"])) : "";
$email = isset($input["email"]) ? strtolower(trim($input["email"])) : "";
$phone = isset($input["phone"]) ? trim($input["phone"]) : "";
$department = isset($input["department"]) ? trim($input["department"]) : "";

// Server-side validation
if ($eventId <= 0 || $studentName === "" || $admissionNo === "" || $email === "" || $phone === "" || $department === "") {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "All registration fields are required."]);
    exit;
}

try {
    // 1. Fetch event capacity
    $stmtEvent = $pdo->prepare("SELECT id, title, capacity FROM events WHERE id = ?");
    $stmtEvent->execute([$eventId]);
    $event = $stmtEvent->fetch();

    if (!$event) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Selected event not found."]);
        exit;
    }

    $capacity = (int) $event["capacity"];

    // 2. Prevent duplicate registration (PS P3 Requirement)
    $stmtDup = $pdo->prepare("SELECT id FROM registrations WHERE event_id = ? AND (admission_no = ? OR email = ?)");
    $stmtDup->execute([$eventId, $admissionNo, $email]);
    if ($stmtDup->fetch()) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Duplicate Registration: You are already registered for '" . $event["title"] . "'."
        ]);
        exit;
    }

    // 3. Prevent over-capacity registration (PS P3 Requirement)
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ?");
    $stmtCount->execute([$eventId]);
    $currentCount = (int) $stmtCount->fetchColumn();

    if ($currentCount >= $capacity) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Registration Closed: '" . $event["title"] . "' has reached full capacity (" . $capacity . " seats)."
        ]);
        exit;
    }

    // 4. Insert new registration (Extra Lab Section 7.2)
    $stmtInsert = $pdo->prepare("
        INSERT INTO registrations (event_id, student_name, admission_no, email, phone, department)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtInsert->execute([$eventId, $studentName, $admissionNo, $email, $phone, $department]);

    // Store in session (Lab 10 Section 6.2)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION["studentName"] = $studentName;
    $_SESSION["admissionNo"] = $admissionNo;

    echo json_encode([
        "success" => true,
        "message" => "Registration confirmed for " . $studentName . " (" . $admissionNo . ")!"
    ]);

} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Duplicate registration detected."]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}
?>
