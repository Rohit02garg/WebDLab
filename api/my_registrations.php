<?php
// Set response header to JSON
header("Content-Type: application/json; charset=utf-8");

// Include database connection
require_once __DIR__ . "/../config/db.php";

// Read admission number using professor's ternary isset pattern
$admissionNo = isset($_GET["admission_no"]) ? trim($_GET["admission_no"]) : (isset($_GET["admissionNo"]) ? trim($_GET["admissionNo"]) : "");

// Validate admission number
if ($admissionNo === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Please provide an Admission Number."
    ]);
    exit;
}

$admissionNo = strtoupper($admissionNo);

try {
    // Query active registrations for this student
    $stmt = $pdo->prepare("
        SELECT 
            r.id AS registration_id,
            r.student_name,
            r.admission_no,
            r.email,
            r.phone,
            r.department,
            r.registered_at,
            e.id AS event_id,
            e.title AS event_title,
            e.category,
            e.venue,
            e.date_time,
            e.capacity
        FROM registrations r
        INNER JOIN events e ON r.event_id = e.id
        WHERE r.admission_no = ?
        ORDER BY e.date_time ASC
    ");
    $stmt->execute([$admissionNo]);
    $registrations = $stmt->fetchAll();

    // Format registration records using foreach loop
    $formatted = [];
    foreach ($registrations as $row) {
        $dateTimeObj = new DateTime($row["date_time"]);
        $regTimeObj = new DateTime($row["registered_at"]);

        $formatted[] = [
            "registration_id" => intval($row["registration_id"]),
            "student_name" => $row["student_name"],
            "admission_no" => $row["admission_no"],
            "email" => $row["email"],
            "phone" => $row["phone"],
            "department" => $row["department"],
            "registered_on" => $regTimeObj->format("M d, Y h:i A"),
            "event_id" => intval($row["event_id"]),
            "event_title" => $row["event_title"],
            "category" => $row["category"],
            "venue" => $row["venue"],
            "event_date" => $dateTimeObj->format("M d, Y"),
            "event_time" => $dateTimeObj->format("h:i A"),
            "capacity" => intval($row["capacity"])
        ];
    }

    // Return student registrations in JSON format
    echo json_encode([
        "success" => true,
        "count" => count($formatted),
        "registrations" => $formatted
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch registrations: " . $e->getMessage()
    ]);
    exit;
}
?>
