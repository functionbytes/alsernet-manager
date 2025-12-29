<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'position' => ['nullable', 'integer', 'min:0'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^(fas|far|fab) fa-[a-z0-9-]+$/'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'active' => ['boolean'],
            'visibility' => ['in:public,private,hidden'],
            'prestashop_id' => ['nullable', 'integer'],
            'sync_prestashop' => ['boolean'],
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título de la categoría es obligatorio',
            'title.max' => 'El título no puede exceder 255 caracteres',
            'parent_id.exists' => 'La categoría padre seleccionada no existe',
            'slug.unique' => 'Este slug ya está en uso por otra categoría',
            'slug.regex' => 'El slug solo puede contener letras minúsculas, números y guiones',
            'icon.regex' => 'El icono debe ser una clase válida de Font Awesome 6 (ej: fas fa-tag)',
            'color.regex' => 'El color debe ser un código HEX válido (ej: #90bb13)',
            'image.image' => 'El archivo debe ser una imagen',
            'image.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif o svg',
            'image.max' => 'La imagen no puede superar los 2MB',
            'visibility.in' => 'La visibilidad debe ser: public, private o hidden',
        ];
    }

    /**
     * Get custom attribute names for validation errors
     */
    public function attributes(): array
    {
        return [
            'title' => 'título',
            'parent_id' => 'categoría padre',
            'position' => 'posición',
            'slug' => 'slug',
            'meta_title' => 'meta título',
            'meta_description' => 'meta descripción',
            'meta_keywords' => 'meta palabras clave',
            'icon' => 'icono',
            'color' => 'color',
            'image' => 'imagen',
            'active' => 'activo',
            'visibility' => 'visibilidad',
        ];
    }
}
