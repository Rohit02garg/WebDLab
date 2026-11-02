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

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Q47 EXAM TASK: Explain input sanitization - trim/strtoupper/strtolower
 * NOTE: Lines below already ARE the sanitization:
 * trim() = removes spaces from start/end
 * strtoupper() = converts to UPPERCASE (roll numbers: 24je0917 -> 24JE0917)
 * strtolower() = converts to lowercase (emails: Rohit@... -> rohit@...)
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 */
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
    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Q7 EXAM TASK: Change the error message text for missing fields
     * HOW TO USE: Replace the message string below
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * "message" => "Please fill in all fields before submitting your registration."
     */
    echo json_encode(["success" => false, "message" => "All registration fields are required."]);
    exit;
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Q50 EXAM TASK: Add session guard (reject unauthenticated requests)
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
 * Q8 EXAM TASK: Add limit: max 3 registrations per student
 * HOW TO USE: Uncomment/add this block right here (before try {}):
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE admission_no = ?");
 * $stmtTotal->execute([$admissionNo]);
 * if ($stmtTotal->fetchColumn() >= 3) {
 *     http_response_code(409);
 *     echo json_encode(["success" => false, "message" => "Quota Exceeded: You cannot register for more than 3 events."]);
 *     exit;
 * }
 *
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Q13 EXAM TASK: Restrict specific event to one department only
 * HOW TO USE: Uncomment/add this block right here (before try {}):
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * if ($eventId === 4 && $department !== "Computer Science & Engineering") {
 *     http_response_code(403);
 *     echo json_encode(["success" => false, "message" => "Access Restricted: This event is only for CSE students."]);
 *     exit;
 * }
 *
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Q46 EXAM TASK: Validate phone number must be exactly 10 digits
 * HOW TO USE: Uncomment/add this block right here (before try {}):
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * if (!preg_match('/^[0-9]{10}$/', $phone)) {
 *     http_response_code(422);
 *     echo json_encode(["success" => false, "message" => "Invalid phone number. Must be exactly 10 digits."]);
 *     exit;
 * }
 */

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

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Q9 EXAM TASK: Change duplicate check (only check roll number, not email)
     * HOW TO USE: Replace the prepare & execute below with this:
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * $stmtDup = $pdo->prepare("SELECT id FROM registrations WHERE event_id = ? AND admission_no = ?");
     * $stmtDup->execute([$eventId, $admissionNo]);
     */
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

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Q43 EXAM TASK: Prevent time conflict registrations
     * HOW TO USE: Uncomment/add this block right here:
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * $stmtTime = $pdo->prepare("
     *     SELECT e.title FROM registrations r
     *     JOIN events e ON r.event_id = e.id
     *     WHERE r.admission_no = ? AND e.date_time = (SELECT date_time FROM events WHERE id = ?)
     * ");
     * $stmtTime->execute([$admissionNo, $eventId]);
     * $conflict = $stmtTime->fetch();
     * if ($conflict) {
     *     http_response_code(409);
     *     echo json_encode(["success" => false, "message" => "Schedule Conflict: Already registered for '" . $conflict["title"] . "' at this time!"]);
     *     exit;
     * }
     */

    // 3. Prevent over-capacity registration (PS P3 Requirement)
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ?");
    $stmtCount->execute([$eventId]);
    $currentCount = (int) $stmtCount->fetchColumn();

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Q35 EXAM TASK: Add waitlist when event is full
     * HOW TO USE: Replace the if ($currentCount >= $capacity) block below with:
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * if ($currentCount >= $capacity) {
     *     $stmtWait = $pdo->prepare("INSERT IGNORE INTO waitlist (event_id, admission_no, student_name) VALUES (?, ?, ?)");
     *     $stmtWait->execute([$eventId, $admissionNo, $studentName]);
     *     echo json_encode(["success" => true, "message" => "Event is full! Added to WAITLIST for '" . $event["title"] . "'."]);
     *     exit;
     * }
     *
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Q44 EXAM TASK: Atomic capacity enforcement using transactions (SELECT FOR UPDATE)
     * HOW TO USE: Replace the entire try {} block with the version from PRACTICE_ANSWERS.md Q44
     * Key lines to show TA: $pdo->beginTransaction(); and "SELECT ... FOR UPDATE";
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     */
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

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Q12 EXAM TASK: Write registration to a log file
     * Q49 EXAM TASK: Write detailed log (with title + roll + name)
     * HOW TO USE: Uncomment one of these lines right after execute():
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Q12:
     * $logEntry = date("Y-m-d H:i:s") . " | BOOKED | Event: $eventId | Roll: $admissionNo | Email: $email" . PHP_EOL;
     * file_put_contents(__DIR__ . "/../registrations.log", $logEntry, FILE_APPEND);
     *
     * Q49:
     * $logEntry = date("Y-m-d H:i:s") . " | BOOKED | Event #$eventId (" . ($event["title"] ?? "Unknown") . ") | Student: $admissionNo - $studentName | Dept: $department" . PHP_EOL;
     * file_put_contents(__DIR__ . "/../registrations.log", $logEntry, FILE_APPEND);
     */

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
