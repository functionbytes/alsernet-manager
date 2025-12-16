<?php

namespace App\Services\Documents;

use App\Models\Document\Document;
use Illuminate\Support\Facades\Log;

class DocumentMailService
{
    /**
     * Envía notificación inicial pidiendo carga de documentación
     * Usa plantilla de BD
     */
    public static function sendUploadNotification(Document $document): bool
    {
        try {
            $email = $document->customer_email ?? $document->customer?->email;

            if (! $email) {
                Log::warning('No email found for document notification', [
                    'document_uid' => $document->uid,
                    'order_id' => $document->order_id,
                ]);

                return false;
            }

            // Usar plantilla de BD
            $result = DocumentEmailTemplateService::sendInitialRequest($document);

            if ($result) {
                Log::info('Document upload notification sent successfully', [
                    'document_uid' => $document->uid,
                    'recipient' => $email,
                    'order_id' => $document->order_id,
                ]);
            }

            return $result;
        } catch (\Throwable $exception) {
            Log::error('Failed to send document upload notification', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Envía recordatorio para cargar documentación
     * Usa plantilla de BD
     */
    public static function sendReminder(Document $document): bool
    {
        try {
            // Recargar documento para verificar si ya fue cargado
            $document = $document->fresh();

            if (! $document) {
                Log::warning('Document not found for reminder');

                return false;
            }

            // Si no hay documentos faltantes, el documento está completo - no enviar recordatorio
            if (empty($document->getMissingDocuments())) {
                Log::info('Document is complete, skipping reminder', [
                    'document_uid' => $document->uid,
                ]);

                return false;
            }

            $email = $document->customer_email ?? $document->customer?->email;

            if (! $email) {
                Log::warning('No email found for document reminder', [
                    'document_uid' => $document->uid,
                    'order_id' => $document->order_id,
                ]);

                return false;
            }

            // Usar plantilla de BD
            $result = DocumentEmailTemplateService::sendReminder($document);

            if ($result) {
                Log::info('Document reminder sent successfully', [
                    'document_uid' => $document->uid,
                    'recipient' => $email,
                    'order_id' => $document->order_id,
                ]);
            }

            return $result;
        } catch (\Throwable $exception) {
            Log::error('Failed to send document reminder', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Envía confirmación cuando el documento es cargado
     * Usa plantilla de BD
     */
    public static function sendUploadedConfirmation(Document $document): bool
    {
        try {
            $email = $document->customer_email ?? $document->customer?->email;

            if (! $email) {
                Log::warning('No email found for document confirmation', [
                    'document_uid' => $document->uid,
                    'order_id' => $document->order_id,
                ]);

                return false;
            }

            // Usar plantilla de BD
            $result = DocumentEmailTemplateService::sendUploadConfirmation($document);

            if ($result) {
                Log::info('Document uploaded confirmation sent successfully', [
                    'document_uid' => $document->uid,
                    'recipient' => $email,
                    'order_id' => $document->order_id,
                ]);
            }

            return $result;
        } catch (\Throwable $exception) {
            Log::error('Failed to send document uploaded confirmation', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Envía todos los emails relacionados con un documento de forma batch
     * Útil para reintentos o procesamiento en lote
     */
    public static function sendAll(Document $document, array $types = ['notification', 'confirmation']): array
    {
        $results = [
            'notification' => false,
            'confirmation' => false,
            'reminder' => false,
        ];

        try {
            if (in_array('notification', $types)) {
                $results['notification'] = self::sendUploadNotification($document);
            }

            if (in_array('confirmation', $types)) {
                $results['confirmation'] = self::sendUploadedConfirmation($document);
            }

            if (in_array('reminder', $types)) {
                $results['reminder'] = self::sendReminder($document);
            }

            return $results;
        } catch (\Throwable $exception) {
            Log::error('Batch email sending failed', [
                'document_uid' => $document->uid ?? null,
                'exception' => $exception->getMessage(),
            ]);

            return $results;
        }
    }
}
