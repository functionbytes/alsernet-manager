<?php

namespace Modules\Document\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Document\Entities\Document;

/**
 * Trait for controllers that send document emails
 *
 * Consolidates common logic for:
 * - Capturing adminId before session release
 * - Session management
 * - Document retrieval and authorization
 * - Error handling for email sending
 */
trait SendsDocumentEmails
{
    /**
     * Send a document email with standardized flow
     *
     * @param  Request  $request  The HTTP request
     * @param  string  $uid  Document UID
     * @param  callable  $sender  Callback that sends the email. Receives ($document, $adminId, $request)
     * @param  string|null  $authPolicy  Authorization policy method (default: null = no authorization)
     */
    protected function sendEmail(
        Request $request,
        string $uid,
        callable $sender,
        ?string $authPolicy = null
    ): JsonResponse {
        // 1. Capture adminId BEFORE releasing session
        $adminId = auth()->id();

        // 2. Release session to prevent blocking
        $this->releaseSession($request);

        // 3. Find document
        $document = Document::where('uid', $uid)->firstOrFail();

        // 4. Authorize if policy specified
        //if ($authPolicy) {
         //   $this->authorize($authPolicy, $document);
        //}

        // 5. Call sender callback and handle response
        $sent = $sender($document, $adminId, $request);

        // 6. Return standardized response
        if (! $sent) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar el email. Verifica que el documento tenga email del cliente o intenta de nuevo en unos segundos.',
            ], 429);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email enviado correctamente',
            'recipient' => $document->customer_email,
        ]);
    }

    /**
     * Release session to prevent blocking concurrent requests
     *
     * @param  Request  $request  The HTTP request
     */
    protected function releaseSession(Request $request): void
    {
        if ($request->hasSession()) {
            session()->save();
            session()->migrate(false);
        }
    }

    /**
     * Validate request data with custom messages
     *
     * @param  Request  $request  The HTTP request
     * @param  array  $rules  Validation rules
     * @param  array  $messages  Custom error messages
     * @return array Validated data
     */
    protected function validateEmailRequest(Request $request, array $rules, array $messages = []): array
    {
        return $request->validate($rules, $messages);
    }
}
