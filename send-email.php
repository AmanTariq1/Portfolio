<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function respond(int $status, string $message): void
{
    http_response_code($status);
    echo json_encode(
        ['message' => $message],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(405, 'This endpoint only accepts contact form submissions.');
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$requestHost = $_SERVER['HTTP_HOST'] ?? '';
if ($origin !== '' && $requestHost !== '') {
    $originHost = parse_url($origin, PHP_URL_HOST);
    $requestHost = parse_url('http://' . $requestHost, PHP_URL_HOST);
    if (!is_string($originHost) || !is_string($requestHost) ||
        strtolower($originHost) !== strtolower($requestHost)) {
        respond(403, 'The form submission could not be verified.');
    }
}

foreach (['name', 'email', 'message'] as $field) {
    if (!isset($_POST[$field]) || !is_string($_POST[$field])) {
        respond(400, 'Please complete all contact form fields.');
    }
}

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$message = trim($_POST['message']);

if ($name === '' || $email === '' || $message === '') {
    respond(400, 'Please complete all contact form fields.');
}
if (strlen($name) > 120 || strlen($email) > 254 || strlen($message) > 5000 ||
    preg_match('/[\r\n]/', $name) === 1 ||
    filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    respond(400, 'Please check your name, email and message, then try again.');
}

$smtpHost = trim((string) getenv('SMTP_HOST'));
$smtpUsername = trim((string) getenv('SMTP_USER'));
$smtpPassword = (string) getenv('SMTP_PASS');
$smtpFrom = trim((string) (getenv('SMTP_FROM') ?: $smtpUsername));
$recipient = trim((string) (getenv('CONTACT_TO') ?: 'amantariqa344@gmail.com'));
$encryption = strtolower(trim((string) (getenv('SMTP_ENCRYPTION') ?: 'tls')));
$portValue = getenv('SMTP_PORT');
$port = filter_var(
    $portValue === false || $portValue === '' ? '587' : $portValue,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1, 'max_range' => 65535]]
);

if ($smtpHost === '' || $smtpUsername === '' || $smtpPassword === '' ||
    filter_var($smtpFrom, FILTER_VALIDATE_EMAIL) === false ||
    filter_var($recipient, FILTER_VALIDATE_EMAIL) === false ||
    $port === false || !in_array($encryption, ['tls', 'ssl'], true)) {
    error_log('Portfolio contact form: SMTP environment configuration is incomplete or invalid.');
    respond(503, 'Email is not configured yet. Please try again later.');
}

$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoload)) {
    error_log('Portfolio contact form: Composer dependencies are missing.');
    respond(503, 'Email is not configured yet. Please try again later.');
}

require_once $autoload;

try {
    $mailer = new PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = $smtpHost;
    $mailer->SMTPAuth = true;
    $mailer->Username = $smtpUsername;
    $mailer->Password = $smtpPassword;
    $mailer->Port = $port;
    $mailer->SMTPSecure = $encryption === 'ssl'
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Timeout = 15;
    $mailer->CharSet = PHPMailer::CHARSET_UTF8;
    $mailer->setFrom($smtpFrom, 'Aman Tariq Portfolio');
    $mailer->addAddress($recipient);
    $mailer->addReplyTo($email, $name);
    $mailer->Subject = 'Portfolio contact form message';
    $mailer->Body = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";
    $mailer->send();
} catch (MailException $exception) {
    error_log('Portfolio contact form: SMTP delivery failed: ' . $exception->getMessage());
    respond(502, 'Your message could not be sent right now. Please try again later.');
}

respond(200, 'Thanks for your message. I will get back to you soon.');
