<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use App\Services\WebhookSignatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected WebhookSignatureService $signatureService;

    public function __construct(WebhookSignatureService $signatureService)
    {
        $this->signatureService = $signatureService;
    }

    /**
     * Handle any HTTP method for the tender endpoint
     * Returns proper JSON for unsupported methods
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleTender(Request $request): JsonResponse
    {
        $method = $request->method();

        if ($method === 'POST') {
            return $this->receiveTender($request);
        }

        // For GET requests, return endpoint info
        if ($method === 'GET') {
            return response()->json([
                'success' => true,
                'message' => 'Jobs.ps Tender Webhook Endpoint',
                'endpoint' => url('/api/v1/tenders/receive'),
                'method' => 'POST',
                'status' => 'active',
            ], 200);
        }

        // For other methods, return method not allowed
        return response()->json([
            'success' => false,
            'message' => 'Method not allowed. Use POST to submit tenders.',
            'allowed_methods' => ['POST', 'GET'],
        ], 405);
    }

    /**
     * Receive tender webhook from jobs.ps
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function receiveTender(Request $request): JsonResponse
    {
        try {
            // Get raw body for signature verification - use php://input to avoid any Laravel modifications
            $rawBody = file_get_contents("php://input");

            // Verify signature
            $signature = $request->header('X-Signature');

            if (empty($signature)) {
                Log::warning('Webhook received without X-Signature header', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Missing X-Signature header',
                ], 401);
            }

            if (!$this->signatureService->verify($rawBody, $signature)) {
                Log::warning('Webhook signature verification failed', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 401);
            }

            // Replay-attack protection: cache the signature hash and reject
            // duplicates inside a 1-hour window. The signature is a function
            // of the payload, so reusing the same (payload, signature) pair
            // yields the same key. Without this, a captured valid signed
            // payload could be replayed forever. (bugs.md H-34)
            $replayKey = 'webhook_sig:' . hash('sha256', $signature);
            if (\Illuminate\Support\Facades\Cache::has($replayKey)) {
                Log::warning('Webhook replay detected — rejecting', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate request',
                ], 409);
            }
            \Illuminate\Support\Facades\Cache::put($replayKey, true, 3600);

            // Parse the payload (data is already validated by jobs.ps)
            $data = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Webhook received invalid JSON', [
                    'error' => json_last_error_msg(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON payload',
                ], 400);
            }

            // Process the tender - create or update
            $tender = $this->processWebhookTender($data);

            Log::debug('Tender webhook processed successfully', [
                'external_id' => $data['id'],
                'tender_id' => $tender->id,
                'action' => $tender->wasRecentlyCreated ? 'created' : 'updated',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tender processed successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Process the tender data from webhook
     *
     * @param array $data
     * @return Tender
     */
    protected function processWebhookTender(array $data): Tender
    {
        $externalId = $data['id'] ?? null;
        if (empty($externalId)) {
            throw new \InvalidArgumentException('Missing required field: id');
        }

        $externalSource = 'jobs.ps';

        // Determine status based on deadline
        $deadline = null;
        $status = 'open';
        if (!empty($data['deadline'])) {
            // Carbon::parse on garbage like "now+999years" or "2026-13-99"
            // throws and bubbles up as a 500. Validate as date first; fall
            // through to status='open' / null deadline if it doesn't parse.
            // (bugs.md M-61)
            try {
                $deadline = \Carbon\Carbon::parse($data['deadline']);
            } catch (\Throwable $e) {
                Log::warning('Webhook tender deadline failed to parse', [
                    'raw' => $data['deadline'],
                    'error' => $e->getMessage(),
                ]);
                $deadline = null;
            }
        }
        if ($deadline) {
            $daysUntilDeadline = now()->diffInDays($deadline, false);

            if ($daysUntilDeadline < 0) {
                $status = 'closed';
            } elseif ($daysUntilDeadline <= 14) {
                $status = 'closing_soon';
            }
        }

        // Clean and format the content
        $title = $this->cleanApiText($data['title'] ?? '');
        if (empty($title)) {
            throw new \InvalidArgumentException('Missing required field: title');
        }

        $content = $this->cleanApiText($data['content'] ?? '');
        $shortDescription = $this->createShortDescription($content);
        $companyName = $this->cleanApiText($data['company']['name'] ?? '');

        // Prepare tender data
        $tenderData = [
            'external_id' => $externalId,
            'external_source' => $externalSource,
            'title' => $title,
            'short_description' => $shortDescription,
            'description' => $content,
            'company_name' => $companyName,
            'company_url' => $data['company']['url'] ?? null,
            'publisher' => $companyName ?: 'Jobs.ps',
            'publisher_type' => 'other',
            'deadline' => $deadline?->toDateString(),
            'source_url' => $data['url'] ?? null,
            'locations' => $data['locations'] ?? [],
            'location' => !empty($data['locations']) ? implode(', ', $data['locations']) : null,
            'status' => $status,
            'published_date' => now()->toDateString(),
        ];

        // Update or create the tender
        $tender = Tender::updateOrCreate(
            [
                'external_id' => $externalId,
                'external_source' => $externalSource,
            ],
            $tenderData
        );

        return $tender;
    }

    /**
     * Clean text from API - decode HTML entities, normalize whitespace
     *
     * @param string $text
     * @return string
     */
    protected function cleanApiText(string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // Decode HTML entities (handles &nbsp;, &amp;, etc.)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Replace multiple spaces/non-breaking spaces with single space
        $text = preg_replace('/[\x{00A0}\x{2000}-\x{200A}\x{202F}\x{205F}\x{3000}]+/u', ' ', $text);

        // Normalize line breaks
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Replace multiple newlines with double newline (paragraph break)
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // Trim whitespace from each line
        $lines = explode("\n", $text);
        $lines = array_map('trim', $lines);
        $text = implode("\n", $lines);

        // Remove excessive whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text);

        // Final trim
        $text = trim($text);

        return $text;
    }

    /**
     * Create a short description from the full content
     *
     * @param string $content
     * @return string
     */
    protected function createShortDescription(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        // Get first paragraph or first 200 characters
        $paragraphs = explode("\n\n", $content);
        $firstParagraph = trim($paragraphs[0] ?? '');

        if (mb_strlen($firstParagraph) > 200) {
            // Cut at word boundary
            $short = mb_substr($firstParagraph, 0, 200);
            $lastSpace = mb_strrpos($short, ' ');
            if ($lastSpace !== false) {
                $short = mb_substr($short, 0, $lastSpace);
            }
            return $short . '...';
        }

        return $firstParagraph;
    }
}
