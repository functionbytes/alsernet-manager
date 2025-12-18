<?php

namespace App\Http\Requests\Helpdesks;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manager.helpdesk.tickets.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Customer information
            'customer_id' => 'required|integer|exists:helpdesk.helpdesk_customers,id',

            // Ticket details
            'category_id' => 'required|integer|exists:helpdesk.helpdesk_ticket_categories,id',
            'subject' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:10|max:10000',
            'priority' => 'required|in:low,normal,high,urgent',

            // Optional assignment
            'assignee_id' => 'nullable|integer|exists:mysql.users,id',
            'group_id' => 'nullable|integer|exists:helpdesk.helpdesk_groups,id',
            'status_id' => 'nullable|integer|exists:helpdesk.helpdesk_ticket_statuses,id',

            // SLA policy (optional, will use category default if not provided)
            'sla_policy_id' => 'nullable|integer|exists:helpdesk.helpdesk_ticket_sla_policies,id',

            // Custom fields (dynamic based on category)
            'custom_fields' => 'nullable|array',
            'custom_fields.*' => 'nullable',

            // Tags
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',

            // Attachments
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip,rar',

            // Source (optional, defaults to 'manager')
            'source' => 'sometimes|in:manager,widget,portal,api,email',
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
            'customer_id.required' => 'Debe seleccionar un cliente.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',

            'category_id.required' => 'Debe seleccionar una categoría.',
            'category_id.exists' => 'La categoría seleccionada no existe.',

            'subject.required' => 'El asunto es obligatorio.',
            'subject.min' => 'El asunto debe tener al menos :min caracteres.',
            'subject.max' => 'El asunto no puede exceder :max caracteres.',

            'description.required' => 'La descripción es obligatoria.',
            'description.min' => 'La descripción debe tener al menos :min caracteres.',
            'description.max' => 'La descripción no puede exceder :max caracteres.',

            'priority.required' => 'Debe seleccionar una prioridad.',
            'priority.in' => 'La prioridad seleccionada no es válida.',

            'assignee_id.exists' => 'El agente seleccionado no existe.',
            'group_id.exists' => 'El grupo seleccionado no existe.',
            'status_id.exists' => 'El estado seleccionado no existe.',
            'sla_policy_id.exists' => 'La política SLA seleccionada no existe.',

            'custom_fields.array' => 'Los campos personalizados deben ser un arreglo válido.',

            'tags.array' => 'Las etiquetas deben ser un arreglo válido.',
            'tags.*.max' => 'Cada etiqueta no puede exceder :max caracteres.',

            'attachments.array' => 'Los adjuntos deben ser un arreglo válido.',
            'attachments.*.file' => 'Cada adjunto debe ser un archivo válido.',
            'attachments.*.max' => 'Cada archivo no puede exceder 10 MB.',
            'attachments.*.mimes' => 'Solo se permiten archivos PDF, DOC, DOCX, XLS, XLSX, imágenes y archivos ZIP/RAR.',

            'source.in' => 'La fuente seleccionada no es válida.',
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

        // Set default source if not provided
        if (! $this->has('source')) {
            $this->merge(['source' => 'manager']);
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
}
