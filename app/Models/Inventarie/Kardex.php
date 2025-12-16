<?php


namespace App\Models\Inventarie;

use Illuminate\Support\Facades\Http;

class Kardex
{

    public function searchParameters(string $parameters,string $value)
    {
        return $this->makeRequest([$parameters => $value]);
    }

    private function makeRequest(array $params)
    {
        $url = 'http://192.168.1.120:8007/';

        try {

            $response = Http::get($url, $params);

            if ($response->successful()) {
                return json_decode($response->body());
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Error en la consulta externa: ' . $e->getMessage());
            return null;
        }
    }


}

