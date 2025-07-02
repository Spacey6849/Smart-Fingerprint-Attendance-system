<?php
require_once '../includes/config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $batch = $_POST['batch'] ?? '';
        $roll_number = trim($_POST['roll_number'] ?? '');
        $fid1 = intval($_POST['fingerprint_id1'] ?? 0);
        $fid2 = intval($_POST['fingerprint_id2'] ?? 0);
        $class = 'ECOMP'; // Set the class as ECOMP by default
        
        // Validate inputs
        if (empty($name) || empty($email) || empty($phone) || empty($roll_number) || empty($batch)) {
            throw new Exception("Name, email, phone number, roll number, and batch are required");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        // Simple phone validation
        if (!preg_match("/^[0-9+\-\s()]{10,15}$/", $phone)) {
            throw new Exception("Invalid phone number format");
        }
        
        if ($fid1 <= 0 || $fid2 <= 0) {
            throw new Exception("Fingerprint IDs must be positive numbers");
        }
        
        // Check if roll number already exists
        $check_roll = $conn->prepare("SELECT id FROM students WHERE roll_number = ?");
        $check_roll->bind_param("s", $roll_number);
        $check_roll->execute();
        
        if ($check_roll->get_result()->num_rows > 0) {
            throw new Exception("Roll number already in use");
        }
        
        // Check if fingerprint IDs are already in use
        $check = $conn->prepare("SELECT id FROM students 
                               WHERE fingerprint_id1 = ? OR fingerprint_id2 = ? 
                               OR fingerprint_id1 = ? OR fingerprint_id2 = ?");
        $check->bind_param("iiii", $fid1, $fid1, $fid2, $fid2);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            throw new Exception("Fingerprint ID already in use");
        }
        
        // Insert new student - now with separate class and batch fields
        $stmt = $conn->prepare("INSERT INTO students 
                              (name, email, phone, roll_number, fingerprint_id1, fingerprint_id2, class, batch) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiiss", $name, $email, $phone, $roll_number, $fid1, $fid2, $class, $batch);
        
        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $message = "Student enrolled successfully! Fingerprint IDs: $fid1 and $fid2";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Student</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --success: #4cc9f0;
            --error: #f72585;
            --dark: #3a0ca3;
            --light: #f8f9fa;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --radius: 8px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        
        header {
            margin-bottom: 30px;
            text-align: center;
        }
        
        h1 {
            color: var(--dark);
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 16px;
        }
        
        .message {
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .message i {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .success-message {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .error-message {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--error);
            border-left: 4px solid var(--error);
        }
        
        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .full-width {
            grid-column: span 2;
        }
        
        .form-group {
            margin-bottom: 5px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: var(--radius);
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            font-size: 16px;
            border-radius: var(--radius);
            cursor: pointer;
            transition: background 0.3s;
            font-weight: 600;
            grid-column: span 2;
        }
        
        button:hover {
            background: var(--dark);
        }
        
        .instructions {
            margin-top: 40px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: var(--radius);
            border-left: 4px solid var(--primary-light);
        }
        
        .instructions h3 {
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .instructions ol {
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 8px;
        }
        
        .icon-input {
            position: relative;
        }
        
        .icon-input i {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            color: #aaa;
            pointer-events: none;
        }
        
        .icon-input input {
            padding-left: 40px;
        }
        
        .class-badge {
            display: inline-block;
            background-color: var(--primary-light);
            color: white;
            padding: 10px 15px;
            border-radius: var(--radius);
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .select-wrapper {
            position: relative;
        }
        
        .select-wrapper i {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            color: #aaa;
            z-index: 1;
            pointer-events: none;
        }
        
        .select-wrapper select {
            padding-left: 40px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23a0aec0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.5em;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
            }
            
            form {
                grid-template-columns: 1fr;
            }
            
            button, .full-width {
                grid-column: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Student Enrollment</h1>
            <p class="subtitle">Register new students with fingerprint authentication</p>
        </header>
        
        <?php if ($message): ?>
            <div class="message success-message">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="class-badge">
            <i class="fas fa-school"></i> Department: ECOMP
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <div class="icon-input">
                    <i class="fas fa-user"></i>
                    <input type="text" id="name" name="name" placeholder="Enter full name" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="roll_number">Roll Number</label>
                <div class="icon-input">
                    <i class="fas fa-id-card"></i>
                    <input type="text" id="roll_number" name="roll_number" placeholder="Enter roll number" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="icon-input">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Enter email address" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <div class="icon-input">
                    <i class="fas fa-phone"></i>
                    <input type="tel" id="phone" name="phone" placeholder="Enter phone number" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="batch">Batch</label>
                <div class="select-wrapper">
                    <i class="fas fa-users"></i>
                    <select id="batch" name="batch" required>
                        <option value="" disabled selected>Select batch</option>
                        <option value="E1">Batch E1</option>
                        <option value="E2">Batch E2</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="fingerprint_id1">Primary Fingerprint ID</label>
                <div class="icon-input">
                    <i class="fas fa-fingerprint"></i>
                    <input type="number" id="fingerprint_id1" name="fingerprint_id1" min="1" placeholder="Primary fingerprint ID" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="fingerprint_id2">Backup Fingerprint ID</label>
                <div class="icon-input">
                    <i class="fas fa-fingerprint"></i>
                    <input type="number" id="fingerprint_id2" name="fingerprint_id2" min="1" placeholder="Backup fingerprint ID" required>
                </div>
            </div>
            
            <button type="submit" class="full-width">
                <i class="fas fa-user-plus"></i> Enroll Student
            </button>
        </form>
        
        <div class="instructions">
            <h3><i class="fas fa-info-circle"></i> Instructions</h3>
            <ol>
                <li>First enroll fingerprints using the Adruino UNO enrollment program</li>
                <li>Note the fingerprint IDs assigned during enrollment</li>
                <li>Enter those IDs in this form along with student details</li>
                <li>Each fingerprint ID should be unique across all students</li>
                <li>Roll number must be unique for each student</li>
                <li>All students will be enrolled in the ECOMP department</li>
                <li>Select the appropriate batch (E1 or E2) for the student</li>
            </ol>
        </div>
    </div>
</body>
</html>