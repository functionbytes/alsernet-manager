<?php

include_once dirname(__FILE__).'/BaseAction.php';

/**
 * FormAction
 *
 * Acción para envío de formularios.
 * Este es un ejemplo de cómo reutilizar BaseAction para diferentes tipos de acciones.
 *
 * Solo necesita:
 * 1. Definir $endpoint y $actionType
 * 2. Implementar mapResponse()
 * 3. Crear métodos públicos específicos (submitForm, etc.)
 */
class FormAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();

        $this->endpoint = 'api/forms';
        $this->actionType = 'forms';
    }

    /**
     * Envía un formulario
     *
     * @param  array  $formData  Datos del formulario
     * @param  string  $formType  Tipo de formulario (contact, newsletter, etc.)
     * @param  array  $context  Contexto adicional
     * @return array Resultado
     */
    public function submitForm(array $formData, $formType = 'contact', array $context = [])
    {
        $payload = [
            'action' => 'submit',
            'type' => $formType,
            'data' => $formData,
        ];

        return $this->execute($payload, $context);
    }

    /**
     * Mapea respuesta de ApiManager a estructura estándar
     *
     * @param  array  $response  Respuesta de ApiManager
     * @return array Respuesta mapeada
     */
    protected function mapResponse(array $response)
    {
        $responseData = $response['response'] ?? [];

        return [
            'status' => $response['status'],  // 'success' | 'pending' | 'error'
            'request_id' => $response['request_id'] ?? null,
            'data' => [
                'message' => $responseData['data']['message'] ?? 'Formulario enviado',
                'submission_id' => $responseData['data']['submission_id'] ?? null,
                'validation_errors' => $responseData['data']['validation_errors'] ?? [],
            ],
            'error' => $response['message'] ?? null,
        ];
    }
}
