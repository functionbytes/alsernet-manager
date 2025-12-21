<?php

namespace App\Http\Requests\Managers\Settings\Suppliers;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierSourceRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:api,feed,scraper,ftp,sftp,email,webhook',
            'url' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'refresh_interval' => 'nullable|integer|min:60|max:86400',
            'timeout' => 'nullable|integer|min:5|max:300',
            'retry_attempts' => 'nullable|integer|min:0|max:10',
            'retry_delay' => 'nullable|integer|min:1|max:3600',
            'metadata' => 'nullable|array',
            'configuration' => 'nullable|array',
            'configuration.auth_type' => 'nullable|string|in:none,basic,bearer,oauth2,api_key',
            'configuration.headers' => 'nullable|array',
            'configuration.params' => 'nullable|array',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la fuente es obligatorio',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'type.required' => 'El tipo de fuente es obligatorio',
            'type.in' => 'El tipo de fuente debe ser: api, feed, scraper, ftp, sftp, email o webhook',
            'url.max' => 'La URL no puede exceder 500 caracteres',
            'refresh_interval.min' => 'El intervalo de actualización debe ser al menos 60 segundos',
            'refresh_interval.max' => 'El intervalo de actualización no puede exceder 24 horas',
            'timeout.min' => 'El timeout debe ser al menos 5 segundos',
            'timeout.max' => 'El timeout no puede exceder 5 minutos',
            'retry_attempts.max' => 'Los intentos de reintento no pueden exceder 10',
            'retry_delay.max' => 'El retraso entre reintentos no puede exceder 1 hora',
        ];
    }
}
