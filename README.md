# Smart Fingerprint Attendance System

A web-based attendance management system using fingerprint authentication, built with PHP and MySQL, designed to run locally with XAMPP.

## Features

- **Fingerprint-based attendance**: Integrates with fingerprint hardware for secure student check-in
- **Admin dashboard**: View, filter, and export attendance records
- **Flexible filtering**: Filter attendance by month, specific date, subject, batch, or student
- **Statistics & charts**: Visualize attendance data with interactive graphs (Chart.js)
- **Student info**: View student contact details
- **CSV export**: Download filtered attendance data
- **Responsive UI**: Clean, modern interface for desktop and mobile
- **Automated tasks**: Automatic marking of absent students and email reports
- **Timetable integration**: Class schedules stored in database

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

3. **Start XAMPP Services**
    - Start Apache and MySQL from the XAMPP Control Panel

4. **Create Database**
    - Open [phpMyAdmin](http://localhost/phpmyadmin/)
    - Create a new database named `attendance_db`

5. **Import Database Structure**
    - In phpMyAdmin, select the `attendance_db` database
    - Click "Import" tab
    - Choose the `attendance_db.sql` file from the project's database folder
    - Click "Go" to import

6. **Configure Database Connection**
    - Edit `includes/config.php` and set your MySQL credentials:
    ```php
    $db_host = 'localhost';
    $db_user = 'root';          // Default XAMPP username
    $db_pass = '';              // Default XAMPP password (empty)
    $db_name = 'attendance_db'; // Database name
    ```

7. **Access the Application**
    - Open your browser and go to:  
      [http://localhost/attendance_system/admin/studentdashboard.php](http://localhost/attendance_system/admin/studentdashboard.php)

## Database Structure

The system uses the following main tables:

### `students` Table
- Stores student information including:
  - Name, roll number, email, phone
  - Fingerprint IDs (for biometric authentication)
  - Batch and class information

### `attendance_logs` Table
- Records all attendance events with:
  - Student ID and name
  - Timestamp and class period
  - Subject and attendance status (Present/Absent/Late)
  - Device ID and sync status

### Timetable Tables (`e1_timetable`, `e2_timetable`)
- Stores class schedules for different batches
- Contains periods for each day of the week
- Used to determine expected attendance

## Automated Tasks Setup (Windows Task Scheduler)

1. **Mark Absent Students**
   - Creates daily records for students who didn't check in
   - Set to run at end of school day (e.g. 6:00 PM)

2. **Daily Attendance Report**
   - Emails daily summary to administrators
   - Set to run after absent marking (e.g. 6:30 PM)

3. **Monthly Attendance Report**
   - Emails monthly summary on 1st of each month
   - Provides overview of attendance trends

## Sample Data

The database comes pre-loaded with:
- 4 sample students across 2 batches (E1 and E2)
- Sample attendance records for multiple days
- Complete timetable for both batches

## Customization

- **Add/Remove Students**: Modify the `students` table
- **Update Timetables**: Edit the `e1_timetable` and `e2_timetable` tables
- **Email Recipients**: Modify the report scripts
- **Class Periods**: Adjust in the timetable tables

## Notes

- Fingerprint hardware integration requires compatible scanner
- For production use, implement proper security measures
- Test all automated tasks after setup

## License

MIT License

Copyright (c) [2025] [Spacey6849]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
---

**Developed for Smart Attendance Solutions**

If you have any Questions on this project , you can contact me on instagram @spacey6849 ;)
