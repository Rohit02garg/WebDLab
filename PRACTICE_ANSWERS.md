# 🧪 PRACTICE EXAM — 50 QUESTIONS WITH COMPLETE ANSWERS
### Campus Event Registration System — IIT (ISM) Dhanbad
**Rohit Garg | 24JE0917 | NCSM301 Web Dev Lab**

> [!IMPORTANT]
> **HOW TO USE:**  
> TA gives you a task → Find that question number → Copy-paste the EXACT code shown → Refresh browser to verify.  
> All changes are in `c:\xampp\htdocs\project\` files. Keep this file open in VS Code during exam.

---

## ⚡ QUICK LOOKUP TABLE

| What they ask you to change | Question # |
|---|---|
| Navbar/header background color | Q1 |
| Button color | Q2 |
| Card border style | Q3 |
| Success/Error message color | Q4 |
| Font size anywhere | Q5 |
| Page title in browser tab | Q6 |
| Error message text wording | Q7 |
| Capacity limit per student | Q8 |
| Duplicate check logic | Q9 |
| Email domain restriction | Q10 |
| Block cancellation near event time | Q11 |
| Add log file on registration | Q12 |
| Block registration for specific dept | Q13 |
| Add category dropdown in admin | Q14 |
| Delete event feature | Q15 |
| Admin capacity update | Q16 |
| Filter/search registrants table | Q17 |
| Clear all registrations for event | Q18 |
| Sort events on frontend | Q19 |
| Search/filter event cards | Q20 |
| Show "Filling Fast" badge | Q21 |
| Countdown timer on cards | Q22 |
| Confirmation popup before booking | Q23 |
| Event card layout (grid/column) | Q24 |
| Progress bar color | Q25 |
| Character counter on description | Q26 |
| Category filter buttons | Q27 |
| Change "Register Now" button text | Q28 |
| Session auto-logout timeout | Q29 |
| Restrict admin from registering | Q30 |
| Remember Me cookie | Q31 |
| Change success alert message | Q32 |
| Add new column to DB | Q33 |
| Add SQL view | Q34 |
| Add waitlist feature | Q35 |
| Team/partner roll number field | Q36 |
| Export CSV button | Q37 |
| Department breakdown in admin | Q38 |
| Print attendance sheet button | Q39 |
| Add rating field | Q40 |
| Track login time | Q41 |
| Password change feature | Q42 |
| Time conflict check | Q43 |
| Transaction/atomic capacity lock | Q44 |
| XSS protection / htmlspecialchars | Q45 |
| Validate phone number format | Q46 |
| Sanitize all input fields | Q47 |
| HTTP response codes | Q48 |
| Registration log audit trail | Q49 |
| Session guard on API files | Q50 |

---

## 🎨 LEVEL 1 — CSS / VISIBLE UI CHANGES

---

### Q1 — Change the top navigation bar background color

**Task:** "Change the header/navbar background from dark grey to dark blue."

📁 **File:** `assets/css/style.css`  
📍 **Line 17** — Change:
```css
/* BEFORE (line 17): */
background: #333;

/* AFTER — paste this: */
background: #003366;
```
✅ **Verify:** Refresh `index.php` → Top bar turns dark navy blue.

---

### Q2 — Change the Register Now button color

**Task:** "Change the primary button color to green / red / any other color."

📁 **File:** `assets/css/style.css`  
📍 **Lines 204–209** — Find this block and change:
```css
/* BEFORE: */
.btn-primary,
.btn-register {
  background: #003366;
  color: #fff;
  border: 1px solid #003366;
}

/* AFTER (example: green): */
.btn-primary,
.btn-register {
  background: #1a7a1a;
  color: #fff;
  border: 1px solid #1a7a1a;
}
```
✅ **Verify:** Refresh → "Register Now" button is now green.

---

### Q3 — Change event card border style

**Task:** "Make event cards have a blue left border / thicker border / colored border."

📁 **File:** `assets/css/style.css`  
📍 **Lines 112–117** — Find `.event-card` and add/change:
```css
/* BEFORE: */
.event-card {
  background: #fff;
  border: 1px solid #ccc;
  padding: 16px;
  width: 100%;
}

/* AFTER (blue left border): */
.event-card {
  background: #fff;
  border: 1px solid #ccc;
  border-left: 4px solid #003366;
  padding: 16px;
  width: 100%;
}
```
✅ **Verify:** Each event card now has a thick blue left border.

---

### Q4 — Change success/error modal alert colors

**Task:** "Make success messages show in blue instead of green."

📁 **File:** `assets/css/style.css`  
📍 **Lines 340–344** — Find `.modal-alert.success`:
```css
/* BEFORE: */
.modal-alert.success {
  border-color: green;
  color: green;
  background: #e6ffe6;
}

/* AFTER (blue theme): */
.modal-alert.success {
  border-color: #003366;
  color: #003366;
  background: #e6eeff;
}
```
✅ **Verify:** Successfully register → Green popup is now blue.

---

### Q5 — Change font size of event title / description

**Task:** "Make event titles bigger" OR "Make description text smaller."

📁 **File:** `assets/css/style.css`

**For event title (line 124):**
```css
/* BEFORE: */
.event-title {
  font-size: 18px;

/* AFTER (larger): */
.event-title {
  font-size: 22px;
```

**For description (line 129):**
```css
/* BEFORE: */
.event-desc {
  font-size: 13px;

/* AFTER (smaller): */
.event-desc {
  font-size: 11px;
```
✅ **Verify:** Refresh → Card titles/descriptions are visibly bigger/smaller.

---

### Q6 — Change the browser tab title (page title)

**Task:** "Change the title shown in the browser tab."

📁 **File:** `index.php` → Find `<title>` tag (around line 13):
```html
<!-- BEFORE: -->
<title>Campus Events — IIT (ISM) Dhanbad</title>

<!-- AFTER: -->
<title>Student Event Portal — IIT (ISM) Dhanbad</title>
```

📁 **For admin page:** `admin.php` line 88:
```html
<!-- BEFORE: -->
<title>Admin Dashboard - IIT (ISM) Dhanbad</title>

<!-- AFTER: -->
<title>Event Management Panel - IIT (ISM) Dhanbad</title>
```
✅ **Verify:** Look at browser tab → Title changes immediately on refresh.

---

## 🔤 LEVEL 2 — MESSAGE / TEXT CHANGES (PHP)

---

### Q7 — Change an error message text

**Task:** "Change the error shown when registration fails due to missing fields."

📁 **File:** `api/register.php`  
📍 **Line 32** — Find and change:
```php
// BEFORE (line 32):
echo json_encode(["success" => false, "message" => "All registration fields are required."]);

// AFTER:
echo json_encode(["success" => false, "message" => "Please fill in all fields before submitting your registration."]);
```
✅ **Verify:** Submit the registration form with empty fields → New message appears in red box.

---

### Q8 — Add a limit: max 3 registrations per student

**Task:** "A student should not be able to register for more than 3 events total."

📁 **File:** `api/register.php`  
📍 **After line 34** (after the validation block, before the `try {`), **INSERT** this:
```php
// === ADD THIS BLOCK (after line 34, before line 36) ===
// Check total registrations for this student
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE admission_no = ?");
$stmtTotal->execute([$admissionNo]);
if ($stmtTotal->fetchColumn() >= 3) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Quota Exceeded: You cannot register for more than 3 events."]);
    exit;
}
```
✅ **Verify:** Register for 4 events with the same roll number → 4th attempt shows "Quota Exceeded."

---

### Q9 — Change what duplicate registration check looks for

**Task:** "Allow same email to register for different events, but not same roll number twice."

📁 **File:** `api/register.php`  
📍 **Line 51** — Find and change:
```php
// BEFORE (line 51):
$stmtDup = $pdo->prepare("SELECT id FROM registrations WHERE event_id = ? AND (admission_no = ? OR email = ?)");
$stmtDup->execute([$eventId, $admissionNo, $email]);

// AFTER (only check roll number, not email):
$stmtDup = $pdo->prepare("SELECT id FROM registrations WHERE event_id = ? AND admission_no = ?");
$stmtDup->execute([$eventId, $admissionNo]);
```
✅ **Verify:** Same roll number for same event → Still blocked. Same email for same event → Now allowed.

---

### Q10 — Restrict email to @iitism.ac.in domain only

**Task:** "Only students with @iitism.ac.in email should be allowed to register."

📁 **File:** `register_process.php`  
📍 **Find the email validation section** (around line 55–65), **ADD** before the INSERT:
```php
// === ADD THIS CHECK ===
$studentEmail = isset($_POST["studentEmail"]) ? strtolower(trim($_POST["studentEmail"])) : "";

if (!str_ends_with($studentEmail, "@iitism.ac.in")) {
    header("Location: register.php?error=" . urlencode("Only @iitism.ac.in email addresses are accepted."));
    exit;
}
```
✅ **Verify:** Try registering with `rohit@gmail.com` → Redirected with error. Try `rohit@iitism.ac.in` → Accepted.

---

### Q11 — Block cancellations within 2 hours of event

**Task:** "Students should not be able to cancel if the event starts within 2 hours."

📁 **File:** `api/cancel.php`  
📍 **After line 26** (after validation block, before the `try {`), **INSERT**:
```php
// === ADD THIS BLOCK ===
// Fetch event start time
$stmtEv = $pdo->prepare("SELECT e.date_time FROM events e WHERE e.id = ?");
$stmtEv->execute([$eventId]);
$eventDateTime = $stmtEv->fetchColumn();

// Block if within 2 hours of start
if ($eventDateTime && (strtotime($eventDateTime) - time()) < (2 * 3600) && strtotime($eventDateTime) > time()) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Late Cancel Policy: Cannot cancel within 2 hours of event start."]);
    exit;
}
```
✅ **Verify:** In phpMyAdmin, set an event's `date_time` to 1 hour from now. Click Cancel Seat → Blocked with policy message.

---

### Q12 — Write registration to a log file

**Task:** "Every time someone registers, append a line to `registrations.log`."

📁 **File:** `api/register.php`  
📍 **After line 81** (after the `$stmtInsert->execute(...)` line), **INSERT**:
```php
// === ADD THIS LOG LINE ===
$logEntry = date("Y-m-d H:i:s") . " | BOOKED | Event: $eventId | Roll: $admissionNo | Email: $email" . PHP_EOL;
file_put_contents(__DIR__ . "/../registrations.log", $logEntry, FILE_APPEND);
```
✅ **Verify:** Register for an event → Open `c:\xampp\htdocs\project\registrations.log` → See timestamped entry.

---

### Q13 — Restrict specific event to one department only

**Task:** "Only Computer Science students can register for Event ID 4 (CodeISM Sprint)."

📁 **File:** `api/register.php`  
📍 **After line 34** (after validation, before `try {`), **INSERT**:
```php
// === ADD THIS DEPARTMENT RESTRICTION ===
if ($eventId === 4 && $department !== "Computer Science & Engineering") {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Access Restricted: This event is only for Computer Science & Engineering students."
    ]);
    exit;
}
```
✅ **Verify:** Log in as Mining Engineering student → Try to register for Event 4 → Blocked with restriction message.

---

## 🛠️ LEVEL 3 — ADMIN PANEL CHANGES (admin.php)

---

### Q14 — Add category dropdown to admin publish form

**Task:** "The admin form hardcodes category as 'Campus'. Make it a dropdown."

📁 **File:** `admin.php`

**Step 1** — Find line 36–40 (the INSERT query with hardcoded `'Campus'`) and **REPLACE**:
```php
// BEFORE (lines 36-40):
    if ($title !== "" && $desc !== "" && $dateTime !== "" && $venue !== "" && $capacity > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO events (title, category, description, date_time, venue, capacity)
            VALUES (?, 'Campus', ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $desc, $dateTime, $venue, $capacity]);

// AFTER (read category from POST):
    $category = isset($_POST["category"]) ? trim($_POST["category"]) : "Campus";
    if ($title !== "" && $desc !== "" && $dateTime !== "" && $venue !== "" && $capacity > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO events (title, category, description, date_time, venue, capacity)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $category, $desc, $dateTime, $venue, $capacity]);
```

**Step 2** — In the HTML form section, find line 163 (the submit button div) and **INSERT BEFORE IT**:
```html
<!-- ADD this form-group BEFORE the submit button div -->
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
```
✅ **Verify:** Publish event with "Sports" → Check phpMyAdmin `events` table → `category` column shows 'Sports'.

---

### Q15 — Verify delete event functionality

**Task:** "Explain/demonstrate how event deletion works."

📁 **File:** `admin.php`  
📍 **Lines 17–24** — This is the delete handler:
```php
// Lines 17-24: DELETE handler
if (isset($_GET["delete"])) {
    $delId = intval($_GET["delete"]);      // Get event ID from URL
    if ($delId > 0) {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$delId]);          // Runs DELETE SQL query
        header("Location: admin.php?msg=deleted");  // Redirect with message
        exit;
    }
}
```
✅ **To demo:** Click "Delete" link on any event → Confirm popup → Event disappears + "Event deleted" message shown.

---

### Q16 — Add capacity update form in admin

**Task:** "Allow admin to change an event's capacity."

📁 **File:** `admin.php`

**Step 1** — After line 47 (after the publish handler's closing brace), **INSERT**:
```php
// === INSERT AFTER LINE 47 ===
// Handle Capacity Update
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

**Step 2** — Find line 223 (the closing `</div>` of seat-capacity-block), **INSERT AFTER IT**:
```html
<!-- INSERT AFTER THE seat-capacity-block closing div (after line 224) -->
<form action="admin.php" method="post" style="display:inline-flex; gap:6px; margin-top:8px;">
  <input type="hidden" name="action" value="update_capacity">
  <input type="hidden" name="event_id" value="<?php echo $ev["id"]; ?>">
  <input type="number" name="new_capacity" value="<?php echo $ev["capacity"]; ?>" style="width:70px;" min="1" required>
  <button type="submit" class="btn-secondary" style="padding:2px 8px; font-size:11px;">Update Capacity</button>
</form>
```
✅ **Verify:** Change Badminton capacity from 6 to 10 → Click Update → Remaining Seats increases.

---

### Q17 — Add live search/filter above registrants table

**Task:** "Add a search box above the registrants table that filters by name or roll number."

📁 **File:** `admin.php`  
📍 **Line 232** — Find `<table style="width: 100%...` and **INSERT BEFORE IT**:
```html
<!-- INSERT BEFORE THE TABLE (before line 232) -->
<input type="text" placeholder="Search by name or roll number..." 
       onkeyup="var v=this.value.toLowerCase(); $(this).next('table').find('tbody tr').filter(function(){$(this).toggle($(this).text().toLowerCase().indexOf(v)>-1);});" 
       style="width:100%; padding:4px; font-size:11px; margin-bottom:4px; border:1px solid #ccc;">
```
✅ **Verify:** Type "24JE" in the box → Only matching rows stay visible. Clear → All rows return.

---

### Q18 — Add "Reset Seats" / clear all registrations button

**Task:** "Admin should be able to wipe all registrations for an event."

📁 **File:** `admin.php`

**Step 1** — After line 24 (after delete handler), **INSERT**:
```php
// === INSERT AFTER LINE 24 ===
// Handle Clear All Registrations
if (isset($_GET["clear_registrations"])) {
    $clrId = intval($_GET["clear_registrations"]);
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE event_id = ?");
    $stmt->execute([$clrId]);
    header("Location: admin.php?msg=cleared");
    exit;
}
```

**Step 2** — Find the Delete `<a>` link (around line 191–196) and **ADD AFTER IT**:
```html
<!-- ADD next to the Delete button -->
<a href="admin.php?clear_registrations=<?php echo $ev["id"]; ?>" 
   onclick="return confirm('Clear all registrations for this event?');"
   class="btn-secondary" style="text-decoration:none; display:inline-block; padding:4px 8px; font-size:12px;">
  Reset Seats
</a>
```
✅ **Verify:** Click "Reset Seats" → Registrants table becomes empty, remaining seats = capacity.

---

## 🔲 LEVEL 3 — FRONTEND (app.js) CHANGES

---

### Q19 — Add sort dropdown (Fewest Seats, Earliest Date)

**Task:** "Add a dropdown to sort event cards."

📁 **File 1:** `index.php` — Find the `<div id="eventsGrid">` and **INSERT BEFORE IT**:
```html
<!-- ADD BEFORE eventsGrid div -->
<select id="sortEventsSelect" class="form-control" style="width:200px; margin-bottom:15px;">
  <option value="default">Sort: Default (By Date)</option>
  <option value="seats_asc">Sort: Fewest Seats Left</option>
  <option value="cap_desc">Sort: Highest Capacity</option>
</select>
```

📁 **File 2:** `assets/js/app.js` — At the **END** of `$(document).ready(function() { ... })` block (before its closing `});` at line 58), **INSERT**:
```javascript
// ADD INSIDE $(document).ready() before its closing });
$("#sortEventsSelect").on("change", function () {
  var sortMode = $(this).val();
  var sorted = currentEvents.slice(); // copy array
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
✅ **Verify:** Select "Fewest Seats Left" → Cards reorder (most full events jump to top).

---

### Q20 — Add live search box to filter event cards

**Task:** "Add a text input that filters event cards in real time by title or venue."

📁 **File 1:** `index.php` — **INSERT BEFORE** the eventsGrid div:
```html
<div style="margin-bottom:15px;">
  <input type="text" id="eventSearchInput" class="form-control" 
         placeholder="Search events by title or venue..." style="padding:10px; font-size:14px;">
</div>
```

📁 **File 2:** `assets/js/app.js` — Inside `$(document).ready()` (before its closing `});` at line 58), **INSERT**:
```javascript
// ADD INSIDE $(document).ready()
$("#eventSearchInput").on("keyup", function () {
  var searchVal = $(this).val().toLowerCase().trim();
  $(".event-card").each(function () {
    var cardText = $(this).text().toLowerCase();
    $(this).toggle(cardText.indexOf(searchVal) > -1);
  });
});
```
✅ **Verify:** Type "Robo" → Only RoboSoccer card visible. Clear → All cards back.

---

### Q21 — Show "FILLING FAST!" badge when seats ≤ 2

**Task:** "Show an urgent orange badge on cards with 2 or fewer seats left."

📁 **File:** `assets/js/app.js`  
📍 **Line 100** — Find `'<h3 class="event-title">' + escapeHtml(ev.title) + '</h3>' +` and **REPLACE IT** with:
```javascript
// REPLACE line 100 with:
'<h3 class="event-title">' + escapeHtml(ev.title) + 
(ev.remaining_seats > 0 && ev.remaining_seats <= 2 
  ? '<span style="background:orange;color:white;padding:2px 6px;font-size:11px;font-weight:bold;border-radius:3px;margin-left:8px;">FILLING FAST! Only ' + ev.remaining_seats + ' Left!</span>' 
  : '') +
'</h3>' +
```
✅ **Verify:** Parakram Badminton (1 seat remaining) now shows orange "FILLING FAST!" badge.

---

### Q22 — Add countdown timer to each event card

**Task:** "Show a live countdown 'Starts in: Xd Yh Zm' on each event card."

📁 **File:** `assets/js/app.js`  
📍 **Line 104** — Find the venue meta-item line and **INSERT AFTER IT**:
```javascript
// INSERT AFTER line 104 (after the venue meta-item line):
'<div class="meta-item"><span class="countdown-timer" data-target="' + ev.date_formatted + '">' +
'<strong>Countdown:</strong> Calculating...</span></div>' +
```

Then at the **very bottom of app.js** (after line 334), **ADD**:
```javascript
// ADD AT THE VERY BOTTOM OF app.js
setInterval(function () {
  $(".countdown-timer").each(function () {
    var target = new Date($(this).data("target")).getTime();
    var now = new Date().getTime();
    var diff = target - now;
    if (diff <= 0) { $(this).html("<strong>Event Started!</strong>"); return; }
    var d = Math.floor(diff / (1000 * 60 * 60 * 24));
    var h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    $(this).html("<strong>Starts in:</strong> " + d + "d " + h + "h " + m + "m");
  });
}, 1000);
```
✅ **Verify:** Refresh → Cards show ticking countdown timers.

---

### Q23 — Add confirmation popup before booking

**Task:** "Show a confirm() dialog before the registration AJAX call fires."

📁 **File:** `assets/js/app.js`  
📍 **Lines 143–144** — Find `function submitRegistration() {` and the `$btn.prop...` line. **INSERT BETWEEN them**:
```javascript
function submitRegistration() {
  // ADD THIS BLOCK RIGHT AFTER THE FUNCTION OPENING:
  var evName = $("#modalEventTitle").text();
  if (!confirm("Reserve a seat for '" + evName + "'? Your seat will be allocated immediately.")) {
    return; // Stop if user cancels
  }
  // rest of function continues...
  var $btn = $("#btnSubmitReg");
```
✅ **Verify:** Click "Confirm Registration" in modal → Browser shows confirm popup BEFORE any booking happens.

---

### Q24 — Change event cards from column to 2-column grid

**Task:** "Display event cards in 2 columns side by side instead of stacked."

📁 **File:** `assets/css/style.css`  
📍 **Lines 106–110** — Find `.events-grid` and **REPLACE**:
```css
/* BEFORE (lines 106-110): */
.events-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* AFTER (2-column grid): */
.events-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
  gap: 16px;
}
```
✅ **Verify:** Refresh → Cards now appear in 2 columns on wide screen.

---

### Q25 — Change progress bar color (full = different color)

**Task:** "Make the full/capacity progress bar show in orange instead of red."

📁 **File:** `assets/css/style.css`  
📍 **Lines 187–189**:
```css
/* BEFORE: */
.progress-bar-fill.full {
  background: red;
}

/* AFTER (orange): */
.progress-bar-fill.full {
  background: #ff6600;
}
```
✅ **Verify:** Full events (like E-Summit) now show an orange progress bar instead of red.

---

### Q26 — Add character counter on admin description textarea

**Task:** "Show remaining character count as admin types in description."

📁 **File:** `admin.php`  
📍 **Line 144** — Find the `<textarea>` for description and **REPLACE IT** with:
```html
<!-- REPLACE line 144 with: -->
<textarea class="form-control" id="description" name="description" rows="2" 
          maxlength="300" placeholder="Brief details about the event..." 
          oninput="$('#charCount').text(300 - this.value.length + ' chars remaining')" required></textarea>
<small id="charCount" style="color:#666;">300 chars remaining</small>
```
✅ **Verify:** Type in description → Counter decrements in real time. At 290 chars: shows "10 chars remaining".

---

## 🔐 LEVEL 4 — AUTHENTICATION / SESSION

---

### Q27 — Add category filter pill buttons

**Task:** "Add buttons like 'All / Technical / Cultural / Sports' to filter event cards."

📁 **File 1:** `index.php` — Find `<div id="eventsGrid"` and **INSERT BEFORE IT**:
```html
<!-- ADD BEFORE eventsGrid -->
<div style="display:flex; gap:8px; margin-bottom:15px; flex-wrap:wrap;">
  <button class="btn-nav active btn-filter" data-cat="all">All</button>
  <button class="btn-nav btn-filter" data-cat="Technical">Technical</button>
  <button class="btn-nav btn-filter" data-cat="Cultural">Cultural</button>
  <button class="btn-nav btn-filter" data-cat="Sports">Sports</button>
  <button class="btn-nav btn-filter" data-cat="Literary">Literary</button>
</div>
```

📁 **File 2:** `assets/js/app.js` — Inside `$(document).ready()`, **INSERT**:
```javascript
// ADD INSIDE $(document).ready()
$(document).on("click", ".btn-filter", function () {
  $(".btn-filter").removeClass("active");
  $(this).addClass("active");
  var cat = $(this).data("cat");
  if (cat === "all") {
    renderEvents(currentEvents);
  } else {
    var filtered = currentEvents.filter(function (e) { return e.category === cat; });
    renderEvents(filtered);
  }
});
```
✅ **Verify:** Click "Sports" → Only Badminton card shown. Click "All" → Everything returns.

---

### Q28 — Change "Register Now" button text

**Task:** "Change the button text from 'Register Now' to 'Book My Seat'."

📁 **File:** `assets/js/app.js`  
📍 **Line 95** — Find and **REPLACE**:
```javascript
// BEFORE (line 95):
: '<button type="button" class="btn-register" onclick="openRegistrationModal(' + ev.id + ')">Register Now</button>';

// AFTER:
: '<button type="button" class="btn-register" onclick="openRegistrationModal(' + ev.id + ')">Book My Seat</button>';
```
✅ **Verify:** Refresh → All available event cards now say "Book My Seat."

---

### Q29 — Session auto-logout after inactivity

**Task:** "Automatically log out student if they are inactive for 5 minutes."

📁 **File:** `index.php`  
📍 **After line 2** (after `session_start();`), **INSERT**:
```php
// === INSERT AFTER session_start(); ===
// Auto-logout after 5 minutes inactivity
$timeout = 300; // 300 seconds = 5 minutes
if (isset($_SESSION["last_activity"]) && (time() - $_SESSION["last_activity"]) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: login.php?error=" . urlencode("Session expired. Please login again."));
    exit;
}
$_SESSION["last_activity"] = time();
```
> **Demo trick:** Set `$timeout = 5;` to test immediately — wait 6 seconds then refresh.

✅ **Verify:** With 5-second timeout, wait 6 sec, refresh → Redirected to login page with "Session expired."

---

### Q30 — Disable Register Now button for admins

**Task:** "When logged in as admin, show 'Admin View Only' instead of Register Now."

📁 **File:** `index.php`

This change is in the PHP that generates the page **OR** in the JavaScript. The easiest way:

Find where the page loads `sessionStudent` (look for `window.sessionStudent`) and instead change the JS rendering:

📁 **File:** `assets/js/app.js`  
📍 **Lines 93–95** — Find the `btnHtml` block and **REPLACE**:
```javascript
// REPLACE lines 93-95:
var isAdmin = window.sessionStudent && window.sessionStudent.isAdmin;
var btnHtml = isFull
  ? '<button type="button" class="btn-register" disabled>Event Full</button>'
  : isAdmin
    ? '<button type="button" class="btn-secondary" disabled>Admin View Only</button>'
    : '<button type="button" class="btn-register" onclick="openRegistrationModal(' + ev.id + ')">Register Now</button>';
```
✅ **Verify:** Log in as ADMIN/admin123 → Visit index.php → Cards show "Admin View Only" (disabled grey button).

---

### Q31 — Remember Me cookie on login

**Task:** "Add a 'Remember Me' checkbox that saves admission number in a cookie for 7 days."

📁 **File 1:** `login.php` — Find the login form (around admission number input) and **ADD**:
```html
<!-- ADD below the admission number input -->
<div style="margin:10px 0;">
  <label style="font-weight:normal; font-size:13px;">
    <input type="checkbox" name="rememberMe" value="1" 
           <?php echo isset($_COOKIE["remember_adm"]) ? "checked" : ""; ?>>
    Remember my Admission No for 7 days
  </label>
</div>
```

📁 **File 2:** `login_process.php` — Find the section where session is set after login success, **ADD**:
```php
// ADD after the successful login section:
if (!empty($_POST["rememberMe"])) {
    setcookie("remember_adm", $student["admission_no"], time() + (7 * 24 * 3600), "/");
} else {
    setcookie("remember_adm", "", time() - 3600, "/"); // Clear cookie if unchecked
}
```

📁 **File 3:** `login.php` — Find the admission number `<input>` and **ADD** value attribute:
```html
<!-- CHANGE the admission number input to pre-fill from cookie: -->
<input type="text" class="form-control" id="admissionNo" name="admissionNo" 
       value="<?php echo htmlspecialchars($_COOKIE['remember_adm'] ?? ''); ?>" 
       placeholder="e.g. 24JE0917" required>
```
✅ **Verify:** Login with Remember Me → Logout → Return to login.php → Admission number is pre-filled.

---

## 🗄️ LEVEL 4 — DATABASE / SQL CHANGES

---

### Q32 — Change a success/cancel message in PHP

**Task:** "Change the cancellation success message text."

📁 **File:** `api/cancel.php`  
📍 **Line 39** — Find and change:
```php
// BEFORE (line 39):
"message" => "Registration cancelled successfully. Your seat has been released!"

// AFTER:
"message" => "Your registration has been removed. The seat is now available to others."
```
✅ **Verify:** Cancel a registration → New message text appears in the popup.

---

### Q33 — Add a new column to the database

**Task:** "Add an `organizer` column to the events table."

**Step 1 — SQL in phpMyAdmin:**
```sql
ALTER TABLE events ADD COLUMN organizer VARCHAR(100) DEFAULT 'Student Gymkhana';
```

**Step 2 — Show it in API response (`api/get_events.php`):**  
Find the array where event data is built (around line 45–55) and add:
```php
"organizer" => $ev["organizer"] ?? "Student Gymkhana",
```

**Step 3 — Show it on cards (`assets/js/app.js`):**  
After the venue meta-item (line 104), **ADD**:
```javascript
'<div class="meta-item"><span><strong>Organizer:</strong> ' + escapeHtml(ev.organizer || 'Student Gymkhana') + '</span></div>' +
```
✅ **Verify:** All cards now show "Organizer: Student Gymkhana."

---

### Q34 — Create a SQL View for event occupancy

**Task:** "Create a database view showing event occupancy percentage."

**SQL to run in phpMyAdmin → SQL tab:**
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

**To demonstrate:**
```sql
SELECT * FROM view_event_occupancy;
```
✅ **Verify:** Run the SELECT → Shows table with occupancy_rate column for every event.

---

### Q35 — Add waitlist when event is full

**Task:** "When event is at capacity, put student on waitlist instead of rejecting them."

**Step 1 — SQL in phpMyAdmin:**
```sql
CREATE TABLE IF NOT EXISTS waitlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    admission_no VARCHAR(50),
    student_name VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(event_id, admission_no)
);
```

**Step 2 — `api/register.php` (Lines 67–74):**  
Find the `if ($currentCount >= $capacity)` block and **REPLACE**:
```php
// REPLACE lines 67-74:
if ($currentCount >= $capacity) {
    // Add to waitlist instead of rejecting
    $stmtWait = $pdo->prepare("INSERT IGNORE INTO waitlist (event_id, admission_no, student_name) VALUES (?, ?, ?)");
    $stmtWait->execute([$eventId, $admissionNo, $studentName]);
    echo json_encode(["success" => true, "message" => "Event is full! You have been added to the WAITLIST for '" . $event["title"] . "'."]);
    exit;
}
```
✅ **Verify:** Register for a full event → Gets waitlist message instead of rejection. Check `waitlist` table in phpMyAdmin.

---

### Q36 — Add partner/team field to registration modal

**Task:** "Add an optional 'Teammate Admission No' field in the registration modal."

📁 **File:** `index.php` — Find the registration modal form. Look for the department `<div class="form-group">` (around line 215) and **ADD AFTER IT**:
```html
<!-- ADD inside registration modal form, after department field -->
<div class="form-group" style="margin-top:10px;">
  <label for="regPartner">Teammate / Partner Admission No <em style="font-weight:normal;">(Optional)</em></label>
  <input type="text" class="form-control" id="regPartner" name="partner_admission_no" 
         placeholder="e.g. 24JE0101">
</div>
```
✅ **Verify:** Click Register Now → Modal opens → Shows new "Teammate" field below department.

---

## 📊 LEVEL 4 — ADMIN REPORTING FEATURES

---

### Q37 — Add Export CSV button for each event

**Task:** "Allow admin to download a CSV of all registrants for an event."

**Step 1 — Create new file `export_csv.php` in project root:**
```php
<?php
session_start();
if (empty($_SESSION["isAdmin"])) { http_response_code(403); exit("Unauthorized"); }
require_once "config/db.php";
$eventId = intval($_GET["event_id"] ?? 0);

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=event_{$eventId}_registrants.csv");

$out = fopen("php://output", "w");
fputcsv($out, ["Admission No", "Student Name", "Email", "Phone", "Department", "Registered At"]);

$stmt = $pdo->prepare("SELECT admission_no, student_name, email, phone, department, registered_at FROM registrations WHERE event_id = ? ORDER BY registered_at ASC");
$stmt->execute([$eventId]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($out, $row);
}
fclose($out);
```

**Step 2 — `admin.php`** — Find the Delete `<a>` link (around line 191) and **ADD BESIDE IT**:
```html
<!-- ADD next to Delete link -->
<a href="export_csv.php?event_id=<?php echo $ev["id"]; ?>" 
   class="btn-secondary" 
   style="text-decoration:none; display:inline-block; padding:4px 8px; font-size:12px;">
  Export CSV
</a>
```
✅ **Verify:** Click "Export CSV" → Browser downloads `.csv` file with registrant data.

---

### Q38 — Show department-wise breakdown under each event

**Task:** "Under each event, show how many registrants are from each department."

📁 **File:** `admin.php`  
📍 Find the `<strong>Registrants (...):</strong>` line (around line 228). **INSERT AFTER IT**:
```php
<!-- INSERT AFTER the Registrants count line -->
<?php
  $deptCounts = [];
  foreach ($evRegistrants as $reg) {
      $deptCounts[$reg["department"]] = ($deptCounts[$reg["department"]] ?? 0) + 1;
  }
?>
<?php if (!empty($deptCounts)): ?>
<div style="font-size:11px; color:#555; margin:4px 0 8px;">
  <strong>Dept Breakdown: </strong>
  <?php foreach ($deptCounts as $dept => $c): ?>
    <?php echo htmlspecialchars($dept) . ": <strong>$c</strong> | "; ?>
  <?php endforeach; ?>
</div>
<?php endif; ?>
```
✅ **Verify:** Open admin panel → Under each event, see "CSE: 2 | Mining: 1 |" etc.

---

### Q39 — Add Print Attendance Sheet button

**Task:** "Add a 'Print' button that opens the browser print dialog."

📁 **File 1:** `admin.php` — Find line 172–174 (`<div class="events-section-header">`) and **ADD inside it**:
```html
<!-- ADD inside events-section-header div -->
<button onclick="window.print()" class="btn-secondary" style="font-size:11px; padding:4px 10px;">
  🖨️ Print Attendance Sheet
</button>
```

📁 **File 2:** `assets/css/style.css` — At the **very bottom** of the file, **ADD**:
```css
/* ADD AT BOTTOM OF style.css */
@media print {
  .top-bar, .controls-card, .btn-cancel-reg, .btn-secondary, a { display: none !important; }
  body { background: #fff; color: #000; }
  .event-card { page-break-inside: avoid; border: 1px solid #333; }
}
```
✅ **Verify:** Click "Print Attendance Sheet" → Print dialog opens showing clean table layout.

---

## 🔒 LEVEL 5 — SECURITY & ADVANCED

---

### Q40 — Add a star rating field to registrations

**Task:** "Allow students to give a 1–5 rating after an event."

**Step 1 — SQL:**
```sql
ALTER TABLE registrations ADD COLUMN rating TINYINT NULL;
```

**Step 2 — Create `api/rate_event.php`:**
```php
<?php
session_start();
header("Content-Type: application/json");
require_once "../config/db.php";

$input = json_decode(file_get_contents("php://input"), true);
$regId = intval($input["registration_id"] ?? 0);
$rating = intval($input["rating"] ?? 0);

if ($regId <= 0 || $rating < 1 || $rating > 5) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Invalid rating. Must be 1-5."]);
    exit;
}

$stmt = $pdo->prepare("UPDATE registrations SET rating = ? WHERE id = ?");
$stmt->execute([$rating, $regId]);
echo json_encode(["success" => true, "message" => "Rating saved! Thank you."]);
```
✅ **Verify:** Run in browser console:  
`fetch('api/rate_event.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({registration_id:1,rating:5})})`  
Then check phpMyAdmin → `registrations` table → `rating` column = 5.

---

### Q41 — Track and show last login time

**Task:** "Record when student last logged in and show it in the navbar."

**Step 1 — SQL:**
```sql
ALTER TABLE students ADD COLUMN last_login_at DATETIME NULL;
```

**Step 2 — `login_process.php`** — After successful login verification, **ADD**:
```php
// ADD after login verification succeeds:
$pdo->prepare("UPDATE students SET last_login_at = NOW() WHERE admission_no = ?")->execute([$student["admission_no"]]);
$_SESSION["lastLoginDisplay"] = date("M d, g:i A");
```

**Step 3 — `index.php`** — Find the nav section and **ADD**:
```html
<!-- ADD in the navbar near logout button -->
<?php if (!empty($_SESSION["lastLoginDisplay"])): ?>
  <span style="font-size:11px; color:#ccc; margin-right:10px;">
    Last login: <?php echo $_SESSION["lastLoginDisplay"]; ?>
  </span>
<?php endif; ?>
```
✅ **Verify:** Log out → Log back in → Navbar shows "Last login: Nov 02, 8:30 PM."

---

### Q42 — Allow students to change their password

**Task:** "Add a password change feature."

**Create `api/change_password.php`:**
```php
<?php
session_start();
header("Content-Type: application/json");
if (!isset($_SESSION["admissionNo"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Not logged in."]);
    exit;
}
require_once "../config/db.php";

$input = json_decode(file_get_contents("php://input"), true);
$oldPass = $input["old_password"] ?? "";
$newPass = $input["new_password"] ?? "";

if (strlen($newPass) < 6) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "New password must be at least 6 characters."]);
    exit;
}

$stmt = $pdo->prepare("SELECT password FROM students WHERE admission_no = ?");
$stmt->execute([$_SESSION["admissionNo"]]);
$hash = $stmt->fetchColumn();

if (!password_verify($oldPass, $hash)) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Old password is incorrect."]);
    exit;
}

$newHash = password_hash($newPass, PASSWORD_DEFAULT);
$pdo->prepare("UPDATE students SET password = ? WHERE admission_no = ?")->execute([$newHash, $_SESSION["admissionNo"]]);
echo json_encode(["success" => true, "message" => "Password changed successfully!"]);
```
✅ **Verify:** Call from browser console with old and new passwords. Then log out and try logging in with new password.

---

### Q43 — Prevent time conflict registrations

**Task:** "Block a student from registering for two events at the same date/time."

📁 **File:** `api/register.php`  
📍 **After line 60** (after duplicate check block), **INSERT**:
```php
// === ADD TIME CONFLICT CHECK ===
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
    echo json_encode(["success" => false, "message" => "Schedule Conflict: You are already registered for '" . $conflict["title"] . "' at this same time!"]);
    exit;
}
```
✅ **Verify:** In phpMyAdmin, set 2 events to same `date_time`. Register for both with same roll → Second blocked with conflict message.

---

### Q44 — Use database transaction for atomic capacity enforcement

**Task:** "Make the capacity check atomic/thread-safe using a transaction."

📁 **File:** `api/register.php`  
📍 **Replace the entire `try { ... }` block (lines 36–103)** with:
```php
try {
    $pdo->beginTransaction();

    // Lock the event row (prevents race condition)
    $stmtEvent = $pdo->prepare("SELECT id, title, capacity FROM events WHERE id = ? FOR UPDATE");
    $stmtEvent->execute([$eventId]);
    $event = $stmtEvent->fetch();

    if (!$event) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Event not found."]);
        exit;
    }

    $capacity = (int) $event["capacity"];

    // Check duplicate
    $stmtDup = $pdo->prepare("SELECT id FROM registrations WHERE event_id = ? AND admission_no = ?");
    $stmtDup->execute([$eventId, $admissionNo]);
    if ($stmtDup->fetch()) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Already registered for '" . $event["title"] . "'."]);
        exit;
    }

    // Check capacity (INSIDE transaction = atomic)
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ?");
    $stmtCount->execute([$eventId]);
    $currentCount = (int) $stmtCount->fetchColumn();

    if ($currentCount >= $capacity) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Event is full."]);
        exit;
    }

    // Insert registration
    $stmtInsert = $pdo->prepare("INSERT INTO registrations (event_id, student_name, admission_no, email, phone, department) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtInsert->execute([$eventId, $studentName, $admissionNo, $email, $phone, $department]);

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Registration confirmed for $studentName ($admissionNo)!"]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
```
✅ **Verify:** Show TA the `beginTransaction()` and `FOR UPDATE` lock in the code. Explain: "This prevents two students from grabbing the last seat simultaneously."

---

### Q45 — Explain XSS protection (htmlspecialchars)

**Task:** "Where is XSS protection applied and how does it work?"

**Answer to explain:**  
All `admin.php` output uses `htmlspecialchars()`:

```php
// In admin.php (examples at lines 187, 188, 205, 249, 250, 251, 252):
echo htmlspecialchars($ev["title"]);        // Converts < > & " to safe HTML entities
echo htmlspecialchars($r["student_name"]);  // Prevents <script> injection
```

**If TA asks you to ADD it somewhere it's missing (e.g., in cancel.php line 45):**
```php
// BEFORE:
"message" => "No active registration found for " . $admissionNo . " in this event."

// AFTER (protected):
"message" => "No active registration found for " . htmlspecialchars($admissionNo) . " in this event."
```
✅ **Verify:** Try entering `<script>alert(1)</script>` as a name → It shows as literal text, not a popup.

---

### Q46 — Add phone number format validation

**Task:** "Only allow 10-digit phone numbers in registration."

📁 **File:** `api/register.php`  
📍 **After line 30** (validation block), find where error is returned and **ADD**:
```php
// ADD inside the validation check or after it:
if (!preg_match('/^[0-9]{10}$/', $phone)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Invalid phone number. Must be exactly 10 digits."]);
    exit;
}
```
✅ **Verify:** Submit registration with phone "abc" or "12345" → Error: "Must be exactly 10 digits."

---

### Q47 — Explain input sanitization with trim()

**Task:** "Where is input sanitization applied? Add trim() to a field."

📁 **File:** `api/register.php` — Lines 22–27 already have `trim()`:
```php
// Lines 22-27 — this IS the sanitization:
$studentName = isset($input["student_name"]) ? trim($input["student_name"]) : "";
$admissionNo = isset($input["admission_no"]) ? strtoupper(trim($input["admission_no"])) : "";
$email       = isset($input["email"])        ? strtolower(trim($input["email"]))        : "";
$phone       = isset($input["phone"])        ? trim($input["phone"])                    : "";
$department  = isset($input["department"])   ? trim($input["department"])               : "";
```

**`trim()` removes leading/trailing spaces.**  
**`strtoupper()` converts to uppercase (for roll numbers like 24je0917 → 24JE0917).**  
**`strtolower()` converts to lowercase (for consistent email storage).**

✅ **To demo:** Submit with `  24JE0917  ` (spaces) as roll number → Still works because `trim()` removes them.

---

### Q48 — Explain HTTP response codes used in the project

**Task:** "What HTTP codes does the API return? Add/change one."

**Current codes in project:**
```
200 OK         → Default success
405 Method Not Allowed  → Non-POST request to register.php / cancel.php
422 Unprocessable      → Missing required fields
409 Conflict           → Duplicate registration / event full
404 Not Found          → Event ID doesn't exist
500 Server Error       → Database/PDO exception
```

**If TA asks you to add 403 (Forbidden) for department restriction:**
```php
// In api/register.php (after the validation block):
http_response_code(403);  // 403 = Forbidden
echo json_encode(["success" => false, "message" => "Access denied for your department."]);
exit;
```
✅ **Verify:** Call API with wrong department → Browser dev tools → Network tab shows status 403.

---

### Q49 — Improve registration log with more details

**Task:** "The log file should include the student name and event title, not just IDs."

📁 **File:** `api/register.php`  
📍 **After line 81** (after the execute insert line), **ADD / REPLACE** the log entry:
```php
// ADD after the INSERT execute line:
$logEntry = date("Y-m-d H:i:s") 
    . " | BOOKED"
    . " | Event #" . $eventId . " (" . ($event["title"] ?? "Unknown") . ")"
    . " | Student: " . $admissionNo . " - " . $studentName
    . " | Email: " . $email
    . " | Dept: " . $department
    . PHP_EOL;
file_put_contents(__DIR__ . "/../registrations.log", $logEntry, FILE_APPEND);
```
✅ **Verify:** Register → Open `registrations.log` → Each line now shows full details.

---

### Q50 — Add session guard to API files

**Task:** "The API should reject requests from users who are not logged in."

📁 **File:** `api/register.php`  
📍 **After line 3** (`header("Content-Type: application/json...")`), **INSERT**:
```php
// ADD after header() line:
session_start();
if (!isset($_SESSION["admissionNo"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized: Please login first."]);
    exit;
}
```

📁 **Same for `api/cancel.php`** — After line 3, add the same block.

✅ **Verify:** Open browser console (without being logged in) and run:  
`fetch('api/register.php', {method:'POST', body:JSON.stringify({})})` → Returns `{"success":false,"message":"Unauthorized: Please login first."}`

---

## 🧠 QUICK CONCEPTS TO EXPLAIN VERBALLY

| Term | Say This |
|---|---|
| `$pdo->prepare()` | "Prepares a safe SQL query with placeholders to prevent SQL injection" |
| `$stmt->execute([...])` | "Runs the prepared query with actual values substituted for ?" |
| `$stmt->fetch()` | "Gets one row from the query result" |
| `$stmt->fetchAll()` | "Gets ALL rows from the result as an array" |
| `$stmt->fetchColumn()` | "Gets a single value (like COUNT(*)) from the first column" |
| `session_start()` | "Starts the PHP session so we can read/write $_SESSION variables" |
| `$_SESSION["key"]` | "Stores user login info on the server between page requests" |
| `json_encode([...])` | "Converts PHP array to JSON string to send back to JavaScript" |
| `http_response_code(409)` | "Sets the HTTP status code — 409 = Conflict (like duplicate entry)" |
| `header("Location: x")` | "Redirects browser to another page" |
| `htmlspecialchars()` | "Converts < > & to safe HTML — prevents XSS (script injection)" |
| `preg_match()` | "Tests a string against a regex pattern for validation" |
| `file_put_contents()` | "Writes text to a file; FILE_APPEND adds to end instead of overwriting" |
| `fetch()` in JS | "Makes an HTTP request from browser to PHP API without page reload (AJAX)" |
| `JSON.stringify()` | "Converts JS object to JSON string to send as request body" |
| `.then()` in JS | "Handles the response after the async fetch() call completes" |

---

## 📂 FILE → PURPOSE REFERENCE

| File | What it does |
|---|---|
| `index.php` | Main student page — shows events, modals |
| `admin.php` | Admin dashboard — publish/delete events, view registrants |
| `login.php` | Login form HTML |
| `login_process.php` | Login validation + session creation |
| `register.php` | Student account registration form |
| `register_process.php` | Inserts new student into DB |
| `logout.php` | Destroys session, redirects to login |
| `config/db.php` | PDO database connection (`$pdo` variable) |
| `api/get_events.php` | Returns all events as JSON (called by JS) |
| `api/register.php` | Handles booking (INSERT into registrations) |
| `api/cancel.php` | Handles cancellation (DELETE from registrations) |
| `api/my_registrations.php` | Returns student's bookings as JSON |
| `assets/css/style.css` | All styling — colors, layout, buttons |
| `assets/js/app.js` | All frontend logic — AJAX, event cards, modals |
| `schema.sql` | Database setup — CREATE TABLE statements |

---

*Last updated: 2026-11-02 | Exam prep file — keep this open in VS Code during evaluation*

