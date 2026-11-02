# Campus Event Registration System

IIT (ISM) Dhanbad - NCSM301 Web Development Laboratory
Technical Project Implementation

---

## 1. Project Overview

The Campus Event Registration System is a full-stack, session-authenticated web portal engineered for IIT (ISM) Dhanbad. The platform enables students to explore upcoming collegiate events, register for seats with real-time capacity tracking, and manage or cancel reservations through interactive modals without full page refreshes. An administrative dashboard provides role-protected controls to publish events, view real-time registrant rosters, and delete events with cascading database cleanup.

The system is deployed on XAMPP (Apache HTTP Server, PHP 8, and MariaDB) using a procedural three-tier architecture with native PDO database interactions and client-side AJAX via the Fetch API and jQuery 3.7.1.

---

## 2. Technology Stack

* **Presentation Tier**: HTML5, Vanilla CSS3 (responsive design, no external CSS framework), jQuery 3.7.1 (DOM manipulation and modal controls), Native JavaScript Fetch API.
* **Application Tier**: PHP 8.x (procedural), PHP Session Management, bcrypt password hashing.
* **Data Tier**: MariaDB / MySQL via PHP Data Objects (PDO) with prepared statements and parameter binding.
* **Environment**: XAMPP on localhost (`http://localhost/project/`).

---

## 3. Code Files and Project Structure

The project directory structure is organized as follows:

```text
project/
|-- index.php              Student event portal with dynamic card grid and modal drawer
|-- login.php              Student and administrator login form interface
|-- login_process.php      Authentication processor, password verification, session initialization
|-- register.php           Student account registration form
|-- register_process.php   Account creation processor, roll regex validation, password hashing
|-- admin.php              Administrator dashboard for publishing, monitoring, and deleting events
|-- logout.php             Session teardown and redirection handler
|-- schema.sql             Database DDL schema, relational constraints, and seed data
|-- README.md              Technical documentation, architecture guide, and setup instructions
|-- config/
|   `-- db.php             Centralized PDO database connection with strict exception modes
|-- api/
|   |-- get_events.php     REST API: returns event cards with live calculated seat metrics
|   |-- register.php       REST API: processes event bookings, enforces capacity and duplicates
|   |-- cancel.php         REST API: cancels student event bookings and frees reserved seats
|   `-- my_registrations.php REST API: returns student registration list with IDOR session guard
`-- assets/
    |-- css/
    |   `-- style.css      Global typography, layout grid, seat counter cards, and modal styles
    `-- js/
        |-- app.js         Client application logic, AJAX workflows, modal lifecycle, and DOM escaping
        `-- jquery.min.js  Local fallback for jQuery 3.7.1 library
```

### Detailed File Descriptions

* `index.php`: Main student-facing portal. Implements session validation to ensure only authenticated students gain access. Performs initial server-side rendering of event cards using a SQL LEFT JOIN query, which is subsequently refreshed dynamically via asynchronous JavaScript. Houses three modular popups: Event Confirmation, Quick Cancellation, and the "My Registrations" management drawer.
* `login.php`: Public authentication entry point. Displays login credentials inputs, validation messages, and flash error/success alerts passed via query string parameters. Automatically redirects authenticated sessions to `index.php`.
* `login_process.php`: Validates incoming POST credentials against the `students` database table using PDO prepared statements. Uses `password_verify()` to authenticate bcrypt password hashes. Populates session keys (`admissionNo`, `studentName`, `studentEmail`, `studentPhone`, `studentDepartment`, `isAdmin`) and redirects users to their appropriate role-based interface.
* `register.php`: New account signup portal. Features client-side pattern matching for IIT (ISM) admission numbers, password length constraints, and departmental dropdown options.
* `register_process.php`: Server-side registration logic. Enforces admission number format regex (`/^[0-9]{2}[A-Z]{2}[0-9]{4}$/`), checks password match and length constraints, generates bcrypt hashes via `password_hash($p, PASSWORD_DEFAULT)`, and traps MySQL error code 1062 to catch duplicate registrations cleanly.
* `admin.php`: Administrative control center protected by `$_SESSION["isAdmin"]`. Provides an event creation form that validates inputs before executing prepared INSERT statements. Renders existing events with real-time capacity meters and registrant tables ordered chronologically. Features single-click event deletion with browser confirmation and cascading foreign key removals.
* `logout.php`: Destroys the active session by invoking `session_unset()` followed by `session_destroy()`, terminating server-side session data and redirecting the browser to `login.php`.
* `schema.sql`: Complete SQL script containing table definitions (`students`, `events`, `registrations`), referential integrity constraints with ON DELETE CASCADE, compound UNIQUE indexes for duplicate prevention, administrative account credentials, and 8 realistic campus events with pre-seeded registrations.
* `config/db.php`: Database connection bootstrap. Configures PDO with `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`, `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`, and disables prepared statement emulation (`PDO::ATTR_EMULATE_PREPARES => false`) for maximum security.
* `api/get_events.php`: JSON API endpoint. Aggregates event records with subquery counts from the registrations table. Returns JSON arrays containing total capacity, registered seat counts, remaining seats, percentage filled, and boolean capacity flags.
* `api/register.php`: JSON API endpoint handling student event sign-ups. Performs validation against payload data, queries database records to verify existing capacity, checks for duplicate bookings by roll number or email address, writes new records, and synchronizes session state. Returns appropriate HTTP status codes (200, 404, 405, 409, 422, 500).
* `api/cancel.php`: JSON API endpoint. Receives target event ID and admission number via POST JSON, verifies parameter presence, executes a targeted DELETE statement against `registrations`, and releases the reserved seat.
* `api/my_registrations.php`: JSON API endpoint. Queries all events registered under an admission number. Includes an explicit IDOR protection guard verifying that the requested admission number matches the authenticated session user, returning HTTP 401 Unauthorized upon discrepancy.
* `assets/css/style.css`: Clean, framework-free stylesheet adhering to classic institutional layout guidelines. Sets border-box sizing globally, styles top navigation headers, formats responsive form grids, and controls modal visibility classes.
* `assets/js/app.js`: Client-side logic handler. Manages event loading cycles via Fetch API, serializes JSON payloads for booking and cancellation requests, updates DOM elements with jQuery, manages modal dialog states, and passes dynamic text through `escapeHtml()` for cross-site scripting defense.
* `assets/js/jquery.min.js`: Production build of jQuery 3.7.1 utilized for fast DOM selection and event wiring, loaded as a fallback if the primary CDN is unreachable.

---

## 4. Prerequisites

To execute and test this project locally, ensure the following software is installed:

1. **XAMPP** (version 8.0 or higher), containing:
   * Apache HTTP Server
   * PHP 8.x
   * MariaDB / MySQL
2. Any modern standard web browser (Google Chrome, Mozilla Firefox, Microsoft Edge, or Safari).

---

## 5. Deployment and Execution Steps

Follow these steps sequentially to set up and run the application:

### Step 1: Place Project in Web Root
Copy or clone the `project` directory into your XAMPP web root directory (`htdocs`):

* On Windows:
  ```text
  C:\xampp\htdocs\project\
  ```
* On Linux:
  ```text
  /opt/lampp/htdocs/project/
  ```
* On macOS:
  ```text
  /Applications/XAMPP/htdocs/project/
  ```

Ensure that the web root contains all application files directly inside the `project` folder such that `index.php` is accessible at `http://localhost/project/index.php`.

### Step 2: Start Application Services
1. Launch the **XAMPP Control Panel**.
2. Start the **Apache** module by clicking **Start**.
3. Start the **MySQL** module by clicking **Start**.
4. Confirm that both service indicators transition to green and display assigned port numbers (80/443 for Apache, 3306 for MySQL).

### Step 3: Import Database Schema and Seed Data
1. Open your web browser and navigate to the phpMyAdmin interface:
   ```text
   http://localhost/phpmyadmin/
   ```
2. Navigate to the **Import** tab located in the top navigation menu.
3. Click **Choose File** and locate `schema.sql` located within the root of the project folder.
4. Click **Import** (or **Go**) at the bottom of the page.

*Alternative SQL Method*:
1. Open the **SQL** tab inside phpMyAdmin.
2. Open `schema.sql` in any text editor, select and copy the complete text.
3. Paste the contents into the query box and click **Go**.

Upon successful execution, phpMyAdmin will create the `campus_events_db` database, construct the three required tables (`students`, `events`, `registrations`), and insert sample seed data.

### Step 4: Access Application
Open a web browser and visit:
```text
http://localhost/project/
```
Unauthenticated requests will automatically be redirected to `login.php`.

---

## 6. Default Login and Testing Credentials

### Student Accounts
You may create a new account via the registration interface (`register.php`) using any valid IIT (ISM) admission number format (e.g., `24JE0917`), or test with pre-seeded accounts:

* **Admission Number**: `24JE0917`
* **Pre-registered Events**: Concetto Autonomous RoboSoccer (ID: 2) and CodeISM Winter Coding Sprint (ID: 4)

### Administrator Account
To access the management panel, publish events, inspect registrant rosters, or delete events:

* **Admission / Roll No**: `ADMIN` (or `Admin`)
* **Password**: `admin123`
* **Direct URL**: `http://localhost/project/admin.php`

---

## 7. REST API Reference

All API endpoints return JSON with an appropriate HTTP response code:

| Endpoint | Method | Payload / Params | Status Codes | Description |
| :--- | :---: | :--- | :--- | :--- |
| `api/get_events.php` | GET | None | 200, 500 | Fetches list of all events with live seat counts, remaining seats, and percent filled metrics. |
| `api/register.php` | POST | JSON: `event_id`, `student_name`, `admission_no`, `email`, `phone`, `department` | 200, 404, 405, 409, 422, 500 | Validates fields, checks capacity and duplicate constraints, registers user, and updates session. |
| `api/cancel.php` | POST | JSON: `event_id`, `admission_no` | 200, 404, 405, 422, 500 | Deletes targeted registration row and restores seat count. |
| `api/my_registrations.php` | GET | Query string: `admission_no` | 200, 400, 401, 500 | Returns booked events for given admission number. Enforces IDOR security check against session. |

---

## 8. Security Measures

* **SQL Injection Prevention**: Prepared statements with bound parameters via PDO across all database interactions; emulation mode disabled (`PDO::ATTR_EMULATE_PREPARES => false`).
* **Cross-Site Scripting (XSS) Server-Side**: All user-supplied output in PHP templates is sanitized using `htmlspecialchars()`.
* **Cross-Site Scripting (XSS) Client-Side**: All server-returned strings pass through an `escapeHtml()` utility function before injection into the DOM.
* **Insecure Direct Object Reference (IDOR)**: `api/my_registrations.php` verifies that incoming roll number parameters match the active authenticated session before returning registration records.
* **Duplicate Prevention**: Enforced via dual-layer protection: application-level pre-checks before insertion, and database-level compound UNIQUE keys on `(event_id, admission_no)` and `(event_id, email)`.
* **Over-Capacity Mitigation**: Server-side query re-verifies real-time available seat count immediately prior to inserting any registration record.
* **Password Security**: Passwords hashed using bcrypt standard algorithm through `password_hash()` and authenticated via `password_verify()`.

