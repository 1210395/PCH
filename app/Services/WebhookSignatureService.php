<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Verifies RSA-SHA256 signatures on incoming Jobs.ps webhook requests.
 *
 * The public key is loaded from the filesystem path configured in
 * JOBS_PS_PUBLIC_KEY_PATH. Signature verification can be bypassed for
 * development by setting JOBS_PS_SKIP_VERIFICATION=true (logs a warning).
 */
class WebhookSignatureService
{
    protected ?string $publicKey = null;

    public function __construct()
    {
        $this->loadPublicKey();
    }

    /**
     * Load the public key from configuration
     */
    protected function loadPublicKey(): void
    {
        $publicKeyPath = config('webhooks.jobs_ps.public_key_path');

        if ($publicKeyPath && file_exists($publicKeyPath)) {
            $this->publicKey = file_get_contents($publicKeyPath);
        } else {
            // Try loading from config directly (for testing or inline key)
            $this->publicKey = config('webhooks.jobs_ps.public_key');
        }

        if (empty($this->publicKey)) {
            Log::warning('Jobs.ps webhook public key not configured');
        }
    }

    /**
     * Verify the signature of a webhook request
     *
     * @param string $payload The raw request body
     * @param string $signature The base64-encoded signature from X-Signature header
     * @return bool
     */
    public function verify(string $payload, string $signature): bool
    {
        // Skip verification if enabled (for development/testing only)
        if (config('webhooks.jobs_ps.skip_verification', false)) {
            Log::warning('Skipping webhook signature verification (skip_verification enabled)');
            return true;
        }

        if (empty($this->publicKey)) {
            Log::error('Cannot verify webhook signature: public key not configured', [
                'key_path' => config('webhooks.jobs_ps.public_key_path'),
            ]);
            return false;
        }

        try {
            // Decode the base64 signature
            $decodedSignature = base64_decode($signature, true);

            if ($decodedSignature === false) {
                Log::warning('Failed to decode base64 signature', [
                    'signature_length' => strlen($signature),
                ]);
                return false;
            }

            // Load public key resource
            $publicKeyResource = openssl_pkey_get_public($this->publicKey);

            if ($publicKeyResource === false) {
                Log::error('Failed to load public key', [
                    'error' => openssl_error_string(),
                ]);
                return false;
            }

            // Verify using OpenSSL
            $result = openssl_verify(
                $payload,
                $decodedSignature,
                $publicKeyResource,
                OPENSSL_ALGO_SHA256
            );

            if ($result === 1) {
                \Log::debug('Webhook signature verified successfully');
                return true;
            }

            if ($result === 0) {
                Log::warning('Webhook signature verification failed: signature does not match', [
                    'payload_length' => strlen($payload),
                    'signature_length' => strlen($decodedSignature),
                ]);
                return false;
            }

            // $result === -1 means an error occurred
            Log::error('OpenSSL error during signature verification', [
                'error' => openssl_error_string(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Exception during webhook signature verification', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if the service is properly configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->publicKey);
    }
}
