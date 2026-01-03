<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class BaseManagerController extends Controller
{
    /**
     * Return a success JSON response
     */
    protected function success(string $message, array $data = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Return an error JSON response
     */
    protected function error(string $message, array $data = [], int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Get the pagination per page value
     */
    protected function getPaginationPerPage(): int
    {
        return config('app.pagination_per_page', 15);
    }

    /**
     * Get all available permissions
     */
    protected function getAvailablePermissions(): array
    {
        return Permission::all()->toArray();
    }

    /**
     * Update environment file with key-value pairs
     */
    protected function updateEnvFile(array $data): void
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envFile, $envContent);
    }
}
