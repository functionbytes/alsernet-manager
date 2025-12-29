<?php

namespace Modules\Warehouse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
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
            'floor_id' => 'required|integer|exists:warehouse_floors,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'style_id' => 'nullable|integer|exists:warehouse_location_styles,id',
            'code' => 'required|string|min:1|max:50|unique:warehouse_locations,code',
            'position_x' => 'nullable|numeric|min:0',
            'position_y' => 'nullable|numeric|min:0',
            'total_levels' => 'required|integer|min:1|max:100',
            'available' => 'boolean',
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
            'floor_id.required' => 'El piso es obligatorio.',
            'floor_id.integer' => 'El piso debe ser un número.',
            'floor_id.exists' => 'El piso seleccionado no existe.',

            'warehouse_id.required' => 'El almacén es obligatorio.',
            'warehouse_id.integer' => 'El almacén debe ser un número.',
            'warehouse_id.exists' => 'El almacén seleccionado no existe.',

            'style_id.integer' => 'El estilo debe ser un número.',
            'style_id.exists' => 'El estilo seleccionado no existe.',

            'code.required' => 'El código de ubicación es obligatorio.',
            'code.string' => 'El código debe ser texto.',
            'code.min' => 'El código debe tener al menos :min caracteres.',
            'code.max' => 'El código no puede exceder :max caracteres.',
            'code.unique' => 'El código de ubicación ya existe.',

            'position_x.numeric' => 'La posición X debe ser un número.',
            'position_x.min' => 'La posición X no puede ser negativa.',

            'position_y.numeric' => 'La posición Y debe ser un número.',
            'position_y.min' => 'La posición Y no puede ser negativa.',

            'total_levels.required' => 'El número de niveles es obligatorio.',
            'total_levels.integer' => 'El número de niveles debe ser un número entero.',
            'total_levels.min' => 'Debe haber al menos :min nivel.',
            'total_levels.max' => 'No puede haber más de :max niveles.',

            'available.boolean' => 'El estado debe ser verdadero o falso.',

            'notes.string' => 'Las notas deben ser texto.',
            'notes.max' => 'Las notas no pueden exceder :max caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => trim($this->code),
            ]);
        }

        if ($this->has('notes')) {
            $this->merge([
                'notes' => trim($this->notes),
            ]);
        }

        // Set default available to true if not provided
        if (!$this->has('available')) {
            $this->merge(['available' => true]);
        }
    }
}
