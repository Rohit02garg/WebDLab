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
$studentName = isset($input["student_name"]) ? trim($input["student_name"]) : (isset($input["studentName"]) ? trim($input["studentName"]) : "");
$admissionNo = isset($input["admission_no"]) ? trim($input["admission_no"]) : (isset($input["admissionNo"]) ? trim($input["admissionNo"]) : "");
$email = isset($input["email"]) ? trim($input["email"]) : "";
$phone = isset($input["phone"]) ? trim($input["phone"]) : "";
$department = isset($input["department"]) ? trim($input["department"]) : "";

// Format and sanitize values
$eventId = intval($eventId);
$admissionNo = strtoupper($admissionNo);
$email = strtolower($email);

// Server-side input validation
$errors = [];

if ($eventId <= 0) {
    $errors[] = "Invalid event selection.";
}

if ($studentName === "" || strlen($studentName) < 2) {
    $errors[] = "Full Name is required (minimum 2 characters).";
}

if ($admissionNo === "" || strlen($admissionNo) < 4) {
    $errors[] = "Valid Admission Number / Roll No is required (e.g., 24JE0456).";
}

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "A valid email address is required.";
}

if ($phone === "" || !preg_match("/^[0-9+\-\s()]{7,15}$/", $phone)) {
    $errors[] = "A valid phone number (7-15 digits) is required.";
}

if ($department === "") {
    $errors[] = "Please specify your department / branch.";
}

// If validation errors exist, return 422 with error list
if (count($errors) > 0) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => implode(" ", $errors),
        "errors" => $errors
    ]);
    exit;
}

// Execute transaction with row locking to prevent over-capacity registrations
try {
    // Begin transaction
    $pdo->beginTransaction();

    // 1. Lock the selected event row
    $stmtEvent = $pdo->prepare("SELECT id, title, venue, capacity FROM events WHERE id = ? FOR UPDATE");
    $stmtEvent->execute([$eventId]);
    $event = $stmtEvent->fetch();

    if (!$event) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Selected event could not be found."
        ]);
        exit;
    }

    $capacity = intval($event["capacity"]);

    // 2. Count existing registrations for this event
    $stmtCount = $pdo->prepare("SELECT COUNT(*) AS total FROM registrations WHERE event_id = ?");
    $stmtCount->execute([$eventId]);
    $currentCount = intval($stmtCount->fetchColumn());

    // 3. Prevent duplicate registration by admission number or email
    $stmtDup = $pdo->prepare("SELECT id FROM registrations WHERE event_id = ? AND (admission_no = ? OR email = ?)");
    $stmtDup->execute([$eventId, $admissionNo, $email]);
    $existingReg = $stmtDup->fetch();

    if ($existingReg) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Duplicate Registration: Admission No '" . $admissionNo . "' is already registered for '" . $event["title"] . "'."
        ]);
        exit;
    }

    // 4. Prevent registration if maximum capacity has been reached
    if ($currentCount >= $capacity) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Registration Closed: '" . $event["title"] . "' has reached its maximum capacity of " . $capacity . " seats."
        ]);
        exit;
    }

    // 5. Insert new student registration into database
    $stmtInsert = $pdo->prepare("
        INSERT INTO registrations (event_id, student_name, admission_no, email, phone, department)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtInsert->execute([
        $eventId,
        $studentName,
        $admissionNo,
        $email,
        $phone,
        $department
    ]);

    $registrationId = $pdo->lastInsertId();

    // Commit transaction
    $pdo->commit();

    // Store registration in PHP session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION["studentName"] = $studentName;
    $_SESSION["admissionNo"] = $admissionNo;
    $_SESSION["topic"] = $event["title"];

    // Calculate dynamically updated remaining seats
    $newRegisteredCount = $currentCount + 1;
    $newRemainingSeats = max(0, $capacity - $newRegisteredCount);
    $percentFilled = min(100, round(($newRegisteredCount / $capacity) * 100));

    // Return success response with updated seat counts
    echo json_encode([
        "success" => true,
        "message" => "Registration confirmed for " . $studentName . " (" . $admissionNo . ") at '" . $event["title"] . "'!",
        "data" => [
            "registration_id" => $registrationId,
            "event_id" => $eventId,
            "event_title" => $event["title"],
            "student_name" => $studentName,
            "admission_no" => $admissionNo,
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

    // Check for MySQL duplicate key error code 1062
    if ($e->errorInfo[1] == 1062) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "You are already registered for this event."
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Registration error: " . $e->getMessage()
        ]);
    }
    exit;
}
?>
