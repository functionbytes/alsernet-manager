<?php

namespace App\Http\Requests\Managers\Settings\Suppliers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
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
        $uid = $this->route('uid');

        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('suppliers', 'code')->ignore($uid, 'uid'),
            ],
            'website' => 'nullable|url|max:500',
            'description' => 'nullable|string|max:1000',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'priority' => 'nullable|integer|min:1|max:100',
            'content_type' => 'nullable|string|in:products,descriptions,images,specifications',
            'is_active' => 'nullable|boolean',
            'api_rate_limit' => 'nullable|integer|min:1|max:1000',
            'api_rate_period' => 'nullable|string|in:second,minute,hour,day',
            'metadata' => 'nullable|array',
            'config' => 'nullable|string',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del proveedor es obligatorio',
            'name.max' => 'El nombre del proveedor no puede exceder 255 caracteres',
            'code.required' => 'El código del proveedor es obligatorio',
            'code.unique' => 'Ya existe un proveedor con este código',
            'code.max' => 'El código no puede exceder 50 caracteres',
            'website.url' => 'El sitio web debe ser una URL válida',
            'contact_email.email' => 'El email de contacto debe ser válido',
            'api_rate_limit.min' => 'El límite de tasa debe ser al menos 1',
            'api_rate_limit.max' => 'El límite de tasa no puede exceder 1000',
            'api_rate_period.in' => 'El período debe ser: second, minute, hour o day',
        ];
    }
}
