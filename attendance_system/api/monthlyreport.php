<?php
require_once '../includes/config.php';
require_once '../vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Define subjects
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
    // For testing purposes, use May 2025 instead of last month
    // You can change this back to use '-1 month' in production
    $firstDayLastMonth = date('Y-m-01', strtotime('2025-05-01'));
    $lastDayLastMonth = date('Y-m-t', strtotime('2025-05-01'));
    $monthName = date('F Y', strtotime('2025-05-01'));
    
    // Count total school days in the month - improved to ensure we get days with any attendance
    $query = "SELECT COUNT(DISTINCT DATE(timestamp)) as total_days 
              FROM attendance_logs 
              WHERE timestamp BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $firstDayLastMonth, $lastDayLastMonth);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalDaysRow = $result->fetch_assoc();
    $totalDays = $totalDaysRow['total_days'];
    
    // Ensure we have at least 1 day if there's any data
    if ($totalDays == 0) {
        $query = "SELECT COUNT(*) as count FROM attendance_logs WHERE timestamp BETWEEN ? AND ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $firstDayLastMonth, $lastDayLastMonth);
        $stmt->execute();
        $result = $stmt->get_result();
        $countRow = $result->fetch_assoc();
        if ($countRow['count'] > 0) {
            $totalDays = 1; // Set at least 1 day if we have some attendance records
        }
    }
    
    // Get student attendance data for the month with subject breakdown
    $query = "SELECT s.id, s.name, s.email, s.class, 
              a.subject,
              SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present_days,
              SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) as absent_days,
              SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) as late_days
              FROM students s
              LEFT JOIN attendance_logs a ON s.id = a.student_id 
              AND (a.timestamp BETWEEN ? AND ?)
              GROUP BY s.id, a.subject";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $firstDayLastMonth, $lastDayLastMonth);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendanceData = $result->fetch_all(MYSQLI_ASSOC);
    
    // Organize data by student
    $attendance = [];
    foreach ($attendanceData as $row) {
        if (!isset($attendance[$row['id']])) {
            $attendance[$row['id']] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'class' => $row['class'],
                'subjects' => [],
                'total_present' => 0,
                'total_absent' => 0,
                'total_late' => 0
            ];
        }
        
        // Add subject data if it exists
        if (!empty($row['subject'])) {
            $attendance[$row['id']]['subjects'][$row['subject']] = [
                'present_days' => (int)$row['present_days'],
                'absent_days' => (int)$row['absent_days'],
                'late_days' => (int)$row['late_days']
            ];
            
            // Add to totals
            $attendance[$row['id']]['total_present'] += (int)$row['present_days'];
            $attendance[$row['id']]['total_absent'] += (int)$row['absent_days'];
            $attendance[$row['id']]['total_late'] += (int)$row['late_days'];
        }
    }
    
    // Create CSV in memory
    $csvData = "Student Name,Email,Class";
    
    // Add subject headers
    foreach ($subjects as $subject) {
        $csvData .= ",{$subject} Present,{$subject} Absent,{$subject} Percentage";
    }
    
    // Add overall metrics
    $csvData .= ",Total Present Days,Total Absent Days,Total Late Days,Overall Attendance Rate(%)\n";
    
    foreach ($attendance as $student) {
        $csvData .= '"' . str_replace('"', '""', $student['name']) . '",';
        $csvData .= '"' . str_replace('"', '""', $student['email']) . '",';
        $csvData .= '"' . str_replace('"', '""', $student['class']) . '",';
        
        // Add subject data
        foreach ($subjects as $subject) {
            if (isset($student['subjects'][$subject])) {
                $subjectData = $student['subjects'][$subject];
                $presentDays = $subjectData['present_days'];
                $absentDays = $subjectData['absent_days'];
                $lateDays = $subjectData['late_days'];
                $totalSubjectDays = $presentDays + $absentDays + $lateDays;
                $subjectAttendanceRate = ($totalSubjectDays > 0) ? 
                    round((($presentDays + $lateDays) / $totalSubjectDays) * 100, 2) : 0;
                
                $csvData .= '"' . $presentDays . '",';
                $csvData .= '"' . $absentDays . '",';
                $csvData .= '"' . $subjectAttendanceRate . '%",';
            } else {
                // No data for this subject
                $csvData .= '"0","0","0%",';
            }
        }
        
        // Add overall metrics
        $totalPresent = $student['total_present'];
        $totalAbsent = $student['total_absent'];
        $totalLate = $student['total_late'];
        $totalStudentDays = $totalPresent + $totalAbsent + $totalLate;
        $attendanceRate = ($totalStudentDays > 0) ? 
            round((($totalPresent + $totalLate) / $totalStudentDays) * 100, 2) : 0;
        
        $csvData .= '"' . $totalPresent . '",';
        $csvData .= '"' . $totalAbsent . '",';
        $csvData .= '"' . $totalLate . '",';
        $csvData .= '"' . $attendanceRate . '%"';
        $csvData .= "\n";
    }
    
    // Get detailed daily attendance for each student by subject
    $dailyAttendance = [];
    foreach (array_keys($attendance) as $studentId) {
        $query = "SELECT DATE(timestamp) as date, subject, status
                FROM attendance_logs
                WHERE student_id = ? AND (timestamp BETWEEN ? AND ?)
                ORDER BY timestamp";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $studentId, $firstDayLastMonth, $lastDayLastMonth);
        $stmt->execute();
        $result = $stmt->get_result();
        $dailyLogs = $result->fetch_all(MYSQLI_ASSOC);
        
        // Only add to daily attendance if there are records
        if (count($dailyLogs) > 0) {
            $dailyAttendance[$studentId] = $dailyLogs;
        }
    }
    
    // For debugging: Log attendance counts
    error_log("Month: $monthName, Total Days: $totalDays");
    foreach ($attendance as $student) {
        error_log("Student: {$student['name']}, Total Present: {$student['total_present']}, Total Absent: {$student['total_absent']}, Total Late: {$student['total_late']}");
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
    
    // Set sender
    $mail->setFrom(SMTP_FROM, 'Attendance System');
    
    // First, send a summary report to the admin
    // TODO: Change the recipient email to your actual admin email
    $adminMail = clone $mail;
    $adminMail->addAddress('admin@example.com', 'School Admin'); // <-- Change this to your admin email
    
    // Content for admin
    $adminMail->isHTML(true);
    $adminMail->Subject = 'Monthly Attendance Report - ' . $monthName;
    
    $adminHtml = "<h1>Monthly Attendance Report for " . htmlspecialchars($monthName) . "</h1>";
    $adminHtml .= "<p>Attached is the CSV file with detailed attendance records for all students.</p>";
    $adminHtml .= "<h3>Summary:</h3>";
    $adminHtml .= "<p>Total School Days: " . $totalDays . "</p>";
    $adminHtml .= "<p>Total Students: " . count($attendance) . "</p>";
    
    // Calculate overall attendance rate
    $totalPresent = 0;
    $totalAttendances = 0;
    foreach ($attendance as $student) {
        $totalPresent += $student['total_present'] + $student['total_late'];
        $totalStudentDays = $student['total_present'] + $student['total_absent'] + $student['total_late'];
        $totalAttendances += $totalStudentDays;
    }
    $overallRate = ($totalAttendances > 0) ? round(($totalPresent / $totalAttendances) * 100, 2) : 0;
    $adminHtml .= "<p>Overall Attendance Rate: " . $overallRate . "%</p>";
    
    // Subject-wise attendance summary for admin
    $adminHtml .= "<h3>Subject-wise Attendance Summary:</h3>";
    $adminHtml .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse'>";
    $adminHtml .= "<tr><th>Subject</th><th>Present</th><th>Absent</th><th>Late</th><th>Attendance Rate</th></tr>";
    
    $subjectTotals = [];
    // Initialize subject totals
    foreach ($subjects as $subject) {
        $subjectTotals[$subject] = [
            'present' => 0,
            'absent' => 0,
            'late' => 0
        ];
    }
    
    // Sum up subject totals
    foreach ($attendance as $student) {
        foreach ($subjects as $subject) {
            if (isset($student['subjects'][$subject])) {
                $subjectTotals[$subject]['present'] += $student['subjects'][$subject]['present_days'];
                $subjectTotals[$subject]['absent'] += $student['subjects'][$subject]['absent_days'];
                $subjectTotals[$subject]['late'] += $student['subjects'][$subject]['late_days'];
            }
        }
    }
    
    // Display subject totals
    foreach ($subjects as $subject) {
        $present = $subjectTotals[$subject]['present'];
        $absent = $subjectTotals[$subject]['absent'];
        $late = $subjectTotals[$subject]['late'];
        $total = $present + $absent + $late;
        $rate = ($total > 0) ? round((($present + $late) / $total) * 100, 2) : 0;
        
        $adminHtml .= "<tr>";
        $adminHtml .= "<td>" . htmlspecialchars($subject) . "</td>";
        $adminHtml .= "<td>" . $present . "</td>";
        $adminHtml .= "<td>" . $absent . "</td>";
        $adminHtml .= "<td>" . $late . "</td>";
        $adminHtml .= "<td>" . $rate . "%</td>";
        $adminHtml .= "</tr>";
    }
    
    $adminHtml .= "</table>";
    $adminMail->Body = $adminHtml;
    $adminMail->AltBody = "Monthly Attendance Report for " . $monthName;
    
    // Attach CSV to admin email
    $adminMail->addStringAttachment($csvData, 'monthly_attendance_' . $monthName . '.csv');
    
    // Send admin email
    $adminMail->send();
    
    // Now send individual reports to each student
    $sentCount = 0;
    foreach ($attendance as $student) {
        if (!filter_var($student['email'], FILTER_VALIDATE_EMAIL)) {
            continue;
        }
        
        // Create a personalized email for each student
        $studentMail = clone $mail;
        $studentMail->clearAddresses();
        $studentMail->addAddress($student['email'], $student['name']);
        
        // Content for student
        $studentMail->isHTML(true);
        $studentMail->Subject = 'Your Monthly Attendance Report - ' . $monthName;
        
        $totalPresent = $student['total_present'];
        $totalAbsent = $student['total_absent'];
        $totalLate = $student['total_late'];
        $totalStudentDays = $totalPresent + $totalAbsent + $totalLate;
        $attendanceRate = ($totalStudentDays > 0) ? 
            round((($totalPresent + $totalLate) / $totalStudentDays) * 100, 2) : 0;
        
        $studentHtml = "<h1>Hello " . htmlspecialchars($student['name']) . "</h1>";
        $studentHtml .= "<p>Here is your attendance report for " . htmlspecialchars($monthName) . ":</p>";
        $studentHtml .= "<h3>Overall Summary:</h3>";
        $studentHtml .= "<ul>";
        $studentHtml .= "<li>Total School Days: " . $totalDays . "</li>";
        $studentHtml .= "<li>Days Present: " . $totalPresent . "</li>";
        $studentHtml .= "<li>Days Absent: " . $totalAbsent . "</li>";
        $studentHtml .= "<li>Days Late: " . $totalLate . "</li>";
        $studentHtml .= "<li>Overall Attendance Rate: " . $attendanceRate . "%</li>";
        $studentHtml .= "</ul>";
        
        // Add subject-wise breakdown
        $studentHtml .= "<h3>Subject-wise Attendance:</h3>";
        $studentHtml .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse'>";
        $studentHtml .= "<tr><th>Subject</th><th>Present</th><th>Absent</th><th>Late</th><th>Attendance Rate</th></tr>";
        
        foreach ($subjects as $subject) {
            $present = isset($student['subjects'][$subject]) ? $student['subjects'][$subject]['present_days'] : 0;
            $absent = isset($student['subjects'][$subject]) ? $student['subjects'][$subject]['absent_days'] : 0;
            $late = isset($student['subjects'][$subject]) ? $student['subjects'][$subject]['late_days'] : 0;
            $total = $present + $absent + $late;
            $rate = ($total > 0) ? round((($present + $late) / $total) * 100, 2) : 0;
            
            // Determine color based on attendance rate
            $color = '';
            if ($total > 0) { // Only add color if there's data
                if ($rate < 80) {
                    $color = 'red';
                } elseif ($rate >= 80 && $rate < 90) {
                    $color = 'orange';
                } else {
                    $color = 'green';
                }
            }
            
            $studentHtml .= "<tr>";
            $studentHtml .= "<td>" . htmlspecialchars($subject) . "</td>";
            $studentHtml .= "<td>" . $present . "</td>";
            $studentHtml .= "<td>" . $absent . "</td>";
            $studentHtml .= "<td>" . $late . "</td>";
            $studentHtml .= "<td style='color:" . $color . "'>" . $rate . "%</td>";
            $studentHtml .= "</tr>";
        }
        
        $studentHtml .= "</table>";
        
        // Add a detailed breakdown if daily attendance data is available
        if (isset($dailyAttendance[$student['id']]) && !empty($dailyAttendance[$student['id']])) {
            $studentHtml .= "<h3>Daily Attendance Breakdown:</h3>";
            $studentHtml .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse'>";
            $studentHtml .= "<tr><th>Date</th><th>Subject</th><th>Status</th></tr>";
            
            foreach ($dailyAttendance[$student['id']] as $log) {
                $date = date('M d, Y', strtotime($log['date']));
                $subject = $log['subject'] ?? 'N/A';
                $status = $log['status'];
                $color = '';
                
                switch ($status) {
                    case 'Present':
                        $color = 'green';
                        break;
                    case 'Absent':
                        $color = 'red';
                        break;
                    case 'Late':
                        $color = 'orange';
                        break;
                }
                
                $studentHtml .= "<tr>";
                $studentHtml .= "<td>" . $date . "</td>";
                $studentHtml .= "<td>" . htmlspecialchars($subject) . "</td>";
                $studentHtml .= "<td style='color:" . $color . "'>" . $status . "</td>";
                $studentHtml .= "</tr>";
            }
            
            $studentHtml .= "</table>";
        }
        
        // Add recommendations based on attendance, only if student has any attendance records
        if ($totalStudentDays > 0) {
            $studentHtml .= "<h3>Recommendations:</h3>";
            
            if ($attendanceRate < 80) {
                $studentHtml .= "<p style='color:red'>Your overall attendance is below 80%. Please make an effort to improve your attendance as it might affect your academic performance.</p>";
            } elseif ($attendanceRate >= 80 && $attendanceRate < 90) {
                $studentHtml .= "<p style='color:orange'>Your overall attendance is good but could be improved. Try to minimize absences in the coming month.</p>";
            } else {
                $studentHtml .= "<p style='color:green'>Excellent overall attendance! Keep up the good work.</p>";
            }
            
            // Add subject-specific recommendations
            $lowAttendanceSubjects = [];
            foreach ($subjects as $subject) {
                if (isset($student['subjects'][$subject])) {
                    $present = $student['subjects'][$subject]['present_days'];
                    $absent = $student['subjects'][$subject]['absent_days'];
                    $late = $student['subjects'][$subject]['late_days'];
                    $total = $present + $absent + $late;
                    $rate = ($total > 0) ? round((($present + $late) / $total) * 100, 2) : 0;
                    
                    if ($total > 0 && $rate < 80) {
                        $lowAttendanceSubjects[] = $subject;
                    }
                }
            }
            
            if (!empty($lowAttendanceSubjects)) {
                $studentHtml .= "<p style='color:red'>Subjects that need attention: " . 
                    implode(", ", $lowAttendanceSubjects) . 
                    ". Please focus on improving attendance in these subjects.</p>";
            }
        } else {
            $studentHtml .= "<h3>Note:</h3>";
            $studentHtml .= "<p>No attendance data was found for this month. This could be due to holidays or technical issues.</p>";
        }
        
        $studentHtml .= "<p>If you have any concerns regarding your attendance record, please contact the school administration.</p>";
        $studentHtml .= "<p>Thank you,<br>School Administration</p>";
        
        $studentMail->Body = $studentHtml;
        $studentMail->AltBody = "Your Monthly Attendance Report for " . $monthName;
        
        // Create personalized CSV for the student
        $personalCsvData = "Subject,Present Days,Absent Days,Late Days,Attendance Rate(%)\n";
        
        foreach ($subjects as $subject) {
            $present = isset($student['subjects'][$subject]) ? $student['subjects'][$subject]['present_days'] : 0;
            $absent = isset($student['subjects'][$subject]) ? $student['subjects'][$subject]['absent_days'] : 0;
            $late = isset($student['subjects'][$subject]) ? $student['subjects'][$subject]['late_days'] : 0;
            $total = $present + $absent + $late;
            $rate = ($total > 0) ? round((($present + $late) / $total) * 100, 2) : 0;
            
            $personalCsvData .= '"' . $subject . '",';
            $personalCsvData .= '"' . $present . '",';
            $personalCsvData .= '"' . $absent . '",';
            $personalCsvData .= '"' . $late . '",';
            $personalCsvData .= '"' . $rate . '%"' . "\n";
        }
        
        // Add overall totals row
        $personalCsvData .= '"OVERALL",';
        $personalCsvData .= '"' . $totalPresent . '",';
        $personalCsvData .= '"' . $totalAbsent . '",';
        $personalCsvData .= '"' . $totalLate . '",';
        $personalCsvData .= '"' . $attendanceRate . '%"';
        
        // Attach personal CSV to student email
        $studentMail->addStringAttachment($personalCsvData, 'your_attendance_' . $monthName . '.csv');
        
        // Send student email
        $studentMail->send();
        $sentCount++;
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Monthly reports sent successfully',
        'month' => $monthName,
        'admin_sent' => true,
        'students_sent' => $sentCount,
        'subjects_included' => count($subjects)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Message could not be sent. Error: ' . $e->getMessage()
    ]);
}
?>