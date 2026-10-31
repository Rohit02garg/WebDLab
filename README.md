# Campus Event Registration System • IIT (ISM) Dhanbad

A clean, responsive, and beginner-friendly campus event registration portal developed for **Indian Institute of Technology (ISM) Dhanbad**. The portal allows students to explore campus events, track total capacity and dynamically updated remaining seats in real time. It features server-side concurrency locking to prevent duplicate registrations and over-capacity bookings, with asynchronous AJAX requests ensuring zero full-page reloads.

---

## Technical Stack (Strictly Vanilla)
- **HTML**: Standard HTML5 markup
- **CSS**: Pure entry-level CSS (`assets/css/style.css`)
- **JavaScript & jQuery**: Native JS & jQuery 3.7.1 (`assets/js/app.js`, `assets/js/jquery.min.js`)
- **PHP**: Vanilla PHP 8 with PDO prepared statements (`config/db.php`)
- **Database**: MariaDB / MySQL (InnoDB engine, transactions, row-level `FOR UPDATE` locking, unique constraints)
- **Server**: Apache Server (via XAMPP)

---

## Project Directory Structure
```text
project/
│
├── index.php                 # Main event portal (displays events, capacity, remaining seats)
├── login.php                 # Student login page (Admission No + Password)
├── login_process.php         # Server-side authentication & session handler
├── register.php              # Student registration page (Account creation)
├── register_process.php      # Registration processing & password hashing (bcrypt)
├── logout.php                # Session termination & redirect
├── schema.sql                # Complete MariaDB database schema & sample data
├── README.md                 # Project documentation
│
├── config/
│   └── db.php                # Database configuration & secure PDO connection
│
├── api/
│   ├── get_events.php        # API: Fetch all campus events with live seat statistics
│   ├── register.php          # API: Atomic registration with row locking & validation
│   ├── cancel.php            # API: Registration cancellation & dynamic seat restoration
│   └── my_registrations.php  # API: Retrieve registered events for active student
│
└── assets/
    ├── css/
    │   └── style.css         # Plain minimal stylesheet
    └── js/
        ├── jquery.min.js     # jQuery 3.7.1 library
        └── app.js            # Asynchronous AJAX controller & dynamic DOM updater
```

---

## Setup Instructions (XAMPP on Windows)

### 1. Place Project in `htdocs`
Place the `project` folder inside your XAMPP `htdocs` directory:
```text
C:\xampp\htdocs\project
```

### 2. Start Apache & MySQL
1. Open the **XAMPP Control Panel**.
2. Click **Start** for **Apache**.
3. Click **Start** for **MySQL**.

### 3. Import Database
In phpMyAdmin (`http://localhost/phpmyadmin`):
1. Click **Import** tab.
2. Select `schema.sql` from the project folder.
3. Click **Import**.
This creates the `campus_events_db` database with `events`, `students`, and `registrations` tables.

### 4. Open in Browser
Visit:
```text
http://localhost/project
```

---

## Concurrency Control & Duplicate Prevention

1. **Row-Level Locking (`FOR UPDATE`)**:
   `api/register.php` locks the event row within a PDO transaction to prevent race conditions during concurrent bookings.
2. **Over-Capacity Prevention**:
   The server checks registered count against event capacity while locked; rejects requests with HTTP 409 Conflict if full.
3. **Duplicate Registration Prevention**:
   Enforced via database unique constraints (`UNIQUE(event_id, admission_no)` and `UNIQUE(event_id, email)`).
