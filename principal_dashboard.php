<?php
session_start();

$errors = [];
$totalApproved = 0;
$totalPending = 0;
$totalRejected = 0;


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

    // Count approved leave requests
    $stmt_approved = $db->query("SELECT COUNT(*) as total FROM leavereq WHERE status = 'Approved'");
    $totalApproved = $stmt_approved->fetch(PDO::FETCH_ASSOC)['total'];

    // Count pending leave requests
    $stmt_pending = $db->query("SELECT COUNT(*) as total FROM leavereq WHERE status = 'Pending'");
    $totalPending = $stmt_pending->fetch(PDO::FETCH_ASSOC)['total'];

    // Count rejected leave requests
    $stmt_rejected = $db->query("SELECT COUNT(*) as total FROM leavereq WHERE status = 'Rejected'");
    $totalRejected = $stmt_rejected->fetch(PDO::FETCH_ASSOC)['total'];
    $totalAsWhole=$totalApproved+$totalPending+$totalRejected;

} catch (PDOException $e) {
    // Handle database connection errors
    $errors[] = 'Database connection error: ' . $e->getMessage();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal Dashboard</title>
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

        .card-header {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            border-bottom: none;
            font-size: 24px;
            font-weight: bold;
        }

        .btn {
            margin: 10px;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }

        .leave-card {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .leave-card h4 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .leave-card p {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .leave-card .approved {
            color: #fff;
            background-color: #28a745;
            padding: 10px;
            border-radius: 5px;
        }

        .leave-card .pending {
            color: #fff;
           background-color:#FFC94A;
            padding: 10px;
            border-radius: 5px;
        }

        .leave-card .rejected {
            color: #fff;
            background-color:#c82333 ;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Principal Dashboard</h1>
        <div class="row">
            <div class="col-md-12">
                <h3 class="text-center mb-4">Actions:</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">Add Department</div>
                    <div class="card-body d-flex justify-content-center">
                        <a href="addDept.php" class="btn btn-danger">Add Department</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">View Departments</div>
                    <div class="card-body d-flex justify-content-center">
                        <a href="adminData.php" class="btn btn-success">View Departments</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-4 leave-card">
                    <div class="card-header">Leave Requests</div>
                    <div class="card-body">
                        <h4>Total Active Requests: <?php echo $totalAsWhole; ?></h4>
                        <p class="approved">Approved: <?php echo $totalApproved; ?></p>
                        <p class="pending">Pending: <?php echo $totalPending; ?></p>
                        <p class="rejected">Rejected: <?php echo $totalRejected; ?></p>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>