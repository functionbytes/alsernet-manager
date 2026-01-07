<?php

/**
 * DocumentValidator - Validador de Documentos con Resiliencia
 *
 * Esta clase proporciona una capa de abstracción resiliente para la validación de documentos
 * de pedidos que requieren licencias (armas cortas, rifles, escopetas, DNI, etc.).
 *
 * CARACTERÍSTICAS PRINCIPALES:
 * ────────────────────────────────────────────────────────────────────────────
 * ✅ Circuit Breaker Pattern - Verifica disponibilidad del servidor antes de enviar
 * ✅ Queue System - Guarda peticiones como "pending" si el servidor está caído
 * ✅ Automatic Retry - El sistema de cron procesa automáticamente las pendientes
 * ✅ Normalized Response - Estructura de respuesta consistente independiente del resultado
 * ✅ i18n Support - Traducciones automáticas según contexto del usuario
 *
 * FLUJO DE TRABAJO:
 * ────────────────────────────────────────────────────────────────────────────
 * 1. validateDocuments() recibe UID y tipo de documento
 * 2. ApiManager verifica disponibilidad con EndpointAvailabilityChecker
 * 3. Si servidor disponible → Procesa inmediatamente
 * 4. Si servidor NO disponible → Guarda en BD como "pending"
 * 5. Cron job procesa pendientes cada 5-15 minutos
 * 6. Usuario recibe feedback apropiado en ambos casos
 *
 * EJEMPLO DE USO BÁSICO:
 * ────────────────────────────────────────────────────────────────────────────
 * ```php
 * // Crear instancia del validador
 * $validator = new DocumentValidator();
 *
 * // Validar documentos de un pedido
 * $result = $validator->validateDocuments(
 *     'ORDER-12345',           // UID del pedido
 *     'corta',                 // Tipo: corta|rifle|escopeta|dni
 *     [
 *         'customer_id' => 789,
 *         'order_reference' => 'ORDER-12345'
 *     ]
 * );
 *
 * // Interpretar resultado
 * if ($result['status'] === 'success') {
 *     echo "Documentos validados: " . count($result['data']['uploaded_documents']);
 * } elseif ($result['status'] === 'pending') {
 *     echo "Servidor no disponible. Petición guardada con ID: " . $result['request_id'];
 * } else {
 *     echo "Error: " . $result['message'];
 * }
 * ```
 *
 * EJEMPLO CON VERIFICACIÓN DE ESTADO:
 * ────────────────────────────────────────────────────────────────────────────
 * ```php
 * // Si guardaste el request_id cuando el servidor estaba caído
 * $requestId = 12345;
 *
 * // Verificar estado actual de la petición
 * $status = $validator->checkPendingRequestStatus($requestId);
 *
 * echo "Estado: " . $status['status'];
 * echo "Reintentos: " . $status['retry_count'] . "/" . $status['max_retries'];
 * echo "Próximo intento: " . $status['next_retry_at'];
 * ```
 *
 * ESTRUCTURA DE RESPUESTA:
 * ────────────────────────────────────────────────────────────────────────────
 * Array con los siguientes campos:
 * - status: 'success' | 'pending' | 'error'
 * - type: string - Tipo de documento solicitado
 * - label: string - Etiqueta traducida del tipo de documento
 * - upload: bool - Si se permite subir documentos
 * - data: array - Contiene required_documents, uploaded_documents, missing_documents
 * - message: string - Mensaje descriptivo del resultado
 * - request_id: int|null - ID de la petición (solo si status='pending')
 * - reason: string|null - Razón del fallo (solo si status='pending')
 * - error: string|null - Detalles del error (solo si status='error')
 *
 * TIPOS DE DOCUMENTO SOPORTADOS:
 * ────────────────────────────────────────────────────────────────────────────
 * - 'corta': Arma corta (requiere DNI + Licencia tipo B/F)
 * - 'rifle': Rifle rayado (requiere DNI + Licencia tipo D)
 * - 'escopeta': Escopeta (requiere DNI + Licencia tipo E)
 * - 'dni': Armas de aire comprimido (solo requiere DNI)
 * - Otros: Pasaporte o permiso de conducir
 *
 * INTEGRACIÓN CON LARAVEL:
 * ────────────────────────────────────────────────────────────────────────────
 * Espera que Laravel tenga configurado el endpoint:
 * POST /api/orders/validate-documents
 *
 * Y los health check endpoints:
 * GET /api/health/ping
 * GET /api/health/documents
 *
 * @author     Alsernet Development Team
 *
 * @version    1.0.0
 *
 * @since      2025-01-06
 * @see        ApiManager Para la gestión de peticiones HTTP
 * @see        DocumentsEndpointLogger Para el registro de peticiones
 * @see        EndpointAvailabilityChecker Para verificación de disponibilidad
 * @link       /modules/alsernetforms/DOCUMENTATION.md Documentación completa
 * @link       /modules/alsernetforms/README_PENDING_REQUESTS.md Sistema de peticiones pendientes
 */

include_once dirname(__FILE__).'/ApiManager.php';
include_once dirname(__FILE__).'/loggers/DocumentsEndpointLogger.php';

class DocumentValidator
{
    /**
     * Gestor de peticiones HTTP con Circuit Breaker integrado
     *
     * @var ApiManager
     *
     * @see ApiManager::sendRequest() Para verificación de disponibilidad y envío
     */
    private $apiManager;

    /**
     * Logger especializado para peticiones de tipo 'documents'
     *
     * @var DocumentsEndpointLogger
     *
     * @see DocumentsEndpointLogger::logRequest() Para registro inicial
     * @see DocumentsEndpointLogger::markAsServerUnavailable() Para marcar pendientes
     */
    private $logger;

    /**
     * Endpoint relativo de Laravel para validación de documentos
     *
     * Se concatena con apiBaseUrl configurado en ApiManager
     * URL completa: https://webadminpruebas.a-alvarez.com/api/orders/validate-documents
     *
     * @var string
     */
    private $validationEndpoint = 'api/orders/validate-documents';

    /**
     * Constructor de DocumentValidator
     *
     * Inicializa las dependencias necesarias:
     * - ApiManager: Para comunicación HTTP con Laravel
     * - DocumentsEndpointLogger: Para registro de peticiones
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->apiManager = new ApiManager;
        $this->logger = new DocumentsEndpointLogger;
    }

    /**
     * Valida documentos de un pedido con resiliencia ante fallos del servidor
     *
     * Este es el método principal de la clase. Realiza validación de documentos
     * verificando primero la disponibilidad del servidor Laravel.
     *
     * COMPORTAMIENTO SEGÚN DISPONIBILIDAD:
     * ────────────────────────────────────────────────────────────────────────────
     * ✅ Servidor disponible:
     *    - Envía petición POST a /api/orders/validate-documents
     *    - Retorna respuesta con status='success' o 'error'
     *    - Registra en BD con status='success'
     *
     * ❌ Servidor NO disponible:
     *    - NO envía petición al servidor
     *    - Guarda payload en BD con status='pending'
     *    - Retorna respuesta con status='pending' y request_id
     *    - Cron job procesará cuando servidor vuelva
     *
     * EJEMPLO DE USO:
     * ────────────────────────────────────────────────────────────────────────────
     * ```php
     * $validator = new DocumentValidator();
     *
     * // Validar documentos de arma corta
     * $result = $validator->validateDocuments(
     *     'abc123xyz',
     *     'corta',
     *     [
     *         'customer_id' => $customer->id,
     *         'order_reference' => $order->reference,
     *         'ip_address' => $_SERVER['REMOTE_ADDR']
     *     ]
     * );
     *
     * // Manejar resultado
     * switch ($result['status']) {
     *     case 'success':
     *         $missing = $result['data']['missing_documents'];
     *         if (empty($missing)) {
     *             echo "Todos los documentos están completos";
     *         } else {
     *             echo "Faltan: " . implode(', ', $missing);
     *         }
     *         break;
     *
     *     case 'pending':
     *         // Guardar request_id en sesión o cookie para verificar después
     *         $_SESSION['pending_validation_id'] = $result['request_id'];
     *         echo "Servidor temporalmente no disponible. ";
     *         echo "Tu petición se procesará automáticamente.";
     *         break;
     *
     *     case 'error':
     *         Logger::error('Validation error: ' . $result['error']);
     *         echo "Error al validar documentos: " . $result['message'];
     *         break;
     * }
     * ```
     *
     * @param  string  $uid  Identificador único del pedido/token.
     *                       Puede ser el ID del carrito, referencia del pedido,
     *                       o token personalizado.
     * @param  string|null  $documentType  Tipo de documento a validar.
     *                                     Valores válidos:
     *                                     - 'corta': Arma corta (Licencia B/F)
     *                                     - 'rifle': Rifle rayado (Licencia D)
     *                                     - 'escopeta': Escopeta (Licencia E)
     *                                     - 'dni': Solo DNI (armas aire comprimido)
     *                                     - null: Determinar automáticamente
     * @param  array  $additionalContext  Datos adicionales para el contexto.
     *                                    Campos opcionales pero recomendados:
     *                                    - 'customer_id' (int): ID del cliente
     *                                    - 'order_reference' (string): Referencia del pedido
     *                                    - 'cart_id' (int): ID del carrito
     *                                    - 'ip_address' (string): IP del usuario
     *                                    - 'user_agent' (string): Navegador del usuario
     * @return array Resultado de la validación con estructura normalizada:
     *               ```php
     *               [
     *               'status' => 'success|pending|error',
     *               'type' => 'corta|rifle|escopeta|dni',
     *               'label' => 'Handgun permit',  // Traducido
     *               'upload' => true|false,        // Si puede subir documentos
     *               'data' => [
     *               'required_documents' => ['DNI', 'Licencia B'],
     *               'uploaded_documents' => ['DNI'],
     *               'missing_documents' => ['Licencia B']
     *               ],
     *               'message' => 'Validation successful',
     *               'request_id' => 12345,         // Solo si status='pending'
     *               'reason' => 'Server timeout',  // Solo si status='pending'
     *               'error' => 'Connection failed' // Solo si status='error'
     *               ]
     *               ```
     *
     * @since  1.0.0
     * @see    ApiManager::sendRequest() Para el envío HTTP con Circuit Breaker
     * @see    parseValidationResponse() Para normalización de respuestas
     * @see    checkPendingRequestStatus() Para verificar estado de peticiones pendientes
     *
     * @example
     * // Validación básica
     * $result = $validator->validateDocuments('ORDER-123', 'dni');
     * @example
     * // Validación con contexto completo
     * $result = $validator->validateDocuments('abc123', 'corta', [
     *     'customer_id' => 456,
     *     'order_reference' => 'ORD-789',
     *     'ip_address' => '192.168.1.1'
     * ]);
     */
    public function validateDocuments($uid, $documentType = null, array $additionalContext = [])
    {
        // Preparar payload para el endpoint de validación
        $payload = [
            'uid' => $uid,
            'document_type' => $documentType,
        ];

        // Añadir contexto adicional si existe (customer_id, order_reference, etc.)
        if (! empty($additionalContext)) {
            $payload = array_merge($payload, $additionalContext);
        }

        // Intentar enviar la petición con verificación de disponibilidad del servidor
        // El ApiManager verificará automáticamente si el servidor está disponible
        // antes de enviar la petición (Circuit Breaker Pattern)
        $response = $this->apiManager->sendRequest(
            'POST',                         // Método HTTP
            $this->validationEndpoint,      // api/orders/validate-documents
            $payload,                       // Datos a enviar
            'documents',                    // Tipo de petición (para logger apropiado)
            [],                             // Headers adicionales (vacío usa defaults)
            true                            // Verificar disponibilidad (Circuit Breaker activo)
        );

        // Interpretar y normalizar la respuesta según el estado recibido
        return $this->parseValidationResponse($response, $uid, $documentType);
    }

    /**
     * Parsea y normaliza la respuesta de validación en formato estándar
     *
     * Transforma la respuesta cruda del ApiManager en una estructura consistente
     * independientemente de si la validación fue exitosa, está pendiente, o falló.
     *
     * NORMALIZACIÓN DE RESPUESTAS:
     * ────────────────────────────────────────────────────────────────────────────
     * Este método garantiza que SIEMPRE se retorne un array con la misma estructura,
     * facilitando el manejo en capas superiores (controllers, templates).
     *
     * CASOS MANEJADOS:
     * ────────────────────────────────────────────────────────────────────────────
     * 1. SUCCESS: Servidor disponible y validación completada
     *    - upload = true (puede subir documentos)
     *    - data contiene información de Laravel sobre documentos
     *
     * 2. PENDING: Servidor NO disponible, petición en cola
     *    - upload = false (no puede subir hasta que se procese)
     *    - data contiene lista de documentos requeridos (desde PrestaShop)
     *    - request_id para tracking posterior
     *
     * 3. ERROR: Servidor disponible pero validación falló
     *    - upload = false
     *    - error contiene detalles del fallo
     *
     * @param  array  $response  Respuesta cruda del ApiManager.
     *                           Contiene: status, message, response, request_id, reason
     * @param  string  $uid  UID del pedido (para logging y debugging)
     * @param  string|null  $documentType  Tipo de documento validado.
     *                                     Usado para generar etiquetas y listas de requeridos
     * @return array Respuesta normalizada con estructura consistente:
     *               - Siempre incluye: status, type, label, upload, data, message
     *               - Condicional: request_id, reason (si pending), error (si error)
     *
     * @since  1.0.0
     * @see    validateDocuments() Método que invoca este parser
     * @see    getDocumentLabel() Para obtener etiquetas traducidas
     * @see    getRequiredDocumentsByType() Para listas de documentos requeridos
     *
     * @example
     * // Respuesta de éxito
     * [
     *     'status' => 'success',
     *     'type' => 'corta',
     *     'label' => 'Permiso de arma corta',
     *     'upload' => true,
     *     'data' => ['uploaded_documents' => ['DNI', 'Licencia B']],
     *     'message' => 'Validation successful'
     * ]
     * @example
     * // Respuesta pendiente (servidor caído)
     * [
     *     'status' => 'pending',
     *     'type' => 'rifle',
     *     'upload' => false,
     *     'data' => ['missing_documents' => ['DNI', 'Licencia D']],
     *     'message' => 'Server unavailable, validation queued',
     *     'request_id' => 12345,
     *     'reason' => 'Connection timeout'
     * ]
     */
    private function parseValidationResponse(array $response, $uid, $documentType)
    {
        $status = $response['status'] ?? 'error';

        switch ($status) {
            case 'success':
                // Validación exitosa, retornar datos
                return [
                    'status' => 'success',
                    'type' => $documentType,
                    'label' => $this->getDocumentLabel($documentType),
                    'upload' => true,
                    'data' => $response['response'] ?? [],
                    'message' => 'Validation successful',
                ];

            case 'pending':
                // Servidor no disponible, petición en cola
                return [
                    'status' => 'pending',
                    'type' => $documentType,
                    'label' => $this->getDocumentLabel($documentType),
                    'upload' => false,
                    'data' => [
                        'required_documents' => $this->getRequiredDocumentsByType($documentType),
                        'uploaded_documents' => [],
                        'missing_documents' => $this->getRequiredDocumentsByType($documentType),
                    ],
                    'message' => $response['message'] ?? 'Server unavailable, validation queued',
                    'request_id' => $response['request_id'] ?? null,
                    'reason' => $response['reason'] ?? 'Unknown',
                ];

            case 'error':
            default:
                // Error en la validación
                return [
                    'status' => 'error',
                    'type' => $documentType,
                    'label' => $this->getDocumentLabel($documentType),
                    'upload' => false,
                    'data' => [
                        'required_documents' => $this->getRequiredDocumentsByType($documentType),
                        'uploaded_documents' => [],
                        'missing_documents' => $this->getRequiredDocumentsByType($documentType),
                    ],
                    'message' => $response['message'] ?? 'Validation failed',
                    'error' => $response['response']['error'] ?? 'Unknown error',
                ];
        }
    }

    /**
     * Obtiene la etiqueta traducida para un tipo de documento
     *
     * Retorna el nombre del tipo de documento en el idioma actual del contexto.
     * Utiliza el sistema de traducción de PrestaShop (Module::l()).
     *
     * TIPOS SOPORTADOS Y SUS TRADUCCIONES:
     * ────────────────────────────────────────────────────────────────────────────
     * - 'corta'    → "Handgun permit" (ES: "Permiso de arma corta")
     * - 'rifle'    → "Rifle permit" (ES: "Permiso de rifle")
     * - 'escopeta' → "Shotgun license" (ES: "Licencia de escopeta")
     * - 'dni'      → "ID document" (ES: "Documento de identidad")
     * - Otros      → "Document validation" (ES: "Validación de documento")
     *
     * @param  string|null  $documentType  Tipo de documento (corta, rifle, escopeta, dni).
     *                                     Si es null o desconocido, retorna etiqueta genérica
     * @return string Etiqueta traducida según idioma del contexto actual.
     *                La traducción se obtiene del archivo de idioma del módulo
     *
     * @since  1.0.0
     * @see    Context::getContext()->language->iso_code Para obtener idioma actual
     * @see    Module::l() Para traducción i18n
     *
     * @example
     * // Con contexto en español (iso_code = 'es')
     * $label = $this->getDocumentLabel('corta');
     * // Retorna: "Permiso de arma corta"
     * @example
     * // Con contexto en inglés (iso_code = 'en')
     * $label = $this->getDocumentLabel('rifle');
     * // Retorna: "Rifle permit"
     */
    private function getDocumentLabel($documentType)
    {
        $iso = Context::getContext()->language->iso_code;
        $module = Module::getInstanceByName('alsernetforms');

        $labels = [
            'corta' => $module->l('Handgun permit', 'DocumentValidator', $iso),
            'rifle' => $module->l('Rifle permit', 'DocumentValidator', $iso),
            'escopeta' => $module->l('Shotgun license', 'DocumentValidator', $iso),
            'dni' => $module->l('ID document', 'DocumentValidator', $iso),
        ];

        return $labels[$documentType] ?? $module->l('Document validation', 'DocumentValidator', $iso);
    }

    /**
     * Obtiene la lista de documentos requeridos según el tipo de arma/producto
     *
     * Retorna un array con los nombres traducidos de los documentos que el cliente
     * debe proporcionar según el tipo de producto que está comprando.
     *
     * LÓGICA DE NEGOCIO (LEGISLACIÓN ESPAÑOLA):
     * ────────────────────────────────────────────────────────────────────────────
     * - Arma CORTA: Requiere DNI + Licencia tipo B (armas cortas) o F (tiro olímpico)
     * - RIFLE rayado: Requiere DNI + Licencia tipo D (armas largas rayadas)
     * - ESCOPETA: Requiere DNI + Licencia tipo E (escopetas)
     * - DNI (aire comprimido): Solo requiere DNI (armas de aire comprimido)
     * - OTROS: Pasaporte o permiso de conducir (ambas caras si es tarjeta)
     *
     * IMPORTANTE: Esta lista es estática y se usa cuando el servidor NO está disponible.
     * Cuando el servidor SÍ está disponible, Laravel retorna la lista real de documentos
     * basada en el pedido específico del cliente.
     *
     * @param  string|null  $documentType  Tipo de documento/arma.
     *                                     Valores: 'corta', 'rifle', 'escopeta', 'dni', otros
     * @return array Lista de strings con nombres de documentos traducidos.
     *               Cada elemento es una descripción del documento requerido
     *
     * @since  1.0.0
     * @see    parseValidationResponse() Usa este método cuando status='pending'
     * @see    getDocumentLabel() Para etiquetas de tipos de documento
     *
     * @example
     * // Para arma corta en español
     * $docs = $this->getRequiredDocumentsByType('corta');
     * // Retorna:
     * // [
     * //     'DNI (ambas caras)',
     * //     'Permiso de arma corta (tipo B) o permiso de tiro olímpico (tipo F)'
     * // ]
     * @example
     * // Para DNI en inglés
     * $docs = $this->getRequiredDocumentsByType('dni');
     * // Retorna: ['ID (both sides)']
     */
    private function getRequiredDocumentsByType($documentType)
    {
        $iso = Context::getContext()->language->iso_code;
        $module = Module::getInstanceByName('alsernetforms');

        switch ($documentType) {
            case 'corta':
                return [
                    $module->l('ID (both sides)', 'DocumentValidator', $iso),
                    $module->l('Handgun permit (type B) or Olympic shooting permit (type F)', 'DocumentValidator', $iso),
                ];
            case 'rifle':
                return [
                    $module->l('ID (both sides)', 'DocumentValidator', $iso),
                    $module->l('Rifled long-range firearm permit (type D)', 'DocumentValidator', $iso),
                ];
            case 'escopeta':
                return [
                    $module->l('ID (both sides)', 'DocumentValidator', $iso),
                    $module->l('Shotgun license (type E)', 'DocumentValidator', $iso),
                ];
            case 'dni':
                return [
                    $module->l('ID (both sides)', 'DocumentValidator', $iso),
                ];
            default:
                return [
                    $module->l('Passport or driving licence (both sides)', 'DocumentValidator', $iso),
                ];
        }
    }

    /**
     * Verifica el estado actual de una petición pendiente
     *
     * Consulta la base de datos para obtener el estado actual de una petición
     * que fue guardada como "pending" cuando el servidor no estaba disponible.
     *
     * CASOS DE USO:
     * ────────────────────────────────────────────────────────────────────────────
     * - Usuario recibió un request_id cuando servidor estaba caído
     * - Quiere verificar si su validación ya fue procesada
     * - Dashboard de administración para monitorear peticiones
     * - Sistema de notificaciones para alertar cuando se complete
     *
     * ESTADOS POSIBLES DE UNA PETICIÓN:
     * ────────────────────────────────────────────────────────────────────────────
     * - 'pending': Aún no procesada, esperando próximo reintento
     * - 'processing': Actualmente siendo procesada por el cron
     * - 'success': Procesada exitosamente, validación completada
     * - 'failed': Alcanzó máximo de reintentos, falló permanentemente
     * - 'server_unavailable': Servidor sigue no disponible
     *
     * @param  int  $requestId  ID de la petición en la tabla alsernet_forms_requests.
     *                          Este ID se retorna en validateDocuments() cuando status='pending'
     * @return array|false Array con información completa de la petición si existe.
     *                     False si no se encuentra el ID en la base de datos.
     *
     *                     Estructura del array retornado:
     *                     ```php
     *                     [
     *                         'id_alsernetforms_request' => 12345,
     *                         'endpoint_type' => 'documents',
     *                         'method' => 'POST',
     *                         'url' => 'https://webadmin.../api/orders/validate-documents',
     *                         'payload' => '{"uid":"abc123","document_type":"corta"}',
     *                         'response' => '{"status":"success",...}',  // null si no procesado
     *                         'status' => 'pending',
     *                         'retry_count' => 2,
     *                         'max_retries' => 3,
     *                         'last_error' => 'Connection timeout',
     *                         'created_at' => '2025-01-06 10:30:00',
     *                         'synced_at' => null,                       // null si no completado
     *                         'next_retry_at' => '2025-01-06 10:45:00'
     *                     ]
     *                     ```
     *
     * @since  1.0.0
     * @see    validateDocuments() Retorna el request_id cuando status='pending'
     * @see    getPendingRequestsForUid() Para obtener todas las peticiones de un pedido
     *
     * @example
     * // Verificar si una petición pendiente ya fue procesada
     * $requestId = $_SESSION['pending_validation_id'] ?? null;
     *
     * if ($requestId) {
     *     $status = $validator->checkPendingRequestStatus($requestId);
     *
     *     if ($status) {
     *         switch ($status['status']) {
     *             case 'success':
     *                 echo "¡Validación completada exitosamente!";
     *                 unset($_SESSION['pending_validation_id']);
     *                 break;
     *
     *             case 'pending':
     *                 $retries = $status['retry_count'] . '/' . $status['max_retries'];
     *                 echo "Aún pendiente. Reintento {$retries}";
     *                 echo "Próximo intento: " . $status['next_retry_at'];
     *                 break;
     *
     *             case 'failed':
     *                 echo "Error: " . $status['last_error'];
     *                 break;
     *         }
     *     } else {
     *         echo "Petición no encontrada";
     *     }
     * }
     */
    public function checkPendingRequestStatus($requestId)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'alsernet_forms_requests
                WHERE id_alsernetforms_request = '.(int) $requestId;

        return \Db::getInstance()->getRow($sql);
    }

    /**
     * Obtiene todas las peticiones pendientes asociadas a un pedido específico
     *
     * Busca en la base de datos todas las peticiones de tipo 'documents' que estén
     * pendientes (no procesadas aún) para un UID concreto. Útil para mostrar al
     * usuario todas sus validaciones pendientes.
     *
     * CASOS DE USO:
     * ────────────────────────────────────────────────────────────────────────────
     * - Mostrar al cliente todas sus validaciones pendientes en "Mi cuenta"
     * - Dashboard de administración: ver peticiones pendientes por pedido
     * - Notificar al cliente cuando todas sus validaciones se completen
     * - Detectar si hay múltiples reintentos para el mismo pedido
     *
     * DIFERENCIA CON checkPendingRequestStatus():
     * ────────────────────────────────────────────────────────────────────────────
     * - checkPendingRequestStatus(): Busca por ID de petición (request_id)
     * - getPendingRequestsForUid(): Busca por UID de pedido (puede retornar múltiples)
     *
     * POR QUÉ PUEDE HABER MÚLTIPLES PETICIONES PARA UN UID:
     * ────────────────────────────────────────────────────────────────────────────
     * - Usuario intentó validar cuando servidor estaba caído → pending
     * - Usuario recargó página → nueva petición pending
     * - Usuario tiene varios productos que requieren diferentes documentos
     *
     * @param  string  $uid  Identificador único del pedido/token.
     *                       El mismo UID que se pasó a validateDocuments()
     * @return array Lista de peticiones pendientes para este UID.
     *               Array vacío si no hay peticiones pendientes.
     *               Ordenado por fecha de creación (más recientes primero).
     *
     *               Cada elemento contiene:
     *               ```php
     *               [
     *                   'id_alsernetforms_request' => 123,
     *                   'endpoint_type' => 'documents',
     *                   'status' => 'pending|server_unavailable',
     *                   'retry_count' => 1,
     *                   'max_retries' => 3,
     *                   'created_at' => '2025-01-06 10:30:00',
     *                   'next_retry_at' => '2025-01-06 10:35:00',
     *                   'payload' => '{"uid":"abc123",...}',
     *                   // ... otros campos
     *               ]
     *               ```
     *
     * @since  1.0.0
     * @see    validateDocuments() Método que crea estas peticiones pendientes
     * @see    checkPendingRequestStatus() Para verificar una petición específica por ID
     *
     * @example
     * // Ver todas las validaciones pendientes de un pedido
     * $uid = 'ORDER-12345';
     * $pendingValidations = $validator->getPendingRequestsForUid($uid);
     *
     * if (!empty($pendingValidations)) {
     *     echo "Tienes " . count($pendingValidations) . " validaciones pendientes:";
     *
     *     foreach ($pendingValidations as $request) {
     *         $payload = json_decode($request['payload'], true);
     *         $docType = $payload['document_type'] ?? 'unknown';
     *
     *         echo "- Tipo: {$docType}";
     *         echo "  Estado: {$request['status']}";
     *         echo "  Reintentos: {$request['retry_count']}/{$request['max_retries']}";
     *         echo "  Próximo intento: {$request['next_retry_at']}";
     *     }
     * } else {
     *     echo "No hay validaciones pendientes para este pedido";
     * }
     * @example
     * // Verificar si ALGUNA validación sigue pendiente
     * $pending = $validator->getPendingRequestsForUid($uid);
     * $allCompleted = empty($pending);
     *
     * if ($allCompleted) {
     *     // Permitir finalizar pedido
     *     echo "Todas las validaciones completadas. Puedes proceder con el pago.";
     * } else {
     *     // Bloquear checkout
     *     echo "Aún hay validaciones pendientes. Por favor espera.";
     * }
     */
    public function getPendingRequestsForUid($uid)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'alsernet_forms_requests
                WHERE endpoint_type = "documents"
                AND payload LIKE "%\"uid\":\"'.pSQL($uid).'\"%"
                AND status IN ("pending", "server_unavailable")
                ORDER BY created_at DESC';

        return \Db::getInstance()->executeS($sql);
    }
}
