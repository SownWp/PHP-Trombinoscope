<?php

function uploadToCloudinary(string $filePath): ?string
{
    $cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'];
    $apiKey    = $_ENV['CLOUDINARY_API_KEY'];
    $apiSecret = $_ENV['CLOUDINARY_API_SECRET'];
    $timestamp = time();

    $params = [
        'folder'    => 'trombinoscope',
        'timestamp' => $timestamp,
    ];

    ksort($params);
    $signatureString = '';
    foreach ($params as $key => $value) {
        $signatureString .= "$key=$value&";
    }
    $signatureString = rtrim($signatureString, '&') . $apiSecret;
    $signature = sha1($signatureString);

    $postFields = [
        'file'      => new CURLFile($filePath),
        'api_key'   => $apiKey,
        'timestamp' => $timestamp,
        'signature' => $signature,
        'folder'    => 'trombinoscope',
    ];

    $ch = curl_init("https://api.cloudinary.com/v1_1/$cloudName/image/upload");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    return $result['secure_url'] ?? null;
}

function avatarUrl(?string $avatar): string
{
    if (!$avatar || $avatar === 'default.svg') {
        return 'https://api.dicebear.com/7.x/personas/svg?seed=default&backgroundColor=e2ddd6';
    }
    if (str_starts_with($avatar, 'http')) {
        return $avatar;
    }
    return 'uploads/' . $avatar;
}
