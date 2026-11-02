<?php
// Lab 10: Call session_start() before any output
session_start();

// Lab 10 Section 7.5: Session guard - checks admissionNo and isAdmin
if (!isset($_SESSION["admissionNo"]) || empty($_SESSION["isAdmin"])) {
    header("Location: login.php");
    exit;
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * CHEAT SHEET Q50: Protect admin route with secret passcode (?admin_key=iitism2026)
 * HOW TO USE: Add this gatekeeper check right here:
 * if (isset($_GET["admin_key"]) && $_GET["admin_key"] === "iitism2026") {
 *     $_SESSION["admin_verified"] = true;
 * }
 * if (empty($_SESSION["admin_verified"])) {
 *     header("Location: login.php?error=" . urlencode("Admin clearance key required."));
 *     exit;
 * }
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 */
// Extra Lab Section 6: Include PDO database connection
require_once "config/db.php";

$msg = isset($_GET["msg"]) ? trim($_GET["msg"]) : "";

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * CHEAT SHEET Q22: Add Event Status Toggle (Active vs Postponed)
 * SQL: ALTER TABLE events ADD COLUMN is_active TINYINT DEFAULT 1;
 * HOW TO USE: Add this status toggle handler here:
 * if (isset($_GET["toggle_status"])) {
 *     $tId = intval($_GET["toggle_status"]);
 *     $pdo->query("UPDATE events SET is_active = NOT is_active WHERE id = $tId");
 *     header("Location: admin.php");
 *     exit;
 * }
 * And add toggle link in card: <a href="admin.php?toggle_status=<?php echo $ev['id']; ?>">Toggle Status</a>
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 */
// Handle Delete Event (GET)
if (isset($_GET["delete"])) {
    $delId = intval($_GET["delete"]);
    if ($delId > 0) {
        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
         * Q14 EXAM TASK: Add Category Dropdown (currently hardcoded as 'Campus')
         * HOW TO USE: Read category from POST and change the INSERT:
         * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
         * $category = isset($_POST["category"]) ? trim($_POST["category"]) : "Campus";
         * $stmt = $pdo->prepare("INSERT INTO events (title, category, description, date_time, venue, capacity) VALUES (?, ?, ?, ?, ?, ?)");
         * $stmt->execute([$title, $category, $desc, $dateTime, $venue, $capacity]);
         * (Also add <select name="category"> in HTML form below)
         */
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$delId]);
        header("Location: admin.php?msg=deleted");
        exit;
    }
}
/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Q18 EXAM TASK: Add 'Reset Seats' - clear all registrations for an event
 * HOW TO USE: Uncomment this handler here + add HTML link next to Delete button
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * if (isset($_GET["clear_registrations"])) {
 *     $clrId = intval($_GET["clear_registrations"]);
 *     $stmt = $pdo->prepare("DELETE FROM registrations WHERE event_id = ?");
 *     $stmt->execute([$clrId]);
 *     header("Location: admin.php?msg=cleared");
 *     exit;
 * }
 */

// Handle Publish Event (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "publish") {
    $title    = isset($_POST["title"])       ? trim($_POST["title"])       : "";
    $desc     = isset($_POST["description"]) ? trim($_POST["description"]) : "";
    $dateTime = isset($_POST["date_time"])   ? trim($_POST["date_time"])   : "";
    $venue    = isset($_POST["venue"])       ? trim($_POST["venue"])       : "";
    $capacity = isset($_POST["capacity"])    ? intval($_POST["capacity"])  : 0;

    if ($title !== "" && $desc !== "" && $dateTime !== "" && $venue !== "" && $capacity > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO events (title, category, description, date_time, venue, capacity)
            VALUES (?, 'Campus', ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $desc, $dateTime, $venue, $capacity]);
        header("Location: admin.php?msg=published");
        exit;
    } else {
        header("Location: admin.php?msg=error");
        exit;
    }
}
/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Q16 EXAM TASK: Allow admin to update event capacity
 * HOW TO USE: Uncomment this handler here + add HTML form inside event card:
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "update_capacity") {
 *     $evId = intval($_POST["event_id"]);
 *     $newCap = intval($_POST["new_capacity"]);
 *     if ($evId > 0 && $newCap > 0) {
 *         $stmt = $pdo->prepare("UPDATE events SET capacity = ? WHERE id = ?");
 *         $stmt->execute([$newCap, $evId]);
 *         header("Location: admin.php?msg=updated");
 *         exit;
 *     }
 * }
 */

// Query all events and their registration counts
$events = [];
try {
    $stmt = $pdo->query("
        SELECT 
            e.id, e.title, e.description, e.date_time, e.venue, e.capacity,
            COUNT(r.id) AS registered_count,
            (e.capacity - COUNT(r.id)) AS remaining_seats
        FROM events e
        LEFT JOIN registrations r ON e.id = r.event_id
        GROUP BY e.id
        ORDER BY e.date_time ASC
    ");
    $events = $stmt->fetchAll();
} catch (Exception $e) {
    $events = [];
}

// Query all registrants
$registrantsByEvent = [];
try {
    $stmtReg = $pdo->query("
        SELECT event_id, student_name, admission_no, email, phone, department, registered_at
        FROM registrations
        ORDER BY registered_at ASC
    ");
    $allRegs = $stmtReg->fetchAll();
    foreach ($allRegs as $row) {
        $registrantsByEvent[$row["event_id"]][] = $row;
    }
} catch (Exception $e) {
    $registrantsByEvent = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - IIT (ISM) Dhanbad</title>
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
          <p>Admin Event Management Dashboard</p>
        </div>
      </div>

      <nav class="nav-actions">
        <a href="logout.php" class="btn-nav" style="text-decoration: none;">
          Logout
        </a>
      </nav>
    </div>
  </header>

  <main class="page-container" style="max-width: 860px; margin-top: 20px;">

    <!-- Notification Messages -->
    <?php if ($msg === "published"): ?>
      <div class="modal-alert success" style="display: block; margin-bottom: 15px;">
        Event published successfully!
      </div>
    <?php elseif ($msg === "deleted"): ?>
      <div class="modal-alert success" style="display: block; margin-bottom: 15px;">
        Event and associated registrations deleted.
      </div>
    <?php elseif ($msg === "error"): ?>
      <div class="modal-alert error" style="display: block; margin-bottom: 15px;">
        Please fill in all event fields correctly. Capacity must be greater than 0.
      </div>
    <?php endif; ?>

    <!-- 1. Publish Event Form -->
    <div class="controls-card" style="margin-bottom: 25px;">
      <h2 style="margin-top: 0; font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 6px;">
        Publish New Campus Event
      </h2>
      <form action="admin.php" method="post">
        <input type="hidden" name="action" value="publish">

        <div class="form-group">
          <label for="title">Event Title <span class="req">*</span></label>
          <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Concetto: AI Hackathon" required>
        </div>

        <div class="form-group">
          <label for="description">Event Description <span class="req">*</span></label>
          <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  Q26 EXAM TASK: Add character counter on description textarea
  HOW TO USE: Replace the <textarea> line below with these two lines:
  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  <textarea class="form-control" id="description" name="description" rows="2" maxlength="300"
            placeholder="Brief details about the event..."
            oninput="$('#charCount').text(300 - this.value.length + ' chars remaining')" required></textarea>
  <small id="charCount" style="color:#666;">300 chars remaining</small>
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
          <textarea class="form-control" id="description" name="description" rows="2" placeholder="Brief details about the event..." required></textarea>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label for="date_time">Date &amp; Time <span class="req">*</span></label>
            <input type="datetime-local" class="form-control" id="date_time" name="date_time" required>
          </div>
          <div class="form-group">
            <label for="venue">Venue <span class="req">*</span></label>
            <input type="text" class="form-control" id="venue" name="venue" placeholder="e.g. Penman Auditorium, SAC" required>
          </div>
        </div>

        <div class="form-group">
          <label for="capacity">Total Seat Capacity <span class="req">*</span></label>
          <input type="number" class="form-control" id="capacity" name="capacity" min="1" max="1000" placeholder="e.g. 50" required>
        </div>

        <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
          Q14 EXAM TASK (HTML): Add Category dropdown to Publish form
          HOW TO USE: Add this form-group before the submit button below:
          ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
          <div class="form-group">
            <label for="category">Event Category <span class="req">*</span></label>
            <select class="form-control" id="category" name="category" required>
              <option value="Technical">Technical</option>
              <option value="Cultural">Cultural</option>
              <option value="Sports">Sports</option>
              <option value="Literary">Literary</option>
              <option value="Campus">Campus (General)</option>
            </select>
          </div>
        ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
        <div style="margin-top: 10px;">
          <button type="submit" class="btn-primary" style="padding: 6px 14px;">
            Publish Event
          </button>
        </div>
      </form>
    </div>

    <!-- 2. Events & Registrants Queue -->
    <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
      Q39 EXAM TASK: Add 'Print Attendance Sheet' button
      HOW TO USE: Add inside events-section-header div:
      <button onclick="window.print()" class="btn-secondary" style="font-size:11px;padding:4px 10px;">Print Attendance Sheet</button>
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
    <div class="events-section-header">
      <h2>Existing Events (<?php echo count($events); ?>)</h2>
    </div>

    <div class="events-grid">
      <?php if (empty($events)): ?>
        <p style="color: #666;">No campus events published yet.</p>
      <?php else: ?>
        <?php foreach ($events as $ev): 
          $dt = new DateTime($ev["date_time"]);
          $evRegistrants = $registrantsByEvent[$ev["id"]] ?? [];
        ?>
        <div class="event-card" id="admin-event-<?php echo $ev["id"]; ?>">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
            <div>
              <h3 class="event-title"><?php echo htmlspecialchars($ev["title"]); ?></h3>
              <p class="event-desc"><?php echo htmlspecialchars($ev["description"]); ?></p>
            </div>
            <div>
              <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                Q37 EXAM TASK: Add 'Export CSV' button
                <a href="export_csv.php?event_id=<?php echo $ev['id']; ?>" class="btn-secondary" style="text-decoration:none;display:inline-block;padding:4px 8px;font-size:12px;">Export CSV</a>
                Q18 EXAM TASK: Add 'Reset Seats' button
                <a href="admin.php?clear_registrations=<?php echo $ev['id']; ?>" onclick="return confirm('Reset all seats?');" class="btn-secondary" style="text-decoration:none;display:inline-block;padding:4px 8px;font-size:12px;">Reset Seats</a>
              ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
              <a href="admin.php?delete=<?php echo $ev["id"]; ?>" 
                 onclick="return confirm('Are you sure you want to delete this event? All its registrations will be deleted too.');"
                 class="btn-cancel-reg" 
                 style="text-decoration: none; display: inline-block; padding: 4px 10px; font-size: 12px; white-space: nowrap;">
                Delete
              </a>
            </div>
          </div>

          <div class="event-meta-list">
            <div class="meta-item">
              <span><strong>Date:</strong> <?php echo $dt->format("M d, Y • h:i A"); ?></span>
            </div>
            <div class="meta-item">
              <span><strong>Venue:</strong> <?php echo htmlspecialchars($ev["venue"]); ?></span>
            </div>
          </div>

          <div class="seat-capacity-block">
            <div class="seat-stats-row">
              <div class="stat-seat-col">
                <div class="val"><?php echo intval($ev["capacity"]); ?></div>
                <div class="lbl">Total Capacity</div>
              </div>
              <div class="stat-seat-col">
                <div class="val"><?php echo intval($ev["registered_count"]); ?></div>
                <div class="lbl">Registered</div>
              </div>
              <div class="stat-seat-col highlight-remaining">
                <div class="val"><?php echo max(0, intval($ev["remaining_seats"])); ?></div>
                <div class="lbl">Remaining Seats</div>
              </div>
            </div>
          </div>

          <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
          Q16 EXAM TASK (HTML): Update capacity form inside event card
          HOW TO USE: Add this form right here after seat-capacity-block:
          <form action="admin.php" method="post" style="display:inline-flex;gap:6px;margin-top:8px;">
            <input type="hidden" name="action" value="update_capacity">
            <input type="hidden" name="event_id" value="<?php echo $ev['id']; ?>">
            <input type="number" name="new_capacity" value="<?php echo $ev['capacity']; ?>" style="width:70px;" min="1" required>
            <button type="submit" class="btn-secondary" style="padding:2px 8px;font-size:11px;">Update Capacity</button>
          </form>
        ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
          <!-- 3. Registrants View -->
          <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            Q38 EXAM TASK: Show department-wise breakdown under each event
            HOW TO USE: Add this PHP block after Registrants count line:
            <?php
              $deptCounts = [];
              foreach ($evRegistrants as $reg) { $deptCounts[$reg['department']] = ($deptCounts[$reg['department']] ?? 0) + 1; }
            ?>
            <?php if (!empty($deptCounts)): ?>
            <div style="font-size:11px;color:#555;margin:4px 0 8px;">
              <strong>Dept Breakdown: </strong>
              <?php foreach ($deptCounts as $dept => $c): echo htmlspecialchars($dept) . ': <strong>' . $c . '</strong> | '; endforeach; ?>
            </div>
            <?php endif; ?>
            Q17 EXAM TASK: Add live search/filter above registrants table
            HOW TO USE: Add before <table> element:
            <input type="text" placeholder="Filter registrants by roll or name..."
                   onkeyup="var v=this.value.toLowerCase();$(this).next('table').find('tbody tr').filter(function(){$(this).toggle($(this).text().toLowerCase().indexOf(v)>-1);});"
                   style="width:100%;padding:4px;font-size:11px;margin-bottom:4px;border:1px solid #ccc;">
          ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
          <div style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 8px;">
            <strong style="font-size: 13px;">Registrants (<?php echo count($evRegistrants); ?>):</strong>
            <?php if (empty($evRegistrants)): ?>
              <p style="font-size: 12px; color: #777; margin: 4px 0 0;">No students registered for this event yet.</p>
            <?php else: ?>
              <table style="width: 100%; font-size: 12px; margin-top: 6px; border-collapse: collapse;">
                <thead>
                  <tr style="background: #f5f5f5; text-align: left; border-bottom: 1px solid #ddd;">
                    <th style="padding: 4px;">#</th>
                    <th style="padding: 4px;">Admission No</th>
                    <th style="padding: 4px;">Student Name</th>
                    <th style="padding: 4px;">Email</th>
                    <th style="padding: 4px;">Department</th>
                    <th style="padding: 4px;">Registered At</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($evRegistrants as $idx => $r): 
                    $rDt = new DateTime($r["registered_at"]);
                  ?>
                  <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 4px;"><?php echo ($idx + 1); ?></td>
                    <td style="padding: 4px; font-weight: bold;"><?php echo htmlspecialchars($r["admission_no"]); ?></td>
                    <td style="padding: 4px;"><?php echo htmlspecialchars($r["student_name"]); ?></td>
                    <td style="padding: 4px;"><?php echo htmlspecialchars($r["email"]); ?></td>
                    <td style="padding: 4px;"><?php echo htmlspecialchars($r["department"]); ?></td>
                    <td style="padding: 4px; color: #666;"><?php echo $rDt->format("M d, h:i A"); ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>

        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </main>

  <script src="assets/js/jquery.min.js"></script>

</body>
</html>

