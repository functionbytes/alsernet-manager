<?php

namespace App\Traits\User;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait HasUserScopes
 *
 * Define query scopes reutilizables para el modelo User,
 * facilitando la búsqueda y filtrado de usuarios.
 *
 * @package App\Traits\User
 */
trait HasUserScopes
{
    /**
     * Scope para usuarios disponibles
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('users.available', 1);
    }

    /**
     * Scope para validar múltiples emails
     */
    public function scopeValidationsEmail(Builder $query, string $email)
    {
        return $query->where('email', $email)->get();
    }

    /**
     * Scope para validar un email único
     */
    public function scopeValidationEmail(Builder $query, string $email)
    {
        return $query->where('email', $email)->first();
    }

    /**
     * Scope para usuarios sin validar (sin uid)
     */
    public function scopeValidations(Builder $query)
    {
        return $query->where('uid', null)->get();
    }

    /**
     * Scope para buscar por ID
     */
    public function scopeId(Builder $query, int $id)
    {
        return $query->where('id', $id)->first();
    }

    /**
     * Scope para buscar por UID
     */
    public function scopeUid(Builder $query, string $uid)
    {
        return $query->where('uid', $uid)->first();
    }

    /**
     * Scope para buscar por email
     */
    public function scopeEmail(Builder $query, string $email)
    {
        return $query->where('email', $email)->first();
    }

    /**
     * Scope para filtrar usuarios
     */
    public static function scopeFilter(Builder $query, $request): void
    {
        // filters
        $filters = $request->all();
        if (! empty($filters)) {
            // Implementar filtros específicos aquí
        }
    }

    /**
     * Scope para buscar usuarios
     */
    public static function scopeSearch(Builder $query, string $keyword): void
    {
        $query = $query->select('customers.*')
            ->leftJoin('users', 'users.customer_id', '=', 'customers.id');

        // Keyword
        if (! empty(trim($keyword))) {
            foreach (explode(' ', trim($keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('users.first_name', 'like', '%'.$keyword.'%')
                        ->orWhere('users.last_name', 'like', '%'.$keyword.'%')
                        ->orWhere('users.email', 'like', '%'.$keyword.'%');
                });
            }
        }
    }

    /**
     * Scope para filtrar por plan
     */
    public static function scopeByPlan(Builder $query, $plan): void
    {
        $query->whereHas('subscriptions', function ($q) use ($plan) {
            $q->newOrActive()->where('plan_id', '=', $plan->id);
        });
    }
}
