# Student Disciplinary System

A simple server-rendered PHP application to manage students and disciplinary incident reports. Designed for XAMPP (Windows) with PDO/MySQL.

---

## Project overview
- Purpose: CRUD for students and incidents, quick search, delete confirmation modal, and a site-wide dark mode toggle.
- Stack: PHP (plain), PDO, MySQL/MariaDB, minimal JavaScript for UI behaviors.
- Location: c:\xampp\htdocs\student_disciplinary_system\student-disciplinary-system

---

## Key features
- Student CRUD (create, list, edit, delete).
- Incident CRUD (create, list sorted most-recent-first, edit, delete).
- AJAX search endpoint for student autocomplete.
- Delete confirmation modal requiring hardcoded admin/admin credentials (client-side).
- Site-wide dark mode (persisted in localStorage).

---

## Quick setup
1. Ensure XAMPP (Apache + MySQL) is running.
2. Create a database and import schema (example below).
3. Configure PDO in bootstrap (e.g., `public/index.php`) — set `$GLOBALS['pdo']` or pass a PDO instance to controllers.
4. Place project in `htdocs` and visit via browser: `http://localhost/<base-path>/`.

---

## Database schema (example)

```sql
CREATE TABLE student (
  StudentID INT PRIMARY KEY,
  EnrollmentNo VARCHAR(32) UNIQUE NOT NULL,
  FirstName VARCHAR(128),
  LastName VARCHAR(128),
  DOB DATE NULL,
  Gender VARCHAR(16) NULL,
  Email VARCHAR(255) NULL,
  Phone VARCHAR(64) NULL,
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE staff (
  StaffID INT PRIMARY KEY AUTO_INCREMENT,
  Name VARCHAR(255) NOT NULL
);

CREATE TABLE incidentreport (
  IncidentID INT PRIMARY KEY AUTO_INCREMENT,
  ReportDate DATETIME NULL,
  Location VARCHAR(255) NULL,
  ReporterStaffID INT,
  StudentID INT,
  Description TEXT,
  Status VARCHAR(64) DEFAULT 'Pending',
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ReporterStaffID) REFERENCES staff(StaffID),
  FOREIGN KEY (StudentID) REFERENCES student(StudentID)
);
```

Notes:
- Controllers use `COALESCE(ReportDate, CreatedAt) DESC` to show most recent incidents first when `ReportDate` can be NULL.

---

## File structure

```
student-disciplinary-system/
├─ public/
│  └─ index.php
├─ src/
│  ├─ Controller/
│  │  ├─ StudentController.php
│  │  └─ DisciplinaryController.php
│  ├─ views/
│  │  ├─ _theme_head.php
│  │  └─ _theme_body.php
│  └─ ...
└─ README.md
```

---

## Routing
- `public/index.php` is a simple router. Example routes:
  - `/students` (list)
  - `/students/create`
  - `/students/edit?id=...`
  - `/students/delete?id=...` (POST)
  - `/students/searchJson?q=...` (AJAX)
  - `/incidents`, `/incidents/create`, `/incidents/edit`, `/incidents/delete` (similar)

---

## Controllers — responsibilities
- StudentController
  - `index()` — list students (table with Edit/Delete).
  - `create()` — GET/POST for new student (generates EnrollmentNo).
  - `edit()` — GET/POST for updating student.
  - `delete()` — POST deletes by StudentID.
  - `searchJson()` — returns JSON list for autocomplete.

- DisciplinaryController
  - `index()` — list incidents (most recent first).
  - `create()` — new incident form and insert.
  - `edit()` — edit incident (prefilled form).
  - `delete()` — POST deletes by IncidentID.

---

## Frontend behaviors
- Dark mode
  - Centralized includes: `src/views/_theme_head.php` (CSS variables + dark overrides) and `_theme_body.php` (toggle button + JS).
  - Toggle persists choice in `localStorage` key `site-theme`. Uses `data-theme="dark"` on `<html>`.
  - To enable on a page: inside `<head>` add `<?php require_once __DIR__ . '/../views/_theme_head.php'; ?>` and immediately after `<body>` add `<?php require_once __DIR__ . '/../views/_theme_body.php'; ?>`.

- Delete modal
  - Delete buttons open a modal asking for username/password.
  - Client-side check: username === 'admin' && password === 'admin'. On success the POST form is submitted; on failure an error message is shown.
  - Security note: This is client-side and insecure for production — replace with server-side auth.

- AJAX search
  - Endpoint returns JSON objects: `{ id, enrollment, name }`.
  - Intended for incident forms to populate/select a student.

---

## Security considerations
- The current deletion credential check is client-side only and not secure.
- Recommended changes before production:
  - Implement server-side authentication and authorization.
  - Add CSRF tokens to POST forms.
  - Use HTTPS.
  - Store admin credentials securely (never in client JS).

---

## Maintenance & extension suggestions
- Move repeated HTML/CSS/JS into shared view includes (already done for theme).
- Consider extracting DB code into models or a small framework for routing/views.
- Replace client-side delete auth with proper server-side sessions/roles.
- Normalize colors to CSS variables throughout the project.

---

## Troubleshooting
- Dark mode not applying:
  - Ensure `_theme_head.php` and `_theme_body.php` are included.
  - Check DevTools console for JS errors.
  - Confirm `localStorage.getItem('site-theme')` and `document.documentElement.getAttribute('data-theme')`.

- Delete not working:
  - Ensure delete form uses `method="post"` and modal submit calls `form.submit()`.
  - Check browser console for JS errors.

---