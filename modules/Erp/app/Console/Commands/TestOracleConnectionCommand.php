<?php

namespace Modules\Erp\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestOracleConnectionCommand extends Command
{
    protected $signature = 'erp:test-oracle {--quiet : Suppress output}';
    protected $description = 'Test Oracle database connection for ERP module';

    public function handle(): int
    {
        try {
            $connection = DB::connection('oracle');
            $pdo = $connection->getPdo();

            if (!$this->option('quiet')) {
                $this->info('✓ Oracle connection successful');
                $this->info('Driver: ' . $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
                $this->info('Server version: ' . config('database.connections.oracle.server_version'));
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Oracle connection failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
