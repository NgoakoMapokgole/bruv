<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/PHPMailer-master/src/Exception.php';


$mail = new PHPMailer(true);

try {
    // --- SMTP SETTINGS ---
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ngoakomapokgole2@gmail.com'; // your Gmail
    $mail->Password = 'your_app_password';    // app password (not normal Gmail password)
    $mail->SMTPSecure = 'tls'; // or PHPMailer::ENCRYPTION_STARTTLS
    $mail->Port = 587;

    // --- FROM/TO DETAILS ---
    $mail->setFrom('ngoakomapokgole2@gmail.com', 'Ngoako');
    $mail->addAddress('ngoakomapokgole4@gmail.com', 'Thato'); // recipient
    $mail->addReplyTo('ngoakomapokgole2@gmail.com', 'Ngoako');

    // --- EMAIL CONTENT ---
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from University Server';
    $mail->Body    = '<h2>Hi there!</h2><p>This is a test email sent from cs3-dev using PHPMailer.</p>';
    $mail->AltBody = 'Hi there! This is a plain-text version of the email.';

    // --- SEND ---
    $mail->send();
    echo '✅ Message sent successfully!';
} catch (Exception $e) {
    echo "❌ Message could not be sent. Error: {$mail->ErrorInfo}";
}
?>

?>