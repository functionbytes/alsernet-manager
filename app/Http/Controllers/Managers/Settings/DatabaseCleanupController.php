<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseCleanupController extends Controller
{
    /**
     * Display database cleanup form with table list
     */
    public function index()
    {
        // Check if cleanup is enabled
        $cleanupEnabled = env('CMS_ENABLED_CLEANUP_DATABASE', false);

        if (!$cleanupEnabled) {
            return view('managers.views.settings.database.cleanup.disabled', [
                'pageTitle' => 'Limpieza de Base de Datos',
                'breadcrumb' => 'Configuración / Limpieza de Base de Datos'
            ]);
        }

        try {
            $tables = $this->getTablesList();
            $pageTitle = 'Limpieza de Base de Datos';
            $breadcrumb = 'Configuración / Limpieza de Base de Datos';

            return view('managers.views.settings.database.cleanup.index', compact(
                'tables',
                'pageTitle',
                'breadcrumb',
                'cleanupEnabled'
            ));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al obtener las tablas: ' . $e->getMessage());
        }
    }

    /**
     * Get list of all tables with record count
     */
    private function getTablesList()
    {
        $database = env('DB_DATABASE');
        $tables = [];

        // Get all tables from current database
        $result = DB::select("SELECT TABLE_NAME, TABLE_ROWS
                            FROM INFORMATION_SCHEMA.TABLES
                            WHERE TABLE_SCHEMA = ?
                            ORDER BY TABLE_NAME ASC", [$database]);

        foreach ($result as $row) {
            $tableName = $row->TABLE_NAME;

            // Get actual count (TABLE_ROWS can be inaccurate)
            $actualCount = DB::table($tableName)->count();

            $tables[] = [
                'name' => $tableName,
                'records' => $actualCount,
                'estimated' => $row->TABLE_ROWS
            ];
        }

        return $tables;
    }

    /**
     * Truncate selected tables
     */
    public function truncate(Request $request)
    {
        // Check if cleanup is enabled
        if (!env('CMS_ENABLED_CLEANUP_DATABASE', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Esta característica no está habilitada. Configura CMS_ENABLED_CLEANUP_DATABASE=true en el archivo .env'
            ], 403);
        }

        try {
            $request->validate([
                'tables' => 'required|array|min:1',
                'tables.*' => 'required|string'
            ]);

            $tablesToTruncate = $request->input('tables');
            $database = env('DB_DATABASE');

            // Get all valid tables from database
            $allTables = DB::select("SELECT TABLE_NAME
                                    FROM INFORMATION_SCHEMA.TABLES
                                    WHERE TABLE_SCHEMA = ?", [$database]);

            $validTableNames = array_map(fn($t) => $t->TABLE_NAME, $allTables);

            // Validate all requested tables exist
            foreach ($tablesToTruncate as $table) {
                if (!in_array($table, $validTableNames)) {
                    return response()->json([
                        'success' => false,
                        'message' => "La tabla '$table' no existe o no es válida"
                    ], 400);
                }
            }

            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            $truncatedCount = 0;
            $errors = [];

            // Truncate each table
            foreach ($tablesToTruncate as $table) {
                try {
                    DB::table($table)->truncate();
                    $truncatedCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'table' => $table,
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => "Se limpiaron $truncatedCount tabla(s) pero hubo errores en " . count($errors) . " tabla(s)",
                    'errors' => $errors
                ], 400);
            }

            // Log the cleanup action
            activity()
                ->causedBy(auth()->user())
                ->log("Limpieza de base de datos: Se vaciaron $truncatedCount tabla(s)");

            return response()->json([
                'success' => true,
                'message' => "Se limpiaron correctamente $truncatedCount tabla(s)",
                'truncated_count' => $truncatedCount
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validación fallida',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar la base de datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get table record count via AJAX
     */
    public function getTableCount(Request $request)
    {
        try {
            $tableName = $request->input('table');
            $database = env('DB_DATABASE');

            // Validate table exists
            $exists = DB::select("SELECT TABLE_NAME
                                FROM INFORMATION_SCHEMA.TABLES
                                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                                [$database, $tableName]);

            if (empty($exists)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tabla no encontrada'
                ], 404);
            }

            $count = DB::table($tableName)->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}