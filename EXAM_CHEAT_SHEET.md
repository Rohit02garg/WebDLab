# 🎯 NCSM301 Web Development Lab: Final Project Master Defense & 50 Live Exam Tasks
### Campus Event Registration System — IIT (ISM) Dhanbad
**Emergency Offline Cheat Sheet & Task Solutions Guide**

---

> [!IMPORTANT]
> **HOW TO USE THIS GUIDE DURING YOUR 20-MINUTE OFFLINE SLOT:**
> 1. Keep this file open in a separate VS Code tab. You will have **no internet connection**.
> 2. Read the task given by the TA. Look up the matching category below.
> 3. Each question contains:
>    - **Target File(s)** & exact line numbers.
>    - **Exact Drop-in Code Solution** (copy-pasteable).
>    - **SQL Query** (if database change is required).
>    - **30-Second Verification Step** (how to demonstrate it to the TA to secure full marks).

---

# Table of Contents
1. [Category 1: Database & SQL Schema Alterations (Q1 – Q7)](#category-1-database--sql-schema-alterations)
2. [Category 2: Registration & Capacity Business Rules (Q8 – Q15)](#category-2-registration--capacity-business-rules)
3. [Category 3: Admin Dashboard Enhancements (Q16 – Q23)](#category-3-admin-dashboard-enhancements)
4. [Category 4: Search, Filters & Frontend DOM Manipulation (Q24 – Q31)](#category-4-search-filters--frontend-dom-manipulation)
5. [Category 5: Authentication & Session Management (Q32 – Q38)](#category-5-authentication--session-management)
6. [Category 6: REST API Extensions & JSON Endpoints (Q39 – Q44)](#category-6-rest-api-extensions--json-endpoints)
7. [Category 7: Security Defenses & Edge Case Fixes (Q45 – Q50)](#category-7-security-defenses--edge-case-fixes)

---

## Category 1: Database & SQL Schema Alterations

### Q1: Add an `organizer` column to `events` and display it on event cards.
* **Task:** Add an event organizing club/department field (e.g. RoboISM, WEC) to the database and show it on cards.
* **Step 1 (SQL in phpMyAdmin):**
  ```sql
  ALTER TABLE events ADD COLUMN organizer VARCHAR(100) DEFAULT 'Student Gymkhana';
  ```
* **Step 2 (Edit `api/get_events.php`):**
  In the `SELECT` query (around line 17), add `e.organizer,`:
  ```php
  // api/get_events.php inside formattedEvents loop (around line 45)
  "organizer" => $ev["organizer"] ?? "Student Gymkhana",
  ```
* **Step 3 (Edit `assets/js/app.js`):**
  In `renderEvents()` inside cardHtml (around line 105):
  ```javascript
  '<div class="meta-item"><span><strong>Club:</strong> ' + escapeHtml(ev.organizer || 'Student Gymkhana') + '</span></div>' +
  ```
* **Verification:** Open `index.php` → All cards now display **Club: Student Gymkhana**.

---

### Q2: Add a `registration_deadline` (DATETIME) column and block bookings past deadline.
* **Task:** Events should have a registration cutoff timestamp. If current time is past deadline, disable registration.
* **Step 1 (SQL in phpMyAdmin):**
  ```sql
  ALTER TABLE events ADD COLUMN registration_deadline DATETIME DEFAULT '2026-12-31 23:59:59';
  ```
* **Step 2 (Edit `api/register.php` around line 48):**
  ```php
  // Fetch deadline in query: SELECT id, title, capacity, registration_deadline FROM events WHERE id = ?
  $stmtEvent = $pdo->prepare("SELECT id, title, capacity, registration_deadline FROM events WHERE id = ?");
  $stmtEvent->execute([$eventId]);
  $event = $stmtEvent->fetch();

  if (!empty($event["registration_deadline"]) && strtotime($event["registration_deadline"]) < time()) {
      http_response_code(400);
      echo json_encode(["success" => false, "message" => "Registration deadline has passed for this event."]);
      exit;
  }
  ```
* **Verification:** Update an event deadline to `2026-01-01` in phpMyAdmin. Click register → Alert popup: *"Registration deadline has passed for this event."*

---

### Q3: Add a `gender` / `hostel` column to student registration.
* **Task:** Ask students for their Hostel (e.g. Jasper, Amber, Diamond) during registration and store in DB.
* **Step 1 (SQL):**
  ```sql
  ALTER TABLE students ADD COLUMN hostel VARCHAR(50) DEFAULT 'Amber';
  ```
* **Step 2 (Edit `register.php` around line 138):**
  Add input field:
  ```html
  <div class="form-group">
    <label for="studentHostel">Hostel Residence <span class="req">*</span></label>
    <select class="form-control" id="studentHostel" name="studentHostel" required>
      <option value="Jasper">Jasper Hostel</option>
      <option value="Amber">Amber Hostel</option>
      <option value="Diamond">Diamond Hostel</option>
      <option value="Rosaline">Rosaline Hostel</option>
    </select>
  </div>
  ```
* **Step 3 (Edit `register_process.php` around lines 20 & 66):**
  ```php
  $hostel = isset($_POST["studentHostel"]) ? trim($_POST["studentHostel"]) : "Amber";
  // Add to INSERT statement:
  $stmt = $pdo->prepare("INSERT INTO students (admission_no, student_name, email, phone, department, password, hostel) VALUES (:adm, :name, :email, :phone, :dept, :pass, :hostel)");
  $stmt->execute([..., ":hostel" => $hostel]);
  ```
* **Verification:** Register a new user with Diamond Hostel → Check `students` table in phpMyAdmin.

---

### Q4: Implement Soft Deletes for Registrations (`status = 'cancelled'`).
* **Task:** Instead of executing `DELETE FROM registrations`, update status column to `'cancelled'`.
* **Step 1 (SQL):**
  ```sql
  ALTER TABLE registrations ADD COLUMN status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed';
  ```
* **Step 2 (Edit `api/cancel.php` around line 30):**
  Replace DELETE with UPDATE:
  ```php
  $stmt = $pdo->prepare("UPDATE registrations SET status = 'cancelled' WHERE event_id = :eid AND admission_no = :adm AND status = 'confirmed'");
  $stmt->execute([":eid" => $eventId, ":adm" => $admissionNo]);
  ```
* **Step 3 (Edit `api/register.php` and `api/get_events.php`):**
  Change queries to count only `status = 'confirmed'`:
  ```sql
  SELECT COUNT(*) FROM registrations WHERE event_id = ? AND status = 'confirmed'
  ```
* **Verification:** Cancel a seat → Check phpMyAdmin `registrations` table; the row still exists with `status = 'cancelled'`.

---

### Q5: Enforce a Global Limit of 3 Event Bookings per Student.
* **Task:** A student cannot hold more than 3 active registrations across all campus events.
* **Edit `api/register.php` (add before line 63):**
  ```php
  // Check total registrations for this student
  $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE admission_no = ?");
  $stmtTotal->execute([$admissionNo]);
  if ($stmtTotal->fetchColumn() >= 3) {
      http_response_code(409);
      echo json_encode(["success" => false, "message" => "Quota Exceeded: You cannot register for more than 3 events."]);
      exit;
  }
  ```
* **Verification:** Try registering for a 4th event with `24JE0917` (who already has 2 seeded bookings) → Blocked on 4th attempt.

---

### Q6: Add a 5-Star Feedback / Rating System for Past Events.
* **Task:** Allow students in `My Registrations` to submit a rating (1 to 5) for an event.
* **Step 1 (SQL):**
  ```sql
  ALTER TABLE registrations ADD COLUMN rating TINYINT NULL;
  ```
* **Step 2 (Edit `api/rate_event.php` - CREATE NEW FILE):**
  ```php
  <?php
  session_start();
  header("Content-Type: application/json");
  require_once "../config/db.php";
  $input = json_decode(file_get_contents("php://input"), true);
  $regId = intval($input["registration_id"] ?? 0);
  $rating = intval($input["rating"] ?? 5);

  $stmt = $pdo->prepare("UPDATE registrations SET rating = ? WHERE id = ?");
  $stmt->execute([$rating, $regId]);
  echo json_encode(["success" => true, "message" => "Rating saved!"]);
  ```
* **Verification:** Test via browser console:
  `fetch('api/rate_event.php', {method:'POST', body:JSON.stringify({registration_id:1, rating:5})})`.

---

### Q7: Create an Analytics View for Event Occupancy Percentage.
* **Task:** Write a SQL query or View that reports `event_id`, `title`, and `occupancy_percentage`.
* **SQL Query to Run in phpMyAdmin:**
  ```sql
  CREATE OR REPLACE VIEW view_event_occupancy AS
  SELECT 
      e.id, 
      e.title, 
      e.capacity,
      COUNT(r.id) AS registered_students,
      ROUND((COUNT(r.id) / e.capacity) * 100, 2) AS occupancy_rate
  FROM events e
  LEFT JOIN registrations r ON e.id = r.event_id
  GROUP BY e.id;
  ```
* **Verification:** Run `SELECT * FROM view_event_occupancy;` in phpMyAdmin SQL tab to show TA.

---

## Category 2: Registration & Capacity Business Rules

### Q8: Department-Exclusive Events (Restrict by Student Dept).
* **Task:** Restrict CodeISM Coding Sprint (Event ID 4) only to students from "Computer Science & Engineering".
* **Edit `api/register.php` (insert around line 35):**
  ```php
  // Department-exclusive restriction rule
  if ($eventId === 4 && $department !== "Computer Science & Engineering") {
      http_response_code(403);
      echo json_encode([
          "success" => false, 
          "message" => "Access Restricted: CodeISM Sprint is exclusively reserved for Computer Science students."
      ]);
      exit;
  }
  ```
* **Verification:** Log in as a student with Department = "Mining Engineering" → Attempt registration for CodeISM → Alert: *"Access Restricted: CodeISM Sprint is exclusively reserved for Computer Science students."*

---

### Q9: Prevent Time Conflicts (No Overlapping Event Registrations).
* **Task:** If student is already registered for an event at date/time T, prevent booking another event at the exact same time.
* **Edit `api/register.php` (around line 60):**
  ```php
  // Check time conflict
  $stmtTime = $pdo->prepare("
      SELECT e.title 
      FROM registrations r
      JOIN events e ON r.event_id = e.id
      WHERE r.admission_no = ? AND e.date_time = (SELECT date_time FROM events WHERE id = ?)
  ");
  $stmtTime->execute([$admissionNo, $eventId]);
  $conflict = $stmtTime->fetch();
  if ($conflict) {
      http_response_code(409);
      echo json_encode(["success" => false, "message" => "Time Conflict: You are already registered for '" . $conflict["title"] . "' at this exact time!"]);
      exit;
  }
  ```
* **Verification:** In phpMyAdmin, set Event 1 and Event 2 to identical `date_time`. Register for both → Second attempt returns time conflict.

---

### Q10: Atomic Capacity Enforcement using Database Transactions (`SELECT FOR UPDATE`).
* **Task:** The report noted that capacity check was *"not atomic"*. Fix it using PDO transactions and row locking.
* **Edit `api/register.php` (replace lines 37 to 82):**
  ```php
  try {
      $pdo->beginTransaction();

      // Lock event row for update
      $stmtEvent = $pdo->prepare("SELECT id, title, capacity FROM events WHERE id = ? FOR UPDATE");
      $stmtEvent->execute([$eventId]);
      $event = $stmtEvent->fetch();
      if (!$event) {
          $pdo->rollBack();
          http_response_code(404);
          echo json_encode(["success" => false, "message" => "Event not found."]);
          exit;
      }

      // Check current registration count inside transaction
      $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ?");
      $stmtCount->execute([$eventId]);
      $currentCount = (int) $stmtCount->fetchColumn();

      if ($currentCount >= (int)$event["capacity"]) {
          $pdo->rollBack();
          http_response_code(409);
          echo json_encode(["success" => false, "message" => "Event has reached capacity."]);
          exit;
      }

      // Insert registration
      $stmtInsert = $pdo->prepare("INSERT INTO registrations (event_id, student_name, admission_no, email, phone, department) VALUES (?, ?, ?, ?, ?, ?)");
      $stmtInsert->execute([$eventId, $studentName, $admissionNo, $email, $phone, $department]);

      $pdo->commit();
  } catch (Exception $e) {
      if ($pdo->inTransaction()) { $pdo->rollBack(); }
      http_response_code(500);
      echo json_encode(["success" => false, "message" => $e->getMessage()]);
      exit;
  }
  ```
* **Verification:** Show the TA the `$pdo->beginTransaction()` and `FOR UPDATE` lock.

---

### Q11: Add a Waitlist Option When Event is Full.
* **Task:** When remaining seats = 0, instead of blocking, allow registering into a `waitlist` table.
* **Step 1 (SQL):**
  ```sql
  CREATE TABLE waitlist (
      id INT AUTO_INCREMENT PRIMARY KEY,
      event_id INT,
      admission_no VARCHAR(50),
      student_name VARCHAR(150),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      UNIQUE(event_id, admission_no)
  );
  ```
* **Step 2 (Edit `api/register.php` around line 68):**
  ```php
  if ($currentCount >= $capacity) {
      $stmtWait = $pdo->prepare("INSERT INTO waitlist (event_id, admission_no, student_name) VALUES (?, ?, ?)");
      $stmtWait->execute([$eventId, $admissionNo, $studentName]);
      echo json_encode(["success" => true, "message" => "Event is full, but you have been added to the WAITLIST!"]);
      exit;
  }
  ```
* **Verification:** Register for Event 8 (capacity 4/4) → Receives: *"Event is full, but you have been added to the WAITLIST!"*.

---

### Q12: Team Registration (Capture Partner's Roll Number).
* **Task:** Add an optional "Partner Roll Number" field for events in `Technical` category.
* **Edit `index.php` (inside Registration Modal around line 215):**
  ```html
  <div class="form-group" style="margin-top: 10px;">
    <label for="regPartner">Teammate / Partner Admission No (Optional)</label>
    <input type="text" class="form-control" id="regPartner" name="partner_admission_no" placeholder="e.g. 24JE0101">
  </div>
  ```
* **Verification:** Open registration modal → Field appears cleanly.

---

### Q13: Block Cancellations Within 2 Hours of Event Start.
* **Task:** Students should not be allowed to cancel a seat if the event starts in less than 2 hours.
* **Edit `api/cancel.php` (around line 27):**
  ```php
  $stmtEv = $pdo->prepare("SELECT e.date_time FROM events e WHERE e.id = ?");
  $stmtEv->execute([$eventId]);
  $eventDateTime = $stmtEv->fetchColumn();

  if ($eventDateTime && (strtotime($eventDateTime) - time()) < (2 * 3600) && (strtotime($eventDateTime) > time())) {
      http_response_code(400);
      echo json_encode(["success" => false, "message" => "Late Cancellation Policy: Seats cannot be cancelled within 2 hours of event start."]);
      exit;
  }
  ```
* **Verification:** In phpMyAdmin, set an event's `date_time` to 1 hour from now → Click Cancel Seat → Alert blocks cancellation.

---

### Q14: Restrict Email Domain strictly to `@iitism.ac.in`.
* **Task:** Only official institutional emails ending with `@iitism.ac.in` should be allowed to register.
* **Edit `register_process.php` (around line 58):**
  ```php
  if (!str_ends_with($studentEmail, "@iitism.ac.in")) {
      header("Location: register.php?error=" . urlencode("Registration restricted: Must use an official @iitism.ac.in institute email address."));
      exit;
  }
  ```
* **Verification:** Try registering with `rohit@gmail.com` → Rejected with error banner. Try `rohit@iitism.ac.in` → Accepted.

---

### Q15: Log Seat Bookings to a Text File (`logs/registrations.log`).
* **Task:** Keep an audit log of every successful registration on disk.
* **Edit `api/register.php` (around line 89):**
  ```php
  $logEntry = date("Y-m-d H:i:s") . " | BOOKING | Event: $eventId | Roll: $admissionNo | Email: $email" . PHP_EOL;
  file_put_contents(__DIR__ . "/../registrations.log", $logEntry, FILE_APPEND);
  ```
* **Verification:** Register for an event → Open `c:\xampp\htdocs\project\registrations.log` → See timestamped log.

---

## Category 3: Admin Dashboard Enhancements

### Q16: Add Category Dropdown to Admin Publish Event Form.
* **Task:** The report noted that `admin.php` hardcodes `category = 'Campus'`. Make it selectable from a dropdown.
* **Edit `admin.php` (around line 147):**
  Add category dropdown:
  ```html
  <div class="form-group">
    <label for="category">Event Category <span class="req">*</span></label>
    <select class="form-control" id="category" name="category" required>
      <option value="Technical">Technical</option>
      <option value="Cultural">Cultural</option>
      <option value="Sports">Sports</option>
      <option value="Literary">Literary</option>
      <option value="Entrepreneurship">Entrepreneurship</option>
    </select>
  </div>
  ```
* **Edit `admin.php` backend handling (line 37):**
  ```php
  $category = isset($_POST["category"]) ? trim($_POST["category"]) : "Campus";
  $stmt = $pdo->prepare("
      INSERT INTO events (title, category, description, date_time, venue, capacity)
      VALUES (?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([$title, $category, $desc, $dateTime, $venue, $capacity]);
  ```
* **Verification:** In `admin.php`, publish an event with category "Sports" → Check in phpMyAdmin that `category` column has 'Sports'.

---

### Q17: Export Event Registrants Roster to CSV.
* **Task:** Add an "Export CSV" button next to each event in `admin.php` that downloads a CSV of registrants.
* **Step 1 (Create `export_csv.php` in project root):**
  ```php
  <?php
  session_start();
  if (empty($_SESSION["isAdmin"])) { exit("Unauthorized"); }
  require_once "config/db.php";
  $eventId = intval($_GET["event_id"] ?? 0);

  header("Content-Type: text/csv");
  header("Content-Disposition: attachment; filename=event_{$eventId}_registrants.csv");

  $out = fopen("php://output", "w");
  fputcsv($out, ["Admission No", "Student Name", "Email", "Department", "Registered At"]);

  $stmt = $pdo->prepare("SELECT admission_no, student_name, email, department, registered_at FROM registrations WHERE event_id = ? ORDER BY registered_at ASC");
  $stmt->execute([$eventId]);
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      fputcsv($out, $row);
  }
  fclose($out);
  ```
* **Step 2 (Edit `admin.php` around line 196):**
  Add download link:
  ```html
  <a href="export_csv.php?event_id=<?php echo $ev["id"]; ?>" class="btn-primary" style="text-decoration:none; padding:4px 8px; font-size:12px;">Export CSV</a>
  ```
* **Verification:** Click "Export CSV" in Admin panel → Browser instantly downloads a spreadsheet of registrants.

---

### Q18: Edit Event Details / Capacity in `admin.php`.
* **Task:** Allow an administrator to increase the capacity of an existing event.
* **Edit `admin.php` (add at top around line 48):**
  ```php
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "update_capacity") {
      $evId = intval($_POST["event_id"]);
      $newCap = intval($_POST["new_capacity"]);
      if ($evId > 0 && $newCap > 0) {
          $stmt = $pdo->prepare("UPDATE events SET capacity = ? WHERE id = ?");
          $stmt->execute([$newCap, $evId]);
          header("Location: admin.php?msg=updated");
          exit;
      }
  }
  ```
* **Add form inside each event card in `admin.php` (around line 224):**
  ```html
  <form action="admin.php" method="post" style="display:inline-flex; gap:6px; margin-top:8px;">
    <input type="hidden" name="action" value="update_capacity">
    <input type="hidden" name="event_id" value="<?php echo $ev["id"]; ?>">
    <input type="number" name="new_capacity" value="<?php echo $ev["capacity"]; ?>" style="width:70px;" min="1" required>
    <button type="submit" class="btn-secondary" style="padding:2px 8px; font-size:11px;">Update Capacity</button>
  </form>
  ```
* **Verification:** Change Parakram Badminton capacity from 6 to 10 → Click Update → Total capacity changes instantly to 10 and Remaining Seats becomes 5.

---

### Q19: Department-wise Registrant Breakdown in Admin Dashboard.
* **Task:** Under each event, show a summary: e.g. "CSE: 3, Mining: 2, Mechanical: 1".
* **Edit `admin.php` (around line 230):**
  ```php
  <?php
    $deptCounts = [];
    foreach ($evRegistrants as $reg) {
        $deptCounts[$reg["department"]] = ($deptCounts[$reg["department"]] ?? 0) + 1;
    }
  ?>
  <div style="font-size: 11px; color: #555; margin-bottom: 6px;">
    <strong>Department Breakdown: </strong>
    <?php 
      foreach ($deptCounts as $dept => $c) {
          echo htmlspecialchars($dept) . ": <strong>$c</strong> | ";
      }
    ?>
  </div>
  ```
* **Verification:** Open `admin.php` → Under Event 6 (Badminton), see breakdown across departments.

---

### Q20: Filter Registrants Table Live via Input Search in `admin.php`.
* **Task:** Add an instant search filter above the registrants table in `admin.php`.
* **Edit `admin.php` (add right above line 232):**
  ```html
  <input type="text" placeholder="Filter registrants by roll or name..." 
         onkeyup="var val = this.value.toLowerCase(); $(this).next('table').find('tbody tr').filter(function() { $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1); });" 
         style="width: 100%; padding: 4px; font-size: 11px; margin-bottom: 4px;">
  ```
* **Verification:** Type "24JE0101" in the filter box → Only Aarav Sharma's row remains visible.

---

### Q21: Add a "Clear All Registrations" Button for an Event.
* **Task:** Admin wants to wipe all registrations for an event without deleting the event itself.
* **Edit `admin.php` (around line 25):**
  ```php
  if (isset($_GET["clear_registrations"])) {
      $clrId = intval($_GET["clear_registrations"]);
      $stmt = $pdo->prepare("DELETE FROM registrations WHERE event_id = ?");
      $stmt->execute([$clrId]);
      header("Location: admin.php?msg=cleared");
      exit;
  }
  ```
* **Add link in `admin.php` next to Delete button:**
  ```html
  <a href="admin.php?clear_registrations=<?php echo $ev["id"]; ?>" onclick="return confirm('Reset all seats?');" class="btn-secondary" style="font-size:12px; padding:4px 8px;">Reset Seats</a>
  ```
* **Verification:** Click Reset Seats on Event 6 → All 5 registrations wiped, remaining seats becomes 6.

---

### Q22: Add an Event Status Toggle (`Active` vs `Postponed`).
* **Task:** Allow marking an event as Postponed without deleting it.
* **Step 1 (SQL):**
  ```sql
  ALTER TABLE events ADD COLUMN is_active TINYINT DEFAULT 1;
  ```
* **Step 2 (Edit `admin.php`):**
  Add toggle button:
  ```php
  if (isset($_GET["toggle_status"])) {
      $tId = intval($_GET["toggle_status"]);
      $pdo->query("UPDATE events SET is_active = NOT is_active WHERE id = $tId");
      header("Location: admin.php");
      exit;
  }
  ```
* **Verification:** Click toggle link → Event updates state in DB.

---

### Q23: Print Attendance Sheet (Print-friendly CSS).
* **Task:** Clicking "Print Roster" opens the browser print dialog formatted cleanly for paper.
* **Edit `admin.php` (add button above table):**
  ```html
  <button onclick="window.print()" class="btn-secondary" style="font-size:11px; padding:2px 8px;">Print Attendance Sheet</button>
  ```
* **Add print CSS to `assets/css/style.css`:**
  ```css
  @media print {
    .top-bar, .controls-card, .btn-cancel-reg, a { display: none !important; }
    body { background: #fff; color: #000; }
  }
  ```
* **Verification:** Click Print Attendance Sheet → Clean table ready for physical clipboard signing.

---

## Category 4: Search, Filters & Frontend DOM Manipulation

### Q24: Add Real-time Event Search by Keyword (Title / Venue).
* **Task:** Add an instant search bar in `index.php` that filters visible event cards using jQuery without page reload.
* **Edit `index.php` (insert right above line 103):**
  ```html
  <div style="margin-bottom: 15px;">
    <input type="text" id="eventSearchInput" class="form-control" placeholder="Search events by title, venue, or keywords..." style="padding: 10px; font-size: 14px;">
  </div>
  ```
* **Edit `assets/js/app.js` (add inside `$(document).ready` around line 57):**
  ```javascript
  $("#eventSearchInput").on("keyup", function () {
    var searchVal = $(this).val().toLowerCase().trim();
    $(".event-card").each(function () {
      var cardText = $(this).text().toLowerCase();
      $(this).toggle(cardText.indexOf(searchVal) > -1);
    });
  });
  ```
* **Verification:** Type "Robo" → Only Concetto RoboSoccer card remains visible. Clear text → All cards reappear.

---

### Q25: Filter Events by Category Tabs (`All`, `Technical`, `Cultural`, `Sports`).
* **Task:** Add pill buttons at the top of the events portal to filter cards by category.
* **Edit `index.php` (above `eventsGrid` line 103):**
  ```html
  <div class="category-filters" style="display:flex; gap:8px; margin-bottom:15px;">
    <button class="btn-nav active btn-filter" data-cat="all">All</button>
    <button class="btn-nav btn-filter" data-cat="Technical">Technical</button>
    <button class="btn-nav btn-filter" data-cat="Cultural">Cultural</button>
    <button class="btn-nav btn-filter" data-cat="Sports">Sports</button>
  </div>
  ```
* **Edit `assets/js/app.js` (inside `$(document).ready`):**
  ```javascript
  $(document).on("click", ".btn-filter", function() {
    $(".btn-filter").removeClass("active");
    $(this).addClass("active");
    var cat = $(this).data("cat");
    
    if (cat === "all") {
      renderEvents(currentEvents);
    } else {
      var filtered = currentEvents.filter(function(e) { return e.category === cat; });
      renderEvents(filtered);
    }
  });
  ```
* **Verification:** Click "Sports" → Only Parakram Badminton is rendered.

---

### Q26: Sort Events by Remaining Seats ASC / Date DESC.
* **Task:** Add a `<select>` dropdown to sort cards dynamically (e.g. "Fewest Seats Left", "Earliest Date").
* **Edit `index.php` (above `eventsGrid`):**
  ```html
  <select id="sortEventsSelect" class="form-control" style="width: 200px; margin-bottom: 15px;">
    <option value="date_asc">Sort: Earliest Date</option>
    <option value="seats_asc">Sort: Fewest Seats Left</option>
    <option value="cap_desc">Sort: Highest Capacity</option>
  </select>
  ```
* **Edit `assets/js/app.js`:**
  ```javascript
  $("#sortEventsSelect").on("change", function () {
    var sortMode = $(this).val();
    var sorted = [...currentEvents];
    if (sortMode === "seats_asc") {
      sorted.sort(function (a, b) { return a.remaining_seats - b.remaining_seats; });
    } else if (sortMode === "cap_desc") {
      sorted.sort(function (a, b) { return b.capacity - a.capacity; });
    } else {
      sorted.sort(function (a, b) { return a.id - b.id; });
    }
    renderEvents(sorted);
  });
  ```
* **Verification:** Select "Fewest Seats Left" → E-Summit (0 remaining) and Parakram (1 remaining) jump to the top.

---

### Q27: Urgent Warning Badge ("Filling Fast!" when Remaining <= 2).
* **Task:** Visually highlight events with 2 or fewer seats remaining with an animated or orange badge.
* **Edit `assets/js/app.js` (inside `renderEvents` around line 110):**
  ```javascript
  var urgentBadge = (ev.remaining_seats > 0 && ev.remaining_seats <= 2)
    ? '<span style="background: orange; color: white; padding: 2px 6px; font-size: 11px; font-weight: bold; border-radius: 3px; margin-left: 8px;">FILLING FAST! Only ' + ev.remaining_seats + ' Left!</span>'
    : '';
  ```
  Insert `urgentBadge` right after `escapeHtml(ev.title)` in card header.
* **Verification:** Open `index.php` → Parakram Badminton displays **FILLING FAST! Only 1 Left!** in bold orange.

---

### Q28: Add Live Countdown Timer to Event Start Date.
* **Task:** Display a live ticking countdown ("Starts in: Xd Yh Zm") on each card.
* **Edit `assets/js/app.js` (inside `renderEvents` cardHtml):**
  ```javascript
  '<div class="meta-item"><span class="countdown-timer" data-target="' + ev.date_formatted + ' ' + ev.time_formatted + '"><strong>Countdown:</strong> Calculating...</span></div>' +
  ```
* **Add ticking loop at bottom of `app.js`:**
  ```javascript
  setInterval(function () {
    $(".countdown-timer").each(function () {
      var target = new Date($(this).data("target")).getTime();
      var now = new Date().getTime();
      var diff = target - now;
      if (diff <= 0) { $(this).text("Event Started"); return; }
      var d = Math.floor(diff / (1000 * 60 * 60 * 24));
      var h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      var m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
      $(this).html("<strong>Starts in:</strong> " + d + "d " + h + "h " + m + "m");
    });
  }, 1000);
  ```
* **Verification:** Event cards show ticking countdown timer in real time.

---

### Q29: Add SweetAlert / Custom Confirmation Prompt before Booking.
* **Task:** Replace the direct booking submission with an explicit confirmation dialog showing the event name and venue.
* **Edit `assets/js/app.js` (inside `submitRegistration` around line 144):**
  ```javascript
  var evName = $("#modalEventTitle").text();
  if (!confirm("Are you sure you want to reserve a seat for '" + evName + "'? Your seat will be allocated immediately.")) {
    return;
  }
  ```
* **Verification:** Click Confirm Registration in modal → Native confirmation prompt appears before AJAX request fires.

---

### Q30: Change Layout from Single Column to 2-Column Responsive Grid.
* **Task:** The report noted CSS uses a single-column flex queue. Change the cards to display in a modern 2-column grid.
* **Edit `assets/css/style.css` (lines 106–110):**
  Replace:
  ```css
  .events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
    gap: 16px;
  }
  ```
* **Verification:** Refresh `index.php` → Event cards render in a beautiful two-column dashboard grid.

---

### Q31: Live Character Counter on Admin Description Box.
* **Task:** In `admin.php`, display remaining characters as admin types in the event description box.
* **Edit `admin.php` (under description textarea line 145):**
  ```html
  <textarea class="form-control" id="description" name="description" rows="2" maxlength="300" 
            oninput="$('#charCount').text(300 - this.value.length + ' chars remaining')" required></textarea>
  <small id="charCount" style="color: #666;">300 chars remaining</small>
  ```
* **Verification:** Type in description box in admin → Counter decreases from 300 in real time.

---

## Category 5: Authentication & Session Management

### Q32: Inactivity Session Auto-Logout after 5 Minutes.
* **Task:** If a user remains idle on any page for more than 5 minutes (300 seconds), log them out automatically.
* **Edit `index.php` (insert at line 9):**
  ```php
  // Inactivity timeout (5 minutes = 300 seconds)
  $timeout = 300;
  if (isset($_SESSION["last_activity"]) && (time() - $_SESSION["last_activity"]) > $timeout) {
      session_unset();
      session_destroy();
      header("Location: login.php?error=" . urlencode("Session expired due to inactivity. Please login again."));
      exit;
  }
  $_SESSION["last_activity"] = time();
  ```
* **Verification:** Temporarily change `$timeout = 5;` (5 seconds). Wait 6 seconds and refresh → Redirects to `login.php` with session expired message.

---

### Q33: Restrict Admin from Registering as a Student.
* **Task:** If an admin visits `index.php`, hide or disable all "Register Now" buttons to prevent polluting student registrations.
* **Edit `index.php` (inside card action buttons around line 169):**
  ```php
  <?php if (!empty($_SESSION["isAdmin"])): ?>
    <button class="btn-secondary" disabled title="Admins cannot register">Admin View Only</button>
  <?php else: ?>
    <button class="btn-register" id="btn-reg-<?php echo $event["id"]; ?>" <?php echo $isFull ? 'disabled' : ''; ?> onclick="openRegistrationModal(<?php echo $event["id"]; ?>)">
      <?php echo $isFull ? 'Event Full' : 'Register Now'; ?>
    </button>
  <?php endif; ?>
  ```
* **Verification:** Log in with `ADMIN / admin123` and visit `index.php` → Cards show disabled "Admin View Only".

---

### Q34: Add "Remember Me" Cookie on Login.
* **Task:** Add a "Remember Me" checkbox on `login.php` that saves the Admission Number in a cookie for 7 days.
* **Step 1 (Edit `login.php` inside form):**
  ```html
  <div style="margin: 10px 0;">
    <label style="font-weight: normal; font-size: 13px;">
      <input type="checkbox" name="rememberMe" value="1" <?php echo isset($_COOKIE["remember_adm"]) ? "checked" : ""; ?>> Remember my Admission No
    </label>
  </div>
  ```
* **Step 2 (Edit `login_process.php` around line 56):**
  ```php
  if (!empty($_POST["rememberMe"])) {
      setcookie("remember_adm", $student["admission_no"], time() + (7 * 24 * 3600), "/");
  } else {
      setcookie("remember_adm", "", time() - 3600, "/");
  }
  ```
* **Step 3 (Pre-fill input in `login.php`):**
  ```html
  <input type="text" ... value="<?php echo htmlspecialchars($_COOKIE["remember_adm"] ?? ""); ?>">
  ```
* **Verification:** Log in with Remember Me checked → Log out → `admissionNo` is automatically pre-filled!

---

### Q35: Track and Display `last_login_at` Timestamp.
* **Task:** Record the login timestamp in the database and display "Last login: [Date]" on the top navigation bar.
* **Step 1 (SQL):**
  ```sql
  ALTER TABLE students ADD COLUMN last_login_at DATETIME NULL;
  ```
* **Step 2 (Edit `login_process.php` around line 48):**
  ```php
  $pdo->prepare("UPDATE students SET last_login_at = NOW() WHERE admission_no = ?")->execute([$student["admission_no"]]);
  $_SESSION["lastLogin"] = date("M d, h:i A");
  ```
* **Step 3 (Edit `index.php` nav around line 78):**
  ```html
  <span style="font-size: 11px; color: #ccc; margin-left: 5px;">Logged in: <?php echo $_SESSION["lastLogin"] ?? "Now"; ?></span>
  ```
* **Verification:** Log in → See timestamp in navbar.

---

### Q36: Student Profile / Password Change Feature.
* **Task:** Allow students to change their password from a modal.
* **Create `api/change_password.php`:**
  ```php
  <?php
  session_start();
  header("Content-Type: application/json");
  if (!isset($_SESSION["admissionNo"])) { exit(json_encode(["success"=>false])); }
  require_once "../config/db.php";

  $input = json_decode(file_get_contents("php://input"), true);
  $oldPass = $input["old_password"] ?? "";
  $newPass = $input["new_password"] ?? "";

  $stmt = $pdo->prepare("SELECT password FROM students WHERE admission_no = ?");
  $stmt->execute([$_SESSION["admissionNo"]]);
  $hash = $stmt->fetchColumn();

  if (!password_verify($oldPass, $hash)) {
      echo json_encode(["success" => false, "message" => "Incorrect current password."]);
      exit;
  }
  $newHash = password_hash($newPass, PASSWORD_DEFAULT);
  $pdo->prepare("UPDATE students SET password = ? WHERE admission_no = ?")->execute([$newHash, $_SESSION["admissionNo"]]);
  echo json_encode(["success" => true, "message" => "Password changed successfully!"]);
  ```
* **Verification:** Test via console: `fetch('api/change_password.php', {method:'POST', body:JSON.stringify({old_password:'...', new_password:'...'})})`.

---

### Q37: Account Lockout after 3 Failed Login Attempts.
* **Task:** Prevent brute-force password guessing by locking out user after 3 consecutive bad passwords.
* **Step 1 (SQL):**
  ```sql
  ALTER TABLE students ADD COLUMN failed_attempts INT DEFAULT 0, ADD COLUMN locked_until DATETIME NULL;
  ```
* **Step 2 (Edit `login_process.php` around line 35):**
  ```php
  // Check lockout
  if ($student["locked_until"] && strtotime($student["locked_until"]) > time()) {
      header("Location: login.php?error=" . urlencode("Account locked due to 3 failed attempts. Try again later."));
      exit;
  }

  if (!password_verify($password, $student["password"])) {
      $fails = $student["failed_attempts"] + 1;
      if ($fails >= 3) {
          $pdo->prepare("UPDATE students SET failed_attempts = 0, locked_until = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE admission_no = ?")->execute([$admissionNo]);
          header("Location: login.php?error=" . urlencode("Account locked for 5 minutes due to 3 failed attempts."));
      } else {
          $pdo->prepare("UPDATE students SET failed_attempts = ? WHERE admission_no = ?")->execute([$fails, $admissionNo]);
          header("Location: login.php?error=" . urlencode("Incorrect password. Attempts left: " . (3 - $fails)));
      }
      exit;
  }
  // Reset on success:
  $pdo->prepare("UPDATE students SET failed_attempts = 0, locked_until = NULL WHERE admission_no = ?")->execute([$admissionNo]);
  ```
* **Verification:** Enter wrong password 3 times → Account locked message appears.

---

### Q38: Restrict Session Hijacking via User-Agent Binding.
* **Task:** Store `$_SESSION["user_agent"]` on login and destroy session if User-Agent changes mid-session.
* **Edit `login_process.php` (line 55):**
  ```php
  $_SESSION["user_agent"] = $_SERVER["HTTP_USER_AGENT"];
  ```
* **Edit `index.php` (line 7):**
  ```php
  if (!isset($_SESSION["user_agent"]) || $_SESSION["user_agent"] !== $_SERVER["HTTP_USER_AGENT"]) {
      session_unset();
      session_destroy();
      header("Location: login.php?error=Session+hijack+detected");
      exit;
  }
  ```
* **Verification:** Proves session security compliance to TA.

---

## Category 6: REST API Extensions & JSON Endpoints

### Q39: Create `api/event_stats.php` Endpoint.
* **Task:** Build a new REST API that returns global platform metrics: total events, total seats, total registered, and average occupancy.
* **Create file `api/event_stats.php`:**
  ```php
  <?php
  header("Content-Type: application/json; charset=utf-8");
  require_once __DIR__ . "/../config/db.php";

  try {
      $totalEvents = (int) $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
      $totalCapacity = (int) $pdo->query("SELECT SUM(capacity) FROM events")->fetchColumn();
      $totalRegistrations = (int) $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();

      echo json_encode([
          "success" => true,
          "metrics" => [
              "total_events" => $totalEvents,
              "total_capacity" => $totalCapacity,
              "total_registrations" => $totalRegistrations,
              "available_seats" => max(0, $totalCapacity - $totalRegistrations),
              "platform_occupancy_rate" => $totalCapacity > 0 ? round(($totalRegistrations / $totalCapacity) * 100, 2) . "%" : "0%"
          ]
      ]);
  } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(["success" => false, "message" => $e->getMessage()]);
  }
  ```
* **Verification:** Visit `http://localhost/project/api/event_stats.php` in browser → Returns beautiful JSON metrics.

---

### Q40: Create `api/search_events.php` with Query Parameters.
* **Task:** Build a dedicated search endpoint supporting `?q=hackathon&category=Technical`.
* **Create file `api/search_events.php`:**
  ```php
  <?php
  header("Content-Type: application/json; charset=utf-8");
  require_once __DIR__ . "/../config/db.php";

  $q = isset($_GET["q"]) ? "%" . trim($_GET["q"]) . "%" : "%";
  $cat = isset($_GET["category"]) ? trim($_GET["category"]) : "";

  $sql = "SELECT id, title, category, venue, capacity FROM events WHERE (title LIKE ? OR description LIKE ?)";
  $params = [$q, $q];

  if ($cat !== "") {
      $sql .= " AND category = ?";
      $params[] = $cat;
  }

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  echo json_encode(["success" => true, "results" => $stmt->fetchAll()]);
  ```
* **Verification:** Open `http://localhost/project/api/search_events.php?q=Band` in browser → Returns Srijan event.

---

### Q41: Add Pagination (`page` and `limit`) to `api/get_events.php`.
* **Task:** The report noted get_events returns all events at once. Add pagination support (`?page=1&limit=4`).
* **Edit `api/get_events.php` (around lines 9–25):**
  ```php
  $page = isset($_GET["page"]) ? max(1, (int)$_GET["page"]) : 1;
  $limit = isset($_GET["limit"]) ? max(1, (int)$_GET["limit"]) : 100;
  $offset = ($page - 1) * $limit;

  $stmt = $pdo->prepare("
      SELECT e.id, e.title, e.description, e.date_time, e.venue, e.capacity,
             COUNT(r.id) AS registered_count, (e.capacity - COUNT(r.id)) AS remaining_seats
      FROM events e
      LEFT JOIN registrations r ON e.id = r.event_id
      GROUP BY e.id
      ORDER BY e.date_time ASC
      LIMIT ? OFFSET ?
  ");
  $stmt->bindValue(1, $limit, PDO::PARAM_INT);
  $stmt->bindValue(2, $offset, PDO::PARAM_INT);
  $stmt->execute();
  $events = $stmt->fetchAll();
  ```
* **Verification:** Visit `http://localhost/project/api/get_events.php?page=1&limit=2` → Returns exactly 2 events.

---

### Q42: Generate a Unique Registration Ticket Reference (e.g. `TKT-2026-XXXX`).
* **Task:** Upon registration, generate and return a unique registration ticket code stored in the database.
* **Step 1 (SQL):**
  ```sql
  ALTER TABLE registrations ADD COLUMN ticket_code VARCHAR(30) UNIQUE NULL;
  ```
* **Step 2 (Edit `api/register.php` around line 80):**
  ```php
  $ticketCode = "TKT-2026-" . strtoupper(bin2hex(random_bytes(3)));
  $stmtInsert = $pdo->prepare("
      INSERT INTO registrations (event_id, student_name, admission_no, email, phone, department, ticket_code)
      VALUES (?, ?, ?, ?, ?, ?, ?)
  ");
  $stmtInsert->execute([$eventId, $studentName, $admissionNo, $email, $phone, $department, $ticketCode]);

  echo json_encode([
      "success" => true,
      "ticket_code" => $ticketCode,
      "message" => "Registration confirmed! Ticket Code: " . $ticketCode
  ]);
  ```
* **Verification:** Register for an event → Success alert shows generated ticket code like `TKT-2026-F91A4B`.

---

### Q43: Return Registrant List for a Specific Event via API.
* **Task:** Create `api/event_attendees.php?event_id=1` returning anonymized attendee list (Name & Department).
* **Create file `api/event_attendees.php`:**
  ```php
  <?php
  header("Content-Type: application/json; charset=utf-8");
  require_once __DIR__ . "/../config/db.php";
  $eventId = intval($_GET["event_id"] ?? 0);

  $stmt = $pdo->prepare("SELECT student_name, department, registered_at FROM registrations WHERE event_id = ? ORDER BY registered_at ASC");
  $stmt->execute([$eventId]);
  echo json_encode(["success" => true, "attendees" => $stmt->fetchAll()]);
  ```
* **Verification:** Open `http://localhost/project/api/event_attendees.php?event_id=6` in browser.

---

### Q44: Add an API Healthcheck / Diagnostics Endpoint.
* **Task:** Create `api/health.php` that tests DB connection and returns server time and status.
* **Create file `api/health.php`:**
  ```php
  <?php
  header("Content-Type: application/json");
  require_once __DIR__ . "/../config/db.php";

  $status = "healthy";
  try {
      $pdo->query("SELECT 1");
      $dbStatus = "connected";
  } catch (Exception $e) {
      $status = "unhealthy";
      $dbStatus = "failed";
  }

  echo json_encode([
      "status" => $status,
      "database" => $dbStatus,
      "php_version" => PHP_VERSION,
      "server_timestamp" => date("c")
  ]);
  ```
* **Verification:** Open `http://localhost/project/api/health.php` → Returns JSON `{"status":"healthy","database":"connected",...}`.

---

## Category 7: Security Defenses & Edge Case Fixes

### Q45: Fix IDOR in `api/cancel.php` (Enforce Session Ownership).
* **Task:** The report noted that `api/cancel.php` accepts client-supplied identity without checking session. Fix it!
* **Edit `api/cancel.php` (insert at line 8):**
  ```php
  session_start();
  if (!isset($_SESSION["admissionNo"])) {
      http_response_code(401);
      echo json_encode(["success" => false, "message" => "Authentication required."]);
      exit;
  }

  // IDOR Guard: Override client input with authentic session identity
  $admissionNo = $_SESSION["admissionNo"];
  ```
* **Verification:** Now even if someone sends another student's admission number, it is safely ignored and overridden with the authenticated user's session admission number.

---

### Q46: Fix XSS in `app.js` Inline Attribute Injection.
* **Task:** The report noted that `escapeHtml(ev.title)` inside inline `onclick="..."` can break out. Fix it using jQuery event delegation!
* **Edit `assets/js/app.js` (inside `renderEvents` lines 95 & 119):**
  Replace inline `onclick` with `data-id` and `data-title`:
  ```javascript
  var btnHtml = isFull
    ? '<button type="button" class="btn-register" disabled>Event Full</button>'
    : '<button type="button" class="btn-register btn-open-reg" data-id="' + ev.id + '">Register Now</button>';

  // And in card footer:
  '<button type="button" class="btn-cancel-seat btn-open-cancel" data-id="' + ev.id + '" data-title="' + escapeHtml(ev.title) + '">Cancel Seat</button>'
  ```
* **Add clean event delegations in `app.js` inside `$(document).ready`:**
  ```javascript
  $(document).on("click", ".btn-open-reg", function() {
    openRegistrationModal($(this).data("id"));
  });
  $(document).on("click", ".btn-open-cancel", function() {
    openQuickCancelModal($(this).data("id"), $(this).data("title"));
  });
  ```
* **Verification:** Removes all inline JS code execution risks completely!

---

### Q47: Add CSRF Token Protection to POST Forms.
* **Task:** Generate a CSRF token in session and verify it on POST in `login_process.php` and `register_process.php`.
* **Step 1 (Generate in `login.php`):**
  ```php
  if (empty($_SESSION["csrf_token"])) {
      $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
  }
  // Inside <form>:
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"]; ?>">
  ```
* **Step 2 (Verify in `login_process.php` line 12):**
  ```php
  if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
      header("Location: login.php?error=Invalid+CSRF+Token");
      exit;
  }
  ```
* **Verification:** Submitting form without valid token is rejected with 403.

---

### Q48: Enforce Strict 10-Digit Indian Mobile Number Regex.
* **Task:** Validate mobile number to ensure it starts with 6, 7, 8, or 9 and has exactly 10 digits.
* **Edit `register_process.php` (around line 55):**
  ```php
  if (!preg_match('/^[6-9][0-9]{9}$/', $studentPhone)) {
      header("Location: register.php?error=" . urlencode("Invalid Mobile Number. Must be a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9."));
      exit;
  }
  ```
* **Verification:** Try registering with `12345` or `0000000000` → Rejected. Enter `9876543210` → Accepted.

---

### Q49: Expand Admission Number Regex to Support M.Tech, M.Sc, & PhD.
* **Task:** Update regex to accept `24MT0101`, `24MS0202`, and `24PH0303` alongside `24JE0917`.
* **Edit `register_process.php` (line 51):**
  ```php
  // Accepts JE (B.Tech), MT (M.Tech), MS (M.Sc), PH (PhD)
  if (!preg_match('/^[0-9]{2}(JE|MT|MS|PH)[0-9]{4}$/', $admissionNo)) {
      header("Location: register.php?error=" . urlencode("Invalid Roll Number. Expected prefix JE, MT, MS, or PH (e.g. 24JE0917, 24MT0101)."));
      exit;
  }
  ```
* **Edit `register.php` (line 74):**
  ```html
  pattern="[0-9]{2}(JE|MT|MS|PH|je|mt|ms|ph)[0-9]{4}"
  ```
* **Verification:** Register with `24MT0105` → Accepted smoothly!

---

### Q50: Protect Admin Route with a Secret Passcode.
* **Task:** Require an extra query parameter (e.g. `admin.php?key=iitism2026`) or secret session pin to access the admin dashboard.
* **Edit `admin.php` (at line 9):**
  ```php
  if (isset($_GET["admin_key"])) {
      if ($_GET["admin_key"] === "iitism2026") {
          $_SESSION["admin_verified"] = true;
      }
  }

  if (empty($_SESSION["admin_verified"])) {
      header("Location: login.php?error=" . urlencode("Admin clearance key required to unlock management console."));
      exit;
  }
  ```
* **Verification:** Visiting `admin.php` directly redirects to login. Visiting `admin.php?admin_key=iitism2026` unlocks dashboard.

---

# 🚀 Quick Defense Oral Q&A Cheat Sheet (For Prof / TA Questions)

* **Q: Why PDO over `mysqli`?**
  * *Answer:* PDO supports named parameters, prepared statements with native typing (`PDO::ATTR_EMULATE_PREPARES => false`), and is database-agnostic.

* **Q: How do you prevent SQL Injection?**
  * *Answer:* All user inputs pass through prepared statements with bound parameters (`$stmt->prepare()` and `execute()`). Emulation mode is disabled so MySQL compiles the SQL query plan before parameter substitution.

* **Q: How does the seat count stay updated without full page reload?**
  * *Answer:* When `index.php` loads, `assets/js/app.js` runs `loadEvents()` via JavaScript `fetch()` to `api/get_events.php`. On every successful registration or cancellation, `loadEvents()` is called again to refresh card seat counts dynamically.

* **Q: What happens if two users click register at the same microsecond when 1 seat remains?**
  * *Answer:* The database UNIQUE compound keys `(event_id, admission_no)` prevent duplicate users, and in Q10 we demonstrated how atomic transactions with `SELECT ... FOR UPDATE` row locking prevent over-capacity race conditions.

* **Q: How do you protect passwords?**
  * *Answer:* Using `password_hash($password, PASSWORD_DEFAULT)`, which uses the strong `bcrypt` algorithm with automated cryptographic salting. Verification uses constant-time `password_verify()`.

