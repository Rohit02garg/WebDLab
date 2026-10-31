<?php
// Start session for student registration state (Lab 10 Section 6.1)
session_start();

// Lab 10 Section 7.5: Session guard - if student is not logged in, redirect to login.php
if (!isset($_SESSION["admissionNo"])) {
    header("Location: login.php");
    exit;
}

// Display user-supplied session values safely (Lab 10 Section 5.2)
$currentStudentName = htmlspecialchars($_SESSION["studentName"]);
$currentAdmissionNo = htmlspecialchars($_SESSION["admissionNo"]);

// Include database connection (Extra Lab Section 6)
require_once "config/db.php";

// Fetch initial event list from database for display
$initialEvents = [];
try {
    $stmt = $pdo->query("
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
        GROUP BY e.id
        ORDER BY e.date_time ASC
    ");
    $initialEvents = $stmt->fetchAll();
} catch (Exception $e) {
    // If database connection fails, fallback to empty array
    $initialEvents = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Campus Event Registration Portal for IIT (ISM) Dhanbad. Real-time seat tracking, dynamic registration, and cancellation for campus festivals and technical competitions.">
  <title>IIT (ISM) Dhanbad • Campus Event Registration Portal</title>
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

  <!-- =========================================================================
       Top Institutional Navigation Header
       ========================================================================= -->
  <header class="top-bar">
    <div class="top-bar-container">
      <div class="brand-section">
        <div class="crest-badge" title="Indian Institute of Technology (ISM) Dhanbad">ISM</div>
        <div class="brand-title">
          <h1>IIT (ISM) DHANBAD</h1>
          <p>Campus Event Registration Hub</p>
        </div>
      </div>

      <nav class="nav-actions">
        <button class="btn-nav active" id="navAllEvents">
          <span>🏛️</span> Events
        </button>
        <button class="btn-nav" id="navMyRegistrations">
          <span>🎟️</span> My Registrations
        </button>
        <span style="display: inline-flex; align-items: center; font-size: 12px; color: #fff; padding: 2px 6px; background: #555;">
          👤 <?php echo $currentStudentName; ?> (<?php echo $currentAdmissionNo; ?>)
        </span>
        <?php if (!empty($_SESSION["isAdmin"])): ?>
        <a href="admin.php" class="btn-nav" style="text-decoration: none; display: inline-flex; align-items: center; background: #880000; color: #fff;">
          ⚙️ Admin Panel
        </a>
        <?php endif; ?>
        <a href="logout.php" class="btn-nav" style="text-decoration: none; display: inline-flex; align-items: center;">
          🚪 Logout
        </a>
      </nav>
    </div>
  </header>

  <!-- =========================================================================
       Main Content Area
       ========================================================================= -->
  <main class="main-wrapper page-container">
    
    <!-- Events Section Header -->
    <div class="events-section-header">
      <h2>Upcoming Campus Events</h2>
      <span class="events-count-pill" id="eventsCountPill">8 Available</span>
    </div>

    <!-- Events Grid (Updated Dynamically via AJAX) -->
    <div class="events-grid" id="eventsGrid">
      <?php foreach ($initialEvents as $event): 
        $capacity = intval($event["capacity"]);
        $registered = intval($event["registered_count"]);
        $remaining = max(0, $capacity - $registered);
        $percentFilled = ($capacity > 0) ? min(100, round(($registered / $capacity) * 100)) : 100;
        $isFull = ($remaining <= 0);
        $isFillingFast = !$isFull && ($remaining <= ceil($capacity * 0.25) || $remaining <= 3);

        $progressClass = "";
        $remClass = "";

        if ($isFull) {
            $progressClass = "full";
            $remClass = "full";
        } elseif ($isFillingFast) {
            $progressClass = "fast";
            $remClass = "fast";
        }

        $dt = new DateTime($event["date_time"]);
        $formattedDate = $dt->format("M d, Y");
        $formattedTime = $dt->format("h:i A");
      ?>
      <div class="event-card <?php echo $isFull ? 'is-full' : ''; ?>" id="event-card-<?php echo $event["id"]; ?>" data-id="<?php echo $event["id"]; ?>">
        <!-- Card Body -->
        <div class="card-body">
          <h3 class="event-title"><?php echo htmlspecialchars($event["title"]); ?></h3>
          <p class="event-desc"><?php echo htmlspecialchars($event["description"]); ?></p>

          <!-- Venue & Timing Meta -->
          <div class="event-meta-list">
            <div class="meta-item">
              <span class="meta-icon">📅</span>
              <span><strong>Date:</strong> <?php echo $formattedDate; ?> • <?php echo $formattedTime; ?></span>
            </div>
            <div class="meta-item">
              <span class="meta-icon">📍</span>
              <span><strong>Venue:</strong> <?php echo htmlspecialchars($event["venue"]); ?></span>
            </div>
          </div>

          <!-- Live Dynamic Seat Capacity Counter -->
          <div class="seat-capacity-block">
            <div class="seat-stats-row">
              <div class="stat-seat-col">
                <div class="val" id="cap-<?php echo $event["id"]; ?>"><?php echo $capacity; ?></div>
                <div class="lbl">Total Capacity</div>
              </div>
              <div class="stat-seat-col">
                <div class="val" id="reg-<?php echo $event["id"]; ?>"><?php echo $registered; ?></div>
                <div class="lbl">Registered</div>
              </div>
              <div class="stat-seat-col highlight-remaining <?php echo $remClass; ?>" id="rem-col-<?php echo $event["id"]; ?>">
                <div class="val" id="rem-<?php echo $event["id"]; ?>"><?php echo $remaining; ?></div>
                <div class="lbl">Remaining Seats</div>
              </div>
            </div>

            <!-- Dynamic Animated Progress Bar -->
            <div class="progress-container" title="<?php echo $percentFilled; ?>% seats filled">
              <div class="progress-bar-fill <?php echo $progressClass; ?>" id="progress-<?php echo $event["id"]; ?>" style="width: <?php echo $percentFilled; ?>%;"></div>
            </div>
          </div>
        </div>

        <!-- Card Action Buttons -->
        <div class="card-footer">
          <button class="btn-register" id="btn-reg-<?php echo $event["id"]; ?>" <?php echo $isFull ? 'disabled' : ''; ?> onclick="openRegistrationModal(<?php echo $event["id"]; ?>)">
            <?php echo $isFull ? '🚫 Event Full' : '🎟️ Register Now'; ?>
          </button>
          <button class="btn-cancel-seat" onclick="openQuickCancelModal(<?php echo $event["id"]; ?>, '<?php echo htmlspecialchars(addslashes($event["title"])); ?>')">
            Cancel Seat
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </main>

  <!-- =========================================================================
       Modal 1: Event Registration Confirmation (One-Click, data from session)
       ========================================================================= -->
  <div class="modal-backdrop" id="registrationModal">
    <div class="modal-dialog" style="max-width: 440px;">
      <div class="modal-header">
        <h3>Confirm Event Registration</h3>
        <button class="modal-close-btn" aria-label="Close modal">&times;</button>
      </div>
      <div class="modal-body">
        <!-- Event Summary Box -->
        <div class="modal-event-summary">
          <h4 id="modalEventTitle">Event Name</h4>
          <p id="modalEventMeta">Venue & Timing</p>
          <p id="modalEventRemaining" style="color: green;"></p>
        </div>

        <!-- Student Details (Read-only summary from session) -->
        <div style="background: #f9f9f9; border: 1px solid #ddd; padding: 10px 12px; margin-bottom: 12px; font-size: 13px; line-height: 1.6;">
          <div style="font-weight: bold; margin-bottom: 4px; color: #333;">Registering as:</div>
          <div><strong>Name:</strong> <span id="confirmStudentName"><?php echo $currentStudentName; ?></span></div>
          <div><strong>Admission No:</strong> <span id="confirmAdmissionNo"><?php echo $currentAdmissionNo; ?></span></div>
          <div><strong>Email:</strong> <span id="confirmEmail"><?php echo htmlspecialchars($_SESSION["studentEmail"] ?? ""); ?></span></div>
          <div><strong>Department:</strong> <span id="confirmDepartment"><?php echo htmlspecialchars($_SESSION["studentDepartment"] ?? ""); ?></span></div>
        </div>

        <!-- Alert Notification for Duplicate/Overcapacity Errors -->
        <div class="modal-alert" id="regAlert"></div>

        <!-- Registration Form (All student data stored in session, zero inputs to type) -->
        <form id="registrationForm" novalidate>
          <input type="hidden" id="regEventId" name="event_id">
          <input type="hidden" id="regStudentName" name="student_name" value="<?php echo $currentStudentName; ?>">
          <input type="hidden" id="regAdmissionNo" name="admission_no" value="<?php echo $currentAdmissionNo; ?>">
          <input type="hidden" id="regEmail" name="email" value="<?php echo htmlspecialchars($_SESSION["studentEmail"] ?? ""); ?>">
          <input type="hidden" id="regPhone" name="phone" value="<?php echo htmlspecialchars($_SESSION["studentPhone"] ?? ""); ?>">
          <input type="hidden" id="regDepartment" name="department" value="<?php echo htmlspecialchars($_SESSION["studentDepartment"] ?? ""); ?>">

          <div class="modal-footer">
            <button type="button" class="btn-secondary btn-modal-close">Cancel</button>
            <button type="submit" class="btn-primary" id="btnSubmitReg">
              <span>🎟️</span> Confirm Registration
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       Modal 2: Quick Cancel Seat Modal
       ========================================================================= -->
  <div class="modal-backdrop" id="quickCancelModal">
    <div class="modal-dialog">
      <div class="modal-header">
        <h3>Cancel Event Seat</h3>
        <button class="modal-close-btn" aria-label="Close modal">&times;</button>
      </div>
      <div class="modal-body">
        <p style="margin-bottom: 10px; font-size: 13px; color: #555;">
          To cancel your registration for <strong id="quickCancelEventTitle"></strong> and free your seat, enter your Admission Number:
        </p>

        <div class="modal-alert" id="quickCancelAlert"></div>

        <form id="quickCancelForm">
          <input type="hidden" id="quickCancelEventId">
          <div class="form-group">
            <label for="quickCancelAdmissionNo">Your Admission Number / Roll No <span class="req">*</span></label>
            <input type="text" class="form-control" id="quickCancelAdmissionNo" placeholder="e.g. 24JE0456" required>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn-secondary btn-modal-close">Back</button>
            <button type="submit" class="btn-cancel-reg" id="btnSubmitQuickCancel">
              Confirm Cancellation
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       Modal 3: "My Registrations" Management Drawer
       ========================================================================= -->
  <div class="modal-backdrop" id="myRegistrationsModal">
    <div class="modal-dialog registrations-modal-dialog">
      <div class="modal-header">
        <h3>My Registered Events</h3>
        <button class="modal-close-btn" aria-label="Close modal">&times;</button>
      </div>
      <div class="modal-body">
        <!-- Student Roll No Lookup Bar -->
        <div class="lookup-bar">
          <input type="text" class="form-control" id="lookupAdmissionNo" value="<?php echo $currentAdmissionNo; ?>" placeholder="Enter Admission Number (e.g. 24JE0101, 24JE0917)">
          <button class="btn-primary" id="btnLookupReg" style="white-space: nowrap;">
            <span>🔍</span> Find My Events
          </button>
        </div>

        <!-- Registrations Container -->
        <div id="myRegistrationsList">
          <div class="empty-state">
            <div class="empty-icon">🎟️</div>
            <h4>View or Manage Registrations</h4>
            <p>Enter your IIT (ISM) Admission Number above to view all events you are booked for and release seats if needed.</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary btn-modal-close">Close</button>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       Toast Notification System Container
       ========================================================================= -->
  <div class="toast-container" id="toastContainer"></div>

  <!-- =========================================================================
       Client Scripts: jQuery 3.7.1 & Main App Logic
       ========================================================================= -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>window.jQuery || document.write('<script src="assets/js/jquery.min.js"><\/script>');</script>
  <script>
    window.sessionStudent = {
      admissionNo: <?php echo json_encode($_SESSION["admissionNo"]); ?>,
      studentName: <?php echo json_encode($_SESSION["studentName"]); ?>,
      email: <?php echo json_encode($_SESSION["studentEmail"] ?? ""); ?>,
      phone: <?php echo json_encode($_SESSION["studentPhone"] ?? ""); ?>,
      department: <?php echo json_encode($_SESSION["studentDepartment"] ?? ""); ?>
    };
  </script>
  <script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
