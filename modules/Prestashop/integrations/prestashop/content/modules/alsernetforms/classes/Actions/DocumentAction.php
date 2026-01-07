<?php

/**
 * DocumentAction
 *
 * Acción específica para validación de tokens de documentos.
 * Extiende AbstractAction para manejar la lógica de validación de documentos.
 */
class DocumentAction extends AbstractAction
{
    public function __construct()
    {
        parent::__construct();

        $this->endpointUrl = 'https://webadminpruebas.a-alvarez.com/api/documents/validate-token';
        $this->endpointType = 'documents';
        $this->method = 'POST';
        $this->timeout = 10;
    }

    /**
     * Valida un token de documento
     *
     * @param  string  $token  Token a validar
     * @param  array  $context  Contexto adicional (customer_id, order_id, etc.)
     * @return array Resultado con status, data, etc.
     */
    public function validateToken($token, array $context = [])
    {
        $payload = [
            'token' => $token,
        ];

        return $this->execute($payload, $context);
    }

    /**
     * Procesa una petición pendiente (llamado por el cron)
     *
     * @param  int  $requestId  ID de la petición a procesar
     * @return bool True si se procesó correctamente
     */
    public function processPendingRequest($requestId)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'alsernet_forms_requests WHERE id_alsernetforms_request = '.(int) $requestId;
        $request = $this->db->getRow($sql);

        if (! $request || $request['endpoint_type'] !== 'documents') {
            return false;
        }

        // Marcar como procesando
        $this->updateRequestStatus($requestId, 'processing');

        // Obtener payload
        $payload = json_decode($request['payload'], true);

        // Reintentar envío
        $response = $this->sendRequest($payload);

        if ($response['success']) {
            $this->updateRequestStatus($requestId, 'success', $response['data']);

            return true;
        } else {
            $this->updateRequestStatus($requestId, 'failed', null, $response['error']);

            return false;
        }
    }
}
