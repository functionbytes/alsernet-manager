<?php

namespace Modules\Erp\Http\Resources\Clients;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_cliente' => $this['idcliente'] ?? null,
            'id_web' => $this['idclienteweb'] ?? null,
            'nombre' => $this['nombre'] ?? null,
            'apellidos' => $this['apellidos'] ?? null,
            'dni' => $this['dni'] ?? null,
            'email' => $this['email'] ?? null,
            'telefono' => $this['telefono1'] ?? null,
            'direccion' => $this['direccion'] ?? null,
            'ciudad' => $this['ciudad'] ?? null,
            'provincia' => $this['provincia'] ?? null,
            'codigo_postal' => $this['codigopostal'] ?? null,
            'pais' => $this['pais'] ?? null,
            'razon_social' => $this['razonsocial'] ?? null,
            'cif' => $this['cif'] ?? null,
            'created_at' => $this['fecha_alta'] ?? null,
        ];
    }
}
