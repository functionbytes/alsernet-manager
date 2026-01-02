<?php

namespace Modules\Mailer\Policies;

use App\Models\User;

class MailerSettingsPolicy
{
    public function configure(User $user): bool
    {
        // Super-admin siempre puede
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Managers pueden configurar emails
        if ($user->hasRole('manager')) {
            return $user->can('manager.mailers.configure');
        }

        return false;
    }

    public function viewSettings(User $user): bool
    {
        return $this->configure($user);
    }

    public function manageTemplates(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('manager') && $user->can('manager.mailers.manage-templates');
    }

    public function manageComponents(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('manager') && $user->can('manager.mailers.manage-components');
    }

    public function manageVariables(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('manager') && $user->can('manager.mailers.manage-variables');
    }

    public function manageEndpoints(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('manager') && $user->can('manager.mailers.manage-endpoints');
    }
}
