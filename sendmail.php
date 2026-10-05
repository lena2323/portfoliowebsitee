<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid request.");
}

$name = trim($_POST["name"] ?? "");
$email = filter_var(trim($_POST["email"] ?? ""), FILTER_VALIDATE_EMAIL);
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($name === "" || !$email || $subject === "" || $message === "") {
    exit("Please fill in all required fields.");
}

$to = "lena.jovicic23@gmail.com";

$emailSubject = "Contact Form: " . $subject;

$emailBody =
    "You received a new message from your website.\n\n" .
    "Name: " . $name . "\n" .
    "Email: " . $email . "\n" .
    "Subject: " . $subject . "\n\n" .
    "Message:\n" . $message;

$headers = "From: Rogue Gone Awol <noreply@roguegoneawol.com>\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($to, $emailSubject, $emailBody, $headers)) {

    $confirmationSubject = "Thank you for contacting me";

    $confirmationBody =
        "Hi " . $name . ",\n\n" .
        "Thank you for sending your message! I will get back to you ASAP.\n\n" .
        "Best regards,\n" .
        "Milenija Jovicic";

    $confirmationHeaders =
        "From: Milenija Jovicic <noreply@roguegoneawol.com>\r\n";
    $confirmationHeaders .=
        "Reply-To: noreply@roguegoneawol.com\r\n";
    $confirmationHeaders .=
        "Content-Type: text/plain; charset=UTF-8\r\n";

    mail(
        $email,
        $confirmationSubject,
        $confirmationBody,
        $confirmationHeaders
    );

    header("Refresh: 5; url=/");

    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Message Sent | Rogue Gone Awol</title>
        <link rel="stylesheet" href="style.css">
    </head>

    <body>

        <div class="success-container">
            <h2 class="success-title">
                Message successfully sent!
            </h2>
            <p class="success-text">
                Thank you for contacting me.
            </p>
            <p class="redirect-text">
                You will be automatically redirected to the homepage
                in a few seconds.<br>

                If you are not redirected automatically,
                <a href="/" class="btn-link">click here</a>.
            </p>
        </div>
    <style>
            body {
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: Arial, sans-serif;
                text-align: center;
            }

            .message {
                padding: 40px 20px;
            }

            h1 {
                margin-bottom: 15px;
            }

            p {
                margin: 8px 0;
            }
        </style>
    </body>
    </html>

    <?php

} else {

    http_response_code(500);

    echo "Something went wrong while sending your message. Please try again.";
}

?>
