<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class DatabaseSettingsController extends Controller
{
    /**
     * Display database settings form
     */
    public function index()
    {
        $settings = Setting::getDatabaseSettings();
        $pageTitle = 'Configuración de Base de Datos';
        $breadcrumb = 'Configuración / Base de Datos';

        return view('managers.views.settings.database.index', compact('settings', 'pageTitle', 'breadcrumb'));
    }

    /**
     * Show database edit form
     */
    public function edit()
    {
        $settings = Setting::getDatabaseSettings();
        $rules = Setting::getDatabaseRules();
        $pageTitle = 'Editar Base de Datos';
        $breadcrumb = 'Configuración / Base de Datos';

        return view('managers.views.settings.database.edit', compact('settings', 'rules', 'pageTitle', 'breadcrumb'));
    }

    /**
     * Update database settings
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate(Setting::getDatabaseRules());

            Setting::setDatabaseSettings($validated);

            return redirect()->route('manager.settings.database.index')
                ->with('success', 'Configuración de base de datos actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la configuración: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Test database connection
     */
    public function checkConnection()
    {
        try {
            $settings = Setting::getDatabaseSettings();
            $driver = $settings['db_connection'] ?? 'mysql';

            // Validate based on driver type
            if ($driver === 'mysql') {
                $conn = $this->testMySQLConnection($settings);
            } elseif ($driver === 'pgsql') {
                $conn = $this->testPostgreSQLConnection($settings);
            } elseif ($driver === 'sqlite') {
                $conn = $this->testSQLiteConnection($settings);
            } else {
                throw new \Exception("Driver de base de datos no soportado: {$driver}");
            }

            // Si llegamos aquí, la conexión fue exitosa
            return response()->json([
                'success' => true,
                'status' => 'connected',
                'message' => 'Conexión a base de datos exitosa',
                'version' => $conn['version'] ?? 'Desconocida'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Error en la conexión: ' . $e->getMessage()
            ], 200); // Return 200 so JavaScript success block handles it
        }
    }

    /**
     * Test MySQL connection
     */
    private function testMySQLConnection($settings)
    {
        try {
            $conn = @mysqli_connect(
                $settings['db_host'],
                $settings['db_username'],
                $settings['db_password'],
                $settings['db_database'],
                (int) $settings['db_port']
            );

            if (!$conn) {
                throw new \Exception(mysqli_connect_error() ?: 'Error desconocido en MySQL');
            }

            // Get MySQL version
            $result = mysqli_query($conn, 'SELECT VERSION() as version');
            $version_info = mysqli_fetch_assoc($result);
            mysqli_close($conn);

            return [
                'success' => true,
                'version' => $version_info['version'] ?? 'Desconocida'
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Test PostgreSQL connection
     */
    private function testPostgreSQLConnection($settings)
    {
        try {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $settings['db_host'],
                $settings['db_port'] ?? 5432,
                $settings['db_database']
            );

            $pdo = new \PDO(
                $dsn,
                $settings['db_username'],
                $settings['db_password'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_TIMEOUT => 5
                ]
            );

            // Get PostgreSQL version
            $result = $pdo->query('SELECT version() as version');
            $version_info = $result->fetch(\PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'version' => $version_info['version'] ?? 'Desconocida'
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error de conexión PostgreSQL: ' . $e->getMessage());
        }
    }

    /**
     * Test SQLite connection
     */
    private function testSQLiteConnection($settings)
    {
        try {
            $dbPath = $settings['db_database'] ?? database_path('database.sqlite');

            // SQLite doesn't use host/port, just file path
            $pdo = new \PDO(
                'sqlite:' . $dbPath,
                null,
                null,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ]
            );

            // Get SQLite version
            $result = $pdo->query('SELECT sqlite_version() as version');
            $version_info = $result->fetch(\PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'version' => $version_info['version'] ?? 'Desconocida'
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error de conexión SQLite: ' . $e->getMessage());
        }
    }
}
