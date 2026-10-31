# Campus Event Registration System • IIT (ISM) Dhanbad

A clean, responsive, and beginner-friendly campus event registration portal developed for **Indian Institute of Technology (Indian School of Mines), Dhanbad**. The portal allows students to explore campus events, track total capacity, registered participants, and dynamically updated remaining seats in real time. It features robust server-side concurrency locking to prevent duplicate registrations and over-capacity bookings, with asynchronous AJAX requests ensuring zero full-page reloads.

---

## 🏛️ Campus Context & Highlights
- **Institution**: IIT (ISM) Dhanbad (Estd. 1926)
- **Campus Hostels Featured**: Aquamarine, Amber, Jasper, Diamond, Opal, Topaz, Sapphire, Ruby, Rosaline, Emerald
- **Campus Venues**: Penman Auditorium, Golden Jubilee Lecture Theatre (GJLT), Student Activity Centre (SAC), Central Library Auditorium, Computer Centre, EDC Seminar Hall
- **Major Fests & Events**:
  - **Srijan 2026**: Battle of the Bands (Eastern India's largest socio-cultural fest)
  - **Concetto**: Autonomous RoboSoccer (Annual techno-management fest)
  - **Khanan**: Geo-Mining AI Hackathon (Mining engineering & earth sciences)
  - **Parakram**: Inter-Hostel Badminton Clash (Campus sports fest)
  - **Basant**: Kavyanjali Poetry Slam (Alumni reunion cultural meet)
  - **CodeISM**: Winter Algorithmic Sprint (Competitive programming)

---

## 💻 Technical Stack (Strictly Vanilla)
- **Markup**: Pure HTML5 (Semantic, accessible, SEO meta tags)
- **Styling**: Vanilla CSS3 (`assets/css/style.css` - Custom properties, responsive grid, dynamic progress indicators, glassmorphism)
- **Frontend Logic**: JavaScript & jQuery 3.7.1 (`assets/js/app.js` - Stored locally for 100% offline reliability)
- **Server-Side**: PHP 8 (Clean, modular, procedural/object-oriented with PDO prepared statements)
- **Database**: MariaDB / MySQL with **InnoDB Engine** (Atomic transactions, `FOR UPDATE` row-level locking, and unique constraints)

---

## 📁 Project Directory Structure
```text
Rohanp/
│
├── index.php                 # Main responsive portal landing page
├── schema.sql                # Complete MariaDB database schema & sample data
├── README.md                 # Complete setup, testing, and architecture guide
│
├── config/
│   └── db.php                # Database configuration & secure PDO connection
│
├── api/
│   ├── get_events.php        # API: Fetch all campus events with real-time seat stats
│   ├── register.php          # API: Atomic registration with concurrency locking & validation
│   ├── cancel.php            # API: Registration cancellation & dynamic seat restoration
│   └── my_registrations.php  # API: Retrieve registered events for a student admission no
│
└── assets/
    ├── css/
    │   └── style.css         # Custom campus-themed stylesheet (IIT ISM Navy & Gold)
    └── js/
        ├── jquery.min.js     # jQuery 3.7.1 minified library (offline bundle)
        └── app.js            # Client-side AJAX controller & dynamic DOM updater
```

---

## 🚀 Local Setup Instructions (XAMPP on Windows)

### Prerequisites
1. **XAMPP** installed (typically at `C:\xampp` or `C:\xampp\php`).
2. Apache and MySQL / MariaDB modules.

### Step 1: Place Project in `htdocs`
Ensure the project folder `Rohanp` is placed inside your XAMPP `htdocs` directory:
```text
C:\xampp\php\htdocs\Rohanp
```
*(Or `C:\xampp\htdocs\Rohanp` depending on your XAMPP installation directory).*

### Step 2: Start Apache & MySQL in XAMPP
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

### Step 3: Import the Database Schema
You can import the database using either **phpMyAdmin** or the **Command Line**:

#### Option A: Using phpMyAdmin (Browser)
1. Open your web browser and navigate to `http://localhost/phpmyadmin` (or `http://localhost:8080/phpmyadmin` if Apache uses port 8080).
2. Click the **Import** tab at the top.
3. Click **Choose File** and select `schema.sql` located inside `htdocs/Rohanp/schema.sql`.
4. Click **Import** at the bottom.
5. The `campus_events_db` database along with `events` and `registrations` tables and sample data will be created automatically.

#### Option B: Using Command Line / Terminal
Open PowerShell or Command Prompt and run:
```powershell
# Navigate to your XAMPP mysql directory (or use system PATH)
Get-Content "C:\xampp\php\htdocs\Rohanp\schema.sql" | & "C:\xampp\php\mysql\bin\mysql.exe" -u root
```

### Step 4: Verify Database Connection (`config/db.php`)
The default credentials in `config/db.php` are:
```php
$host     = '127.0.0.1';
$port     = '3306';
$dbname   = 'campus_events_db';
$username = 'root';
$password = '';
```
If your local MariaDB uses a root password or a custom port, adjust these values in [config/db.php](file:///c:/xampp/php/htdocs/Rohanp/config/db.php).

### Step 5: Launch the Application
Open your browser and navigate to:
```text
http://localhost/Rohanp/
```
*(Or `http://localhost:8080/Rohanp/` if your Apache port is 8080, or `http://localhost:8000/` if using the PHP built-in server `php -S localhost:8000`).*

---

## 🔒 Concurrency Control & Duplicate Prevention

### 1. Row-Level Locking (`FOR UPDATE`)
To prevent race conditions when multiple students attempt to register for the final seat simultaneously, `api/register.php` wraps the operation in an InnoDB transaction and locks the event row:
```sql
SELECT id, title, capacity FROM events WHERE id = ? FOR UPDATE;
```
No other transaction can read or update the event's registration state until the transaction commits or rolls back.

### 2. Over-Capacity Prevention
The server counts the registered participants inside the locked transaction:
```php
if ($currentCount >= $capacity) {
    $pdo->rollBack();
    http_response_code(409); // Conflict
    echo json_encode(['success' => false, 'message' => 'Event reached maximum capacity.']);
    exit;
}
```

### 3. Duplicate Registration Prevention
Both server-side application logic and database-level constraints guarantee that a student cannot register twice for the same event:
- **Application Validation**:
  ```sql
  SELECT id FROM registrations WHERE event_id = ? AND (admission_no = ? OR email = ?);
  ```
- **Database Unique Constraints**:
  ```sql
  CONSTRAINT `unique_event_admission` UNIQUE (`event_id`, `admission_no`),
  CONSTRAINT `unique_event_email` UNIQUE (`event_id`, `email`)
  ```

---

## 🧪 Testing Guide

### 1. Test Last Seat Booking & Over-Capacity
- In the initial dataset, **Event 6 (Parakram: Inter-Hostel Badminton Clash)** has a capacity of `6` with `5` registered participants (**1 seat remaining**).
- Click **Register Now** on Event 6.
- Enter details:
  - Full Name: `Manish Tiwari`
  - Admission No: `22JE0999`
  - Email: `manish.t@iitism.ac.in`
  - Mobile: `9876543210`
  - Hostel: `Amber`
  - Department: `Computer Science & Engineering`
- Click **Confirm Registration**.
- Notice:
  1. Success toast notification appears.
  2. Remaining seats dynamically updates from `1` to `0`.
  3. Registered count increases from `5` to `6`.
  4. Status badge changes to **"Full / Capacity Reached"**.
  5. The button dynamically becomes disabled with **"🚫 Event Full"**.
  6. **Zero page reload occurred!**

### 2. Test Duplicate Registration Rejection
- Try registering again for the same event using the same Admission Number (`22JE0999`) or Email (`manish.t@iitism.ac.in`).
- The server will immediately reject the request with HTTP 409 Conflict:
  > *"Duplicate Registration: Admission No '22JE0999' or email is already registered for 'Parakram: Inter-Hostel Badminton Clash'."*

### 3. Test Registration Cancellation & Dynamic Seat Restoration
- Click **My Registrations** in the top navigation bar.
- Enter Admission Number `22JE0999` and click **Find My Events**.
- Your registered event (*Parakram Badminton*) is displayed.
- Click **Cancel Registration** and confirm.
- Notice:
  1. The registration card slides away.
  2. Success toast appears.
  3. Close the modal: On the main page, Event 6's remaining seats has **dynamically increased back to 1**!
  4. The register button is **re-enabled** (*🎟️ Register Now*)!
  5. Hero statistics automatically update.

---

## 📡 REST API Specifications

### `GET api/get_events.php`
- **Parameters**: `id` (optional), `category` (optional)
- **Response**:
```json
{
  "success": true,
  "count": 8,
  "events": [
    {
      "id": 1,
      "title": "Srijan 2026: Battle of the Bands",
      "category": "Cultural",
      "venue": "Penman Auditorium",
      "capacity": 30,
      "registered_count": 2,
      "remaining_seats": 28,
      "percent_filled": 7,
      "is_full": false
    }
  ]
}
```

### `POST api/register.php`
- **Payload**: `event_id`, `student_name`, `admission_no`, `email`, `phone`, `hostel`, `department`
- **Response (200 OK)**:
```json
{
  "success": true,
  "message": "Registration confirmed for Manish Tiwari (22JE0999) at 'Parakram'!",
  "data": {
    "registration_id": 12,
    "event_id": 6,
    "registered_count": 6,
    "remaining_seats": 0,
    "is_full": true
  }
}
```
- **Error (409 Conflict)**: Duplicate registration or event full.

### `POST api/cancel.php`
- **Payload**: `event_id`, `admission_no`
- **Response (200 OK)**:
```json
{
  "success": true,
  "message": "Registration cancelled successfully. Seat freed!",
  "data": {
    "event_id": 6,
    "registered_count": 5,
    "remaining_seats": 1,
    "is_full": false
  }
}
```

### `GET api/my_registrations.php`
- **Parameters**: `admission_no`
- **Response (200 OK)**:
```json
{
  "success": true,
  "count": 1,
  "registrations": [
    {
      "registration_id": 12,
      "event_title": "Parakram: Inter-Hostel Badminton Clash",
      "venue": "SAC Sports Complex",
      "event_date": "Dec 05, 2026",
      "hostel": "Amber"
    }
  ]
}
```
