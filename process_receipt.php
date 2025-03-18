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
        
    // Generate PDF receipt
    $pdf = new \FPDF();
    $pdf->AddPage();

    // Add Coding Club logo (top-left)
    $pdf->Image('logo.jpg', 10, 10, 30);

    // Title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(190, 10, 'Coding Club Membership Receipt', 0, 1, 'C');
    $pdf->Ln(20);

    // Receipt Details
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(190, 10, "Receipt Number: " . $receipt_number, 0, 1);
    $pdf->Cell(190, 10, "Name: " . $name . " " . $surname, 0, 1);
    $pdf->Cell(190, 10, "Student Number: " . $student_number, 0, 1);
    $pdf->Cell(190, 10, "Amount Paid: R" . $amount, 0, 1);
    $qrCodeYPos = $pdf->GetY();
    $pdf->Cell(190, 10, "Received by: " . $exec_member, 0, 1);
    $pdf->Ln(10);
    $pdf->Cell(190, 10, "Thank you for joining the Coding Club!", 0, 1, 'L');
    $pdf->Cell(0, 10, "Scan the QR code to upload your receipt to the form.", 0, 1, 'L');

    // Add QR Code (bottom-right)
    $pdf->Image('qr_code.png', 150,$qrCodeYPos , 40);

    // Output PDF
    $pdf->Output('D', "receipt_$receipt_number.pdf");

    echo "Data added into database";
    echo "Receipt generated successfully.";
    // echo "Receipt generated and sent successfully.";
    
    $stmt->close();
    $conn->close();
}

?>