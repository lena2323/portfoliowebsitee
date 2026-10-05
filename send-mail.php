
<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method Not Allowed");
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($name === "" || $email === "" || $subject === "" || $message === "") {
    http_response_code(400);
    exit("Please fill in all required fields.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit("Invalid email address.");
}

$name = str_replace(["\r", "\n"], "", $name);
$email = str_replace(["\r", "\n"], "", $email);
$subject = str_replace(["\r", "\n"], "", $subject);

$to = "lena.jovicic23@gmail.com";

$emailSubject = "Contact Form: " . $subject;

$emailBody =
    "You received a new message from your website.\n\n" .
    "Name: " . $name . "\n" .
    "Email: " . $email . "\n" .
    "Subject: " . $subject . "\n\n" .
    "Message:\n" .
    $message;

$headers = "From: Rogue Gone Awol <noreply@roguegoneawol.com>\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$sent = mail($to, $emailSubject, $emailBody, $headers);

if (!$sent) {
    http_response_code(500);
    exit("Something went wrong while sending your message.");
}

$confirmationSubject = "Thank you for contacting me";

$confirmationBody =
    "Hi " . $name . ",\n\n" .
    "Thank you for sending your message! I will get back to you ASAP.\n\n" .
    "Best,\n" .
    "Milenija Jovicic";

$confirmationHeaders = "From: Milenija Jovicic <noreply@roguegoneawol.com>\r\n";
$confirmationHeaders .= "Reply-To: noreply@roguegoneawol.com\r\n";
$confirmationHeaders .= "MIME-Version: 1.0\r\n";
$confirmationHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";

mail($email, $confirmationSubject, $confirmationBody, $confirmationHeaders);

header("Location: /pages/success");
exit;

?>