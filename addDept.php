<?php
session_start();

$errors = [];
$success = '';

if (isset($_POST['submit'])) {
    $department = trim($_POST['department']);

    if (empty($department)) {
        $errors[] = 'Department name is required.';
    }

    if (empty($errors)) {
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
          
            // Create the depts table if it doesn't exist
            $db->exec("CREATE TABLE IF NOT EXISTS depts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                department_name VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(6) NOT NULL
            )");

            // Generate 6-digit random password
            $password = mt_rand(100000, 999999);

            // Insert department and password into the depts table
            $stmt = $db->prepare("INSERT INTO depts (department_name, password) VALUES (?, ?)");
            if ($stmt->execute([$department, $password])) {
                $success = 'Department added successfully! Password: ' . $password;
            } else {
                $errors[] = 'Error adding department.';
            }
        } catch (PDOException $e) {
            // Handle database connection errors
            $errors[] = 'Database connection error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Department</title>
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
       
        .btn-secondary {
            background-color: gray;
         
            border-radius: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }
        .btn-secondary:hover {
            
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title text-center">Add Department</h1>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <form action="" method="post">
                            <div class="form-group">
                                <label for="department">Department Name:</label>
                                <input type="text" name="department" id="department" class="form-control" required>
                            </div>

                            <button type="submit" name="submit" class="btn btn-primary btn-block">Add Department</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
