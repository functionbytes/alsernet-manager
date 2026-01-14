<?php

namespace Modules\Erp\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_pedido' => $this['idpedido'] ?? null,
            'numero_pedido' => $this['npedido'] ?? null,
            'numero_pedido_cliente' => $this['npedidocli'] ?? null,
            'serie' => $this['serie'] ?? null,
            'id_cliente' => $this['idcliente'] ?? null,
            'fecha_pedido' => $this['fechapedido'] ?? null,
            'fecha_entrega' => $this['fechaentrega'] ?? null,
            'estado' => $this['estado'] ?? null,
            'total' => $this['total'] ?? null,
            'moneda' => $this['moneda'] ?? null,
            'metodo_pago' => $this['metodopago'] ?? null,
            'direccion_entrega' => $this['direccionentrega'] ?? null,
            'notas' => $this['notas'] ?? null,
            'identificador_origen' => $this['identificadororigen'] ?? null,
        ];
    }
}
