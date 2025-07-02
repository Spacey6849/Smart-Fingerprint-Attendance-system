<?php
require_once '../includes/config.php';

// Check which type of filter is being used (date or month)
$using_date_filter = isset($_GET['date']);
$using_month_filter = isset($_GET['month']);

// Get filter parameters based on which dashboard is calling the export
if ($using_date_filter) {
    // Daily dashboard parameters
    $date = $_GET['date'] ?? date('Y-m-d');
    $batch = $_GET['batch'] ?? '';
    $student_id = $_GET['student_id'] ?? null;
    
    // Build query
    $query = "SELECT a.*, s.name, s.batch 
              FROM attendance_logs a
              JOIN students s ON a.student_id = s.id
              WHERE DATE(a.timestamp) = ?";
              
    $params = [$date];
    $types = "s";
    
    // Set filename
    $filename = "attendance_" . $date . ".csv";
} 
elseif ($using_month_filter) {
    // Monthly dashboard parameters
    $month = $_GET['month'] ?? date('Y-m');
    $batch = $_GET['batch'] ?? '';
    $student_id = $_GET['student_id'] ?? null;
    
    // Build query
    $query = "SELECT a.*, s.name, s.batch 
              FROM attendance_logs a
              JOIN students s ON a.student_id = s.id
              WHERE DATE_FORMAT(a.timestamp, '%Y-%m') = ?";
              
    $params = [$month];
    $types = "s";
    
    // Set filename
    $filename = "attendance_" . $month . ".csv";
} 
else {
    // Fallback to current date if neither parameter is provided
    $date = date('Y-m-d');
    $batch = $_GET['batch'] ?? '';
    $student_id = $_GET['student_id'] ?? null;
    
    // Build query
    $query = "SELECT a.*, s.name, s.batch 
              FROM attendance_logs a
              JOIN students s ON a.student_id = s.id
              WHERE DATE(a.timestamp) = ?";
              
    $params = [$date];
    $types = "s";
    
    // Set filename
    $filename = "attendance_" . $date . ".csv";
}

// Apply common filters for both queries
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

// Execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$attendance = $result->fetch_all(MYSQLI_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Write the CSV header
fputcsv($output, ['Student Name', 'Batch', 'Period', 'Subject', 'Status', 'Timestamp']);

// Write the data rows
foreach ($attendance as $record) {
    $timestamp = new DateTime($record['timestamp']);
    
    // Format data for each row
    $row = [
        $record['name'],
        $record['batch'],
        $record['period'],
        $record['subject'] ?? 'N/A',
        $record['status'],
        $timestamp->format('M d, Y h:i A')
    ];
    
    fputcsv($output, $row);
}

// Close the file pointer
fclose($output);
exit;
?>