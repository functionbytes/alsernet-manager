<?php

include_once dirname(__FILE__).'/BaseAction.php';

/**
 * ChatAction
 *
 * Acción para chat/mensajería.
 * Demuestra la reutilización de BaseAction con diferentes endpoints y mapeos.
 */
class ChatAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();

        $this->endpoint = 'api/chat';
        $this->actionType = 'chat';
    }

    /**
     * Envía un mensaje
     *
     * @param  string  $message  Contenido del mensaje
     * @param  string  $conversationId  ID de conversación
     * @param  array  $context  Contexto adicional
     * @return array Resultado
     */
    public function sendMessage($message, $conversationId = null, array $context = [])
    {
        $payload = [
            'action' => 'send_message',
            'message' => $message,
            'conversation_id' => $conversationId,
        ];

        return $this->execute($payload, $context);
    }

    /**
     * Obtiene mensajes de una conversación
     *
     * @param  string  $conversationId  ID de conversación
     * @param  array  $context  Contexto adicional
     * @return array Resultado
     */
    public function getMessages($conversationId, array $context = [])
    {
        $payload = [
            'action' => 'get_messages',
            'conversation_id' => $conversationId,
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
                'conversation_id' => $responseData['data']['conversation_id'] ?? null,
                'messages' => $responseData['data']['messages'] ?? [],
                'last_message_id' => $responseData['data']['last_message_id'] ?? null,
                'unread_count' => $responseData['data']['unread_count'] ?? 0,
            ],
            'error' => $response['message'] ?? null,
        ];
    }
}
