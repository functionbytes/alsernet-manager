<?php

namespace Modules\Mailer\Services;

use Modules\Mailer\Models\MailerVariable;

/**
 * Service para obtener valores traducidos de variables de email desde la BD
 */
class MailVariableValueService
{
    /**
     * Obtener todos los valores traducidos para un idioma y módulo específicos
     *
     * @param  int  $langId  ID del idioma
     * @param  string|null  $module  Módulo específico (core, documents, orders) o null para todos
     * @return array Array asociativo [KEY => value]
     */
    public static function getTranslatedValues(int $langId, ?string $module = null): array
    {
        $query = MailerVariable::query()
            ->where('is_enabled', true)
            ->with(['translations' => function ($query) use ($langId) {
                $query->where('lang_id', $langId);
            }]);

        // Filtrar por módulo si se especifica
        if ($module) {
            $query->where('module', $module);
        }

        $variables = $query->get();

        $values = [];

        foreach ($variables as $variable) {
            // Obtener la traducción para este idioma
            $translation = $variable->translations->first();

            if ($translation && ! empty($translation->value)) {
                // Usar el valor traducido de la base de datos
                $values[$variable->key] = $translation->value;
            } elseif ($variable->example_value) {
                // Fallback al valor de ejemplo si no hay traducción
                $values[$variable->key] = $variable->example_value;
            }
        }

        return $values;
    }

    /**
     * Obtener el valor de una variable específica
     *
     * @param  string  $key  Clave de la variable (ej: COMPANY_NAME)
     * @param  int  $langId  ID del idioma
     */
    public static function getValue(string $key, int $langId): ?string
    {
        $variable = MailerVariable::query()
            ->where('key', $key)
            ->where('is_enabled', true)
            ->with(['translations' => function ($query) use ($langId) {
                $query->where('lang_id', $langId);
            }])
            ->first();

        if (! $variable) {
            return null;
        }

        $translation = $variable->translations->first();

        if ($translation && ! empty($translation->value)) {
            return $translation->value;
        }

        return $variable->example_value;
    }

    /**
     * Obtener todas las variables disponibles para un módulo
     *
     * @return array Array de claves de variables
     */
    public static function getAvailableKeys(?string $module = null): array
    {
        $query = MailerVariable::query()
            ->where('is_enabled', true);

        if ($module) {
            $query->where('module', $module);
        }

        return $query->pluck('key')->toArray();
    }
}
