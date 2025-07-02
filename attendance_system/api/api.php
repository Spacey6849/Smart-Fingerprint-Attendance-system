<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/config.php';

// Improved getCurrentPeriod function with debug support
function getCurrentPeriod($batch) {
    // For production use actual current time
    $currentTime = time(); // Get current timestamp
    // Remove test time line
    //$currentTime = strtotime('15:00:00'); // Test with a specific time
    
    $currentDay = date('D', $currentTime); // Returns 3-letter day (Mon, Tue, etc.)

    // Get today's date for logging
    $today = date('Y-m-d', $currentTime);
    $currentTimeStr = date('H:i:s', $currentTime);
    
    // Log for debugging
    error_log("Finding period for batch: $batch, day: $currentDay, time: $currentTimeStr");

    $tableName = $batch . '_Timetable';
    $stmt = $GLOBALS['conn']->prepare("SELECT * FROM $tableName WHERE day = ?");
    $stmt->bind_param("s", $currentDay);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        error_log("No classes found for $batch on $currentDay");
        return ['period' => 'No classes', 'subject' => 'None', 'start_time' => null];
    }

    $timetable = $result->fetch_assoc();

    // Define period time ranges
    $periods = [
        'P1' => ['start' => '09:00:00', 'end' => '10:00:00', 'label' => 'P1: 9-10'],
        'P2' => ['start' => '10:00:00', 'end' => '11:00:00', 'label' => 'P2: 10-11'],
        'P3' => ['start' => '11:00:00', 'end' => '12:00:00', 'label' => 'P3: 11-12'],
        'P4' => ['start' => '12:45:00', 'end' => '13:45:00', 'label' => 'P4: 12:45-1:45'],
        'P5' => ['start' => '13:45:00', 'end' => '14:45:00', 'label' => 'P5: 1:45-2:45'],
        'P6' => ['start' => '15:00:00', 'end' => '16:00:00', 'label' => 'P6: 3-4'],
        'P7' => ['start' => '16:00:00', 'end' => '17:00:00', 'label' => 'P7: 4-5']
    ];

    // Get current time as H:i:s
    $currentTimeFormatted = date('H:i:s', $currentTime);
    
    // Find the current period
    foreach ($periods as $period_key => $period_data) {
        $periodStart = strtotime($period_data['start']);
        $periodEnd = strtotime($period_data['end']);
        $currentSeconds = strtotime($currentTimeFormatted);
        
        if ($currentSeconds >= $periodStart && $currentSeconds < $periodEnd) {
            $period_num = substr($period_key, 1, 1); // Extract the number from P1, P2, etc.
            $period_col = "period_" . $period_num;
            
            if (isset($timetable[$period_col]) && !empty($timetable[$period_col])) {
                error_log("Found period $period_key ($period_data[label]) for $batch, subject: {$timetable[$period_col]}");
                return [
                    'period' => $period_data['label'],
                    'subject' => $timetable[$period_col],
                    'start_time' => $period_data['start'] // Return period start time for late calculation
                ];
            }
        }
    }

    error_log("No active class period found for current time: $currentTimeFormatted");
    return ['period' => 'No class', 'subject' => 'None', 'start_time' => null];
}

// Function to get completed period (most recently ended period)
function getCompletedPeriod($batch) {
    // For production use actual current time
    $currentTime = time();
    // Remove test time line
    //$currentTime = strtotime('13:46:00'); // Test with a specific time
    
    $currentDay = date('D', $currentTime);
    $currentTimeStr = date('H:i:s', $currentTime);
    
    error_log("Finding completed period for batch: $batch, day: $currentDay, time: $currentTimeStr");

    $tableName = $batch . '_Timetable';
    $stmt = $GLOBALS['conn']->prepare("SELECT * FROM $tableName WHERE day = ?");
    $stmt->bind_param("s", $currentDay);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        error_log("No classes found for $batch on $currentDay");
        return null;
    }

    $timetable = $result->fetch_assoc();

    // Define period time ranges
    $periods = [
        'P1' => ['start' => '09:00:00', 'end' => '10:00:00', 'label' => 'P1: 9-10'],
        'P2' => ['start' => '10:00:00', 'end' => '11:00:00', 'label' => 'P2: 10-11'],
        'P3' => ['start' => '11:00:00', 'end' => '12:00:00', 'label' => 'P3: 11-12'],
        'P4' => ['start' => '12:45:00', 'end' => '13:45:00', 'label' => 'P4: 12:45-1:45'],
        'P5' => ['start' => '13:45:00', 'end' => '14:45:00', 'label' => 'P5: 1:45-2:45'],
        'P6' => ['start' => '15:00:00', 'end' => '16:00:00', 'label' => 'P6: 3-4'],
        'P7' => ['start' => '16:00:00', 'end' => '17:00:00', 'label' => 'P7: 4-5']
    ];

    // Get current time as H:i:s
    $currentTimeFormatted = date('H:i:s', $currentTime);
    $currentSeconds = strtotime($currentTimeFormatted);
    
    // Find the most recently completed period
    $completedPeriod = null;
    
    foreach ($periods as $period_key => $period_data) {
        $periodEnd = strtotime($period_data['end']);
        
        // If this period has just ended (within the last 5 minutes)
        if ($currentSeconds >= $periodEnd && $currentSeconds < ($periodEnd + 300)) {
            $period_num = substr($period_key, 1, 1);
            $period_col = "period_" . $period_num;
            
            if (isset($timetable[$period_col]) && !empty($timetable[$period_col])) {
                return [
                    'period' => $period_data['label'],
                    'subject' => $timetable[$period_col],
                    'just_ended' => true
                ];
            }
        }
        
        // Keep track of the most recent completed period (regardless of "just ended" status)
        if ($currentSeconds > $periodEnd) {
            $period_num = substr($period_key, 1, 1);
            $period_col = "period_" . $period_num;
            
            if (isset($timetable[$period_col]) && !empty($timetable[$period_col])) {
                $completedPeriod = [
                    'period' => $period_data['label'],
                    'subject' => $timetable[$period_col],
                    'just_ended' => false
                ];
            }
        }
    }
    
    return $completedPeriod;
}

// Improved function to mark absent students for a completed period
function markAbsentStudents($batch, $period, $subject) {
    global $conn;
    $today = date('Y-m-d');
    $marked = 0;
    $markedStudents = [];
    
    // Log start of process
    error_log("Starting to mark absent students for batch: $batch, period: $period, subject: $subject");
    
    // First, get all students from this batch
    $stmt = $conn->prepare("SELECT id, name, roll_number FROM students WHERE batch = ? ORDER BY roll_number");
    $stmt->bind_param("s", $batch);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        error_log("No students found in batch $batch");
        return ['count' => 0, 'students' => []];
    }
    
    $allStudents = [];
    while ($row = $result->fetch_assoc()) {
        $allStudents[$row['id']] = $row;
    }
    
    error_log("Found " . count($allStudents) . " students in batch $batch");
    
    // Then, get all students who are present or late for this period today
    $stmt = $conn->prepare("SELECT student_id FROM attendance_logs 
                          WHERE DATE(timestamp) = ? AND period = ? AND subject = ?");
    $stmt->bind_param("sss", $today, $period, $subject);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $presentCount = $result->num_rows;
    error_log("Found $presentCount students already marked present/late for period $period");
    
    // Remove present students from the list
    while ($row = $result->fetch_assoc()) {
        if (isset($allStudents[$row['student_id']])) {
            unset($allStudents[$row['student_id']]);
        }
    }
    
    // Mark remaining students as absent
    foreach ($allStudents as $student) {
        // Check if already marked absent
        $checkStmt = $conn->prepare("SELECT id FROM attendance_logs 
                                  WHERE student_id = ? AND DATE(timestamp) = ? 
                                  AND period = ? AND subject = ? AND status = 'Absent'");
        $checkStmt->bind_param("isss", $student['id'], $today, $period, $subject);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            error_log("Student {$student['name']} (ID: {$student['id']}) already marked absent for $period");
            continue;
        }
        
        $stmt = $conn->prepare("INSERT INTO attendance_logs 
                              (student_id, name, period, subject, status, timestamp) 
                              VALUES (?, ?, ?, ?, 'Absent', NOW())");
        $stmt->bind_param("isss", $student['id'], $student['name'], $period, $subject);
        
        if ($stmt->execute()) {
            $marked++;
            $markedStudents[] = [
                'id' => $student['id'],
                'name' => $student['name'],
                'roll_number' => $student['roll_number']
            ];
            error_log("Marked student {$student['name']} (ID: {$student['id']}) as absent for $period, $subject");
        } else {
            error_log("Failed to mark student {$student['name']} as absent: " . $conn->error);
        }
    }
    
    error_log("Completed marking $marked students as absent for $period, $subject");
    return ['count' => $marked, 'students' => $markedStudents];
}

// Function to check if the attendance is late (more than 10 minutes after class start)
function isLateAttendance($periodStartTime) {
    if (!$periodStartTime) return false;
    
    // Get current time 
    $currentTime = time();
    $periodStartTimestamp = strtotime($periodStartTime);
    
    // Calculate the difference in minutes
    $diffMinutes = ($currentTime - $periodStartTimestamp) / 60;
    
    // Return true if more than 10 minutes late
    return $diffMinutes > 10;
}

// Function to get all absent students for a specific batch and period
function getAbsentStudents($batch, $period, $subject) {
    global $conn;
    $today = date('Y-m-d');
    
    // First, get all students from this batch
    $stmt = $conn->prepare("SELECT id, name, roll_number FROM students WHERE batch = ? ORDER BY roll_number");
    $stmt->bind_param("s", $batch);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return [];
    }
    
    $allStudents = [];
    while ($row = $result->fetch_assoc()) {
        $allStudents[$row['id']] = $row;
    }
    
    // Then, get all students who are present for this period today
    $stmt = $conn->prepare("SELECT student_id FROM attendance_logs 
                          WHERE DATE(timestamp) = ? AND period = ? AND subject = ?");
    $stmt->bind_param("sss", $today, $period, $subject);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Remove present students from the list
    while ($row = $result->fetch_assoc()) {
        if (isset($allStudents[$row['student_id']])) {
            unset($allStudents[$row['student_id']]);
        }
    }
    
    return array_values($allStudents);
}

// Debug function to test the period calculation for troubleshooting
function testPeriodDetection($batch) {
    $testTimes = [
        '08:30:00' => 'Before classes',
        '09:30:00' => 'Period 1',
        '10:30:00' => 'Period 2',
        '11:30:00' => 'Period 3',
        '13:00:00' => 'Period 4',
        '14:00:00' => 'Period 5',
        '15:30:00' => 'Period 6',
        '16:30:00' => 'Period 7',
        '17:30:00' => 'After classes'
    ];
    
    $results = [];
    foreach ($testTimes as $time => $label) {
        $testTime = strtotime($time);
        $day = date('D');
        
        $tableName = $batch . '_Timetable';
        $stmt = $GLOBALS['conn']->prepare("SELECT * FROM $tableName WHERE day = ?");
        $stmt->bind_param("s", $day);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $results[$label] = "No timetable for $day";
            continue;
        }
        
        $timetable = $result->fetch_assoc();
        
        // Define periods
        $periods = [
            'P1' => ['start' => '09:00:00', 'end' => '10:00:00', 'label' => 'P1: 9-10'],
            'P2' => ['start' => '10:00:00', 'end' => '11:00:00', 'label' => 'P2: 10-11'],
            'P3' => ['start' => '11:00:00', 'end' => '12:00:00', 'label' => 'P3: 11-12'],
            'P4' => ['start' => '12:45:00', 'end' => '13:45:00', 'label' => 'P4: 12:45-1:45'],
            'P5' => ['start' => '13:45:00', 'end' => '14:45:00', 'label' => 'P5: 1:45-2:45'],
            'P6' => ['start' => '15:00:00', 'end' => '16:00:00', 'label' => 'P6: 3-4'],
            'P7' => ['start' => '16:00:00', 'end' => '17:00:00', 'label' => 'P7: 4-5']
        ];
        
        $found = false;
        foreach ($periods as $period_key => $period_data) {
            $periodStart = strtotime($period_data['start']);
            $periodEnd = strtotime($period_data['end']);
            
            if ($testTime >= $periodStart && $testTime < $periodEnd) {
                $period_num = substr($period_key, 1, 1);
                $period_col = "period_" . $period_num;
                
                if (isset($timetable[$period_col])) {
                    $results[$label] = "$period_data[label] - " . $timetable[$period_col];
                    $found = true;
                    break;
                }
            }
        }
        
        if (!$found) {
            $results[$label] = "No class";
        }
    }
    
    return $results;
}

try {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'test_periods':
            // Debug endpoint for testing periods
            $batch = $_GET['batch'] ?? 'E1';
            $results = testPeriodDetection($batch);
            echo json_encode([
                'status' => 'success',
                'batch' => $batch,
                'day' => date('D'),
                'current_time' => date('H:i:s'),
                'periods' => $results
            ]);
            break;
            
        case 'record_attendance':
            $fid = intval($_GET['fid'] ?? 0);
            if ($fid <= 0) throw new Exception("Invalid fingerprint ID");
            
            // Get student data
            $stmt = $conn->prepare("SELECT id, name, batch FROM students 
                                  WHERE fingerprint_id1 = ? OR fingerprint_id2 = ?");
            $stmt->bind_param("ii", $fid, $fid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) throw new Exception("Student not found with fingerprint ID: $fid");
            $student = $result->fetch_assoc();
            
            // Get period info
            $periodInfo = getCurrentPeriod($student['batch']);
            
            // Check if valid class period
            if ($periodInfo['period'] === 'No classes' || $periodInfo['period'] === 'No class') {
                throw new Exception("No classes scheduled for current time. Batch: {$student['batch']}, Time: " . date('H:i:s'));
            }

            // Check if student is late (more than 10 minutes after class start)
            $isLate = isLateAttendance($periodInfo['start_time']);
            $status = $isLate ? 'Late' : 'Present';

            // Check existing attendance
            $check = $conn->prepare("SELECT id FROM attendance_logs 
                                    WHERE student_id = ? AND DATE(timestamp) = CURDATE() 
                                    AND period = ?");
            $check->bind_param("is", $student['id'], $periodInfo['period']);
            $check->execute();
            
            if ($check->get_result()->num_rows > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Attendance already recorded',
                    'student' => $student['name'],
                    'period' => $periodInfo['period'],
                    'subject' => $periodInfo['subject']
                ]);
                exit;
            }
            
            // Insert new record with timestamp and status (Present or Late)
            $insert = $conn->prepare("INSERT INTO attendance_logs 
                                    (student_id, name, period, subject, status, timestamp) 
                                    VALUES (?, ?, ?, ?, ?, NOW())");
            $insert->bind_param("issss", $student['id'], $student['name'], $periodInfo['period'], $periodInfo['subject'], $status);
            
            if (!$insert->execute()) {
                throw new Exception("Database error: " . $conn->error);
            }
            
            echo json_encode([
                'status' => 'success',
                'student' => $student['name'],
                'period' => $periodInfo['period'],
                'subject' => $periodInfo['subject'],
                'attendance_status' => $status,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;

        case 'get_timetable':
            $batch = $_GET['batch'] ?? 'E1';
            $day = $_GET['day'] ?? date('D');
            
            $tableName = $batch . '_Timetable';
            $stmt = $conn->prepare("SELECT * FROM $tableName WHERE day = ?");
            $stmt->bind_param("s", $day);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                echo json_encode(['error' => "No timetable for $day in batch $batch"]);
            } else {
                echo json_encode($result->fetch_assoc());
            }
            break;
            
        case 'get_period':
            // Simple endpoint to check the current period
            $batch = $_GET['batch'] ?? 'E1';
            $periodInfo = getCurrentPeriod($batch);
            echo json_encode([
                'status' => 'success',
                'batch' => $batch,
                'day' => date('D'),
                'time' => date('H:i:s'),
                'period' => $periodInfo['period'],
                'subject' => $periodInfo['subject']
            ]);
            break;
            
        case 'get_absent_students':
            // Endpoint to get absent students for the current period
            $batch = $_GET['batch'] ?? 'E1';
            $periodInfo = getCurrentPeriod($batch);
            
            if ($periodInfo['period'] === 'No classes' || $periodInfo['period'] === 'No class') {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No active class period found'
                ]);
                exit;
            }
            
            $absentStudents = getAbsentStudents($batch, $periodInfo['period'], $periodInfo['subject']);
            
            echo json_encode([
                'status' => 'success',
                'batch' => $batch,
                'period' => $periodInfo['period'],
                'subject' => $periodInfo['subject'],
                'date' => date('Y-m-d'),
                'absent_count' => count($absentStudents),
                'absent_students' => $absentStudents
            ]);
            break;
            
        case 'mark_absent_students':
            // Endpoint to mark absent students for a completed period
            $batch = $_GET['batch'] ?? '';
            $period = $_GET['period'] ?? '';
            $subject = $_GET['subject'] ?? '';
            
            // Manual mode - use passed parameters
            if (!empty($batch) && !empty($period) && !empty($subject)) {
                $result = markAbsentStudents($batch, $period, $subject);
                
                echo json_encode([
                    'status' => 'success',
                    'batch' => $batch,
                    'period' => $period,
                    'subject' => $subject,
                    'marked_absent' => $result['count'],
                    'students' => $result['students']
                ]);
            } 
            // Auto mode - check for recently completed period or after 5 PM
            else {
                $currentHour = date('H');
                if ($currentHour >= 17) { // After 5 PM
                    $batches = ['E1', 'E2'];
                    $totalMarked = 0;
                    $allMarkedStudents = [];
                    $periodsProcessed = [];
                    $todayDay = date('D'); // Current day (e.g., Mon, Tue)
                    
                    foreach ($batches as $batchCode) {
                        // Fetch today's timetable
                        $tableName = $batchCode . '_Timetable';
                        $stmt = $conn->prepare("SELECT * FROM $tableName WHERE day = ?");
                        $stmt->bind_param("s", $todayDay);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows == 0) {
                            error_log("No timetable found for $batchCode on $todayDay");
                            continue;
                        }
                        
                        $timetable = $result->fetch_assoc();
                        
                        // Process all periods (1-7)
                        for ($periodNum = 1; $periodNum <= 7; $periodNum++) {
                            $periodCol = "period_$periodNum";
                            if (!isset($timetable[$periodCol])) continue;
                            
                            $subject = $timetable[$periodCol];
                            if (empty($subject)) continue; // Skip empty periods
                            
                            // Determine period label based on number
                            $periodLabel = [
                                1 => 'P1: 9-10',
                                2 => 'P2: 10-11',
                                3 => 'P3: 11-12',
                                4 => 'P4: 12:45-1:45',
                                5 => 'P5: 1:45-2:45',
                                6 => 'P6: 3-4',
                                7 => 'P7: 4-5'
                            ][$periodNum];
                            
                            // Mark absent students for this period
                            $resultMark = markAbsentStudents($batchCode, $periodLabel, $subject);
                            $totalMarked += $resultMark['count'];
                            $allMarkedStudents = array_merge($allMarkedStudents, $resultMark['students']);
                            $periodsProcessed[] = [
                                'batch' => $batchCode,
                                'period' => $periodLabel,
                                'subject' => $subject,
                                'marked_absent' => $resultMark['count'],
                                'students' => $resultMark['students']
                            ];
                        }
                    }
                    
                    if (!empty($periodsProcessed)) {
                        echo json_encode([
                            'status' => 'success',
                            'total_marked_absent' => $totalMarked,
                            'periods_processed' => $periodsProcessed,
                            'all_students' => $allMarkedStudents
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 'info',
                            'message' => 'No valid periods found to mark absents after 5 PM'
                        ]);
                    }
                } else {
                    // Existing logic for checking recently completed periods
                    $batches = ['E1', 'E2'];
                    $totalMarked = 0;
                    $allMarkedStudents = [];
                    $periodsProcessed = [];
                    
                    foreach ($batches as $batchCode) {
                        $completedPeriod = getCompletedPeriod($batchCode);
                        
                        if ($completedPeriod && isset($completedPeriod['just_ended']) && $completedPeriod['just_ended']) {
                            $result = markAbsentStudents($batchCode, $completedPeriod['period'], $completedPeriod['subject']);
                            $totalMarked += $result['count'];
                            $allMarkedStudents = array_merge($allMarkedStudents, $result['students']);
                            
                            $periodsProcessed[] = [
                                'batch' => $batchCode,
                                'period' => $completedPeriod['period'],
                                'subject' => $completedPeriod['subject'],
                                'marked_absent' => $result['count'],
                                'students' => $result['students']
                            ];
                        }
                    }
                    
                    if (count($periodsProcessed) > 0) {
                        echo json_encode([
                            'status' => 'success',
                            'total_marked_absent' => $totalMarked,
                            'periods_processed' => $periodsProcessed,
                            'all_students' => $allMarkedStudents
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 'info',
                            'message' => 'No periods have just completed'
                        ]);
                    }
                }
            }
            break;
            
        case 'check_completed_periods':
            // Endpoint to check for recently completed periods (without marking absences)
            $batches = $_GET['batches'] ?? 'E1,E2';
            $batchArray = explode(',', $batches);
            $completedPeriods = [];
            
            foreach ($batchArray as $batchCode) {
                $completedPeriod = getCompletedPeriod($batchCode);
                
                if ($completedPeriod) {
                    $completedPeriods[$batchCode] = $completedPeriod;
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'time' => date('H:i:s'),
                'completed_periods' => $completedPeriods
            ]);
            break;
            
        case 'get_attendance_report':
            // Endpoint to get attendance report for a specific date and batch
            $batch = $_GET['batch'] ?? 'E1';
            $date = $_GET['date'] ?? date('Y-m-d');
            
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $date = date('Y-m-d');
            }
            
            // Get all students in the batch
            $stmt = $conn->prepare("SELECT id, name, roll_number FROM students WHERE batch = ? ORDER BY roll_number");
            $stmt->bind_param("s", $batch);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $students = [];
            while ($row = $result->fetch_assoc()) {
                $students[$row['id']] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'roll_number' => $row['roll_number'],
                    'attendance' => []
                ];
            }
            
            // Get all attendance records for the date and batch
            $stmt = $conn->prepare("SELECT a.student_id, a.period, a.subject, a.status 
                                FROM attendance_logs a
                                JOIN students s ON a.student_id = s.id
                                WHERE s.batch = ? AND DATE(a.timestamp) = ?");
            $stmt->bind_param("ss", $batch, $date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                if (isset($students[$row['student_id']])) {
                    $students[$row['student_id']]['attendance'][$row['period']] = [
                        'subject' => $row['subject'],
                        'status' => $row['status']
                    ];
                }
            }
            
            // Get the timetable for that date to identify all periods
            $dayOfWeek = date('D', strtotime($date));
            $tableName = $batch . '_Timetable';
            $stmt = $conn->prepare("SELECT * FROM $tableName WHERE day = ?");
            $stmt->bind_param("s", $dayOfWeek);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $periods = [];
            $subjects = [];
            
            if ($result->num_rows > 0) {
                $timetable = $result->fetch_assoc();
                for ($i = 1; $i <= 7; $i++) {
                    $periodKey = "period_" . $i;
                    $periodLabel = "P" . $i . ": " . ($i < 4 ? ($i + 8) . "-" . ($i + 9) : 
                                  ($i == 4 ? "12:45-1:45" : 
                                   ($i == 5 ? "1:45-2:45" : 
                                    ($i == 6 ? "3-4" : "4-5"))));
                    
                    if (isset($timetable[$periodKey]) && !empty($timetable[$periodKey])) {
                        $periods[] = $periodLabel;
                        $subjects[$periodLabel] = $timetable[$periodKey];
                    }
                }
            }
            
            // Compile report
            $report = [
                'date' => $date,
                'batch' => $batch,
                'day_of_week' => $dayOfWeek,
                'periods' => $periods,
                'period_subjects' => $subjects,
                'students' => array_values($students)
            ];
            
            echo json_encode([
                'status' => 'success',
                'report' => $report
            ]);
            break;
            
        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>