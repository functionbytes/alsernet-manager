<?php

namespace Modules\Modules\Console\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Facades\Module;

class ModulesStatusCommand extends Command
{
    protected $signature = 'modules:status';

    protected $description = 'Display the status of all modules';

    public function handle(): int
    {
        $this->info('Module Status Report');
        $this->line(str_repeat('=', 80));

        $modules = [];
        $enabledCount = 0;
        $disabledCount = 0;

        foreach (Module::all() as $module) {
            $isEnabled = $module->isEnabled();
            if ($isEnabled) {
                $enabledCount++;
            } else {
                $disabledCount++;
            }

            $modules[] = [
                $module->getName(),
                $module->getAlias(),
                $isEnabled ? 'Enabled' : 'Disabled',
                $module->getPriority(),
                $module->getVersion() ?? '1.0.0',
            ];
        }

        $this->table(
            ['Name', 'Alias', 'Status', 'Priority', 'Version'],
            $modules
        );

        $this->line(str_repeat('=', 80));
        $this->info('Total Modules: '.count($modules));
        $this->line("  <fg=green>Enabled:</> {$enabledCount}");
        $this->line("  <fg=yellow>Disabled:</> {$disabledCount}");

        return self::SUCCESS;
    }
}
