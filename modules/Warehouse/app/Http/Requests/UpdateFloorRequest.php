<?php

namespace Modules\Warehouse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFloorRequest extends FormRequest
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
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'name' => 'required|string|min:3|max:100',
            'description' => 'nullable|string|max:1000',
            'level' => 'required|integer|min:1|max:100',
            'available' => 'boolean',
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
            'warehouse_id.required' => 'El almacén es obligatorio.',
            'warehouse_id.integer' => 'El almacén debe ser un número.',
            'warehouse_id.exists' => 'El almacén seleccionado no existe.',

            'name.required' => 'El nombre del piso es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede exceder :max caracteres.',

            'description.string' => 'La descripción debe ser texto.',
            'description.max' => 'La descripción no puede exceder :max caracteres.',

            'level.required' => 'El nivel es obligatorio.',
            'level.integer' => 'El nivel debe ser un número entero.',
            'level.min' => 'El nivel debe ser mayor o igual a :min.',
            'level.max' => 'El nivel no puede ser mayor a :max.',

            'available.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }

        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->description),
            ]);
        }
    }
}
