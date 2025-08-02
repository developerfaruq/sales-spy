<?php

function getLocationInfoByIp($ip) {
    // Use a free IP geolocation API (ip-api.com, ipinfo.io, etc.)
    $response = @file_get_contents("http://ip-api.com/json/{$ip}");
    
    if ($response) {
        $data = json_decode($response, true);
        return [
            'country' => $data['country'] ?? '',
            'region' => $data['regionName'] ?? '',
            'city' => $data['city'] ?? '',
            'latitude' => $data['lat'] ?? '',
            'longitude' => $data['lon'] ?? '',
        ];
    }

    return [];
}

function getBrowser($userAgent) {
    if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
    if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
    if (strpos($userAgent, 'Safari') !== false) return 'Safari';
    if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) return 'Internet Explorer';
    return 'Unknown';
}

function getPlatform($userAgent) {
    if (preg_match('/linux/i', $userAgent)) return 'Linux';
    if (preg_match('/macintosh|mac os x/i', $userAgent)) return 'Mac';
    if (preg_match('/windows|win32/i', $userAgent)) return 'Windows';
    return 'Unknown';
}

function getDevice($userAgent) {
    if (preg_match('/mobile/i', $userAgent)) return 'Mobile';
    if (preg_match('/tablet/i', $userAgent)) return 'Tablet';
    return 'Desktop';
}
