<?php

namespace App\Services\Email;

use App\Models\Mail\MailVariable;

class MailVariableValueService
{
    /**
     * Obtener un array con todos los valores reales de variables traducidas para un idioma específico
     *
     * @param  int  $langId  ID del idioma
     * @param  string|null  $module  Módulo específico (optional, si no se proporciona trae todas)
     * @return array Array con key => value traducido
     */
    public static function getTranslatedValues(int $langId, ?string $module = null): array
    {
        $query = MailVariable::query()
            ->where('is_enabled', true)
            ->with(['translations' => function ($q) use ($langId) {
                $q->where('lang_id', $langId);
            }]);

        if ($module) {
            $query->where(function ($q) use ($module) {
                $q->where('module', $module)
                    ->orWhere('module', 'core');
            });
        }

        $variables = $query->get();
        $result = [];

        foreach ($variables as $variable) {
            $translation = $variable->translations->first();
            // Usar el valor traducido si existe, si no, usar el valor de la variable principal o vacío
            $result[$variable->key] = $translation?->value ?? $variable->value ?? '';
        }

        return $result;
    }

    /**
     * Obtener todas las variables como array con su información completa
     */
    public static function getVariablesWithInfo(int $langId, ?string $module = null): array
    {
        $query = MailVariable::query()
            ->where('is_enabled', true)
            ->with(['translations' => function ($q) use ($langId) {
                $q->where('lang_id', $langId);
            }]);

        if ($module) {
            $query->where(function ($q) use ($module) {
                $q->where('module', $module)
                    ->orWhere('module', 'core');
            });
        }

        $variables = $query->get();
        $result = [];

        foreach ($variables as $variable) {
            $translation = $variable->translations->first();

            $result[$variable->key] = [
                'key' => $variable->key,
                'name' => $translation?->name ?? $variable->name,
                'description' => $translation?->description ?? $variable->description,
                'value' => $translation?->value ?? $variable->value ?? '',
                'example_value' => $variable->example_value,
                'category' => $variable->category,
                'module' => $variable->module,
            ];
        }

        return $result;
    }

    /**
     * Obtener valor de una variable específica traducida
     *
     * @param  string  $key  Clave de la variable (ej: COMPANY_NAME)
     * @param  int  $langId  ID del idioma
     */
    public static function getValue(string $key, int $langId): ?string
    {
        $variable = MailVariable::where('key', $key)
            ->where('is_enabled', true)
            ->with(['translations' => function ($q) use ($langId) {
                $q->where('lang_id', $langId);
            }])
            ->first();

        if (! $variable) {
            return null;
        }

        $translation = $variable->translations->first();

        return $translation?->value ?? $variable->value ?? '';
    }

    /**
     * Obtener valor de múltiples variables a la vez
     *
     * @param  array  $keys  Array de claves (ej: ['COMPANY_NAME', 'SITE_NAME'])
     * @param  int  $langId  ID del idioma
     */
    public static function getValues(array $keys, int $langId): array
    {
        $variables = MailVariable::whereIn('key', $keys)
            ->where('is_enabled', true)
            ->with(['translations' => function ($q) use ($langId) {
                $q->where('lang_id', $langId);
            }])
            ->get();

        $result = [];

        foreach ($variables as $variable) {
            $translation = $variable->translations->first();
            $result[$variable->key] = $translation?->value ?? $variable->value ?? '';
        }

        return $result;
    }
}
