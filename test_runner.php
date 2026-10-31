<?php
/**
 * Campus Event Registration System - Automated Test Suite
 * 
 * Executes end-to-end integration and API tests against the live
 * MariaDB database and PHP endpoints.
 */

// Colors for CLI output
$GREEN  = "\033[32m";
$RED    = "\033[31m";
$YELLOW = "\033[33m";
$BLUE   = "\033[34m";
$BOLD   = "\033[1m";
$RESET  = "\033[0m";

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function assertTest($description, $condition, $details = '') {
    global $totalTests, $passedTests, $failedTests, $GREEN, $RED, $BOLD, $RESET;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo "  [PASS] {$description}\n";
    } else {
        $failedTests++;
        echo "  [FAIL] {$description}\n";
        if ($details) {
            echo "         Details: {$details}\n";
        }
    }
}

function httpPostJson($url, $data) {
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true // Don't crash on 4xx/5xx status codes
        ]
    ];
    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    // Parse response status code
    $statusCode = 0;
    if (isset($http_response_header[0])) {
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        $statusCode = isset($match[1]) ? intval($match[1]) : 0;
    }

    return [
        'status' => $statusCode,
        'body'   => json_decode($response, true),
        'raw'    => $response
    ];
}

function httpGetJson($url) {
    $options = [
        'http' => [
            'header'  => "Accept: application/json\r\n",
            'method'  => 'GET',
            'ignore_errors' => true
        ]
    ];
    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    $statusCode = 0;
    if (isset($http_response_header[0])) {
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        $statusCode = isset($match[1]) ? intval($match[1]) : 0;
    }

    return [
        'status' => $statusCode,
        'body'   => json_decode($response, true),
        'raw'    => $response
    ];
}

$baseUrl = isset($argv[1]) ? $argv[1] : 'http://localhost/project';

echo "======================================================================\n";
echo "  IIT (ISM) DHANBAD • CAMPUS EVENT REGISTRATION TEST SUITE\n";
echo "  Base URL: {$baseUrl}\n";
echo "======================================================================\n\n";

// ---------------------------------------------------------------------------
// SUITE 1: Database Connection & Schema Verification
// ---------------------------------------------------------------------------
echo "--- Suite 1: Database & Initial State Verification ---\n";
require_once __DIR__ . '/config/db.php';
assertTest("Database connection established via PDO", isset($pdo) && $pdo instanceof PDO);

$stmt = $pdo->query("SELECT COUNT(*) FROM events");
$eventCount = intval($stmt->fetchColumn());
assertTest("Events table populated with sample campus events", $eventCount >= 8, "Found: {$eventCount}");

$stmt = $pdo->query("SELECT capacity, COUNT(r.id) as registered FROM events e LEFT JOIN registrations r ON e.id = r.event_id WHERE e.id = 6 GROUP BY e.id");
$ev6 = $stmt->fetch();
assertTest("Event 6 (Parakram) initially has capacity 6 and registered 5 (1 seat left)", $ev6['capacity'] == 6 && $ev6['registered'] == 5, "Capacity: {$ev6['capacity']}, Registered: {$ev6['registered']}");

$stmt = $pdo->query("SELECT capacity, COUNT(r.id) as registered FROM events e LEFT JOIN registrations r ON e.id = r.event_id WHERE e.id = 8 GROUP BY e.id");
$ev8 = $stmt->fetch();
assertTest("Event 8 (E-Summit) initially has capacity 4 and registered 4 (0 seats left / full)", $ev8['capacity'] == 4 && $ev8['registered'] == 4, "Capacity: {$ev8['capacity']}, Registered: {$ev8['registered']}");

echo "\n";

// ---------------------------------------------------------------------------
// SUITE 2: GET /api/get_events.php API
// ---------------------------------------------------------------------------
echo "--- Suite 2: GET /api/get_events.php ---\n";
$res = httpGetJson("{$baseUrl}/api/get_events.php");
assertTest("GET /api/get_events.php returns HTTP 200", $res['status'] === 200);
assertTest("Response contains success: true and list of events", isset($res['body']['success']) && $res['body']['success'] === true && count($res['body']['events']) >= 8);

// Filter by category
$resCat = httpGetJson("{$baseUrl}/api/get_events.php?category=Sports");
assertTest("Filtering by category=Sports returns only Sports events", $resCat['status'] === 200 && count($resCat['body']['events']) === 1 && $resCat['body']['events'][0]['category'] === 'Sports');

echo "\n";

// ---------------------------------------------------------------------------
// SUITE 3: POST /api/register.php (Successful Registration for Last Seat)
// ---------------------------------------------------------------------------
echo "--- Suite 3: Registration for Remaining Seat (Event 6) ---\n";
$testStudent = [
    'event_id'     => 6,
    'student_name' => 'Kavya Sharma',
    'admission_no' => '22JE7777',
    'email'        => 'kavya.sharma@iitism.ac.in',
    'phone'        => '9876500001',
    'department'   => 'Computer Science & Engineering'
];

$regRes = httpPostJson("{$baseUrl}/api/register.php", $testStudent);
assertTest("Registration returns HTTP 200 OK", $regRes['status'] === 200, "Status was: {$regRes['status']}");
assertTest("Registration success message returned", isset($regRes['body']['success']) && $regRes['body']['success'] === true);
assertTest("Dynamic remaining seats drops to 0 and is_full is true", 
    isset($regRes['body']['data']['remaining_seats']) && 
    $regRes['body']['data']['remaining_seats'] === 0 && 
    $regRes['body']['data']['is_full'] === true,
    "Remaining: " . json_encode($regRes['body']['data'] ?? null)
);

echo "\n";

// ---------------------------------------------------------------------------
// SUITE 4: Duplicate Registration Prevention
// ---------------------------------------------------------------------------
echo "--- Suite 4: Duplicate Registration Prevention ---\n";
// Attempt 1: Same roll number
$dupRes1 = httpPostJson("{$baseUrl}/api/register.php", $testStudent);
assertTest("Duplicate Admission No '22JE7777' rejected with HTTP 409 Conflict", $dupRes1['status'] === 409, "Status was: {$dupRes1['status']}");
assertTest("Duplicate error message contains clear explanation", 
    isset($dupRes1['body']['message']) && stripos($dupRes1['body']['message'], 'Duplicate') !== false,
    $dupRes1['body']['message'] ?? ''
);

// Attempt 2: Same email with different roll number
$dupStudentEmail = $testStudent;
$dupStudentEmail['admission_no'] = '22JE7778';
$dupRes2 = httpPostJson("{$baseUrl}/api/register.php", $dupStudentEmail);
assertTest("Duplicate Email 'kavya.sharma@iitism.ac.in' rejected with HTTP 409 Conflict", $dupRes2['status'] === 409, "Status was: {$dupRes2['status']}");

echo "\n";

// ---------------------------------------------------------------------------
// SUITE 5: Over-Capacity Enforcement
// ---------------------------------------------------------------------------
echo "--- Suite 5: Over-Capacity Enforcement (Event 6 is now 6/6) ---\n";
$overStudent = [
    'event_id'     => 6,
    'student_name' => 'Nikhil Gupta',
    'admission_no' => '22JE9999',
    'email'        => 'nikhil.gupta@iitism.ac.in',
    'phone'        => '9876500002',
    'department'   => 'Mining Engineering'
];
$overRes = httpPostJson("{$baseUrl}/api/register.php", $overStudent);
assertTest("Over-capacity booking rejected with HTTP 409 Conflict", $overRes['status'] === 409, "Status was: {$overRes['status']}");
assertTest("Capacity error message explicitly states maximum capacity reached", 
    isset($overRes['body']['message']) && (stripos($overRes['body']['message'], 'capacity') !== false || stripos($overRes['body']['message'], 'closed') !== false),
    $overRes['body']['message'] ?? ''
);

// Verify database row count did not exceed 6
$stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = 6");
$stmt->execute();
$actualRegs = intval($stmt->fetchColumn());
assertTest("Database verification: Event 6 has exactly 6 registrations (never exceeds capacity)", $actualRegs === 6, "Count was: {$actualRegs}");

echo "\n";

// ---------------------------------------------------------------------------
// SUITE 6: GET /api/my_registrations.php
// ---------------------------------------------------------------------------
echo "--- Suite 6: Student Registrations Lookup ---\n";
$myRegRes = httpGetJson("{$baseUrl}/api/my_registrations.php?admission_no=22JE7777");
assertTest("GET /api/my_registrations.php returns HTTP 200", $myRegRes['status'] === 200);
assertTest("Returns active registration for Parakram with campus venue details", 
    isset($myRegRes['body']['count']) && $myRegRes['body']['count'] === 1 && $myRegRes['body']['registrations'][0]['event_id'] === 6,
    json_encode($myRegRes['body'] ?? null)
);

echo "\n";

// ---------------------------------------------------------------------------
// SUITE 7: POST /api/cancel.php (Cancellation & Dynamic Seat Restoration)
// ---------------------------------------------------------------------------
echo "--- Suite 7: Registration Cancellation & Seat Restoration ---\n";
$cancelData = [
    'event_id'     => 6,
    'admission_no' => '22JE7777'
];
$cancelRes = httpPostJson("{$baseUrl}/api/cancel.php", $cancelData);
assertTest("Cancellation returns HTTP 200 OK", $cancelRes['status'] === 200, "Status was: {$cancelRes['status']}");
assertTest("Cancellation success message confirms seat freed", isset($cancelRes['body']['success']) && $cancelRes['body']['success'] === true);
assertTest("Dynamic remaining seats restored back to 1 and registered count back to 5", 
    isset($cancelRes['body']['data']['remaining_seats']) && 
    $cancelRes['body']['data']['remaining_seats'] === 1 && 
    $cancelRes['body']['data']['registered_count'] === 5 &&
    $cancelRes['body']['data']['is_full'] === false,
    json_encode($cancelRes['body']['data'] ?? null)
);

// Re-check public events API to confirm seat count restored
$eventsCheck = httpGetJson("{$baseUrl}/api/get_events.php?id=6");
assertTest("Public API confirms Event 6 has 1 remaining seat available again", 
    $eventsCheck['body']['events'][0]['remaining_seats'] === 1 && 
    $eventsCheck['body']['events'][0]['is_full'] === false
);

echo "\n";

// ---------------------------------------------------------------------------
// SUITE 8: Server-side Validation of Bad / Missing Inputs
// ---------------------------------------------------------------------------
echo "--- Suite 8: Server-Side Input Validation ---\n";
$invalidStudent = [
    'event_id'     => 6,
    'student_name' => '', // Empty name
    'admission_no' => '22JE', // Too short
    'email'        => 'invalid-email-format',
    'phone'        => 'abc',
    'department'   => ''
];
$invRes = httpPostJson("{$baseUrl}/api/register.php", $invalidStudent);
assertTest("Invalid payload returns HTTP 422 Unprocessable Entity", $invRes['status'] === 422, "Status was: {$invRes['status']}");
assertTest("Response contains detailed validation errors list", isset($invRes['body']['errors']) && count($invRes['body']['errors']) > 0);

echo "\n======================================================================\n";
echo "  FINAL TEST RESULTS: {$passedTests} / {$totalTests} PASSED\n";
if ($failedTests === 0) {
    echo "  ALL TESTS COMPLETED SUCCESSFULLY! (100% PASS RATE)\n";
} else {
    echo "  {$failedTests} TESTS FAILED\n";
}
echo "======================================================================\n";

exit($failedTests === 0 ? 0 : 1);
