<?php
session_start();

$errors = [];

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

    // Query to fetch department names and passwords from depts table
    $stmt = $db->query("SELECT department_name, password FROM depts");
    $deptData = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Department Data</title>
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
        .table {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: none;
            color: #fff;
        }
        .table th, .table td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .table th {
            border-top: none;
        }
        .alert {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            border-radius: 10px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Department Data</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <?php if (!empty($deptData)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Password</th>
                            <th>Copy</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deptData as $data): ?>
                            <tr>
                                <td><?php echo $data['department_name']; ?></td>
                                <td><?php echo $data['password']; ?></td>
                                <td>
                                    <button class="btn btn-primary copy-btn" data-password="<?php echo $data['password']; ?>">Copy</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No department data found.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Function to copy password to clipboard
        function copyToClipboard(text) {
            var tempInput = document.createElement("input");
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);
        }

        // Event listener for copy button clicks
        document.querySelectorAll('.copy-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                var password = this.getAttribute('data-password');
                copyToClipboard(password);
                alert('Password copied to clipboard: ' + password);
            });
        });
    </script>
</body>
</html>
