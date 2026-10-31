<?php
// Set response header to JSON
header("Content-Type: application/json; charset=utf-8");

// Include database connection
require_once __DIR__ . "/../config/db.php";

try {
    // Read GET parameters using ternary checks
    $eventId = isset($_GET["id"]) ? trim($_GET["id"]) : "";
    $category = isset($_GET["category"]) ? trim($_GET["category"]) : "";

    // SQL query to fetch events, total capacity, and dynamic remaining seats
    $sql = "
        SELECT 
            e.id,
            e.title,
            e.category,
            e.description,
            e.date_time,
            e.venue,
            e.capacity,
            COUNT(r.id) AS registered_count,
            (e.capacity - COUNT(r.id)) AS remaining_seats
        FROM events e
        LEFT JOIN registrations r ON e.id = r.event_id
    ";

    $params = [];
    $whereConditions = [];

    // Filter by specific event ID if provided
    if ($eventId !== "" && intval($eventId) > 0) {
        $whereConditions[] = "e.id = ?";
        $params[] = intval($eventId);
    }

    // Filter by category if selected
    if ($category !== "" && $category !== "All") {
        $whereConditions[] = "e.category = ?";
        $params[] = $category;
    }

    // Add WHERE clause if filters exist
    if (count($whereConditions) > 0) {
        $sql .= " WHERE " . implode(" AND ", $whereConditions);
    }

    $sql .= " GROUP BY e.id ORDER BY e.date_time ASC";

    // Prepare and execute statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();

    // Format events list for frontend consumption
    $formattedEvents = [];
    foreach ($events as $ev) {
        $capacity = intval($ev["capacity"]);
        $registered = intval($ev["registered_count"]);
        $remaining = max(0, $capacity - $registered);
        $percentFilled = ($capacity > 0) ? min(100, round(($registered / $capacity) * 100)) : 100;
        
        $dateTimeObj = new DateTime($ev["date_time"]);
        $formattedDate = $dateTimeObj->format("M d, Y");
        $formattedTime = $dateTimeObj->format("h:i A");

        $formattedEvents[] = [
            "id" => intval($ev["id"]),
            "title" => $ev["title"],
            "category" => $ev["category"],
            "description" => $ev["description"],
            "date_time_raw" => $ev["date_time"],
            "date_formatted" => $formattedDate,
            "time_formatted" => $formattedTime,
            "venue" => $ev["venue"],
            "capacity" => $capacity,
            "registered_count" => $registered,
            "remaining_seats" => $remaining,
            "percent_filled" => $percentFilled,
            "is_full" => ($remaining <= 0)
        ];
    }

    // Send JSON response with events data
    echo json_encode([
        "success" => true,
        "count" => count($formattedEvents),
        "events" => $formattedEvents
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch events: " . $e->getMessage()
    ]);
    exit;
}
?>
