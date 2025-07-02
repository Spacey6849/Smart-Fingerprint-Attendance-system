# Smart Fingerprint Attendance System

A web-based attendance management system using fingerprint authentication, built with PHP and MySQL, designed to run on local networks (XAMPP) with mobile device support.

## Features

- **Fingerprint-based attendance**: Integrates with fingerprint hardware for secure student check-in
- **Cross-device access**: Works on desktop and mobile browsers
- **Admin dashboard**: View, filter, and export attendance records
- **Real-time statistics**: Interactive charts with Chart.js
- **Automated reporting**: Daily/Monthly email reports
- **Timetable integration**: Class schedules stored in database

## Requirements

- [XAMPP](https://www.apachefriends.org/) (PHP 7.4+ and MySQL)
- Web browser (Chrome, Firefox, Edge, Safari mobile)
- curl (included in project files)
- Local network connection (for mobile access)

## Setup Instructions

### Local Computer Setup
1. **Clone the Repository**
   ```bash
   git clone https://github.com/Spacey6849/smart-fingerprint-attendance-system.git
   ```

2. **Install in XAMPP**
   - Move folder to `C:\xampp\htdocs\attendance_system`
   - Start Apache & MySQL in XAMPP Control Panel

3. **Database Setup**
   ```sql
   CREATE DATABASE attendance_db;
   USE attendance_db;
   SOURCE attendance_system/database/attendance_db.sql;
   ```

4. **Configure Connection**
   Edit `includes/config.php`:
   ```php
   $db_host = 'localhost';
   $db_user = 'root';
   $db_pass = '';
   $db_name = 'attendance_db';
   ```

### Mobile Device Access
1. **Find Your Computer's Local IP**
   - Windows: `ipconfig` (Look for "IPv4 Address")
   - Mac/Linux: `ifconfig` or `ip a`

2. **Connect Devices to Same Network**
   - Ensure phone/tablet is on same WiFi as computer

3. **Access System on Mobile**
   ```
   http://[YOUR_LOCAL_IP]/attendance_system
   Example: http://192.168.1.5/attendance_system
   ```

4. **Port Forwarding (Optional for Remote Access)**
   - Configure router to forward port 80 to your computer
   - Use dynamic DNS if you don't have static IP

## Security Recommendations
```diff
+ Always change default credentials
+ Use .htaccess protection for admin pages
- Don't expose system directly to internet without HTTPS
```

## Troubleshooting
| Issue | Solution |
|-------|----------|
| Connection refused | Check XAMPP is running |
| Database errors | Verify credentials in config.php |
| Mobile can't connect | Disable firewall temporarily |

## License
MIT License *(Full text included in your original)*

---

**Need Help?**  
📩 Contact: [Instagram @spacey6849](https://instagram.com/spacey6849)  
🐛 Report Issues: [GitHub Issues Page](https://github.com/Spacey6849/smart-fingerprint-attendance-system/issues)
