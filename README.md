# Advanced PHP CRUD Project with Validations, Auth, RBAC, File Upload, and API

This project contains the implementations for Task 3 (Validation & Error Handling), Task 4 (Role-Based Access Control, Profile Management with Picture Uploads, and Weather API Integration), and Task 5 (Documentation & Finalization).

To strictly follow instructions, this application has **no CSS styling or layouts (runs in raw browser HTML)**.

## Project Structure

* `db.php` - Establishes SQLite database connection using PDO. Automatically sets up database tables and handles errors securely.
* `register.php` - Secure signup page. Includes JavaScript validation (client-side) and PHP sanitization, email verification, password length checks, and secure hashing (server-side).
* `login.php` - Authentication page that validates credentials and creates sessions.
* `logout.php` - Ends user session.
* `dashboard.php` - Displays user role status, role-based navigation, and displays real-time Hyderabad weather fetched from the Open-Meteo external API.
* `profile.php` - Allows users to view/edit details and upload profile pictures. Features file MIME type verification, size checks (max 2MB), and safe unique filename generation.
* `manage_users.php` - User management table (accessible to Admin role only; returns 403 Forbidden for regular users).
* `edit_user.php` - Admin page to modify any user's role or credentials.
* `delete_user.php` - Admin script to remove user records.
* `uploads/` - Directory where uploaded user profile pictures are stored.

---

## Technical Details

### Task 3: Validation & Error Handling
* **Client-side JS Validation**: Forms include `onsubmit` validation functions checking empty inputs, email formatting using regular expressions, and minimum password lengths.
* **Server-side Validation**: All fields are validated, trimmed, and sanitized in PHP. Prepared SQL statements are used universally for database execution to prevent SQL injection.
* **Error Handling**: Database operations use standard `try-catch` blocks rendering user-friendly generic notices while logging technical details.

### Task 4: Advanced Features
* **Role-Based Access Control (RBAC)**: Users are designated as `admin` or `user` roles. Session parameters check if a user is authorized. Attempting to load admin tools without permission displays a raw HTTP 403 Forbidden page.
* **Profile Management & Uploads**: Users can upload pictures. Code checks:
  * File upload errors
  * Max size (2MB)
  * MIME validation via `finfo` (Only standard JPG, PNG, and GIF allowed)
  * Unique prefix to prevent filename hijacking
* **API Integration**: Fetches real-time weather stats using Open-Meteo JSON APIs with a connection timeout to keep pages responsive.

---

## Running Instructions

### 1. Start the PHP Built-in Server
Open your terminal inside this folder (`C:\Users\B SRAVANTHI\.gemini\antigravity\scratch\php-advanced-tasks`) and start the server:
```bash
php -S localhost:8000
```

### 2. Access the Application
Open your web browser and navigate to:
* **Register Admin/User:** [http://localhost:8000/register.php](http://localhost:8000/register.php)
* **Login:** [http://localhost:8000/login.php](http://localhost:8000/login.php)
* **Dashboard:** [http://localhost:8000/dashboard.php](http://localhost:8000/dashboard.php)
