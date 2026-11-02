<?php
// Set response header to JSON (Lab 8 Section 2)
header("Content-Type: application/json; charset=utf-8");

// Include database connection (Extra Lab Section 6)
require_once __DIR__ . "/../config/db.php";

try {
    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * CHEAT SHEET Q1: Add organizer column to events
 * SQL: ALTER TABLE events ADD COLUMN organizer VARCHAR(100) DEFAULT 'Student Gymkhana';
 * 📍 EXACT LOCATION:
 *   1. In SELECT query below (around line 20), add: e.organizer,
 *   2. In $formattedEvents[] array (around line 55), add: "organizer" => $ev["organizer"] ?? "Student Gymkhana",
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 *
 * CHEAT SHEET Q41: Add pagination (?page=1&limit=4) to api/get_events.php
 * 📍 EXACT LOCATION: Replace lines 10-25 ($stmt = $pdo->query(...) down to $events = $stmt->fetchAll();)
 * ⚡ ACTION: Replace with this prepared query block:
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * $page = isset($_GET["page"]) ? max(1, (int)$_GET["page"]) : 1;
 * $limit = isset($_GET["limit"]) ? max(1, (int)$_GET["limit"]) : 100;
 * $offset = ($page - 1) * $limit;
 * $stmt = $pdo->prepare("SELECT e.id, e.title, e.description, e.date_time, e.venue, e.capacity, COUNT(r.id) AS registered_count, (e.capacity - COUNT(r.id)) AS remaining_seats FROM events e LEFT JOIN registrations r ON e.id = r.event_id GROUP BY e.id ORDER BY e.date_time ASC LIMIT ? OFFSET ?");
 * $stmt->bindValue(1, $limit, PDO::PARAM_INT);
 * $stmt->bindValue(2, $offset, PDO::PARAM_INT);
 * $stmt->execute();
 * $events = $stmt->fetchAll();
 */
    // Query events with dynamic registered count and remaining seats
    $stmt = $pdo->query("
        SELECT 
            e.id,
            e.title,
            e.description,
            e.date_time,
            e.venue,
            e.capacity,
            COUNT(r.id) AS registered_count,
            (e.capacity - COUNT(r.id)) AS remaining_seats
        FROM events e
        LEFT JOIN registrations r ON e.id = r.event_id
        GROUP BY e.id
        ORDER BY e.date_time ASC
    ");
    $events = $stmt->fetchAll();

    // Format events for JSON exchange (Lab 8 Section 2)
    $formattedEvents = [];
    foreach ($events as $ev) {
        $capacity = (int) $ev["capacity"];
        $registered = (int) $ev["registered_count"];
        $remaining = max(0, $capacity - $registered);
        $percentFilled = ($capacity > 0) ? min(100, round(($registered / $capacity) * 100)) : 100;

        $dateTimeObj = new DateTime($ev["date_time"]);
        $formattedDate = $dateTimeObj->format("M d, Y");
        $formattedTime = $dateTimeObj->format("h:i A");

        $formattedEvents[] = [
            "id" => (int) $ev["id"],
            "title" => $ev["title"],
            "description" => $ev["description"],
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

    echo json_encode([
        "success" => true,
        "events" => $formattedEvents
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database query error: " . $e->getMessage()
    ]);
}
?>
