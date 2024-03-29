<?php
session_start();

$errors = [];
$success = '';

// Database connection parameters
$host = 'localhost';
$dbname = 'lms';
$db_user = 'root'; 
$db_password = ''; 

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $roll_no = trim($_POST['roll_no']);
    $semester = trim($_POST['semester']);
    $department = trim($_POST['department']);

    if (empty($name)) {
        $errors[] = 'Name is required.';
    }

    if (empty($email)) {
        $errors[] = 'Email is required.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($roll_no)) {
        $errors[] = 'Roll number is required.';
    } elseif (!is_numeric($roll_no)) {
        $errors[] = 'Roll number should be numeric.';
    }

    if (empty($semester)) {
        $errors[] = 'Semester is required.';
    } elseif (!is_numeric($semester)) {
        $errors[] = 'Semester should be numeric.';
    } elseif ($semester > 8) {
        $errors[] = 'Semester cannot be greater than 8.';
    }

    if (empty($department)) {
        $errors[] = 'Department is required.';
    }

    if (empty($errors)) {
        try {
            // Establishing a connection to the database using PDO
            $db = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_password);

            // Set PDO to throw exceptions on error
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          
            // Create the studentInfo table if it doesn't exist
            $db->exec("CREATE TABLE IF NOT EXISTS studentInfo (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id VARCHAR(10) NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                roll_no VARCHAR(50) NOT NULL,
                semester VARCHAR(50) NOT NULL,
                department VARCHAR(100) NOT NULL
            )");

            // Generate a unique student ID
            $student_id_prefix = substr($department, 0, 3); // Get the first three characters of the department name
            $stmt = $db->query("SELECT MAX(id) AS max_id FROM studentInfo");
            $max_id = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
            $student_id = $student_id_prefix . '_' . ($max_id + 1);

            // Insert user into the studentInfo table
            $stmt = $db->prepare("INSERT INTO studentInfo (student_id, name, email, password, roll_no, semester, department) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$student_id, $name, $email, $password, $roll_no, $semester, $department])) {
                $success = 'User registered successfully!';
            } else {
                $errors[] = 'Error inserting user.';
            }
        } catch (PDOException $e) {
            // Handle database connection errors
            $errors[] = 'Database connection error: ' . $e->getMessage();
        }
    }
}

try {
    // Establishing a connection to the database using PDO
    $db = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_password);

    // Set PDO to throw exceptions on error
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch departments
    $stmt = $db->query("SELECT department_name FROM depts");
    $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Handle database connection errors
    $errors[] = 'Database connection error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>
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
            margin-top:10px;
            border-radius: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }
        .btn-secondary:hover {
            
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }
        
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title text-center">Signup</h1>

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
                                <label for="name">Name:</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="roll_no">Roll Number:</label>
                                <input type="text" name="roll_no" id="roll_no" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="semester">Semester:</label>
                                <input type="text" name="semester" id="semester" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="department">Department:</label>
                                <select name="department" id="department" class="form-control" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" name="submit" class="btn btn-primary btn-block">Register</button>
                            <a href="login.php" class="btn btn-secondary btn-block m-1">Go back to login</a>
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