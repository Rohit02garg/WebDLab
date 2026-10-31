<?php
// Lab 10 Section 6.1: Start session before any output
session_start();

// Extra Lab Section 6: Include PDO database connection
require_once "config/db.php";

// Verify request method is POST (Lab 10 Section 4)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.php");
    exit;
}

// Lab 10 Section 5.1: Read and trim all POST values using isset ternary
$admissionNo = isset($_POST["admissionNo"]) ? trim($_POST["admissionNo"]) : "";
$studentName = isset($_POST["studentName"]) ? trim($_POST["studentName"]) : "";
$studentEmail = isset($_POST["studentEmail"]) ? trim($_POST["studentEmail"]) : "";
$studentPhone = isset($_POST["studentPhone"]) ? trim($_POST["studentPhone"]) : "";
$studentDept = isset($_POST["studentDepartment"]) ? trim($_POST["studentDepartment"]) : "";
$password = isset($_POST["studentPassword"]) ? $_POST["studentPassword"] : "";
$confirmPassword = isset($_POST["confirmPassword"]) ? $_POST["confirmPassword"] : "";

// Validate all required fields are present
$errors = [];
if ($admissionNo === "")
    $errors[] = "Admission Number is required.";
if ($studentName === "")
    $errors[] = "Full Name is required.";
if ($studentEmail === "")
    $errors[] = "Email is required.";
if ($studentPhone === "")
    $errors[] = "Mobile Number is required.";
if ($studentDept === "")
    $errors[] = "Department is required.";
if ($password === "")
    $errors[] = "Password is required.";
if (strlen($password) < 4)
    $errors[] = "Password must be at least 4 characters.";
if ($password !== $confirmPassword)
    $errors[] = "Passwords do not match.";

if (!empty($errors)) {
    header("Location: register.php?error=" . urlencode(implode(" ", $errors)));
    exit;
}

// Normalize Admission Number to uppercase (e.g. 24je0917 -> 24JE0917)
$admissionNo = strtoupper($admissionNo);

// Basic format check for IIT (ISM) Admission Number
if (!preg_match('/^[0-9]{2}[A-Z]{2}[0-9]{4}$/', $admissionNo)) {
    $msg = "Invalid Admission Number format. Expected: 24JE0917 or 24JE0456.";
    header("Location: register.php?error=" . urlencode($msg));
    exit;
}

// Normalize email
$studentEmail = strtolower($studentEmail);

// Hash the password using PHP built-in password_hash (bcrypt)
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Extra Lab: Insert student into database using PDO prepared statement
try {
    $stmt = $pdo->prepare("
        INSERT INTO students (admission_no, student_name, email, phone, department, password)
        VALUES (:adm, :name, :email, :phone, :dept, :pass)
    ");
    $stmt->execute([
        ":adm" => $admissionNo,
        ":name" => $studentName,
        ":email" => $studentEmail,
        ":phone" => $studentPhone,
        ":dept" => $studentDept,
        ":pass" => $hashedPassword
    ]);

    // Redirect to login page with success message
    header("Location: login.php?success=" . urlencode("Account created for " . $admissionNo . ". You can now login."));
    exit;

} catch (PDOException $e) {
    // Check for MySQL duplicate key error code 1062 (admission_no is UNIQUE)
    if ($e->errorInfo[1] == 1062) {
        $msg = "An account with Admission No '" . $admissionNo . "' already exists. Please login instead.";
    } else {
        $msg = "Registration error: " . $e->getMessage();
    }
    header("Location: register.php?error=" . urlencode($msg));
    exit;
}
?>