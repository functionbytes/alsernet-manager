<?php

namespace Modules\Warehouse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferInventoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('warehouse.manage') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from_slot_id' => 'required|integer|exists:warehouse_inventory_slots,id',
            'to_slot_id' => 'required|integer|exists:warehouse_inventory_slots,id|different:from_slot_id',
            'quantity' => 'required|integer|min:1',
            'from_warehouse_id' => 'required|integer|exists:warehouses,id',
            'to_warehouse_id' => 'required|integer|exists:warehouses,id',
            'reason' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
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
            'from_slot_id.required' => 'La ubicación origen es obligatoria.',
            'from_slot_id.integer' => 'La ubicación origen debe ser un número.',
            'from_slot_id.exists' => 'La ubicación origen seleccionada no existe.',

            'to_slot_id.required' => 'La ubicación destino es obligatoria.',
            'to_slot_id.integer' => 'La ubicación destino debe ser un número.',
            'to_slot_id.exists' => 'La ubicación destino seleccionada no existe.',
            'to_slot_id.different' => 'La ubicación destino debe ser diferente de la ubicación origen.',

            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'La cantidad debe ser mayor a :min.',

            'from_warehouse_id.required' => 'El almacén origen es obligatorio.',
            'from_warehouse_id.integer' => 'El almacén origen debe ser un número.',
            'from_warehouse_id.exists' => 'El almacén origen seleccionado no existe.',

            'to_warehouse_id.required' => 'El almacén destino es obligatorio.',
            'to_warehouse_id.integer' => 'El almacén destino debe ser un número.',
            'to_warehouse_id.exists' => 'El almacén destino seleccionado no existe.',

            'reason.string' => 'El motivo debe ser texto.',
            'reason.max' => 'El motivo no puede exceder :max caracteres.',

            'notes.string' => 'Las notas deben ser texto.',
            'notes.max' => 'Las notas no pueden exceder :max caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('reason')) {
            $this->merge([
                'reason' => trim($this->reason),
            ]);
        }

        if ($this->has('notes')) {
            $this->merge([
                'notes' => trim($this->notes),
            ]);
        }
    }
}
