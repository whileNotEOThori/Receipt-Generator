<?php
//load .env file
require __DIR__ .'/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection (update with your credentials)
$host = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $phone = $_POST['phoneNumber'];
    $student_number = $_POST['studentNumber'];
    $amount = $_POST['amount'];
    $exec_member = $_POST['execMember'];
 
    // Generate unique receipt number
    $count_query = $conn->prepare("SELECT COUNT(*) AS count FROM testing");
    $count_query->execute();
    $result = $count_query->get_result();
    $row = $result->fetch_assoc();
    $receipt_number = "CC" . date("Y") . str_pad($row['count'] + 1, 4, "0", STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO testing (receipt_num, name, surname, phone_num, student_num, amount, exec_member) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Check for SQL errors
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssssss", $receipt_number, $name, $surname, $phone, $student_number, $amount, $exec_member);

    if (!$stmt->execute()) {
        die("Execution failed: " . $stmt->error);
    }
        
    // echo "Receipt generated and sent successfully.";
    echo "Data added into database";
    
    $stmt->close();
    $conn->close();
}

?>