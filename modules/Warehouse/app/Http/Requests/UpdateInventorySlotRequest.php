<?php

namespace Modules\Warehouse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventorySlotRequest extends FormRequest
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
            'location_id' => 'required|integer|exists:warehouse_locations,id',
            'product_id' => 'nullable|integer',
            'face' => 'required|in:left,right,front,back',
            'level' => 'required|integer|min:1',
            'section' => 'required|integer|min:1',
            'barcode' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:0',
            'max_quantity' => 'nullable|integer|min:1|gt:quantity',
            'weight_current' => 'required|numeric|min:0',
            'weight_max' => 'nullable|numeric|min:0.01|gt:weight_current',
            'is_occupied' => 'boolean',
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
            'location_id.required' => 'La ubicación es obligatoria.',
            'location_id.integer' => 'La ubicación debe ser un número.',
            'location_id.exists' => 'La ubicación seleccionada no existe.',

            'product_id.integer' => 'El producto debe ser un número.',

            'face.required' => 'La cara de la estantería es obligatoria.',
            'face.in' => 'La cara debe ser: frente, atrás, izquierda o derecha.',

            'level.required' => 'El nivel es obligatorio.',
            'level.integer' => 'El nivel debe ser un número entero.',
            'level.min' => 'El nivel debe ser mayor o igual a :min.',

            'section.required' => 'La sección es obligatoria.',
            'section.integer' => 'La sección debe ser un número entero.',
            'section.min' => 'La sección debe ser mayor o igual a :min.',

            'barcode.string' => 'El código de barras debe ser texto.',
            'barcode.max' => 'El código de barras no puede exceder :max caracteres.',

            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'La cantidad no puede ser negativa.',

            'max_quantity.integer' => 'La cantidad máxima debe ser un número entero.',
            'max_quantity.min' => 'La cantidad máxima debe ser mayor a :min.',
            'max_quantity.gt' => 'La cantidad máxima debe ser mayor que la cantidad actual.',

            'weight_current.required' => 'El peso actual es obligatorio.',
            'weight_current.numeric' => 'El peso actual debe ser un número.',
            'weight_current.min' => 'El peso actual no puede ser negativo.',

            'weight_max.numeric' => 'El peso máximo debe ser un número.',
            'weight_max.min' => 'El peso máximo debe ser mayor a :min.',
            'weight_max.gt' => 'El peso máximo debe ser mayor que el peso actual.',

            'is_occupied.boolean' => 'El estado de ocupación debe ser verdadero o falso.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('barcode')) {
            $this->merge([
                'barcode' => trim($this->barcode),
            ]);
        }

        // Set default weight_current to 0 if not provided
        if (!$this->has('weight_current')) {
            $this->merge(['weight_current' => 0]);
        }

        // Set default is_occupied to false if not provided
        if (!$this->has('is_occupied')) {
            $this->merge(['is_occupied' => false]);
        }
    }
}
