<?php

namespace App\Providers;

use App\Models\Helpdesk\Campaign;
use App\Models\Helpdesk\Ticket;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Campaign\Policies\CampaignPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => \App\Policies\V1\UserPolicy::class,
        Campaign::class => CampaignPolicy::class,
        Ticket::class => \App\Policies\TicketPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

    }
}
