<?php

namespace AutomationHub\Lib;

/**
 * Class HttpClient
 *
 * Secure cURL HTTP client with built-in SSRF (Server-Side Request Forgery) protection,
 * payload JSON serialization, custom header support, and error handling.
 *
 * @package AutomationHub\Lib
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class HttpClient
{
    /**
     * Send an HTTP POST request to a destination URL safely.
     *
     * @param string $url Target HTTP/HTTPS endpoint URL
     * @param array|string $payload Data payload to send
     * @param array $headers Key-value map of HTTP headers
     * @param int $timeout Request timeout in seconds
     * @return array Response array ['status_code' => int, 'body' => string, 'error' => string]
     * @throws \InvalidArgumentException If URL fails SSRF safety checks
     */
    public static function post(string $url, $payload, array $headers = [], int $timeout = 10): array
    {
        self::validateUrl($url);

        $ch = curl_init();

        $formattedHeaders = ['Content-Type: application/json', 'User-Agent: WHMCS-Automation-Hub/1.0 (+https://webwavedigital.co.in)'];
        foreach ($headers as $key => $val) {
            $formattedHeaders[] = is_numeric($key) ? $val : "{$key}: {$val}";
        }

        $body = is_array($payload) ? json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : (string)$payload;

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => $formattedHeaders,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false, // Prevent SSRF via HTTP redirects
        ]);

        $responseBody = curl_exec($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'status_code' => 0,
                'body'        => '',
                'error'       => "cURL Error: " . $curlError,
            ];
        }

        return [
            'status_code' => $statusCode,
            'body'        => (string)$responseBody,
            'error'       => '',
        ];
    }

    /**
     * Validate target URL against Server-Side Request Forgery (SSRF) vulnerabilities.
     * Blocks private/local IP address spaces and restricted schemes.
     *
     * @param string $url URL to check
     * @return void
     * @throws \InvalidArgumentException If URL is unsafe or invalid
     */
    public static function validateUrl(string $url): void
    {
        $url = trim($url);

        if (empty($url)) {
            throw new \InvalidArgumentException("URL cannot be empty.");
        }

        $parsed = parse_url($url);

        if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
            throw new \InvalidArgumentException("Invalid URL format: '{$url}'.");
        }

        $scheme = strtolower($parsed['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new \InvalidArgumentException("Invalid URL scheme '{$scheme}'. Only http and https are allowed.");
        }

        $host = strtolower($parsed['host']);

        // Check for localhost or literal IP targets
        if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1' || $host === '0.0.0.0') {
            throw new \InvalidArgumentException("SSRF Blocked: Localhost target '{$host}' is restricted.");
        }

        // Cloud metadata service IP check
        if ($host === '169.254.169.254') {
            throw new \InvalidArgumentException("SSRF Blocked: Cloud metadata target '{$host}' is restricted.");
        }

        // Resolve domain IP to check against private IP ranges
        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $isPrivate = !filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );

            if ($isPrivate) {
                throw new \InvalidArgumentException("SSRF Blocked: Destination IP '{$ip}' is in a private/reserved network range.");
            }
        }
    }
}
