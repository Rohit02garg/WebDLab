<?php
// Lab 10 Section 6.1: Start session before any output
session_start();

// Extra Lab Section 6: Include PDO database connection
require_once "config/db.php";

// Verify request method is POST (Lab 10 Section 4)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

// Lab 10 Section 5.1: Read and trim POST values using isset ternary
$admissionNo = isset($_POST["admissionNo"])     ? trim($_POST["admissionNo"])   : "";
$password    = isset($_POST["studentPassword"]) ? $_POST["studentPassword"]     : "";

// Validate required fields
$errors = [];
if ($admissionNo === "") $errors[] = "Admission Number is required.";
if ($password    === "") $errors[] = "Password is required.";

if (!empty($errors)) {
    header("Location: login.php?error=" . urlencode(implode(" ", $errors)));
    exit;
}

// Normalize Admission Number to uppercase
$admissionNo = strtoupper($admissionNo);

// Extra Lab: Look up student in database using PDO prepared statement
try {
    $stmt = $pdo->prepare("SELECT admission_no, student_name, email, phone, department, password, role FROM students WHERE admission_no = :adm LIMIT 1");
    $stmt->execute([":adm" => $admissionNo]);
    $student = $stmt->fetch();

    if (!$student) {
        // No account found
        header("Location: login.php?error=" . urlencode("No account found for Admission No '" . $admissionNo . "'. Please register first."));
        exit;
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * CHEAT SHEET Q37: Account Lockout after 3 Failed Password Attempts
     * SQL: ALTER TABLE students ADD COLUMN failed_attempts INT DEFAULT 0, ADD COLUMN locked_until DATETIME NULL;
     * HOW TO USE: Replace the password_verify() block with:
     * if ($student["locked_until"] && strtotime($student["locked_until"]) > time()) {
     *     header("Location: login.php?error=" . urlencode("Account locked for 5 minutes due to 3 failed attempts."));
     *     exit;
     * }
     * if (!password_verify($password, $student["password"])) {
     *     $fails = $student["failed_attempts"] + 1;
     *     if ($fails >= 3) {
     *         $pdo->prepare("UPDATE students SET failed_attempts = 0, locked_until = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE admission_no = ?")->execute([$admissionNo]);
     *     } else {
     *         $pdo->prepare("UPDATE students SET failed_attempts = ? WHERE admission_no = ?")->execute([$fails, $admissionNo]);
     *     }
     *     header("Location: login.php?error=Incorrect+password"); exit;
     * }
     * $pdo->prepare("UPDATE students SET failed_attempts = 0, locked_until = NULL WHERE admission_no = ?")->execute([$admissionNo]);
     *
     * CHEAT SHEET Q38: Bind session to User-Agent (Anti Session Hijacking)
     * HOW TO USE: Add in session setting section below:
     * $_SESSION["user_agent"] = $_SERVER["HTTP_USER_AGENT"];
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     */
    // Verify password using password_verify (matches password_hash from registration)
    if (!password_verify($password, $student["password"])) {
        header("Location: login.php?error=" . urlencode("Incorrect password. Please try again."));
        exit;
    }

    // Lab 10 Section 6.2: Store student profile fields and admin flag in session
    $_SESSION["admissionNo"]       = $student["admission_no"];
    $_SESSION["studentName"]       = $student["student_name"];
    $_SESSION["studentEmail"]      = $student["email"];
    $_SESSION["studentPhone"]      = $student["phone"];
    $_SESSION["studentDepartment"] = $student["department"];
    $_SESSION["isAdmin"]           = ($student["role"] === "admin");

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Q31 EXAM TASK: Remember Me cookie (sets 7-day cookie)
     * HOW TO USE: Uncomment this block right here:
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * if (!empty($_POST["rememberMe"])) {
     *     setcookie("remember_adm", $student["admission_no"], time() + (7 * 24 * 3600), "/");
     * } else {
     *     setcookie("remember_adm", "", time() - 3600, "/");
     * }
     *
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Q41 EXAM TASK: Track and store last login timestamp
     * HOW TO USE: Uncomment this block right here:
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * $pdo->prepare("UPDATE students SET last_login_at = NOW() WHERE admission_no = ?")->execute([$student["admission_no"]]);
     * $_SESSION["lastLoginDisplay"] = date("M d, g:i A");
     * NOTE: Run SQL first: ALTER TABLE students ADD COLUMN last_login_at DATETIME NULL;
     */

    // Redirect admin to admin dashboard; students to index.php
    if (!empty($_SESSION["isAdmin"])) {
        header("Location: admin.php");
    } else {
        header("Location: index.php");
    }
    exit;

} catch (PDOException $e) {
    header("Location: login.php?error=" . urlencode("Login error: " . $e->getMessage()));
    exit;
}
?>
