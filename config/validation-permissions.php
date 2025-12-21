<?php

/**
 * Validation Permissions Configuration
 *
 * Defines which actions are allowed for each validation stage.
 * Used by Document validation workflow to enforce stage-specific permissions.
 *
 * Stages:
 * - documentacion (1st stage): Initial document review
 * - licencias (2nd stage): License/weapon validation (if applicable)
 * - contabilidad (3rd stage): Financial/accounting validation (if applicable)
 */

use App\Enums\Document\ValidationAction;

return [
    // =========================================================================
    // STAGE: DOCUMENTACIÓN (Stage 1)
    // Purpose: Verify that all required documents are submitted correctly
    // =========================================================================
    'documentacion' => [
        'order' => 1,
        'label' => 'Documentación',
        'description' => 'Verificación de documentos requeridos',
        'allowed_actions' => [
            ValidationAction::APPROVE->value,                 // ✅ Validate docs and move to next
            ValidationAction::REJECT->value,                  // ❌ Reject if docs incomplete
            ValidationAction::SEND_APPROVAL_EMAIL->value,     // 📧 Send approval email to customer
            ValidationAction::ADD_COMMENT->value,             // 💬 Add internal comments
            ValidationAction::REQUEST_ADDITIONAL_DOCS->value, // 📎 Request more docs if needed
        ],
        'restricted_actions' => [
            ValidationAction::MOVE_TO_NEXT_STAGE->value,      // Cannot skip full approval
            ValidationAction::ACCESS_ADDITIONAL_FILES->value, // Not applicable at this stage
        ],
    ],

    // =========================================================================
    // STAGE: LICENCIAS (Stage 2)
    // Purpose: Validate licenses and weapon-related documents (conditional stage)
    // =========================================================================
    'licencias' => [
        'order' => 2,
        'label' => 'Licencias',
        'description' => 'Validación de licencias y documentos de armas',
        'allowed_actions' => [
            ValidationAction::APPROVE->value,                 // ✅ Confirm docs ok, move to next
            ValidationAction::ADD_COMMENT->value,             // 💬 Add internal comments
            ValidationAction::REQUEST_ADDITIONAL_DOCS->value, // 📎 Request more docs if needed
        ],
        'restricted_actions' => [
            ValidationAction::REJECT->value,                  // ❌ Cannot reject directly
            ValidationAction::SEND_APPROVAL_EMAIL->value,     // 📧 No approval emails at this stage
            ValidationAction::MOVE_TO_NEXT_STAGE->value,      // Must fully approve to advance
            ValidationAction::ACCESS_ADDITIONAL_FILES->value, // Not applicable at this stage
        ],
        'notes' => 'Intermediate stage: Can only confirm and advance, not reject',
    ],

    // =========================================================================
    // STAGE: CONTABILIDAD (Stage 3)
    // Purpose: Final financial/accounting validation and approval
    // =========================================================================
    'contabilidad' => [
        'order' => 3,
        'label' => 'Contabilidad',
        'description' => 'Validación final de contabilidad',
        'allowed_actions' => [
            ValidationAction::APPROVE->value,                 // ✅ Final approval
            ValidationAction::REJECT->value,                  // ❌ Final rejection
            ValidationAction::SEND_APPROVAL_EMAIL->value,     // 📧 Send final approval email to customer
            ValidationAction::ACCESS_ADDITIONAL_FILES->value, // 📎 Access additional attachments
            ValidationAction::ADD_COMMENT->value,             // 💬 Add internal comments
            ValidationAction::REQUEST_ADDITIONAL_DOCS->value, // 📎 Request more docs if needed
        ],
        'restricted_actions' => [
            ValidationAction::MOVE_TO_NEXT_STAGE->value,      // No next stage after this
        ],
        'notes' => 'Final stage: Full permissions for complete validation and approval',
    ],

    // =========================================================================
    // DEFAULT ACTIONS (available on ALL stages)
    // =========================================================================
    'default_allowed_actions' => [
        ValidationAction::ADD_COMMENT->value,             // 💬 Comments always allowed
        ValidationAction::REQUEST_ADDITIONAL_DOCS->value, // 📎 Can request docs at any stage
    ],

    // =========================================================================
    // RULES & CONSTRAINTS
    // =========================================================================
    'rules' => [
        // Only first and last stages can send approval emails
        'send_approval_email_only_first_last' => true,

        // Only first and last stages can reject
        'reject_only_first_last' => true,

        // Intermediate stages can only advance, not approve completely
        'intermediate_stages_advance_only' => true,

        // Additional files only accessible on final stage
        'additional_files_final_stage_only' => true,
    ],
];
