<?php
// Lab 10: Call session_start() before any output
session_start();

// If student is already logged in, redirect directly to events portal
if (isset($_SESSION["admissionNo"])) {
    header("Location: index.php");
    exit;
}

// Read error message from query string if redirected after failed validation
$error = isset($_GET["error"]) ? trim($_GET["error"]) : "";
// Read success message (shown after successful registration)
$success = isset($_GET["success"]) ? trim($_GET["success"]) : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Registration - IIT (ISM) Dhanbad</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Top Institutional Bar -->
  <header class="top-bar">
    <div class="top-bar-container">
      <div class="brand-section">
        <div class="crest-badge">ISM</div>
        <div class="brand-title">
          <h1>IIT (ISM) DHANBAD</h1>
          <p>Campus Event Registration Hub</p>
        </div>
      </div>
    </div>
  </header>

  <!-- Registration Form -->
  <main class="page-container" style="max-width: 520px; margin-top: 30px;">
    <div class="controls-card">
      <h2 style="margin-top: 0; font-size: 18px; border-bottom: 1px solid #ccc; padding-bottom: 8px;">
        Create Your Account
      </h2>
      <p style="font-size: 13px; color: #555; margin-bottom: 15px;">
        Register once with your details. After that, just login with your admission number and password.
      </p>

      <!-- Lab 10 Section 5.2: Display error safely with htmlspecialchars -->
      <?php if ($error !== ""): ?>
        <div class="modal-alert error" style="display: block; margin-bottom: 15px;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <?php if ($success !== ""): ?>
        <div class="modal-alert success" style="display: block; margin-bottom: 15px;">
          <?php echo htmlspecialchars($success); ?>
        </div>
      <?php endif; ?>

      <!-- Lab 10 Section 4.1: POST form -->
      <form action="register_process.php" method="post">

        <!-- Admission No -->
        <div class="form-group">
          <label for="admissionNo">Admission / Roll No <span class="req">*</span></label>
          <input
            type="text"
            class="form-control"
            id="admissionNo"
            name="admissionNo"
            placeholder="e.g. 24JE0917"
            pattern="[0-9]{2}[A-Za-z]{2}[0-9]{4}"
            title="Format: 2 digits, 2 letters, 4 digits"
            required
            autofocus
          >
          <div class="form-help">e.g. 24JE0917, 24JE0456</div>
        </div>

        <!-- Full Name -->
        <div class="form-group">
          <label for="studentName">Full Name <span class="req">*</span></label>
          <input
            type="text"
            class="form-control"
            id="studentName"
            name="studentName"
            placeholder="e.g. Rohit Garg"
            required
          >
        </div>

        <!-- Email -->
        <div class="form-group">
          <label for="studentEmail">Institute / Personal Email <span class="req">*</span></label>
          <input
            type="email"
            class="form-control"
            id="studentEmail"
            name="studentEmail"
            placeholder="e.g. rohit.24je0917@iitism.ac.in"
            required
          >
        </div>

        <!-- Phone -->
        <div class="form-group">
          <label for="studentPhone">Mobile Number <span class="req">*</span></label>
          <input
            type="tel"
            class="form-control"
            id="studentPhone"
            name="studentPhone"
            placeholder="e.g. 9876543210"
            required
          >
        </div>

        <!-- Department -->
        <div class="form-group">
          <label for="studentDepartment">Academic Department <span class="req">*</span></label>
          <select class="form-control" id="studentDepartment" name="studentDepartment" required>
            <option value="" disabled selected>-- Select Department --</option>
            <option value="Computer Science &amp; Engineering">Computer Science &amp; Engineering</option>
            <option value="Mining Engineering">Mining Engineering</option>
            <option value="Petroleum Engineering">Petroleum Engineering</option>
            <option value="Electronics Engineering">Electronics Engineering</option>
            <option value="Electrical Engineering">Electrical Engineering</option>
            <option value="Mechanical Engineering">Mechanical Engineering</option>
            <option value="Civil Engineering">Civil Engineering</option>
            <option value="Chemical Engineering">Chemical Engineering</option>
            <option value="Applied Geology &amp; Geophysics">Applied Geology &amp; Geophysics</option>
            <option value="Mathematics &amp; Computing">Mathematics &amp; Computing</option>
            <option value="Management Studies">Management Studies</option>
          </select>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label for="studentPassword">Set Password <span class="req">*</span></label>
          <input
            type="password"
            class="form-control"
            id="studentPassword"
            name="studentPassword"
            placeholder="Choose a password"
            minlength="4"
            required
          >
          <div class="form-help">Minimum 4 characters</div>
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
          <label for="confirmPassword">Confirm Password <span class="req">*</span></label>
          <input
            type="password"
            class="form-control"
            id="confirmPassword"
            name="confirmPassword"
            placeholder="Re-enter your password"
            minlength="4"
            required
          >
        </div>

        <div style="margin-top: 15px;">
          <button type="submit" class="btn-primary" style="width: 100%; padding: 8px;">
            Register
          </button>
        </div>
      </form>

      <p style="font-size: 13px; color: #555; margin-top: 15px; text-align: center;">
        Already have an account? <a href="login.php">Login here</a>
      </p>
    </div>
  </main>

</body>
</html>

