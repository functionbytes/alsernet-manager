<?php

namespace Modules\Documents\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Documents\Entities\Document;
use Modules\Documents\Services\DocumentActionService;
use Modules\Documents\Services\DocumentEmailService;

/**
 * API Controller Genérico para Validación de Documentos
 *
 * Detecta automáticamente el perfil del usuario autenticado y ejecuta
 * las acciones correspondientes. NO necesita rutas duplicadas por perfil.
 */
class DocumentValidationController extends Controller
{
    protected DocumentActionService $actionService;
    protected DocumentEmailService $emailService;

    public function __construct(
        DocumentActionService $actionService,
        DocumentEmailService $emailService
    ) {
        $this->actionService = $actionService;
        $this->emailService = $emailService;
    }

    /**
     * Detecta el perfil del usuario autenticado
     *
     * @return string 'administrative'|'weapons'|'accounting'|'manager'
     */
    protected function getUserProfile(): string
    {
        $user = auth()->user();

        // Detectar perfil basado en roles
        if ($user->hasRole('super-admin') || $user->hasRole('manager')) {
            return 'manager';
        }

        if ($user->hasRole('administrative')) {
            return 'administrative';
        }

        if ($user->hasRole('weapons')) {
            return 'weapons';
        }

        if ($user->hasRole('accounting')) {
            return 'accounting';
        }

        // Fallback
        return 'administrative';
    }

    /**
     * Aprobar etapa actual del documento
     */
    public function approveStage(Request $request, string $uid): JsonResponse
    {
        $document = Document::where('uid', $uid)->firstOrFail();
        $profile = $this->getUserProfile();

        // Validar permisos según perfil
        $this->authorize('approve', $document);

        $validated = $request->validate([
            'comments' => 'nullable|string|max:1000',
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        try {
            $result = $this->actionService->approveStage(
                $document,
                $validated['comments'] ?? null,
                $validated['assigned_user_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Documento aprobado exitosamente',
                'document' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Rechazar documento
     */
    public function rejectStage(Request $request, string $uid): JsonResponse
    {
        $document = Document::where('uid', $uid)->firstOrFail();
        $profile = $this->getUserProfile();

        // Validar permisos según perfil
        $this->authorize('reject', $document);

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ]);

        try {
            $result = $this->actionService->rejectStage(
                $document,
                $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Documento rechazado',
                'document' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Enviar email de aprobación
     */
    public function sendApproval(Request $request, string $uid): JsonResponse
    {
        $document = Document::where('uid', $uid)->firstOrFail();
        $profile = $this->getUserProfile();

        $this->authorize('approve', $document);

        try {
            $result = $this->emailService->sendApprovalEmail($document);

            return response()->json([
                'success' => true,
                'message' => 'Email de aprobación enviado',
                'recipient' => $result['recipient'] ?? $document->customer->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Enviar email de rechazo
     */
    public function sendRejection(Request $request, string $uid): JsonResponse
    {
        $document = Document::where('uid', $uid)->firstOrFail();
        $profile = $this->getUserProfile();

        $this->authorize('reject', $document);

        $validated = $request->validate([
            'reason' => 'required|string|min:10',
            'rejected_docs' => 'nullable|array',
        ]);

        try {
            $result = $this->emailService->sendRejectionEmail(
                $document,
                $validated['reason'],
                $validated['rejected_docs'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Email de rechazo enviado',
                'recipient' => $result['recipient'] ?? $document->customer->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Enviar email personalizado
     */
    public function sendCustomEmail(Request $request, string $uid): JsonResponse
    {
        $document = Document::where('uid', $uid)->firstOrFail();
        $profile = $this->getUserProfile();

        $this->authorize('update', $document);

        $validated = $request->validate([
            'subject' => 'required|string|max:200',
            'message' => 'required|string|min:10',
            'template_id' => 'nullable|exists:email_templates,id',
        ]);

        try {
            $result = $this->emailService->sendCustomEmail(
                $document,
                $validated['subject'],
                $validated['message'],
                $validated['template_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Email enviado',
                'recipient' => $result['recipient'] ?? $document->customer->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Enviar recordatorio
     */
    public function sendReminder(Request $request, string $uid): JsonResponse
    {
        $document = Document::where('uid', $uid)->firstOrFail();
        $profile = $this->getUserProfile();

        $this->authorize('update', $document);

        try {
            $result = $this->emailService->sendReminderEmail($document);

            return response()->json([
                'success' => true,
                'message' => 'Recordatorio enviado',
                'recipient' => $result['recipient'] ?? $document->customer->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Solicitar documentos iniciales
     */
    public function requestInitialDocuments(Request $request, string $uid): JsonResponse
    {
        $document = Document::where('uid', $uid)->firstOrFail();
        $profile = $this->getUserProfile();

        $this->authorize('update', $document);

        $validated = $request->validate([
            'template_id' => 'nullable|exists:email_templates,id',
            'custom_message' => 'nullable|string',
        ]);

        try {
            $result = $this->emailService->sendInitialDocumentRequest(
                $document,
                $validated['template_id'] ?? null,
                $validated['custom_message'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de documentos enviada',
                'recipient' => $result['recipient'] ?? $document->customer->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Solicitar documentos faltantes
     */
    public function requestMissingDocuments(Request $request, string $uid): JsonResponse
    {
        $document = Document::where('uid', $uid)->firstOrFail();
        $profile = $this->getUserProfile();

        $this->authorize('update', $document);

        $validated = $request->validate([
            'missing_docs' => 'required|array|min:1',
            'custom_message' => 'nullable|string',
        ]);

        try {
            $result = $this->emailService->sendMissingDocumentsRequest(
                $document,
                $validated['missing_docs'],
                $validated['custom_message'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de documentos faltantes enviada',
                'recipient' => $result['recipient'] ?? $document->customer->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Agregar nota al documento
     */
    public function addNote(Request $request, string $uid): JsonResponse
    {
        $document = Document::where('uid', $uid)->firstOrFail();
        $profile = $this->getUserProfile();

        $this->authorize('update', $document);

        $validated = $request->validate([
            'content' => 'required|string|min:3',
            'is_internal' => 'boolean',
        ]);

        try {
            $note = $document->notes()->create([
                'content' => $validated['content'],
                'is_internal' => $validated['is_internal'] ?? true,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nota agregada',
                'note' => $note,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener historial de acciones
     */
    public function getActionHistory(string $uid): JsonResponse
    {
        $document = Document::where('uid', $uid)->firstOrFail();
        $profile = $this->getUserProfile();

        $this->authorize('view', $document);

        $history = $document->actionHistory()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * Obtener historial de emails
     */
    public function getEmailHistory(string $uid): JsonResponse
    {
        $document = Document::where('uid', $uid)->firstOrFail();
        $profile = $this->getUserProfile();

        $this->authorize('view', $document);

        $emails = $document->emailHistory()
            ->orderBy('sent_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'emails' => $emails,
        ]);
    }

    /**
     * Obtener timeline de estados
     */
    public function getStatusTimeline(string $uid): JsonResponse
    {
        $document = Document::where('uid', $uid)->firstOrFail();
        $profile = $this->getUserProfile();

        $this->authorize('view', $document);

        $timeline = $document->statusHistory()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'timeline' => $timeline,
        ]);
    }

    /**
     * Obtener información del siguiente grupo de validación
     */
    public function getNextStageInfo(string $uid): JsonResponse
    {
        $document = Document::where('uid', $uid)->firstOrFail();
        $profile = $this->getUserProfile();

        $this->authorize('view', $document);

        if ($document->current_stage >= $document->total_stages) {
            return response()->json([
                'success' => false,
                'message' => 'El documento está en la última etapa',
            ], 422);
        }

        $nextStage = $document->current_stage + 1;
        $stages = $document->getValidationWorkflowStages();
        $nextGroupKey = $stages[$nextStage - 1] ?? null;

        if (!$nextGroupKey) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar el siguiente grupo',
            ], 422);
        }

        $nextGroup = \App\Models\Validation\ValidatorGroup::findByKey($nextGroupKey);
        $users = $nextGroup ? $nextGroup->users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->full_name,
                'is_primary' => $user->pivot->priority === 'primary',
            ];
        }) : collect();

        return response()->json([
            'success' => true,
            'next_stage' => $nextStage,
            'next_group_key' => $nextGroupKey,
            'next_group_label' => ucfirst($nextGroupKey),
            'users' => $users,
        ]);
    }

    /**
     * Obtener plantilla de email personalizado
     */
    public function getCustomEmailTemplate(): JsonResponse
    {
        $template = \App\Models\Mail\MailTemplate::where('key', 'custom_document')->first();

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'No hay plantilla configurada',
            ]);
        }

        return response()->json([
            'success' => true,
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'subject' => $template->subject,
            ],
        ]);
    }
}
