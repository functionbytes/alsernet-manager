<?php

namespace Modules\Document\Enums;

/**
 * ValidationAction Enum
 *
 * Define available actions for document validation by stage.
 * Each action can be allowed or disallowed depending on the validation stage.
 */
enum ValidationAction: string
{
    /**
     * Approve/validate the current stage and move to next stage
     * or complete validation if it's the last stage
     */
    case APPROVE = 'approve';

    /**
     * Reject the document and send it back for re-submission
     * Available only on first and last stages
     */
    case REJECT = 'reject';

    /**
     * Send approval email to customer
     * Available only on first and last stages
     */
    case SEND_APPROVAL_EMAIL = 'send_approval_email';

    /**
     * Add comments/notes without approving
     * Available on all stages
     */
    case ADD_COMMENT = 'add_comment';

    /**
     * Request additional documents
     * Available on all stages
     */
    case REQUEST_ADDITIONAL_DOCS = 'request_additional_docs';

    /**
     * Move to next stage without full approval
     * Available only on intermediate stages (not first or last)
     */
    case MOVE_TO_NEXT_STAGE = 'move_to_next_stage';

    /**
     * Access additional attachments/files
     * Available only on final stage (contabilidad)
     */
    case ACCESS_ADDITIONAL_FILES = 'access_additional_files';

    public function label(): string
    {
        return match ($this) {
            self::APPROVE => 'Aprobar',
            self::REJECT => 'Rechazar',
            self::SEND_APPROVAL_EMAIL => 'Enviar correo de aprobación',
            self::ADD_COMMENT => 'Agregar comentario',
            self::REQUEST_ADDITIONAL_DOCS => 'Solicitar documentos adicionales',
            self::MOVE_TO_NEXT_STAGE => 'Pasar a siguiente etapa',
            self::ACCESS_ADDITIONAL_FILES => 'Acceder archivos adicionales',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::APPROVE => 'Valida la etapa actual y avanza a la siguiente (o completa si es la última)',
            self::REJECT => 'Rechaza el documento completo y lo devuelve para revisión',
            self::SEND_APPROVAL_EMAIL => 'Envía correo de aprobación al cliente',
            self::ADD_COMMENT => 'Agrega comentarios o notas internas',
            self::REQUEST_ADDITIONAL_DOCS => 'Solicita documentos adicionales al cliente',
            self::MOVE_TO_NEXT_STAGE => 'Avanza a la siguiente etapa sin aprobar completamente',
            self::ACCESS_ADDITIONAL_FILES => 'Accede a archivos y adjuntos adicionales',
        };
    }
}
