<?php

namespace App\Http\Requests\Managers\Settings\Suppliers;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierPromptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'supplier_id' => 'nullable|exists:suppliers,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:title,description,meta_title,meta_description,tags,full_content',
            'category' => 'required|string|max:100',
            'system_prompt' => 'required|string',
            'user_prompt' => 'required|string',
            'variables' => 'nullable|array',
            'model' => 'nullable|string|max:100',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:8000',
            'is_active' => 'nullable|boolean',
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del prompt es obligatorio',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'type.required' => 'El tipo de prompt es obligatorio',
            'type.in' => 'El tipo debe ser: title, description, meta_title, meta_description, tags o full_content',
            'category.required' => 'La categoría es obligatoria',
            'system_prompt.required' => 'El prompt del sistema es obligatorio',
            'user_prompt.required' => 'El prompt del usuario es obligatorio',
            'temperature.min' => 'La temperatura debe ser al menos 0',
            'temperature.max' => 'La temperatura no puede exceder 2',
            'max_tokens.min' => 'Los tokens máximos deben ser al menos 1',
            'max_tokens.max' => 'Los tokens máximos no pueden exceder 8000',
        ];
    }
}
