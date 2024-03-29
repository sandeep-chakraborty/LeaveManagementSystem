<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: adminLogin.php");
    exit;
}

$errors = [];

try {
    // Database connection parameters
    $host = 'localhost'; 
    $dbname = 'lms';
    $db_user = 'root';
    $db_password = '';

    // Establishing a connection to the database using PDO
    $db = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_password);

    // Set PDO to throw exceptions on error
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve department names from adminInfo table
    $stmt = $db->query("SELECT department_name FROM depts");
    $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Handle database connection errors
    $errors[] = 'Database connection error: ' . $e->getMessage();
}

// Retrieve the logged-in department from the session
$loggedInDepartment = $_SESSION['department'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $remarks = $_POST['remarks'];

    if ($_POST['action'] == 'approve') {
        $confirmation_message = "Are you sure you want to approve this leave request?";
        $confirmation_script = "return confirm('$confirmation_message');";

        echo "<script>$confirmation_script</script>";

        try {
            // Update status to approved and set remarks
            $stmt = $db->prepare("UPDATE leavereq SET status = 'approved', adminRemarks = :remarks WHERE id = :id");
            $stmt->bindParam(':remarks', $remarks);
            $stmt->bindParam(':id', $request_id);
            $stmt->execute();
        } catch (PDOException $e) {
            // Handle database connection errors
            $errors[] = 'Database connection error: ' . $e->getMessage();
        }
    } elseif ($_POST['action'] == 'reject') {
        $confirmation_message = "Are you sure you want to reject this leave request?";
        $confirmation_script = "return confirm('$confirmation_message');";

        echo "<script>$confirmation_script</script>";

        try {
            // Update status to rejected and set remarks
            $stmt = $db->prepare("UPDATE leavereq SET status = 'rejected', adminRemarks = :remarks WHERE id = :id");
            $stmt->bindParam(':remarks', $remarks);
            $stmt->bindParam(':id', $request_id);
            $stmt->execute();
        } catch (PDOException $e) {
            // Handle database connection errors
            $errors[] = 'Database connection error: ' . $e->getMessage();
        }
    }
}

// Retrieve leave requests based on the button clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['view'])) {
    $status = $_POST['view'];
    $query = "SELECT * FROM leavereq WHERE department = :loggedInDepartment";
    if ($status !== 'all') {
        $query .= " AND status = :status";
    }
    try {
        $stmt = $db->prepare($query);
        $stmt->bindParam(':loggedInDepartment', $loggedInDepartment);
        if ($status !== 'all') {
            $stmt->bindParam(':status', $status);
        }
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle database connection errors
        $errors[] = 'Database connection error: ' . $e->getMessage();
    }
} else {
    // Retrieve only pending leave requests for the logged-in department by default
    try {
        $stmt = $db->prepare("SELECT * FROM leavereq WHERE status = 'pending' AND department = :loggedInDepartment");
        $stmt->bindParam(':loggedInDepartment', $loggedInDepartment);
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle database connection errors
        $errors[] = 'Database connection error: ' . $e->getMessage();
    }
}

date_default_timezone_set('Asia/Kolkata');
function deletePassedRequests($db) {
    try {
        $current_date = date('Y-m-d H:i:s');
        $stmt = $db->prepare("DELETE FROM leavereq WHERE to_date < :current_date");
        $stmt->bindParam(':current_date', $current_date);
        $stmt->execute();
    } catch (PDOException $e) {
        // Handle database connection errors
        $errors[] = 'Database connection error: ' . $e->getMessage();
    }
}

// Call the function to delete passed requests
deletePassedRequests($db);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DHSK LMS Admin Base</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #212529;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .table thead th {
            background-color: #343a40;
            color: #ffffff;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.075);
        }
        .remarks-textarea {
            width: 100%;
        }
        .header {
            background-color: #007bff;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-weight: bold;
            font-size: 36px;
        }
        .btn {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DHSK LMS Admin Base</h1>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-16">
                <h2 class="text-center mb-4">Leave Requests </h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <form method="post" action="">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" name="view" value="pending">View Pending Requests</button>
                                <button type="submit" class="btn btn-success" name="view" value="approved">View Approved Requests</button>
                                <button type="submit" class="btn btn-danger" name="view" value="rejected">View Rejected Requests</button>
                                <!-- New button for viewing all requests -->
                                <button type="submit" class="btn btn-info" name="view" value="all">View All Requests</button>
                            </div>
                        </form>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Roll No</th>
                                    <th>Semester</th>
                                    <th>Department</th>
                                    <th>Student ID</th>
                                    <th>reason</th>
                                    <th>From Date</th>
                                    <th>To Date</th>
                                    <th>Status</th>
                                    <th>Admin Remarks</th>
                                    <th>Leaves Taken</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><?php echo $request['name']; ?></td>
                                        <td><?php echo $request['email']; ?></td>
                                        <td><?php echo $request['roll_no']; ?></td>
                                        <td><?php echo $request['semester']; ?></td>
                                        <td><?php echo $request['department']; ?></td>
                                        <td><?php echo $request['student_id']; ?></td>
                                        <td><?php echo $request['remarks']; ?></td>
                                        <td><?php echo $request['from_date']; ?></td>
                                        <td><?php echo $request['to_date']; ?></td>
                                        <td><?php echo $request['status']; ?></td>
                                        <td><?php echo $request['adminRemarks']; ?></td>
                                        <td><?php echo isset($request['leaves_taken']) ? $request['leaves_taken'] : ''; ?></td>
                                        <td>
                                            <form method="post" action="">
                                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                <div class="form-group">
                                                    <textarea class="form-control remarks-textarea" name="remarks" rows="3" placeholder="Remarks"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-success" name="action" value="approve" onclick="return confirm('Are you sure you want to approve this leave request?')">Approve</button>
                                                <button type="submit" class="btn btn-danger" name="action" value="reject" onclick="return confirm('Are you sure you want to reject this leave request?')">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-16">
            <h4 class="text-center">Current Date and Time: <span id="currentDateTime"></span></h4>
            </div>
        </div>
    </div>
    </div>
    </div>
  <script>
 function updateDateTime() {
    var dateTimeElement = document.getElementById('currentDateTime');
    var currentDateTime = new Date();
    var dateOptions = { day: '2-digit', month: '2-digit', year: 'numeric' };
    var timeOptions = { hour: 'numeric', minute: 'numeric', second: 'numeric' };
    var formattedDate = currentDateTime.toLocaleDateString('en-GB', dateOptions);
    var formattedTime = currentDateTime.toLocaleTimeString('en-GB', timeOptions);
    dateTimeElement.textContent = `${formattedDate} ${formattedTime}`;
}

        updateDateTime();
        setInterval(updateDateTime, 1000);
  </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>


