<?php
// Lab 10: Call session_start() before any output
session_start();

// If student is already logged in, redirect directly to events portal
if (isset($_SESSION["admissionNo"])) {
    header("Location: index.php");
    exit;
}

// Read error/success message from query string if redirected
$error   = isset($_GET["error"])   ? trim($_GET["error"])   : "";
$success = isset($_GET["success"]) ? trim($_GET["success"]) : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Login • IIT (ISM) Dhanbad</title>
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

  <!-- Login Form -->
  <main class="page-container" style="max-width: 420px; margin-top: 30px;">
    <div class="controls-card">
      <h2 style="margin-top: 0; font-size: 18px; border-bottom: 1px solid #ccc; padding-bottom: 8px;">
        Student Login
      </h2>

      <!-- Lab 10 Section 5.2: Display messages safely with htmlspecialchars -->
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
      <form action="login_process.php" method="post">

        <!-- Admission No -->
        <div class="form-group">
          <label for="admissionNo">Admission / Roll No <span class="req">*</span></label>
          <input
            type="text"
            class="form-control"
            id="admissionNo"
            name="admissionNo"
            placeholder="e.g. 24JE0917"
            required
            autofocus
          >
        </div>

        <!-- Password -->
        <div class="form-group">
          <label for="studentPassword">Password <span class="req">*</span></label>
          <input
            type="password"
            class="form-control"
            id="studentPassword"
            name="studentPassword"
            placeholder="Enter your password"
            required
          >
        </div>

        <div style="margin-top: 15px;">
          <button type="submit" class="btn-primary" style="width: 100%; padding: 8px;">
            Login
          </button>
        </div>
      </form>

      <p style="font-size: 13px; color: #555; margin-top: 15px; text-align: center;">
        Don't have an account? <a href="register.php">Register here</a>
      </p>
    </div>
  </main>

</body>
</html>
