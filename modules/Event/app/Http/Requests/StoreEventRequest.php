<?php

namespace Modules\Event\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'available' => ['nullable', 'boolean'],
            'color_flag' => ['nullable', 'string'],
            'filter_flag' => ['nullable', 'string'],
            'management_flag' => ['nullable', 'string'],
            'color_buttom' => ['nullable', 'string', 'max:900'],
            'hover_buttom' => ['nullable', 'string', 'max:900'],
            'cms' => ['nullable', 'string'],
            'featured' => ['nullable', 'boolean'],
            'amazing' => ['nullable', 'boolean'],
            'completed' => ['nullable', 'boolean'],
            'iva' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'processing' => ['nullable', 'boolean'],
            'processed' => ['nullable', 'boolean'],
            'banners' => ['nullable', 'string'],
            'banners_unique' => ['nullable', 'string'],
            'banners_backup' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título del evento es requerido',
            'title.max' => 'El título no debe exceder 255 caracteres',
            'start_at.required' => 'La fecha de inicio es requerida',
            'start_at.date' => 'La fecha de inicio debe ser una fecha válida',
            'end_at.required' => 'La fecha de finalización es requerida',
            'end_at.date' => 'La fecha de finalización debe ser una fecha válida',
            'end_at.after' => 'La fecha de finalización debe ser posterior a la fecha de inicio',
        ];
    }
}
