<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Email\SendEndpointEmailJob;
use App\Models\Mail\MailEndpoint;
use App\Models\Mail\MailEndpointLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailEndpointController extends Controller
{
    /**
     * Send email via endpoint
     * POST /api/email-endpoints/{slug}/send
     */
    public function send(Request $request, string $slug): JsonResponse
    {
        // Find endpoint by slug
        $endpoint = MailEndpoint::where('slug', $slug)->first();

        if (! $endpoint) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint not found',
            ], 404);
        }

        if (! $endpoint->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint is inactive',
            ], 403);
        }

        // Validate API token if provided in header
        $providedToken = $request->header('X-API-Token');
        if ($providedToken && $providedToken !== $endpoint->api_token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API token',
            ], 401);
        }

        // Validate required variables
        if ($endpoint->required_variables) {
            $payload = $request->json()->all();
            $missingVars = [];

            foreach ($endpoint->required_variables as $var) {
                if (! data_get($payload, $var)) {
                    $missingVars[] = $var;
                }
            }

            if (! empty($missingVars)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required variables: '.implode(', ', $missingVars),
                    'missing_variables' => $missingVars,
                ], 422);
            }
        }

        // Create endpoint log
        $log = MailEndpointLog::create([
            'email_endpoint_id' => $endpoint->id,
            'payload' => $request->json()->all(),
            'status' => 'pending',
        ]);

        // Dispatch job to send email
        SendEndpointEmailJob::dispatch($log);

        // Update request count
        $endpoint->increment('requests_count');

        return response()->json([
            'success' => true,
            'message' => 'Email queued for sending',
            'log_id' => $log->id,
            'endpoint' => $endpoint->slug,
        ], 202);
    }

    /**
     * Get endpoint info
     * GET /api/email-endpoints/{slug}/info
     */
    public function info(string $slug): JsonResponse
    {
        $endpoint = MailEndpoint::where('slug', $slug)
            ->with('template', 'language')
            ->first();

        if (! $endpoint) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'slug' => $endpoint->slug,
                'name' => $endpoint->name,
                'type' => $endpoint->type,
                'source' => $endpoint->source,
                'expected_variables' => $endpoint->expected_variables ?? [],
                'required_variables' => $endpoint->required_variables ?? [],
                'template' => $endpoint->template ? [
                    'subject' => $endpoint->template->subject,
                    'preview' => substr($endpoint->template->content, 0, 200),
                ] : null,
                'is_active' => $endpoint->is_active,
            ],
        ]);
    }

    /**
     * Get endpoint status and logs
     * GET /api/email-endpoints/{slug}/status
     */
    public function status(string $slug): JsonResponse
    {
        $endpoint = MailEndpoint::where('slug', $slug)->first();

        if (! $endpoint) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint not found',
            ], 404);
        }

        $successCount = $endpoint->successLogs()->count();
        $failedCount = $endpoint->failedLogs()->count();
        $totalCount = $endpoint->requests_count;

        return response()->json([
            'success' => true,
            'data' => [
                'slug' => $endpoint->slug,
                'is_active' => $endpoint->is_active,
                'total_requests' => $totalCount,
                'successful_emails' => $successCount,
                'failed_emails' => $failedCount,
                'success_rate' => $totalCount > 0 ? round(($successCount / $totalCount) * 100, 2) : 0,
                'last_request_at' => $endpoint->last_request_at?->toIso8601String(),
            ],
        ]);
    }
}
