<?php

include_once dirname(__FILE__).'/../ApiManager.php';

/**
 * BaseAction
 *
 * Clase base abstracta para todas las acciones.
 * Patrón Template Method: subclases solo necesitan implementar mapResponse()
 *
 * Uso:
 * - Heredar esta clase
 * - Definir $endpoint y $actionType
 * - Implementar mapResponse() para transformar la respuesta
 */
abstract class BaseAction
{
    protected $apiManager;

    protected $endpoint;  // Ej: 'api/documents', 'api/forms', 'api/chat'

    protected $actionType;  // Ej: 'documents', 'forms', 'chat' - usado para logger

    public function __construct()
    {
        $this->apiManager = new ApiManager;
    }

    /**
     * Ejecuta la acción contra el endpoint
     *
     * @param  array  $payload  Datos a enviar
     * @param  array  $context  Contexto adicional
     * @return array Resultado mapeado con status, data, request_id, error
     */
    final protected function execute(array $payload, array $context = [])
    {
        // ApiManager maneja:
        // - Verificación de disponibilidad
        // - Logging automático via el logger apropiado
        // - Circuit breaker con reintentos
        $response = $this->apiManager->sendRequest(
            'POST',
            $this->endpoint,
            $payload,
            $this->actionType,
            [],
            true  // checkAvailability=true
        );

        // Mapear respuesta según el tipo de acción
        // Cada subclase implementa su propia lógica de mapeo
        return $this->mapResponse($response);
    }

    /**
     * Mapea la respuesta de ApiManager a estructura estándar
     *
     * Estructura esperada:
     * [
     *   'status' => 'success|pending|error',
     *   'request_id' => int|null,
     *   'data' => [...datos específicos del action...],
     *   'error' => string|null
     * ]
     *
     * @param  array  $response  Respuesta de ApiManager
     * @return array Respuesta mapeada
     */
    abstract protected function mapResponse(array $response);
}
