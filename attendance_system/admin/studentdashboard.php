<?php
require_once '../includes/config.php';

// Get filter parameters
$filter_mode = $_GET['filter_mode'] ?? 'monthly';
$month = $_GET['month'] ?? date('Y-m');
$date = $_GET['date'] ?? date('Y-m-d');
$batch = $_GET['batch'] ?? 'E1';
$student_id = $_GET['student_id'] ?? null;
$subject = $_GET['subject'] ?? '';

// Build query for the attendance table
if ($filter_mode === 'date') {
    $query = "SELECT a.*, s.name, s.batch 
              FROM attendance_logs a
              JOIN students s ON a.student_id = s.id
              WHERE DATE(a.timestamp) = ?";
    $params = [$date];
    $types = "s";
} else {
    $query = "SELECT a.*, s.name, s.batch 
              FROM attendance_logs a
              JOIN students s ON a.student_id = s.id
              WHERE DATE_FORMAT(a.timestamp, '%Y-%m') = ?";
    $params = [$month];
    $types = "s";
}
if ($subject) {
    $query .= " AND a.subject = ?";
    $params[] = $subject;
    $types .= "s";
}
if ($batch) {
    $query .= " AND s.batch = ?";
    $params[] = $batch;
    $types .= "s";
}
if ($student_id) {
    $query .= " AND s.id = ?";
    $params[] = $student_id;
    $types .= "i";
}
$query .= " ORDER BY a.timestamp DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$attendance = $result->fetch_all(MYSQLI_ASSOC);

// Get list of students for filter
$students = $conn->query("SELECT id, name FROM students ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Get list of subjects for dropdown (for Specific Date mode)
$subjects_list = $conn->query("SELECT DISTINCT subject FROM attendance_logs WHERE subject IS NOT NULL AND subject != '' ORDER BY subject")->fetch_all(MYSQLI_ASSOC);

// Calculate stats
$total = count($attendance);
$present = 0;
$absent = 0;
$late = 0;

foreach ($attendance as $record) {
    if ($record['status'] === 'Present') $present++;
    else if ($record['status'] === 'Absent') $absent++;
    else if ($record['status'] === 'Late') $late++;
}

// Get student contact info if student_id is set
$student_info = null;
if ($student_id) {
    $student_query = "SELECT name, email, phone FROM students WHERE id = ?";
    $student_stmt = $conn->prepare($student_query);
    $student_stmt->bind_param("i", $student_id);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    $student_info = $student_result->fetch_assoc();
}

// Get data for the attendance graph (for the entire month)
$graph_data = [];
$attendance_totals = ['present' => 0, 'absent' => 0, 'late' => 0];
if ($student_id) {
    // Get daily attendance data
    $graph_query = "SELECT 
                    DATE(timestamp) as date,
                    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late
                    FROM attendance_logs 
                    WHERE student_id = ? 
                    AND DATE_FORMAT(timestamp, '%Y-%m') = ?
                    GROUP BY DATE(timestamp)
                    ORDER BY DATE(timestamp)";
    
    $graph_stmt = $conn->prepare($graph_query);
    $graph_stmt->bind_param("is", $student_id, $month);
    $graph_stmt->execute();
    $graph_result = $graph_stmt->get_result();
    $graph_data = $graph_result->fetch_all(MYSQLI_ASSOC);
    
    // Get totals for pie chart
    $totals_query = "SELECT 
                    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late
                    FROM attendance_logs 
                    WHERE student_id = ? 
                    AND DATE_FORMAT(timestamp, '%Y-%m') = ?";
    
    $totals_stmt = $conn->prepare($totals_query);
    $totals_stmt->bind_param("is", $student_id, $month);
    $totals_stmt->execute();
    $totals_result = $totals_stmt->get_result();
    $attendance_totals = $totals_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4361EE;
            --primary-light: #F0F3FF;
            --secondary: #3F37C9;
            --danger: #F72585;
            --success: #4CC9F0;
            --warning: #F8961E;
            --text-primary: #333;
            --text-secondary: #666;
            --bg-light: #F8F9FA;
            --border-radius: 12px;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #F8F9FA;
            color: var(--text-primary);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background-color: white;
            box-shadow: var(--shadow);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo h1 {
            font-size: 24px;
            color: var(--primary);
            margin: 0;
        }
        
        main {
            padding: 32px 0;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }
        
        .stat-card .stat-title {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }
        
        .stat-card .stat-value {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }
        
        .stat-card .stat-icon {
            position: absolute;
            bottom: 10px;
            right: 10px;
            opacity: 0.2;
            font-size: 48px;
            z-index: 1;
        }
        
        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 32px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            align-items: end;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 6px;
            color: var(--text-secondary);
        }
        
        .form-control {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .btn {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary-light);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 16px;
            text-align: left;
        }
        
        th {
            font-weight: 600;
            background-color: var(--primary-light);
            color: var(--primary);
            position: sticky;
            top: 0;
        }
        
        tbody tr {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }
        
        tbody tr:hover {
            background-color: var(--primary-light);
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-present {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        .status-absent {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }
        
        .status-late {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }
        
        .actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 16px;
            gap: 10px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }
        
        .no-data i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #ddd;
        }
        
        /* Graph styles */
        .graph-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .graph-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 20px;
        }
        
        .graph-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .graph-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        
        .graph-view-toggle {
            display: flex;
            gap: 8px;
        }
        
        .graph-view-btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
            background-color: white;
            cursor: pointer;
            font-size: 14px;
        }
        
        .graph-view-btn.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .graph-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .graph-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        
        .graph-stat-item {
            background-color: var(--bg-light);
            border-radius: var(--border-radius);
            padding: 16px;
        }
        
        .graph-stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .graph-stat-value {
            font-size: 24px;
            font-weight: bold;
        }
        
        .graph-stat-value.present {
            color: var(--success);
        }
        
        .graph-stat-value.absent {
            color: var(--danger);
        }
        
        .graph-stat-value.late {
            color: var(--warning);
        }
        
        .attendance-rate {
            background-color: var(--primary-light);
            padding: 16px;
            border-radius: var(--border-radius);
        }
        
        .attendance-rate-value {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary);
            margin: 8px 0;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-bar-fill {
            height: 100%;
            background-color: var(--primary);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* Student contact info styles */
        .student-contact {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 32px;
        }
        
        .student-contact-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .student-contact-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        
        .student-contact-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        
        .student-contact-item {
            display: flex;
            flex-direction: column;
        }
        
        .student-contact-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }
        
        .student-contact-value {
            font-size: 16px;
            font-weight: 500;
        }
        
        @media (min-width: 768px) {
            .graph-content {
                flex-direction: row;
            }
            
            .chart-container {
                flex: 2;
            }
            
            .graph-stats-container {
                flex: 1;
            }
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .filters-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><i class="fas fa-user-check"></i> Attendance Dashboard</h1>
                </div>
            </div>
        </div>
    </header>
    
    <main class="container">
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-title">Total Records</div>
                <div class="stat-value"><?php echo $total; ?></div>
                <i class="fas fa-users stat-icon" style="color: var(--primary);"></i>
            </div>
            <div class="stat-card">
                <div class="stat-title">Present</div>
                <div class="stat-value" style="color: var(--success);"><?php echo $present; ?></div>
                <i class="fas fa-check-circle stat-icon" style="color: var(--success);"></i>
            </div>
            <div class="stat-card">
                <div class="stat-title">Absent</div>
                <div class="stat-value" style="color: var(--danger);"><?php echo $absent; ?></div>
                <i class="fas fa-times-circle stat-icon" style="color: var(--danger);"></i>
            </div>
            <div class="stat-card">
                <div class="stat-title">Late</div>
                <div class="stat-value" style="color: var(--warning);"><?php echo $late; ?></div>
                <i class="fas fa-clock stat-icon" style="color: var(--warning);"></i>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Filter Attendance Records</h2>
            </div>
            <div class="card-body">
                <form method="GET" class="filters-form" id="filterForm">
                    <div class="form-group">
                        <label for="filter_mode"><i class="fas fa-filter"></i> Filter Mode</label>
                        <select id="filter_mode" name="filter_mode" class="form-control" onchange="toggleFilterMode()">
                            <option value="monthly" <?php echo $filter_mode === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                            <option value="date" <?php echo $filter_mode === 'date' ? 'selected' : ''; ?>>Specific Date</option>
                        </select>
                    </div>
                    <div class="form-group" id="monthGroup" style="display: <?php echo $filter_mode === 'monthly' ? 'block' : 'none'; ?>;">
                        <label for="month"><i class="fas fa-calendar"></i> Month</label>
                        <input type="month" id="month" name="month" class="form-control" value="<?php echo htmlspecialchars($month); ?>">
                    </div>
                    <div class="form-group" id="dateGroup" style="display: <?php echo $filter_mode === 'date' ? 'block' : 'none'; ?>;">
                        <label for="date"><i class="fas fa-calendar-day"></i> Date</label>
                        <input type="date" id="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>">
                    </div>
                    <div class="form-group" id="subjectGroup">
                        <label for="subject"><i class="fas fa-book"></i> Subject</label>
                        <select id="subject" name="subject" class="form-control">
                            <option value="">All Subjects</option>
                            <?php foreach ($subjects_list as $subj): ?>
                                <option value="<?php echo htmlspecialchars($subj['subject']); ?>" <?php echo $subject == $subj['subject'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($subj['subject']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="batch"><i class="fas fa-chalkboard"></i> Batch</label>
                        <select id="batch" name="batch" class="form-control">
                            <option value="">All Batches</option>
                            <option value="E1" <?php echo $batch == 'E1' ? 'selected' : ''; ?>>E1</option>
                            <option value="E2" <?php echo $batch == 'E2' ? 'selected' : ''; ?>>E2</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="student_id"><i class="fas fa-user-graduate"></i> Student</label>
                        <select id="student_id" name="student_id" class="form-control">
                            <option value="">All Students</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>" 
                                    <?php echo $student_id == $student['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                </form>
            </div>
        </div>
        
        <?php if ($student_id && !empty($graph_data)): ?>
        <div class="graph-container">
            <div class="graph-card">
                <div class="graph-header">
                    <h3 class="graph-title">Student Attendance Statistics for <?php echo date('F Y', strtotime($month)); ?></h3>
                    <div class="graph-view-toggle">
                        <button class="graph-view-btn active" onclick="changeChartView('pie')">Pie Chart</button>
                        <button class="graph-view-btn" onclick="changeChartView('bar')">Bar Chart</button>
                        <button class="graph-view-btn" onclick="changeChartView('daily')">Daily View</button>
                    </div>
                </div>
                
                <div class="graph-content">
                    <div class="chart-container">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                    
                    <div class="graph-stats-container">
                        <div class="attendance-rate">
                            <div class="graph-stat-label">Attendance Rate</div>
                            <?php 
                                $total_classes = $attendance_totals['present'] + $attendance_totals['absent'] + $attendance_totals['late'];
                                $attendance_rate = $total_classes > 0 ? round(($attendance_totals['present'] / $total_classes) * 100) : 0;
                            ?>
                            <div class="attendance-rate-value"><?php echo $attendance_rate; ?>%</div>
                            <div class="progress-bar">
                                <div class="progress-bar-fill" style="width: <?php echo $attendance_rate; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="graph-stats">
                            <div class="graph-stat-item">
                                <div class="graph-stat-label">Present</div>
                                <div class="graph-stat-value present"><?php echo $attendance_totals['present']; ?></div>
                            </div>
                            <div class="graph-stat-item">
                                <div class="graph-stat-label">Absent</div>
                                <div class="graph-stat-value absent"><?php echo $attendance_totals['absent']; ?></div>
                            </div>
                            <div class="graph-stat-item">
                                <div class="graph-stat-label">Late</div>
                                <div class="graph-stat-value late"><?php echo $attendance_totals['late']; ?></div>
                            </div>
                        </div>
                        
                        <div class="graph-stat-item">
                            <div class="graph-stat-label">Total Classes</div>
                            <div class="graph-stat-value"><?php echo $total_classes; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Contact Information Section -->
        <?php if ($student_info): ?>
        <div class="student-contact">
            <div class="student-contact-header">
                <h3 class="student-contact-title">Student Contact Information</h3>
            </div>
            <div class="student-contact-details">
                <div class="student-contact-item">
                    <span class="student-contact-label">Name</span>
                    <span class="student-contact-value"><?php echo htmlspecialchars($student_info['name']); ?></span>
                </div>
                <div class="student-contact-item">
                    <span class="student-contact-label">Email</span>
                    <span class="student-contact-value"><?php echo htmlspecialchars($student_info['email']); ?></span>
                </div>
                <div class="student-contact-item">
                    <span class="student-contact-label">Phone</span>
                    <span class="student-contact-value"><?php echo htmlspecialchars($student_info['phone']); ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        
        <div class="actions">
            <button class="btn btn-outline" onclick="exportToCSV()">
                <i class="fas fa-download"></i> Export to CSV
            </button>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Attendance Records</h2>
            </div>
            
            <?php if (count($attendance) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Batch</th>
                                <th>Period</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance as $record): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 32px; height: 32px; background-color: var(--primary-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                                <?php echo substr(htmlspecialchars($record['name']), 0, 1); ?>
                                            </div>
                                            <?php echo htmlspecialchars($record['name']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['batch']); ?></td>
                                    <td><?php echo htmlspecialchars($record['period']); ?></td>
                                    <td><?php echo htmlspecialchars($record['subject'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php
                                            $statusClass = "";
                                            switch(strtolower($record['status'])) {
                                                case 'present':
                                                    $statusClass = "status-present";
                                                    $statusIcon = "fas fa-check-circle";
                                                    break;
                                                case 'absent':
                                                    $statusClass = "status-absent";
                                                    $statusIcon = "fas fa-times-circle";
                                                    break;
                                                case 'late':
                                                    $statusClass = "status-late";
                                                    $statusIcon = "fas fa-clock";
                                                    break;
                                                default:
                                                    $statusClass = "";
                                                    $statusIcon = "fas fa-question-circle";
                                            }
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <i class="<?php echo $statusIcon; ?>"></i>
                                            <?php echo htmlspecialchars($record['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            $timestamp = new DateTime($record['timestamp']);
                                            echo $timestamp->format('M d, Y h:i A'); 
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-search"></i>
                    <p>No attendance records found for selected filters</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        function exportToCSV() {
            const params = new URLSearchParams(window.location.search);
            window.location.href = 'export.php?' + params.toString();
        }
        
        <?php if ($student_id && !empty($graph_data)): ?>
        // Chart.js implementation
        let attendanceChart;
        const chartColors = {
            present: '#4CC9F0',
            absent: '#F72585',
            late: '#F8961E'
        };
        
        function initChart(type = 'pie') {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            
            if (attendanceChart) {
                attendanceChart.destroy();
            }
            
            if (type === 'pie') {
                const chartData = {
                    labels: ['Present', 'Absent', 'Late'],
                    datasets: [{
                        data: [
                            <?php echo $attendance_totals['present']; ?>,
                            <?php echo $attendance_totals['absent']; ?>,
                            <?php echo $attendance_totals['late']; ?>
                        ],
                        backgroundColor: [
                            chartColors.present,
                            chartColors.absent,
                            chartColors.late
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                };
                
                attendanceChart = new Chart(ctx, {
                    type: 'pie',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            } 
            else if (type === 'bar') {
                attendanceChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Attendance'],
                        datasets: [
                            {
                                label: 'Present',
                                data: [<?php echo $attendance_totals['present']; ?>],
                                backgroundColor: chartColors.present
                            },
                            {
                                label: 'Absent',
                                data: [<?php echo $attendance_totals['absent']; ?>],
                                backgroundColor: chartColors.absent
                            },
                            {
                                label: 'Late',
                                data: [<?php echo $attendance_totals['late']; ?>],
                                backgroundColor: chartColors.late
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                stacked: true,
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }
            else if (type === 'daily') {
                // Prepare data for daily view
                const dates = <?php echo json_encode(array_column($graph_data, 'date')); ?>;
                const presentData = <?php echo json_encode(array_column($graph_data, 'present')); ?>;
                const absentData = <?php echo json_encode(array_column($graph_data, 'absent')); ?>;
                const lateData = <?php echo json_encode(array_column($graph_data, 'late')); ?>;
                
                // Format dates to be more readable (e.g., "Jan 01")
                const formattedDates = dates.map(date => {
                    const d = new Date(date);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                });
                
                attendanceChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: formattedDates,
                        datasets: [
                            {
                                label: 'Present',
                                data: presentData,
                                backgroundColor: chartColors.present,
                                borderColor: chartColors.present,
                                borderWidth: 1
                            },
                            {
                                label: 'Absent',
                                data: absentData,
                                backgroundColor: chartColors.absent,
                                borderColor: chartColors.absent,
                                borderWidth: 1
                            },
                            {
                                label: 'Late',
                                data: lateData,
                                backgroundColor: chartColors.late,
                                borderColor: chartColors.late,
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                stacked: false,
                            },
                            y: {
                                stacked: false,
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            tooltip: {
                                callbacks: {
                                    afterBody: function(context) {
                                        const date = dates[context[0].dataIndex];
                                        return `Date: ${date}`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
        
        function changeChartView(type) {
            // Update active button
            document.querySelectorAll('.graph-view-btn').forEach(btn => {
                btn.classList.toggle('active', btn.textContent.toLowerCase().includes(type));
            });
            
            // Reinitialize chart
            initChart(type);
        }
        
        // Initialize with pie chart by default
        document.addEventListener('DOMContentLoaded', function() {
            initChart('pie');
        });
        <?php endif; ?>
        
        function toggleFilterMode() {
            var mode = document.getElementById('filter_mode').value;
            document.getElementById('monthGroup').style.display = (mode === 'monthly') ? 'block' : 'none';
            document.getElementById('dateGroup').style.display = (mode === 'date') ? 'block' : 'none';
            // Subject filter is always visible now
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleFilterMode();
        });
    </script>
</body>
</html>