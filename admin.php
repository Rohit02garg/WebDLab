<?php
// Lab 10: Call session_start() before any output
session_start();

// Lab 10 Section 7.5: Session guard - only allow logged in admin
if (empty($_SESSION["isAdmin"])) {
    header("Location: login.php");
    exit;
}

// Extra Lab Section 6: Include PDO database connection
require_once "config/db.php";

$msg = isset($_GET["msg"]) ? trim($_GET["msg"]) : "";

// Handle Delete Event (GET)
if (isset($_GET["delete"])) {
    $delId = intval($_GET["delete"]);
    if ($delId > 0) {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$delId]);
        header("Location: admin.php?msg=deleted");
        exit;
    }
}

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

        <div style="margin-top: 10px;">
          <button type="submit" class="btn-primary" style="padding: 6px 14px;">
            Publish Event
          </button>
        </div>
      </form>
    </div>

    <!-- 2. Events & Registrants Queue -->
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

          <!-- 3. Registrants View -->
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

</body>
</html>

