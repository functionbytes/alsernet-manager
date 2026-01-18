<?php

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;

class DeepAnalyzePrestashopOrderStates extends Command
{
    protected $signature = 'app:deep-analyze-prestashop-states';

    protected $description = 'Deep analysis of Prestashop order states to understand payment detection';

    public function handle(): int
    {
        try {
            $this->info('🔍 Deep analyzing Prestashop order states...');
            $this->line('');

            $config = [
                'host' => env('DB_HOST_PRESTASHOP', '192.168.1.120'),
                'port' => env('DB_PORT_PRESTASHOP', 3306),
                'database' => env('DB_DATABASE_PRESTASHOP', 'alvarez_ana'),
                'username' => env('DB_USERNAME_PRESTASHOP', 'alvarez_ana'),
                'password' => env('DB_PASSWORD_PRESTASHOP', ''),
            ];

            // Get all order state info
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('📊 PRESTASHOP ORDER STATES DEFINITION');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            $states = $this->getPrestashopStates($config);
            if (! empty($states)) {
                $this->table(['ID', 'Name', 'Paid', 'Invoice', 'Shipped'], $states);
            }

            $this->line('');

            // Get order distribution
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('📈 CURRENT ORDERS STATE DISTRIBUTION');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            $distribution = $this->getOrderStateDistribution($config);
            if (! empty($distribution)) {
                $this->table(['State ID', 'State Name', 'Total Orders'], $distribution);
            }

            $this->line('');

            // Test different payment detection methods
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('🔍 PAYMENT DETECTION METHODS COMPARISON');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            $methods = $this->comparePaidDetectionMethods($config);
            if (! empty($methods)) {
                $this->table(['Detection Method', 'Paid Orders Found'], $methods);
            }

            $this->line('');

            // Get state history
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('📜 STATES USED IN ORDER HISTORY');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            $history = $this->getStatesInHistory($config);
            if (! empty($history)) {
                $this->table(['Description', 'Count'], $history);
            }

            $this->line('');

            // Conclusions
            $this->displayConclusions($methods, $distribution);

            $this->line('');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Analysis failed: '.$e->getMessage());

            return 1;
        }
    }

    private function getPrestashopStates(array $config): array
    {
        try {
            $query = 'SELECT id, name, paid, invoice, shipped FROM aalv_order_state ORDER BY id ASC';
            $output = shell_exec("mysql -h {$config['host']} -u {$config['username']} -p'{$config['password']}' {$config['database']} -sN -e \"".addslashes($query).'" 2>/dev/null');

            if (! $output) {
                return [];
            }

            $states = [];
            $lines = explode("\n", trim($output));

            foreach ($lines as $line) {
                if (! empty($line)) {
                    $parts = explode("\t", $line);
                    if (count($parts) >= 5) {
                        $states[] = [
                            $parts[0],
                            $parts[1] ?? 'Unknown',
                            ($parts[2] == '1' ? '✅ Yes' : '❌ No'),
                            ($parts[3] == '1' ? '✅ Yes' : '❌ No'),
                            ($parts[4] == '1' ? '✅ Yes' : '❌ No'),
                        ];
                    }
                }
            }

            return $states;
        } catch (\Exception $e) {
            $this->error('Error getting states: '.$e->getMessage());

            return [];
        }
    }

    private function getOrderStateDistribution(array $config): array
    {
        try {
            $query = 'SELECT current_state, COUNT(*) as count FROM aalv_orders GROUP BY current_state ORDER BY count DESC';
            $output = shell_exec("mysql -h {$config['host']} -u {$config['username']} -p'{$config['password']}' {$config['database']} -sN -e \"".addslashes($query).'" 2>/dev/null');

            if (! $output) {
                return [];
            }

            $dist = [];
            $lines = explode("\n", trim($output));

            foreach ($lines as $line) {
                if (! empty($line)) {
                    $parts = explode("\t", $line);
                    if (count($parts) >= 2) {
                        $stateId = $parts[0];
                        $stateName = $this->getStateName($stateId, $config);
                        $dist[] = [
                            $stateId,
                            $stateName,
                            $parts[1],
                        ];
                    }
                }
            }

            return $dist;
        } catch (\Exception $e) {
            $this->error('Error getting distribution: '.$e->getMessage());

            return [];
        }
    }

    private function comparePaidDetectionMethods(array $config): array
    {
        try {
            $methods = [
                'Current State = 2' => 'SELECT COUNT(DISTINCT id_order) FROM aalv_orders WHERE current_state = 2',
                'ANY History State = 2' => 'SELECT COUNT(DISTINCT id_order) FROM aalv_order_history WHERE id_order_state = 2',
                'LAST History State = 2' => 'SELECT COUNT(DISTINCT o.id_order) FROM aalv_orders o LEFT JOIN aalv_order_history oh ON o.id_order = oh.id_order AND oh.date_add = (SELECT MAX(date_add) FROM aalv_order_history WHERE id_order = o.id_order) WHERE oh.id_order_state = 2',
            ];

            $results = [];

            foreach ($methods as $name => $query) {
                $output = shell_exec("mysql -h {$config['host']} -u {$config['username']} -p'{$config['password']}' {$config['database']} -sN -e \"".addslashes($query).'" 2>/dev/null');

                $count = 0;
                if ($output) {
                    $count = (int) trim($output);
                }

                $results[] = [
                    $name,
                    $count > 0 ? "✅ {$count} orders" : '❌ 0 orders',
                ];
            }

            return $results;
        } catch (\Exception $e) {
            $this->error('Error comparing methods: '.$e->getMessage());

            return [];
        }
    }

    private function getStatesInHistory(array $config): array
    {
        try {
            $query = 'SELECT COUNT(DISTINCT id_order) as orders_with_state_2 FROM aalv_order_history WHERE id_order_state = 2';
            $output = shell_exec("mysql -h {$config['host']} -u {$config['username']} -p'{$config['password']}' {$config['database']} -sN -e \"".addslashes($query).'" 2>/dev/null');

            if (! $output) {
                return [];
            }

            $count = trim($output);
            return [
                ['Orders with State 2 (Paid)', $count],
            ];
        } catch (\Exception $e) {
            $this->error('Error getting history: '.$e->getMessage());

            return [];
        }
    }

    private function getStateName(string $stateId, array $config): string
    {
        try {
            $query = "SELECT name FROM aalv_order_state WHERE id = {$stateId}";
            $output = shell_exec("mysql -h {$config['host']} -u {$config['username']} -p'{$config['password']}' {$config['database']} -sN -e \"".addslashes($query).'" 2>/dev/null');

            return ! empty($output) ? trim($output) : "Unknown (ID: {$stateId})";
        } catch (\Exception $e) {
            return 'Error';
        }
    }

    private function displayConclusions(array $methods, array $distribution): void
    {
        $this->line('');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('💡 CONCLUSIONS & RECOMMENDATIONS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('');

        // Check if any method found paid orders
        $anyFound = false;
        foreach ($methods as $method) {
            if (strpos($method[1], '✅') === 0) {
                $anyFound = true;
                break;
            }
        }

        if (! $anyFound) {
            $this->warn('⚠️  NO PAID ORDERS FOUND');
            $this->line('');
            $this->error('Possible reasons:');
            $this->error('1. Orders were never marked as paid in Prestashop');
            $this->error('2. All orders are stuck in "Awaiting Payment" state');
            $this->error('3. The paid flag is not being used in this installation');
            $this->line('');
            $this->info('Recommendation:');
            $this->info('- Investigate why orders are not reaching paid status');
            $this->info('- Check Prestashop payment processor configuration');
            $this->info('- Review order workflow in Prestashop admin');
        } else {
            $this->info('✅ PAID ORDERS FOUND');
            $this->line('');
            $this->info('Recommendation:');
            $this->info('- Review the analysis above to find which method works');
            $this->info('- Update the queries to use that method');
        }

        $this->line('');
    }
}
