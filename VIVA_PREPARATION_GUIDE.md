# 🎓 NCSM301 Web Development Lab: Comprehensive Viva Defense Guide
### Campus Event Registration System — IIT (ISM) Dhanbad
**Student:** Rohit Garg | **Admission No:** 24JE0917  
**Tech Stack:** Apache, PHP 8 (Procedural), MariaDB (PDO), HTML5/Vanilla CSS, jQuery 3.7.1, Fetch API (JSON)

---

## 📑 TABLE OF CONTENTS
1. [Core Project Pitch (The 60-Second Opening Answer)](#1-core-project-pitch-the-60-second-opening-answer)
2. [Architecture & Technology Justifications](#2-architecture--technology-justifications)
3. [Database Deep-Dive (Schema, Relations, Constraints & Performance)](#3-database-deep-dive)
4. [Backend & PHP Internals (Sessions, Auth, PDO & Streams)](#4-backend--php-internals)
5. [REST API Layer & HTTP Protocol](#5-rest-api-layer--http-protocol)
6. [Frontend, jQuery & Asynchronous Data Exchange](#6-frontend-jquery--asynchronous-data-exchange)
7. [Security Architecture & Attack Defenses (SQLi, XSS, IDOR, CSRF)](#7-security-architecture--attack-defenses)
8. [The "Grill Me" Questions (Report Flaws, Race Conditions & Edge Cases)](#8-the-grill-me-questions-professors-favorite-traps)
9. [Rapid-Fire Keyword Glossary (Flashcard Style)](#9-rapid-fire-keyword-glossary)

---

## 1. Core Project Pitch (The 60-Second Opening Answer)

> **Prof:** *"Rohit, briefly describe your project, its architecture, and what problem it solves."*

**Your Exact Answer:**
> *"Sir, the Campus Event Registration System is a session-authenticated, full-stack web application designed for IIT (ISM) Dhanbad campus events like Srijan, Concetto, and Khanan.*  
> *It follows a classic **Three-Tier Architecture**:*  
> 1. *The **Presentation Tier** built with semantic HTML5, responsive CSS, and jQuery 3.7.1 with native Fetch API for an asynchronous, single-page application experience.*  
> 2. *The **Application Tier** in procedural PHP 8 handling session management, role-based access control (Admin vs Student), server-side input validation, and REST-style JSON endpoints.*  
> 3. *The **Data Tier** running on MariaDB via PHP Data Objects (PDO) with prepared statements and compound unique constraints to enforce data integrity.*  
> *Students can view live seat availability, register in one click, and manage their bookings, while administrators have a protected dashboard to publish/delete events and monitor real-time attendance rosters."*

---

## 2. Architecture & Technology Justifications

### Q1: Why procedural PHP instead of an OOP framework like Laravel or CodeIgniter?
* **Answer:**
  * *"For an institutional lab project adhering to NCSM301 curriculum specifications, procedural PHP ensures minimal server overhead, zero external dependencies, and instantaneous deployment on standard XAMPP.*
  * *It allows fine-grained control over raw HTTP request/response lifecycles, direct PDO statement preparation, and native session mechanics without framework abstraction hiding the fundamentals."*

### Q2: Why PDO (PHP Data Objects) over `mysqli`?
* **Answer:**
  * **Database Portability:** PDO works with 12 different database drivers (MySQL, PostgreSQL, SQLite, Oracle); `mysqli` only works with MySQL/MariaDB.
  * **Named Parameters:** PDO supports `:admission_no` style placeholders which make complex queries significantly more readable and maintainable than `mysqli`'s positional `?` markers.
  * **Unified Error Handling:** PDO throws catchable `PDOException` objects with standardized SQLSTATE error codes (e.g. `1062` for duplicate entry).
  * **Object/Array Mapping:** Methods like `fetch(PDO::FETCH_ASSOC)` provide flexible data mapping out of the box.

### Q3: Why both server-side initial rendering AND client-side Fetch API?
* **Answer:**
  * *"On initial page request (`index.php`), PHP performs a fast server-side `LEFT JOIN` query to immediately populate cards for fast First Contentful Paint (FCP) and SEO/graceful degradation.*
  * *Immediately after load, `app.js` uses asynchronous `fetch()` to hit `api/get_events.php` and refresh counts after any booking or cancellation without triggering a jarring full-page reload."*

---

## 3. Database Deep-Dive

### Q4: Explain your database schema and table relationships.
* **Tables:**
  1. `students`: Stores credentials and profiles (`id` PK, `admission_no` UNIQUE, `student_name`, `email`, `phone`, `department`, `password` bcrypt hash, `role` 'student'|'admin').
  2. `events`: Stores campus event listings (`id` PK, `title`, `description`, `category`, `date_time`, `venue`, `capacity`).
  3. `registrations`: Bridge/associative entity representing event bookings (`id` PK, `event_id` FK -> `events(id)` ON DELETE CASCADE, `admission_no`, `student_name`, `email`, `phone`, `department`, `registered_at`).

### Q5: Why is `ON DELETE CASCADE` used on `registrations.event_id`?
* **Answer:**
  * *"It guarantees **referential integrity**. If an administrator cancels or deletes an event in `admin.php`, MySQL automatically removes all associated student seat reservations in `registrations`. This prevents orphan records from lingering in the database."*

### Q6: Why did you denormalize fields (`student_name`, `email`, `phone`, `department`) in the `registrations` table instead of just keeping `student_id`?
* **Answer (Critical Defense!):**
  * **Historical Snapshot Integrity:** If a student later updates their email, phone, or name, the historical attendance roster for an already concluded event should reflect their credentials at the exact moment of registration.
  * **Query Optimization:** It enables `admin.php` and `api/event_attendees.php` to fetch full event rosters with a simple single-table query (`SELECT * FROM registrations WHERE event_id = ?`) without costly multi-table `INNER JOIN` operations on large student populations.

### Q7: How did you prevent duplicate bookings at the database level?
* **Answer:**
  * *We enforced two compound `UNIQUE` keys in MariaDB:*
    1. `UNIQUE KEY uq_event_student (event_id, admission_no)`
    2. `UNIQUE KEY uq_event_email (event_id, email)`
  * *Even if an application-layer check fails, MariaDB enforces this constraint at the storage engine level and rejects duplicate records with MySQL error code `1062`."*

### Q8: What SQL query calculates total registered and remaining seats?
* **Answer:**
  ```sql
  SELECT e.id, e.title, e.capacity,
         COUNT(r.id) AS registered_count,
         (e.capacity - COUNT(r.id)) AS remaining_seats
  FROM events e
  LEFT JOIN registrations r ON e.id = r.event_id
  GROUP BY e.id
  ORDER BY e.date_time ASC;
  ```
  * *Why `LEFT JOIN`?* If an event has 0 registrations, an `INNER JOIN` would omit the event entirely. `LEFT JOIN` preserves all events and returns `COUNT(r.id) = 0`.

---

## 4. Backend & PHP Internals

### Q9: Explain how PHP Sessions work internally.
* **Answer:**
  1. `session_start()` checks if an incoming HTTP request contains a cookie named `PHPSESSID`.
  2. If not found, PHP generates a cryptographically secure 26–32 character alphanumeric Session ID and sends it to the browser via the `Set-Cookie` HTTP response header.
  3. On the server filesystem (e.g. `xampp/tmp/sess_<ID>`), PHP creates a serialized file where `$_SESSION` data is persisted between requests.
  4. Subsequent requests pass the `PHPSESSID` cookie, allowing PHP to look up and deserialize the correct user state.

### Q10: What is the difference between `session_unset()` and `session_destroy()`?
* **Answer:**
  * `session_unset()` clears all variables stored in the active `$_SESSION` superglobal in memory (empties the array).
  * `session_destroy()` deletes the physical session storage file on disk and terminates the session storage backend.
  * In `logout.php`, we invoke **both** to guarantee complete state teardown.

### Q11: How is password storage handled? Why not `md5()` or `sha1()`?
* **Answer:**
  * *We use `password_hash($password, PASSWORD_DEFAULT)` and `password_verify($password, $hash)`.*
  * `md5()` and `sha1()` are general-purpose cryptographic hash functions designed to be fast, making them vulnerable to modern GPU brute-force attacks and rainbow tables.
  * `PASSWORD_DEFAULT` utilizes **bcrypt** (or Argon2 in newer PHP), which is a key-derivation function that includes a unique, automatically generated salt per hash and has an adjustable **work factor (cost)**, intentionally making brute-forcing computationally infeasible.

### Q12: Why do your API endpoints read from `php://input`?
* **Answer:**
  ```php
  $input = !empty($_POST) ? $_POST : json_decode(file_get_contents("php://input"), true);
  ```
  * *`$_POST` is only populated by PHP when the HTTP request has `Content-Type: application/x-www-form-urlencoded` or `multipart/form-data`.*
  * *When modern frontend clients (like JavaScript `fetch()`) send `Content-Type: application/json`, PHP does not automatically populate `$_POST`. The raw JSON payload must be read from the `php://input` read-only stream and parsed via `json_decode()`.*

---

## 5. REST API Layer & HTTP Protocol

### Q13: What HTTP status codes does your system use and why?
* **Answer:**
  * `200 OK` — Successful GET retrieval or successful mutation.
  * `400 Bad Request` — Missing parameters in query strings or policy violations.
  * `401 Unauthorized` — Access denied due to unauthenticated session.
  * `403 Forbidden` — Authenticated but unauthorized (e.g. Non-admin hitting admin action or wrong department).
  * `404 Not Found` — Event ID does not exist in the database or cancellation target not found.
  * `405 Method Not Allowed` — Sending a `GET` request to a `POST`-only mutation endpoint (`api/register.php` / `api/cancel.php`).
  * `409 Conflict` — State conflict (event is full or student already registered).
  * `422 Unprocessable Entity` — Syntactically valid request but missing required fields or failing regex.
  * `500 Internal Server Error` — Catch-all for unexpected database/PDO failures.

### Q14: What is the difference between `GET` and `POST` in your API?
* **Answer:**
  * `GET` (`api/get_events.php`, `api/my_registrations.php`) must be **idempotent and safe** — it only retrieves state without modifying database records.
  * `POST` (`api/register.php`, `api/cancel.php`) is a **mutation method** — it creates or alters state in the database, sends data in the HTTP message body rather than URL query parameters, and is not cached by browsers.

---

## 6. Frontend, jQuery & Asynchronous Data Exchange

### Q15: Explain how `loadEvents()` dynamically builds the DOM.
* **Answer:**
  1. `fetch("api/get_events.php")` retrieves the current event array as JSON.
  2. `renderEvents(events)` clears the existing `#eventsGrid` container using jQuery `$grid.empty()`.
  3. It iterates over each event and checks `remaining_seats <= 0`. If full, it renders a disabled button; otherwise, an active one.
  4. It constructs HTML strings safely, passing all user strings through `escapeHtml()` to neutralize script tags.
  5. `$grid.append(cardHtml)` injects all cards into the live DOM in one pass.

### Q16: How did you implement modal dialogs without Bootstrap?
* **Answer:**
  * *We used vanilla CSS classes `.modal-backdrop` and `.modal-backdrop.open`.*
  * By default, `.modal-backdrop` has `display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 999;`.
  * `openModal(id)` simply adds the `.open` class (which sets `display: block`) and sets `document.body.style.overflow = 'hidden'` to prevent background page scrolling.
  * `closeAllModals()` removes `.open` and restores scrolling.

### Q17: What does `e.preventDefault()` do in your form submit handlers?
* **Answer:**
  * *By default, submitting an HTML form causes the browser to reload the page or navigate to the form's `action` URL.*
  * `e.preventDefault()` halts this standard browser submission, allowing our jQuery script to extract the form input values, construct a JSON payload, and transmit it silently via `fetch()` without reloading the page.

---

## 7. Security Architecture & Attack Defenses

### Q18: How does your application prevent SQL Injection?
* **Answer:**
  1. **Prepared Statements:** All SQL queries use parameter placeholders (`?` or `:name`).
  2. **Disabled Emulation:** In `config/db.php`, we explicitly set:
     ```php
     PDO::ATTR_EMULATE_PREPARES => false
     ```
     This forces MariaDB itself to compile the SQL query template and plan **before** receiving user input. The input values are transferred separately across the wire and are strictly treated as literals, making SQL injection mathematically impossible regardless of input quotes or semicolons.

### Q19: How do you protect against Cross-Site Scripting (XSS)?
* **Answer:**
  * **Server-side (PHP):** Any output reflected into HTML (like student names or errors in `index.php` and `admin.php`) is sanitized using `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')`. This converts `<`, `>`, `&`, `"`, and `'` into safe HTML entities (`&lt;`, `&gt;`).
  * **Client-side (JS):** In `app.js`, server-returned event titles and venues pass through `escapeHtml()` before string concatenation into the DOM.

### Q20: What is IDOR (Insecure Direct Object Reference) and where could it occur?
* **Answer:**
  * *IDOR occurs when an application exposes a reference to an internal object (like an admission number) and trusts user-submitted input without verifying that the authenticated user owns that identity.*
  * **In our system:** In `api/cancel.php`, a malicious user could inspect the network traffic and send another student's admission number to cancel their booking.
  * **Mitigation:** We enforce session ownership by checking `$_SESSION["admissionNo"]` and verifying that the cancelling user can only cancel their own seat.

---

## 8. The "Grill Me" Questions (Professor's Favorite Traps!)

### Trap 1: *"Your report states: 'registrations beyond capacity blocked (not atomic)'. What does that mean? What is a race condition here?"*
* **The Trap:** The professor noticed your report's honest technical note!
* **Your Flawless Defense:**
  > *"Sir, in procedural code, `api/register.php` executes two separate steps:*  
  > 1. *It queries `SELECT COUNT(*) FROM registrations WHERE event_id = ?`.*  
  > 2. *If count < capacity, it proceeds to execute `INSERT INTO registrations`.*  
  > *If only 1 seat remains and two students submit at the exact same millisecond, both requests execute step 1 simultaneously, both see count < capacity, and both execute step 2. This causes an **oversubscription race condition**.*  
  > *To make it strictly atomic, we use a database transaction with row locking:*  
  > ```php
  > $pdo->beginTransaction();
  > $stmt = $pdo->prepare('SELECT capacity FROM events WHERE id = ? FOR UPDATE');
  > // check count inside transaction
  > // insert
  > $pdo->commit();
  > ```
  > *The `FOR UPDATE` lock forces concurrent requests to wait sequentially in a queue."*

### Trap 2: *"Why does `index.php` have both CDN jQuery and a local fallback?"*
* **Your Answer:**
  * *Line 360 has:*
    ```html
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>window.jQuery || document.write('<script src="assets/js/jquery.min.js"><\/script>');</script>
    ```
  * *"This ensures high availability. Under normal conditions, loading jQuery from the official Google/jQuery CDN provides fast caching and CDN edge routing. However, during an offline lab evaluation or network outage, `window.jQuery` is undefined, immediately triggering the fallback to the local `assets/js/jquery.min.js` file so the app remains 100% operational."*

### Trap 3: *"Why did you use `datetime-local` input in admin publish form?"*
* **Your Answer:**
  * *`datetime-local` produces ISO-like format `YYYY-MM-DDTHH:MM`, which PHP easily parses into a standard MySQL `DATETIME` (`YYYY-MM-DD HH:MM:SS`) using `new DateTime($_POST['date_time'])`.*

### Trap 4: *"Can a student register twice with different capitalization, like '24je0917' and '24JE0917'?"*
* **Your Answer:**
  * *"No, sir. We sanitize the roll number server-side in `api/register.php` with `$admissionNo = strtoupper(trim($input['admission_no']))`. In addition, MySQL collation for our database is `utf8mb4_unicode_ci`, where `_ci` stands for **case-insensitive**, so database-level unique keys also reject case variations."*

---

## 9. Rapid-Fire Keyword Glossary

| Term | What you should say in 1 sentence |
|---|---|
| **PDO** | Database abstraction interface in PHP providing prepared statements and secure parameter binding. |
| **`ATTR_EMULATE_PREPARES`** | PDO setting which, when false, delegates query compilation to MariaDB rather than PHP emulating it. |
| **bcrypt** | Adaptive, salted cryptographic password hashing algorithm utilized by `password_hash()`. |
| **LEFT JOIN** | SQL join returning all records from the left table (`events`) even when no matching rows exist on the right (`registrations`). |
| **SPA** | Single Page Application — web app that interacts with user by dynamically rewriting current web page rather than loading entire new pages from server. |
| **AJAX** | Asynchronous JavaScript and XML — technique to send/receive data from server asynchronously without page reloads. |
| **Idempotency** | Property of HTTP methods (like GET, PUT, DELETE) where making the same request multiple times produces the identical side-effect. |
| **XSS** | Cross-Site Scripting — injection attack where malicious scripts are executed in trusted browser context; prevented via `htmlspecialchars()`. |
| **IDOR** | Insecure Direct Object Reference — accessing database objects using raw client-supplied parameters without authorization verification. |
| **CSRF** | Cross-Site Request Forgery — tricking authenticated browser into transmitting unauthorized commands; mitigated using secret session tokens. |
| **Bcrypt Cost** | The work factor parameter determining how many iterations of hashing are computed to prevent hardware cracking. |
| **`ON DELETE CASCADE`** | Foreign key property that automatically deletes dependent child records when the referenced parent record is deleted. |

---
*Created for Rohit Garg (24JE0917) — NCSM301 Lab Final Project Defense*
