<?php

namespace App\Providers;

use Modules\Helpdesk\Models\Campaign;
use Modules\Helpdesk\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Campaign\Policies\CampaignPolicy;
use Modules\Mailer\Models\MailerTemplate;
use Modules\Mailer\Policies\MailerTemplatePolicy;
use Modules\Mailer\Models\MailerLayout;
use Modules\Mailer\Policies\MailerComponentPolicy;
use Modules\Mailer\Models\MailerVariable;
use Modules\Mailer\Policies\MailerVariablePolicy;
use Modules\Mailer\Models\MailerEndpoint;
use Modules\Mailer\Policies\MailerEndpointPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        MailerTemplate::class => MailerTemplatePolicy::class,
        MailerLayout::class => MailerComponentPolicy::class,
        MailerVariable::class => MailerVariablePolicy::class,
        MailerEndpoint::class => MailerEndpointPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

    }
}
