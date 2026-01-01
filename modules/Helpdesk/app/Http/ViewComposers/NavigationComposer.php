<?php

namespace Modules\Helpdesk\Http\ViewComposers;

use Illuminate\View\View;

class NavigationComposer
{
    public function compose(View $view): void
    {
        $navigationConfig = config('helpdesk.navigation', []);
        $helpdeskSidebarItems = $this->buildNavigationItems($navigationConfig);
        $view->with('helpdeskNavigation', $helpdeskSidebarItems);
    }

    protected function buildNavigationItems(array $config): array
    {
        if (empty($config)) {
            return [];
        }

        $items = [];

        // Manager settings navigation
        if (auth()->check() && auth()->user()->hasRole(['manager', 'super-admin'])) {
            if (!empty($config['manager_settings'])) {
                $section = $config['manager_settings'];
                $items[] = [
                    'icon' => $section['icon'] ?? 'fas fa-cog',
                    'title' => $section['title'] ?? 'Settings',
                    'route' => $section['route'] ?? null,
                    'url' => $section['route'] ? route($section['route']) : null,
                    'active' => request()->routeIs($section['route'].'*'),
                ];
            }
        }

        // Agent navigation
        if (auth()->check() && auth()->user()->hasRole(['helpdesk-agent', 'super-admin'])) {
            if (!empty($config['agent_menu'])) {
                $section = $config['agent_menu'];
                $items[] = [
                    'icon' => $section['icon'] ?? 'fas fa-headset',
                    'title' => $section['title'] ?? 'Helpdesk',
                    'route' => $section['route'] ?? null,
                    'url' => $section['route'] ? route($section['route']) : null,
                    'active' => request()->routeIs($section['route'].'*'),
                ];
            }
        }

        return $items;
    }
}
