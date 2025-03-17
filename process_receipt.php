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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $phone = $_POST['phone'];
    $student_number = $_POST['student_number'];
    $amount = $_POST['amount'];
    $exec_member = $_POST['exec_member'];
    
    // Generate unique receipt number
    $result = $conn->query("SELECT COUNT(*) AS count FROM receipts");
    $row = $result->fetch_assoc();
    $receipt_number = "CC" . date("Y") . str_pad($row['count'] + 1, 4, "0", STR_PAD_LEFT);
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO receipts (receipt_number, name, surname, phone, student_number, amount, exec_member) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $receipt_number, $name, $surname, $phone, $student_number, $amount, $exec_member);
    $stmt->execute();
        
    // echo "Receipt generated and sent successfully.";
    echo "Data added into database";
    
    $stmt->close();
    $conn->close();
}

?>