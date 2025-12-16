<?php

namespace App\Http\Controllers\Api\Helpdesk;

use App\Http\Controllers\Controller;
use App\Jobs\Helpdesk\ProcessIncomingEmailWebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IncomingEmailWebhookController extends Controller
{
    /**
     * Handle SendGrid inbound parse webhook.
     * Documentation: https://docs.sendgrid.com/for-developers/parsing-email/setting-up-the-inbound-parse-webhook
     */
    public function sendgrid(Request $request): JsonResponse
    {
        try {
            $data = $this->parseSendGridPayload($request);

            if (! $data) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            // Dispatch async job to process email
            ProcessIncomingEmailWebhookJob::dispatch($data);

            return response()->json(['status' => 'received', 'provider' => 'sendgrid'], 200);
        } catch (\Exception $e) {
            Log::error('SendGrid webhook error: '.$e->getMessage());

            return response()->json(['error' => 'Processing error'], 500);
        }
    }

    /**
     * Handle Mailgun webhook.
     * Documentation: https://documentation.mailgun.com/docs/mailgun/user-manual/routes/forward/
     */
    public function mailgun(Request $request): JsonResponse
    {
        try {
            // Verify Mailgun signature
            if (! $this->verifyMailgunSignature($request)) {
                Log::warning('Invalid Mailgun signature');

                return response()->json(['error' => 'Invalid signature'], 403);
            }

            $data = $this->parseMailgunPayload($request);

            if (! $data) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            // Dispatch async job to process email
            ProcessIncomingEmailWebhookJob::dispatch($data);

            return response()->json(['status' => 'received', 'provider' => 'mailgun'], 200);
        } catch (\Exception $e) {
            Log::error('Mailgun webhook error: '.$e->getMessage());

            return response()->json(['error' => 'Processing error'], 500);
        }
    }

    /**
     * Parse SendGrid webhook payload.
     */
    protected function parseSendGridPayload(Request $request): ?array
    {
        if (! $request->has('email')) {
            return null;
        }

        return [
            'provider' => 'sendgrid',
            'message_id' => $request->input('Message-ID'),
            'in_reply_to' => $request->input('In-Reply-To'),
            'references' => $request->input('References'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'cc' => $request->input('cc'),
            'bcc' => $request->input('bcc'),
            'subject' => $request->input('subject'),
            'body_text' => $request->input('text'),
            'body_html' => $request->input('html'),
            'headers' => $this->parseSendGridHeaders($request),
            'attachments' => $this->parseSendGridAttachments($request),
        ];
    }

    /**
     * Parse Mailgun webhook payload.
     */
    protected function parseMailgunPayload(Request $request): ?array
    {
        if (! $request->has('sender')) {
            return null;
        }

        return [
            'provider' => 'mailgun',
            'message_id' => $request->input('Message-Id'),
            'in_reply_to' => $request->input('In-Reply-To'),
            'references' => $request->input('References'),
            'from' => $request->input('sender'),
            'to' => $request->input('recipient'),
            'cc' => $request->input('Cc'),
            'bcc' => $request->input('Bcc'),
            'subject' => $request->input('subject'),
            'body_text' => $request->input('body-plain'),
            'body_html' => $request->input('body-html'),
            'headers' => $this->parseMailgunHeaders($request),
            'attachments' => $this->parseMailgunAttachments($request),
        ];
    }

    /**
     * Extract headers from SendGrid payload.
     */
    protected function parseSendGridHeaders(Request $request): array
    {
        return [
            'Message-ID' => $request->input('Message-ID'),
            'In-Reply-To' => $request->input('In-Reply-To'),
            'References' => $request->input('References'),
            'Subject' => $request->input('subject'),
            'Date' => $request->input('date'),
            'From' => $request->input('from'),
            'To' => $request->input('to'),
        ];
    }

    /**
     * Extract headers from Mailgun payload.
     */
    protected function parseMailgunHeaders(Request $request): array
    {
        return [
            'Message-ID' => $request->input('Message-Id'),
            'In-Reply-To' => $request->input('In-Reply-To'),
            'References' => $request->input('References'),
            'Subject' => $request->input('subject'),
            'Date' => $request->input('Date'),
            'From' => $request->input('sender'),
            'To' => $request->input('recipient'),
        ];
    }

    /**
     * Extract attachments from SendGrid payload.
     */
    protected function parseSendGridAttachments(Request $request): array
    {
        $attachments = [];

        // SendGrid provides file uploads for attachments
        foreach ($request->file() as $field => $files) {
            // Skip non-attachment fields
            if (! str_starts_with($field, 'attachment')) {
                continue;
            }

            if (! is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if ($file) {
                    $attachments[] = [
                        'filename' => $file->getClientOriginalName(),
                        'mime' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'content' => file_get_contents($file->getPathname()),
                    ];
                }
            }
        }

        return $attachments;
    }

    /**
     * Extract attachments from Mailgun payload.
     */
    protected function parseMailgunAttachments(Request $request): array
    {
        $attachments = [];

        // Mailgun provides file uploads for attachments
        foreach ($request->file() as $field => $files) {
            // Skip non-attachment fields
            if (! str_starts_with($field, 'attachment')) {
                continue;
            }

            if (! is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if ($file) {
                    $attachments[] = [
                        'filename' => $file->getClientOriginalName(),
                        'mime' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'content' => file_get_contents($file->getPathname()),
                    ];
                }
            }
        }

        return $attachments;
    }

    /**
     * Verify Mailgun signature for webhook authenticity.
     * Prevents processing of spoofed webhooks.
     */
    protected function verifyMailgunSignature(Request $request): bool
    {
        $signingKey = config('helpdesk.email.mailgun.signing_key');

        if (! $signingKey) {
            Log::warning('Mailgun signing key not configured');

            return false;
        }

        $timestamp = $request->input('timestamp');
        $token = $request->input('token');
        $signature = $request->input('signature');

        if (! $timestamp || ! $token || ! $signature) {
            return false;
        }

        // Prevent replay attacks (signature is valid for 15 minutes)
        if (abs(time() - (int) $timestamp) > 900) {
            Log::warning('Mailgun webhook timestamp too old');

            return false;
        }

        // Compute signature
        $data = "{$timestamp}{$token}";
        $computedSignature = hash_hmac('sha256', $data, $signingKey);

        return hash_equals($computedSignature, $signature);
    }
}
