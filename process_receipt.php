<?php
//load .env file
require __DIR__ .'/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////DATABASE SECTION////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
    $email_address = $student_number . "@vossie.net";
 
    // Generate unique receipt number
    $count_query = $conn->prepare("SELECT COUNT(*) AS count FROM receipts");
    $count_query->execute();
    $result = $count_query->get_result();
    $row = $result->fetch_assoc();
    $receipt_number = "CC" . date("Y") . str_pad($row['count'] + 1, 4, "0", STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO receipts (receipt_num, name, surname, phone_num, student_num, amount, exec_member) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Check for SQL errors
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssssss", $receipt_number, $name, $surname, $phone, $student_number, $amount, $exec_member);

    if (!$stmt->execute()) {
        die("Execution failed: " . $stmt->error);
    }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////PDF SECTION////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
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
    $pdf->Cell(190, 10, "Phone Number: " . $phone, 0, 1);
    $pdf->Cell(190, 10, "Date: " . date("D") . ", " . date("d") . " " . date("M") . " " . date("Y"), 0, 1);
    $pdf->Cell(190, 10, "Amount Paid: R" . $amount, 0, 1);
    $qrCodeYPos = $pdf->GetY();
    $pdf->Cell(190, 10, "Received by: " . $exec_member, 0, 1);
    $pdf->Ln(10);
    $pdf->Cell(190, 10, "Thank you for joining the Coding Club!", 0, 1, 'L');
    $pdf->Cell(0, 10, "Scan the QR code to upload your receipt to the form.", 0, 1, 'L');

    // Add QR Code (bottom-right)
    $pdf->Image('qr_code.png', 150,$qrCodeYPos , 40);

    // Output PDF
    // $pdf->Output('D', "receipt_$receipt_number.pdf");
    $pdf->Output('F', "receipts/receipt_$receipt_number.pdf");

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////EMAIL SECTION////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Host = $_ENV['EMAIL_HOST'];
    $mail->Username = $_ENV['EMAIL_USERNAME'];
    $mail->Password = $_ENV['EMAIL_PASSWORD'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    
    $mail->setFrom($_ENV['EMAIL_ADDRESS'], 'Coding Club');
    $mail->addAddress($email_address);
    $mail->addAttachment("receipts/receipt_$receipt_number.pdf");
    $mail->addReplyTo($_ENV['EMAIL_ADDRESS'],'Coding Club');

    $form_link = $_ENV['FORM_LINK'];
    $email_body = "Dear $name $surname,<br/>Your membership fee payment of R$amount has been received. Attached is your receipt. Please follow the link or scan the QR code on the receipt to complete the Google form where you will upload this receipt as proof of payment. Link: $form_link <br/>Thank you for joining the Coding Club!<br/><br/>Kind regards,<br/>Treasurer - Coding Club";
   
    $mail->IsHTML(true);
    $mail->Subject = "Coding Club Membership Fee Payment Receipt";
    $mail->Body = $email_body;
    $mail->AltBody = $email_body;

   if (!$mail->send())
    {
        echo "Email not sent<br/>";
    }
    else
    {
        echo "Email sent<br/>";
    }
   
    echo "Data added into database<br/>";
    echo "Receipt generated and sent successfully.<br/>";
    
    $stmt->close();
    $conn->close();
}

?>