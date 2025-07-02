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

## Requirements

- [XAMPP](https://www.apachefriends.org/) (PHP 7.4+ and MySQL)
- Web browser (Chrome, Firefox, Edge, etc.)

## Setup Instructions

1. **Clone or Download the Repository**
    ```bash
    git clone https://github.com/yourusername/smart-fingerprint-attendance-system.git
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

## Directory Structure

```
attendance_system/
├── admin/
│   ├── studentdashboard.php
│   └── ... (other admin files)
├── api/
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

## Customization

- **Add/Remove Batches**:  
  Update batch options in the filter dropdown and database as needed.
- **Subjects**:  
  Subjects are auto-populated from attendance records.

## Notes

- This project is for educational/demo purposes. For production, secure authentication and hardware integration are required.
- Fingerprint hardware integration is assumed to be handled by a separate module that communicates with the API.

## License

MIT License

---

**Developed for Smart Attendance Solutions**
