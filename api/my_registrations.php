<?php
// Lab 8 Section 2: JSON Response
header("Content-Type: application/json; charset=utf-8");

// Extra Lab Section 6: PDO Connection
require_once __DIR__ . "/../config/db.php";

$admissionNo = isset($_GET["admission_no"]) ? strtoupper(trim($_GET["admission_no"])) : "";

if ($admissionNo === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Please provide an Admission Number."]);
    exit;
}

try {
    // Query registrations with joined event details (Extra Lab Section 3.1 & 7.1)
    $stmt = $pdo->prepare("
        SELECT 
            r.id AS registration_id,
            r.event_id,
            r.admission_no,
            e.title AS event_title,
            e.venue,
            e.date_time
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        WHERE r.admission_no = ?
        ORDER BY e.date_time ASC
    ");
    $stmt->execute([$admissionNo]);
    $rows = $stmt->fetchAll();

    $formatted = [];
    foreach ($rows as $row) {
        $dt = new DateTime($row["date_time"]);
        $formatted[] = [
            "registration_id" => (int) $row["registration_id"],
            "event_id" => (int) $row["event_id"],
            "admission_no" => $row["admission_no"],
            "event_title" => $row["event_title"],
            "venue" => $row["venue"],
            "event_date" => $dt->format("M d, Y"),
            "event_time" => $dt->format("h:i A")
        ];
    }

    echo json_encode([
        "success" => true,
        "registrations" => $formatted
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Query failed: " . $e->getMessage()]);
}
?>
