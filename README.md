Here's the updated text file with the Task Scheduler trigger instructions added:

# Smart Fingerprint Attendance System

A web-based attendance management system using fingerprint authentication, built with PHP and MySQL, designed to run locally with XAMPP.

## Features

- **Fingerprint-based attendance**: Integrates with fingerprint hardware for secure student check-in.
- **Admin dashboard**: View, filter, and export attendance records.
- **Flexible filtering**: Filter attendance by month, specific date, subject, batch, or student.
- **Statistics & charts**: Visualize attendance data with interactive graphs (Chart.js).
- **Student info**: View student contact details.
- **CSV export**: Download filtered attendance data.
- **Responsive UI**: Clean, modern interface for desktop and mobile.
- **Automated tasks**: Automatic marking of absent students and email reports.

## Requirements

- [XAMPP](https://www.apachefriends.org/) (PHP 7.4+ and MySQL)
- Web browser (Chrome, Firefox, Edge, etc.)
- curl (included in the project files)

## Setup Instructions

1. **Clone or Download the Repository**
    ```bash
    git clone https://github.com/Spacey6849/smart-fingerprint-attendance-system.git
    ```

2. **Copy to XAMPP Directory**
    - Move the project folder to `C:\xampp\htdocs\attendance_system`

3. **Import the Database**
    - Open [phpMyAdmin](http://localhost/phpmyadmin/)
    - Create a new database (e.g., `attendance_system`)
    - Import the provided SQL file:  
      `database/attendance_system.sql`

4. **Configure Database Connection**
    - Edit `includes/config.php` and set your MySQL username/password if different from default (`root`/no password).

5. **Start XAMPP Services**
    - Start Apache and MySQL from the XAMPP Control Panel.

6. **Access the Application**
    - Open your browser and go to:  
      [http://localhost/attendance_system/admin/studentdashboard.php](http://localhost/attendance_system/admin/studentdashboard.php)

7. **Set Up Automated Tasks in Windows Task Scheduler**
    - Open Task Scheduler (search for "Task Scheduler" in Windows Start menu)
    - Click "Create Task" in the right panel
    - General tab:
      - Name: "Mark Absent Students"
      - Description: "Automatically marks students as absent at scheduled time"
      - Select "Run whether user is logged on or not"
    - Triggers tab:
      - Click "New"
      - Set to "Daily" at your preferred time (e.g. 6:00 PM)
      - Click OK
    - Actions tab:
      - Click "New"
      - Action: "Start a program"
      - Program/script: `C:\Users\moses\Downloads\curl-8.13.0_4-win64-mingw\curl-8.13.0_4-win64-mingw\bin\curl.exe`
      - Arguments: `-s http://localhost/attendance_system/api/api.php?action=mark_absent_students`
      - Click OK
    - Repeat the process for the email reports:
      - Daily report:
        - Name: "Send Daily Attendance Report"
        - Set trigger time (e.g. 6:30 PM)
        - Same curl path
        - Arguments: `-s http://localhost/attendance_system/api/sendreport.php`
      - Monthly report:
        - Name: "Send Monthly Attendance Report"
        - Set trigger to "Monthly" on day 1 at specific time
        - Same curl path
        - Arguments: `-s http://localhost/attendance_system/api/monthlyreport.php`

## Directory Structure

```
attendance_system/
├── admin/
│   ├── studentdashboard.php
│   └── ... (other admin files)
├── api/
│   ├── api.php
│   ├── sendreport.php
│   ├── monthlyreport.php
│   └── test.php
├── includes/
│   └── config.php
├── database/
│   └── attendance_system.sql
├── assets/
│   ├── css/
│   └── js/
└── export.php
```

## Usage

- **Admin Dashboard**:  
  Log in as admin to view and filter attendance records, view statistics, and export data.
- **Attendance Logging**:  
  Students scan their fingerprint; attendance is automatically recorded in the system.
- **Filtering**:  
  Use the filter form to select by month, specific date, subject, batch, or student.
- **Automated Tasks**:
  - Absent students are automatically marked daily
  - Daily and monthly attendance reports are sent via email automatically

## Customization

- **Add/Remove Batches**:  
  Update batch options in the filter dropdown and database as needed.
- **Subjects**:  
  Subjects are auto-populated from attendance records.
- **Email Recipients**:  
  Modify the report scripts to change who receives the automated emails.

## Notes

- This project is for educational/demo purposes. For production, secure authentication and hardware integration are required.
- Fingerprint hardware integration is assumed to be handled by a separate module that communicates with the API.
- The curl commands are configured to run silently (with -s flag) and output to NUL to avoid popups.

## License

MIT License

---

**Developed for Smart Attendance Solutions**
