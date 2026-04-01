<?php

function sendMail(string $toEmail, string $subject, string $html): bool
{
    $host = $_ENV['SMTP_HOST'] ?? '';
    $port = (int)($_ENV['SMTP_PORT'] ?? 2525);
    $user = $_ENV['SMTP_USER'] ?? '';
    $pass = $_ENV['SMTP_PASS'] ?? '';
    $from = 'noreply@trombinoscope.fr';

    if ($host === '' || $user === '' || $pass === '') {
        return false;
    }

    $socket = @fsockopen($host, $port, $errno, $errstr, 30);
    if (!$socket) {
        return false;
    }

    $read = function () use ($socket) {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $response;
    };

    $read();

    fputs($socket, "EHLO localhost\r\n");
    $read();

    fputs($socket, "AUTH LOGIN\r\n");
    $read();

    fputs($socket, base64_encode($user) . "\r\n");
    $read();

    fputs($socket, base64_encode($pass) . "\r\n");
    $ehlo = $read();
    if (strpos($ehlo, '235') === false) {
        fclose($socket);
        return false;
    }

    fputs($socket, "MAIL FROM:<$from>\r\n");
    $read();

    fputs($socket, "RCPT TO:<$toEmail>\r\n");
    $read();

    fputs($socket, "DATA\r\n");
    $read();

    $message  = "From: Trombinoscope <$from>\r\n";
    $message .= "To: <$toEmail>\r\n";
    $message .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "\r\n";
    $message .= $html . "\r\n";
    $message .= ".\r\n";

    fputs($socket, $message);
    $response = $read();

    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return strpos($response, '250') !== false;
}
