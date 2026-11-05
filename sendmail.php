<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Invalid request.");
}

/* ---------- settings ---------- */

$to = "lena.jovicic23@gmail.com";
$maxPerHour = 3;          // messages allowed from one IP per hour
$minSeconds = 4;          // faster than this after page load = bot
$blockedIps = [];         // add IPs here to block them, e.g. ["1.2.3.4"]

// Cloudflare Turnstile secret key (dash.cloudflare.com > Turnstile)
// while it is empty the captcha check is skipped so the form keeps working
$turnstileSecret = "0x4AAAAAAF00wRGPmMvs3D40lweSIwk2Djo";

// private folder for the rate limit and the spam log, closed to the web
$dataDir = __DIR__ . "/.data";
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0700, true);
    @file_put_contents($dataDir . "/.htaccess", "Require all denied\nDeny from all\n");
}

/* ---------- helpers ---------- */

function clientIp(): string
{
    // Cloudflare sends the real visitor IP in this header
    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER["REMOTE_ADDR"] ?? "unknown";
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : "unknown";
}

function logAttempt(string $dataDir, string $ip, string $reason): void
{
    $line = date("Y-m-d H:i:s") . " | " . $ip . " | " . $reason . " | " .
        substr(str_replace(["\r", "\n"], " ", $_SERVER["HTTP_USER_AGENT"] ?? "-"), 0, 200) . "\n";
    @file_put_contents($dataDir . "/blocked.log", $line, FILE_APPEND | LOCK_EX);
}

// bots get the normal success page so they think it worked and don't try harder
function fakeSuccess(string $dataDir, string $ip, string $reason): void
{
    logAttempt($dataDir, $ip, $reason);
    showSuccess();
    exit;
}

function clean(string $value): string
{
    // removes line breaks so nobody can inject extra mail headers
    return trim(str_replace(["\r", "\n", "%0a", "%0d"], " ", $value));
}

function looksLikeGibberish(string $text): bool
{
    // keyboard mashing like "wkdjwfhwwj": 7+ consonants in a row
    return preg_match("/[bcdfghjklmnpqrstvwxz]{7,}/i", $text) === 1;
}

function rateLimited(string $dataDir, string $ip, int $max): bool
{
    $file = $dataDir . "/ratelimit.json";
    $fp = @fopen($file, "c+");
    if (!$fp) {
        return false;
    }
    flock($fp, LOCK_EX);
    $data = json_decode(stream_get_contents($fp) ?: "{}", true) ?: [];
    $now = time();

    // forget anything older than an hour
    foreach ($data as $key => $times) {
        $data[$key] = array_values(array_filter($times, fn($t) => $t > $now - 3600));
        if (!$data[$key]) {
            unset($data[$key]);
        }
    }

    $limited = count($data[$ip] ?? []) >= $max;
    if (!$limited) {
        $data[$ip][] = $now;
    }

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data));
    flock($fp, LOCK_UN);
    fclose($fp);

    return $limited;
}

function captchaPassed(string $secret, string $ip): bool
{
    $token = $_POST["cf-turnstile-response"] ?? "";
    if ($token === "") {
        return false;
    }

    $context = stream_context_create([
        "http" => [
            "method" => "POST",
            "header" => "Content-Type: application/x-www-form-urlencoded\r\n",
            "content" => http_build_query(["secret" => $secret, "response" => $token, "remoteip" => $ip]),
            "timeout" => 10,
        ],
    ]);
    $result = @file_get_contents("https://challenges.cloudflare.com/turnstile/v0/siteverify", false, $context);

    return $result !== false && (json_decode($result, true)["success"] ?? false) === true;
}

function showSuccess(): void
{
    header("Refresh: 5; url=/");
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex">
        <title>Message Sent | roguegoneawol</title>
        <link rel="stylesheet" href="style.css">
    </head>

    <body>

        <div class="success-container">
            <h1>
                Message successfully sent!
            </h1>
            <p>
                Thank you for contacting me.
            </p>
            <p>
                You will be automatically redirected to the homepage
                in a few seconds.<br>
                If you are not redirected automatically please <br>
                <a href="/" class="btn-link">click here</a>.
            </p>
        </div>
    <style>
            body {
                margin: 0;
                height: auto;
                display: flex;
                align-items: center;
                justify-content: center;
                padding:20px;
                text-align:center;
                margin-top: 140px;
            }

            h1 {
                margin-bottom: 15px;
            }

            p {
                margin: 0;
                margin-top: 8px;
            }

            a{
                color:#ef83ab;
            }
        </style>
    </body>
    </html>
    <?php
}

/* ---------- checks ---------- */

$ip = clientIp();

if (in_array($ip, $blockedIps, true)) {
    fakeSuccess($dataDir, $ip, "blocked IP");
}

// honeypot: hidden field, only bots fill it
if (trim($_POST["website"] ?? "") !== "") {
    fakeSuccess($dataDir, $ip, "honeypot");
}

// timestamp set by script.js when the page loads
$formTs = (int) ($_POST["form_ts"] ?? 0);
$elapsed = time() - $formTs;
if ($formTs === 0 || $elapsed < $minSeconds || $elapsed > 86400) {
    fakeSuccess($dataDir, $ip, "timing (" . $elapsed . "s)");
}

if ($turnstileSecret !== "" && !captchaPassed($turnstileSecret, $ip)) {
    logAttempt($dataDir, $ip, "captcha failed");
    exit("Captcha check failed. Please go back and try again.");
}

$name = clean($_POST["name"] ?? "");
$email = filter_var(clean($_POST["email"] ?? ""), FILTER_VALIDATE_EMAIL);
$subject = clean($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($name === "" || !$email || $subject === "" || $message === "") {
    exit("Please fill in all required fields.");
}

if (mb_strlen($name) > 80 || mb_strlen($subject) > 150 || mb_strlen($message) > 5000) {
    exit("Your message is too long.");
}

$urlPattern = "/(https?:\/\/|www\.|\.(com|net|org|ru|xyz|top|info)\b)/i";
if (preg_match($urlPattern, $name . " " . $subject)) {
    fakeSuccess($dataDir, $ip, "link in name/subject");
}

if (preg_match_all("/https?:\/\/|www\./i", $message) > 2) {
    fakeSuccess($dataDir, $ip, "too many links");
}

if (looksLikeGibberish($name . " " . $subject . " " . $message)) {
    fakeSuccess($dataDir, $ip, "gibberish");
}

if (rateLimited($dataDir, $ip, $maxPerHour)) {
    http_response_code(429);
    logAttempt($dataDir, $ip, "rate limit");
    exit("You've sent too many messages. Please try again later.");
}

/* ---------- send ---------- */

$emailSubject = "Contact Form: " . $subject;

$emailBody =
    "You received a new message from your website.\n\n" .
    "Name: " . $name . "\n" .
    "Email: " . $email . "\n" .
    "Subject: " . $subject . "\n\n" .
    "Message:\n" . $message . "\n\n" .
    "----\n" .
    "IP: " . $ip . "\n" .
    "Browser: " . clean($_SERVER["HTTP_USER_AGENT"] ?? "-") . "\n" .
    "Sent: " . date("Y-m-d H:i:s");

$headers = "From: roguegoneawol <noreply@roguegoneawol.com>\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($to, $emailSubject, $emailBody, $headers)) {

    // no name in the auto-reply, so spammers can't use it to send their text to other people
    $confirmationSubject = "Thank you for contacting me";

    $confirmationBody =
        "Hi,\n\n" .
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

    showSuccess();

} else {

    http_response_code(500);

    echo "Something went wrong while sending your message. Please try again.";
}
