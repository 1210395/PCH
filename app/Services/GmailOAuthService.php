<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Manages Gmail API OAuth2 access tokens for sending transactional email.
 *
 * Exchanges a stored refresh token (from config services.google.refresh_token)
 * for a short-lived access token, caching it until expiry to avoid repeated
 * token refresh requests. Used alongside PHPMailer or Laravel Mail.
 */
class GmailOAuthService
{
    private string $clientId;
    private string $clientSecret;
    private string $refreshToken;

    public function __construct()
    {
        $this->clientId = config('services.google.client_id', '');
        $this->clientSecret = config('services.google.client_secret', '');
        $this->refreshToken = config('services.google.refresh_token', '');
    }

    /**
     * Get a valid access token, refreshing if needed.
     */
    public function getAccessToken(): ?string
    {
        // Check cache first
        $cached = Cache::get('gmail_oauth_access_token');
        if ($cached) {
            return $cached;
        }

        if (empty($this->refreshToken)) {
            Log::error('Gmail OAuth: No refresh token configured');
            return null;
        }

        return $this->refreshAccessToken();
    }

    /**
     * Use the refresh token to get a new access token.
     */
    private function refreshAccessToken(): ?string
    {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->refreshToken,
                'grant_type' => 'refresh_token',
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Log::error('Gmail OAuth: cURL error - ' . $error);
            return null;
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || !isset($data['access_token'])) {
            Log::error('Gmail OAuth: Token refresh failed', [
                'http_code' => $httpCode,
                'error' => $data['error'] ?? 'unknown',
                'description' => $data['error_description'] ?? '',
            ]);
            return null;
        }

        $expiresIn = ($data['expires_in'] ?? 3600) - 60; // 60s buffer
        Cache::put('gmail_oauth_access_token', $data['access_token'], $expiresIn);

        return $data['access_token'];
    }

    /**
     * Send an email using Gmail API over HTTPS.
     */
    public function sendEmail(string $to, string $subject, string $htmlBody, ?string $fromName = null, ?string $fromEmail = null): bool
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            throw new \RuntimeException('Gmail API: No access token available. Check refresh token config.');
        }

        $from = $fromEmail ?? config('mail.from.address');
        $name = $fromName ?? config('mail.from.name');

        // Build MIME message
        $mime = $this->buildMimeMessage($from, $name, $to, $subject, $htmlBody);

        // Base64url encode
        $encodedMessage = rtrim(strtr(base64_encode($mime), '+/', '-_'), '=');

        $ch = curl_init('https://gmail.googleapis.com/gmail/v1/users/me/messages/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['raw' => $encodedMessage]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('Gmail API: cURL error - ' . $error);
        }

        if ($httpCode !== 200) {
            $data = json_decode($response, true);
            $errorMsg = $data['error']['message'] ?? $response;

            Log::error('Gmail API: Send failed', [
                'http_code' => $httpCode,
                'error' => $errorMsg,
                'response' => $response,
            ]);

            // If token expired, clear cache and retry once
            if ($httpCode === 401) {
                Cache::forget('gmail_oauth_access_token');
                $newToken = $this->getAccessToken();
                if ($newToken) {
                    return $this->sendEmailWithToken($newToken, $from, $name, $to, $subject, $htmlBody);
                }
            }

            throw new \RuntimeException("Gmail API Error (HTTP $httpCode): $errorMsg");
        }

        return true;
    }

    private function sendEmailWithToken(string $token, string $from, string $name, string $to, string $subject, string $htmlBody): bool
    {
        $mime = $this->buildMimeMessage($from, $name, $to, $subject, $htmlBody);
        $encodedMessage = rtrim(strtr(base64_encode($mime), '+/', '-_'), '=');

        $ch = curl_init('https://gmail.googleapis.com/gmail/v1/users/me/messages/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['raw' => $encodedMessage]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    private function buildMimeMessage(string $from, string $name, string $to, string $subject, string $htmlBody): string
    {
        $boundary = md5(uniqid());

        $headers = "From: {$name} <{$from}>\r\n";
        $headers .= "To: {$to}\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $headers .= "\r\n";

        // Plain text version
        $plainText = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlBody));

        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= base64_encode($plainText) . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= base64_encode($htmlBody) . "\r\n";
        $body .= "--{$boundary}--\r\n";

        return $headers . $body;
    }

    /**
     * Generate the OAuth2 authorization URL.
     */
    public static function getAuthorizationUrl(string $redirectUri): string
    {
        $params = [
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/gmail.send',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for tokens.
     */
    public static function exchangeCode(string $code, string $redirectUri): ?array
    {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode !== 200 || !isset($data['refresh_token'])) {
            Log::error('Gmail OAuth: Code exchange failed', [
                'http_code' => $httpCode,
                'response' => $data,
            ]);
            return null;
        }

        return $data;
    }
}
