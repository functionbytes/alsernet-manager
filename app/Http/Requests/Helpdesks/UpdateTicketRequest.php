<?php

namespace App\Http\Requests\Helpdesks;

use App\Models\Helpdesk\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        if (! $ticket) {
            return false;
        }

        // Check if user has update permission
        if (! $this->user()?->can('manager.helpdesk.tickets.update')) {
            return false;
        }

        // If user can only update their own tickets
        if ($this->user()->can('manager.helpdesk.tickets.update.own')) {
            return $ticket->assignee_id === $this->user()->id;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Ticket details
            'category_id' => 'sometimes|integer|exists:helpdesk.helpdesk_ticket_categories,id',
            'subject' => 'sometimes|string|min:5|max:255',
            'description' => 'sometimes|string|min:10|max:10000',
            'priority' => 'sometimes|in:low,normal,high,urgent',

            // Assignment
            'assignee_id' => 'nullable|integer|exists:mysql.users,id',
            'group_id' => 'nullable|integer|exists:helpdesk.helpdesk_groups,id',
            'status_id' => 'sometimes|integer|exists:helpdesk.helpdesk_ticket_statuses,id',

            // SLA policy
            'sla_policy_id' => 'nullable|integer|exists:helpdesk.helpdesk_ticket_sla_policies,id',

            // Custom fields
            'custom_fields' => 'sometimes|array',
            'custom_fields.*' => 'nullable',

            // Tags
            'tags' => 'sometimes|array',
            'tags.*' => 'string|max:50',

            // Archive status
            'is_archived' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'La categoría seleccionada no existe.',

            'subject.min' => 'El asunto debe tener al menos :min caracteres.',
            'subject.max' => 'El asunto no puede exceder :max caracteres.',

            'description.min' => 'La descripción debe tener al menos :min caracteres.',
            'description.max' => 'La descripción no puede exceder :max caracteres.',

            'priority.in' => 'La prioridad seleccionada no es válida.',

            'assignee_id.exists' => 'El agente seleccionado no existe.',
            'group_id.exists' => 'El grupo seleccionado no existe.',
            'status_id.exists' => 'El estado seleccionado no existe.',
            'sla_policy_id.exists' => 'La política SLA seleccionada no existe.',

            'custom_fields.array' => 'Los campos personalizados deben ser un arreglo válido.',

            'tags.array' => 'Las etiquetas deben ser un arreglo válido.',
            'tags.*.max' => 'Cada etiqueta no puede exceder :max caracteres.',

            'is_archived.boolean' => 'El estado de archivado debe ser verdadero o falso.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and normalize data
        if ($this->has('subject')) {
            $this->merge([
                'subject' => trim($this->subject),
            ]);
        }

        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->description),
            ]);
        }

        // Clean tags
        if ($this->has('tags') && is_array($this->tags)) {
            $this->merge([
                'tags' => array_filter(array_map('trim', $this->tags)),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateCategoryRequiredFields($validator);
            $this->validateStatusTransition($validator);
            $this->validateClosedTicketModification($validator);
        });
    }

    /**
     * Validate that required fields for the selected category are present
     */
    protected function validateCategoryRequiredFields($validator): void
    {
        if (! $this->has('category_id')) {
            return;
        }

        $category = \App\Models\Helpdesk\TicketCategory::find($this->category_id);

        if (! $category || ! $category->required_fields) {
            return;
        }

        $customFields = $this->custom_fields ?? [];

        foreach ($category->required_fields as $requiredField) {
            if (! isset($customFields[$requiredField]) || empty($customFields[$requiredField])) {
                $validator->errors()->add(
                    "custom_fields.{$requiredField}",
                    "El campo '{$requiredField}' es obligatorio para esta categoría."
                );
            }
        }
    }

    /**
     * Validate status transition
     */
    protected function validateStatusTransition($validator): void
    {
        if (! $this->has('status_id')) {
            return;
        }

        $ticket = $this->route('ticket');
        $newStatus = \App\Models\Helpdesk\TicketStatus::find($this->status_id);

        if (! $ticket || ! $newStatus) {
            return;
        }

        // Prevent reopening closed tickets without proper permission
        if ($ticket->isClosed() && $newStatus->is_open) {
            if (! $this->user()->can('manager.helpdesk.tickets.reopen')) {
                $validator->errors()->add(
                    'status_id',
                    'No tiene permisos para reabrir tickets cerrados.'
                );
            }
        }

        // Check if changing to a closed status
        if (! $ticket->isClosed() && ! $newStatus->is_open) {
            // Optionally validate that ticket is resolved before closing
            if (! $ticket->isResolved() && ! $this->user()->can('manager.helpdesk.tickets.close.unresolved')) {
                $validator->errors()->add(
                    'status_id',
                    'El ticket debe estar resuelto antes de cerrarlo.'
                );
            }
        }
    }

    /**
     * Validate modification of closed tickets
     */
    protected function validateClosedTicketModification($validator): void
    {
        $ticket = $this->route('ticket');

        if (! $ticket || ! $ticket->isClosed()) {
            return;
        }

        // Prevent modification of certain fields on closed tickets
        $restrictedFields = ['category_id', 'priority', 'description'];

        foreach ($restrictedFields as $field) {
            if ($this->has($field)) {
                $validator->errors()->add(
                    $field,
                    'No se puede modificar este campo en un ticket cerrado.'
                );
            }
        }
    }

    /**
     * Get only the fields that can be modified
     */
    public function getModifiableFields(): array
    {
        $ticket = $this->route('ticket');
        $user = $this->user();

        $baseFields = [
            'assignee_id', 'group_id', 'status_id', 'tags', 'is_archived',
        ];

        // If ticket is open, allow modification of more fields
        if ($ticket && ! $ticket->isClosed()) {
            $baseFields = array_merge($baseFields, [
                'category_id', 'subject', 'description', 'priority',
                'sla_policy_id', 'custom_fields',
            ]);
        }

        return array_intersect_key($this->validated(), array_flip($baseFields));
    }
}
