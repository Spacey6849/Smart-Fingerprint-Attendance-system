<?php
require_once '../includes/config.php';
require_once '../vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Define the subjects
$subjects = [
    'Minor Degree',
    'DBMS',
    'COA',
    'ACD',
    'Java',
    'MIV',
    'ECO',
    'Java lab',
    'ACD lab',
    'Minor Degree Lab',
    'DBMS lab',
    'Sports'
];

try {
    // Get yesterday's attendance
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    // Get student information and overall present count
    $query = "SELECT s.id, s.name, s.email, s.class, 
              COUNT(a.id) as present_count,
              (SELECT COUNT(DISTINCT DATE(timestamp)) FROM attendance_logs WHERE status = 'Present' OR status = 'Absent') as total_days
              FROM students s
              LEFT JOIN attendance_logs a ON s.id = a.student_id 
              AND DATE(a.timestamp) = ? 
              AND a.status = 'Present'
              GROUP BY s.id";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $yesterday);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get subject-specific attendance data
    $subjectAttendance = [];
    foreach ($attendance as $student) {
        $studentId = $student['id'];
        $subjectAttendance[$studentId] = [];
        
        foreach ($subjects as $subject) {
            // Get present count for this subject
            $subjectQuery = "SELECT 
                COUNT(CASE WHEN status = 'Present' THEN 1 END) as present_count,
                COUNT(CASE WHEN status = 'Absent' THEN 1 END) as absent_count,
                COUNT(*) as total_count
                FROM attendance_logs 
                WHERE student_id = ? AND subject = ?";
            
            $subjectStmt = $conn->prepare($subjectQuery);
            $subjectStmt->bind_param("is", $studentId, $subject);
            $subjectStmt->execute();
            $subjectResult = $subjectStmt->get_result();
            $subjectData = $subjectResult->fetch_assoc();
            
            $present = $subjectData['present_count'] ?? 0;
            $absent = $subjectData['absent_count'] ?? 0;
            $total = $subjectData['total_count'] ?? 0;
            $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
            
            $subjectAttendance[$studentId][$subject] = [
                'present' => $present,
                'absent' => $absent,
                'percentage' => $percentage
            ];
        }
    }
    
    // Create CSV in memory
    $csvData = "Student Name,Email,Class";
    foreach ($subjects as $subject) {
        $csvData .= ',"' . $subject . ' (Present)","' . $subject . ' (Absent)","' . $subject . ' (%)';
    }
    $csvData .= "\n";
    
    foreach ($attendance as $row) {
        $csvData .= '"' . str_replace('"', '""', $row['name']) . '",';
        $csvData .= '"' . str_replace('"', '""', $row['email']) . '",';
        $csvData .= '"' . str_replace('"', '""', $row['class']) . '"';
        
        $studentId = $row['id'];
        foreach ($subjects as $subject) {
            $subjectData = $subjectAttendance[$studentId][$subject] ?? ['present' => 0, 'absent' => 0, 'percentage' => 0];
            $csvData .= ',"' . $subjectData['present'] . '","' . $subjectData['absent'] . '","' . $subjectData['percentage'] . '%"';
        }
        
        $csvData .= "\n";
    }
    
    // Create PHPMailer instance
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    
    // Recipients
    // TODO: Change the recipient email to your actual admin email
    $mail->setFrom(SMTP_FROM, 'Attendance System');
    $mail->addAddress('admin@example.com', 'School Admin'); // <-- Change this to your admin email
    
    // Get all unique emails from attendance
    $emails = array_unique(array_column($attendance, 'email'));
    foreach ($emails as $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mail->addBCC($email);
        }
    }
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Daily Attendance Report - ' . $yesterday;
    
    // Create HTML table for email body
    $html = "<h1>Attendance Report for " . htmlspecialchars($yesterday) . "</h1>";
    $html .= "<p>Attached is the CSV file with detailed attendance records.</p>";
    $html .= "<h3>Summary:</h3>";
    $html .= "<p>Total Students: " . count($attendance) . "</p>";
    
    // Add subject-specific summary
    $html .= "<h3>Subject Attendance Overview:</h3>";
    $html .= "<table border='1' cellpadding='5' cellspacing='0'>";
    $html .= "<tr><th>Subject</th><th>Present</th><th>Absent</th><th>Percentage</th></tr>";
    
    foreach ($subjects as $subject) {
        $totalPresent = 0;
        $totalAbsent = 0;
        $studentCount = count($attendance);
        
        foreach ($attendance as $row) {
            $studentId = $row['id'];
            $subjectData = $subjectAttendance[$studentId][$subject] ?? ['present' => 0, 'absent' => 0];
            $totalPresent += $subjectData['present'];
            $totalAbsent += $subjectData['absent'];
        }
        
        $totalClasses = $totalPresent + $totalAbsent;
        $percentage = $totalClasses > 0 ? round(($totalPresent / $totalClasses) * 100, 2) : 0;
        
        $html .= "<tr>";
        $html .= "<td>" . htmlspecialchars($subject) . "</td>";
        $html .= "<td>" . $totalPresent . "</td>";
        $html .= "<td>" . $totalAbsent . "</td>";
        $html .= "<td>" . $percentage . "%</td>";
        $html .= "</tr>";
    }
    
    $html .= "</table>";
    
    $mail->Body = $html;
    $mail->AltBody = "Please view this email in an HTML-enabled email client";
    
    // Attach CSV
    $mail->addStringAttachment($csvData, 'attendance_' . $yesterday . '.csv');
    
    // Debug SMTP communication if needed
    $mail->SMTPDebug = 3; // Shows full SMTP conversation
    $mail->Debugoutput = function($str, $level) {
        error_log("SMTP: $str");
    };
    
    $mail->send();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Report sent successfully',
        'recipients' => count($emails),
        'subjects' => count($subjects)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Message could not be sent. Error: ' . $mail->ErrorInfo,
        'details' => $e->getMessage()
    ]);
}
?>