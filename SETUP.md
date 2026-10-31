# Campus Event Registration System - Setup Guide (P3)

This guide provides step-by-step instructions for teammates to run the project locally using XAMPP (Apache, PHP 8, and MariaDB).

---

## 1. Prerequisites
* **XAMPP** installed on your system (includes Apache web server, PHP 8, and MariaDB/MySQL).
* Any modern web browser (Chrome, Firefox, Edge).

---

## 2. Setup Steps

### Step 1: Copy Project to XAMPP Document Root
Clone or copy the `project` folder into your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\project\
```
*(On macOS/Linux, place inside `/opt/lampp/htdocs/project/` or `/Applications/XAMPP/htdocs/project/`).*

---

### Step 2: Start Apache and MySQL
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL** (MariaDB).
4. Verify that both modules turn green.

---

### Step 3: Import the Database
1. Open your browser and navigate to:
   ```
   http://localhost/phpmyadmin/
   ```
2. Click on the **Import** tab at the top.
3. Click **Choose File** and select `schema.sql` located in the `project/` root folder.
4. Click **Go** (or **Import**) at the bottom.
   
*(Alternatively: Open the **SQL** tab in phpMyAdmin, paste the entire contents of `schema.sql`, and click **Go**).*

This will automatically:
* Create the database `campus_events_db`.
* Create the tables: `students`, `events`, and `registrations`.
* Seed default campus events and the pre-configured administrator account.

---

### Step 4: Open the Application
Navigate to the project in your browser:
```
http://localhost/project/
```
If you are not logged in, you will be redirected to the login page (`login.php`).

---

## 3. Login Credentials

### Student Access
* **Option A (Create New)**: Click **"Register here"** on the login page, fill in your details (e.g. Roll No: `24JE0917`), and log in.
* **Option B (Existing Seed)**: Register any student account with format `24JExxxx`.

### Admin Access
To publish new campus events, delete events, or view registered students:
* **Admission / Roll No**: `Admin` *(or `ADMIN`)*
* **Password**: `admin123`

---

## 4. Tech Stack & File Structure
* **Frontend**: Vanilla HTML5, minimal CSS (`assets/css/style.css`), jQuery 3.7.1, and native JavaScript `fetch()` API (`assets/js/app.js`).
* **Backend**: Vanilla PHP 8 with PDO prepared statements (`config/db.php`).
* **Database**: MariaDB / MySQL (`schema.sql`).
* **APIs**:
  * `api/get_events.php` — Fetches real-time event details, total capacity, and remaining seats.
  * `api/register.php` — Registers a student, prevents duplicate bookings, and enforces maximum capacity.
  * `api/cancel.php` — Releases a student's reserved seat.
  * `api/my_registrations.php` — Retrieves all booked events for a student.

