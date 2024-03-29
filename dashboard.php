<?php
session_start();

// Check if user is not logged in, redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Database connection parameters
$host = 'localhost'; 
$dbname = 'lms'; 
$db_user = 'root';
$db_password = ''; 

try {
    // Establishing a connection to the database using PDO
    $db = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_password);

    // Set PDO to throw exceptions on error
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve user information from the database
    $stmt = $db->prepare("SELECT * FROM studentinfo WHERE email = ?");
    $stmt->execute([$_SESSION['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Create the leaveReq table if it doesn't exist
    $createTableQuery = "CREATE TABLE IF NOT EXISTS leaveReq (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        roll_no VARCHAR(50) NOT NULL,
        semester VARCHAR(50) NOT NULL,
        department VARCHAR(100) NOT NULL,
        student_id VARCHAR(10) NOT NULL,
        remarks TEXT,
        from_date DATE NOT NULL,
        to_date DATE NOT NULL,
        status VARCHAR(20) DEFAULT 'Pending',
        adminRemarks TEXT,
        LeavesTaken INT DEFAULT 0
    )";
    $db->exec($createTableQuery);
} catch (PDOException $e) {
    // Handle database connection errors
    $error = 'Database connection error: ' . $e->getMessage();
}

// Function to calculate leave days excluding Sundays
function calculateLeaveDays($from_date, $to_date) {
    $start = new DateTime($from_date);
    $end = new DateTime($to_date);
    $interval = new DateInterval('P1D');
    $daterange = new DatePeriod($start, $interval, $end);

    $leaveDays = 0;
    foreach ($daterange as $date) {
        if ($date->format('N') != 7) { // Exclude Sundays
            $leaveDays++;
        }
    }
    return $leaveDays;
}

// Process leave request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['from_date'], $_POST['to_date'], $_POST['reason'])) {
        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];
        $reason = $_POST['reason'];

        // Calculate leave days excluding Sundays
        $leaveDays = calculateLeaveDays($from_date, $to_date);

        // Update LeavesTaken column in leaveReq table
        try {
            $stmt = $db->prepare("INSERT INTO leaveReq (name, email, password, roll_no, semester, department, student_id, remarks, from_date, to_date, LeavesTaken) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user['name'], $_SESSION['username'], $user['password'], $user['roll_no'], $user['semester'], $user['department'], $user['student_id'], $reason, $from_date, $to_date, $leaveDays]);
            // Set success message
            $success = 'Leave request submitted successfully!';
        } catch (PDOException $e) {
            // Handle database errors
            $error = 'Leave request submission error: ' . $e->getMessage();
        }
    } else {
        $error = "Please fill in all the fields.";
    }
}

// Retrieve previously applied leaves
try {
    $stmt = $db->prepare("SELECT * FROM leaveReq WHERE email = ?");
    $stmt->execute([$_SESSION['username']]);
    $previousLeaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle database errors
    $error = 'Error fetching previously applied leaves: ' . $e->getMessage();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #667db6, #0082c8);
            color: #fff;
            font-family: 'Roboto', sans-serif;
        }
        .container {
            margin-top: 100px;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .form-control {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            border: none;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }
        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }
        .btn-info {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.4);
            border-radius: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }
        .btn-info:hover {
            background-color: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            border-radius: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }
        .table {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: none;
            color: #fff;
        }
        .table th {
            border-top: none;
        }
        .table td, .table th {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .status-approved {
            color: #28a745;
            font-weight: bold;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-rejected {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title text-center mb-4">Leave Request</h1>
                        <?php if (isset($user)): ?>
                            <p><strong>Name:</strong> <?php echo $user['name']; ?></p>
                            <p><strong>Semester:</strong> <?php echo $user['semester']; ?></p>
                            <p><strong>Department:</strong> <?php echo $user['department']; ?></p>
                            <?php
                            // Check if the user has any approved leave requests
                            $stmt = $db->prepare("SELECT COUNT(*) FROM leaveReq WHERE email = ? AND status = 'Approved'");
                            $stmt->execute([$_SESSION['username']]);
                            $approvedLeavesCount = $stmt->fetchColumn();

                            if ($approvedLeavesCount > 0) {
                                $error = "You cannot submit a new leave request because you already have an approved leave request.";
                            }
                            ?>
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>
                            <?php if (!isset($error)): ?>
                                <form method="post" onsubmit="return confirm('Are you sure you want to submit this leave request?');">
                                    <div class="form-group">
                                        <label for="from_date">From Date:</label>
                                        <input type="date" class="form-control" id="from_date" name="from_date" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="to_date">To Date:</label>
                                        <input type="date" class="form-control" id="to_date" name="to_date" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="reason">Reason:</label>
                                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            <button id="showStatusBtn" class="btn btn-info mt-3">Show Application Status</button>
                        <?php else: ?>
                            <p>Error fetching user information.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Table to display previously applied leaves -->
        <div class="row justify-content-center mt-5" id="statusTable" style="display:none;">
            <div class="col-md-8">
                <h2 class="text-center mb-4">Previously Applied Leaves</h2>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>Status</th>
                            <th>Admin Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previousLeaves as $leave): ?>
                            <tr class="<?php echo 'status-' . strtolower($leave['status']); ?>">
                                <td><?php echo $leave['from_date']; ?></td>
                                <td><?php echo $leave['to_date']; ?></td>
                                <td><?php echo $leave['status']; ?></td>
                                <td><?php echo $leave['adminRemarks']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // JavaScript to toggle visibility of the table and change button text/color
        $(document).ready(function() {
            $('#showStatusBtn').click(function() {
                $('#statusTable').toggle();
                var btn = $(this);
                if (btn.text() === 'Show Application Status') {
                    btn.text('Hide Application Status').removeClass('btn-info').addClass('btn-danger');
                } else {
                    btn.text('Show Application Status').removeClass('btn-danger').addClass('btn-info');
                }
            });
        });
    </script>
</body>
</html>