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
    $stmt = $pdo->prepare("SELECT admission_no, student_name, email, phone, department, password FROM students WHERE admission_no = :adm LIMIT 1");
    $stmt->execute([":adm" => $admissionNo]);
    $student = $stmt->fetch();

    if (!$student) {
        // No account found
        header("Location: login.php?error=" . urlencode("No account found for Admission No '" . $admissionNo . "'. Please register first."));
        exit;
    }

    // Verify password using password_verify (matches password_hash from registration)
    if (!password_verify($password, $student["password"])) {
        header("Location: login.php?error=" . urlencode("Incorrect password. Please try again."));
        exit;
    }

    // Lab 10 Section 6.2: Store all student profile fields in session
    $_SESSION["admissionNo"]       = $student["admission_no"];
    $_SESSION["studentName"]       = $student["student_name"];
    $_SESSION["studentEmail"]      = $student["email"];
    $_SESSION["studentPhone"]      = $student["phone"];
    $_SESSION["studentDepartment"] = $student["department"];

    // Lab 10 Section 7.4: Redirect to main events portal after storing session
    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    header("Location: login.php?error=" . urlencode("Login error: " . $e->getMessage()));
    exit;
}
?>
