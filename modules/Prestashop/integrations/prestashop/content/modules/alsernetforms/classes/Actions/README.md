# Actions System

Sistema de acciones reutilizable para comunicación con endpoints de Laravel.

## Arquitectura

```
BaseAction (abstract)
    ├─ DocumentAction
    ├─ FormAction
    ├─ ChatAction
    └─ [TuNuevaAction]
         ↓
    ApiManager
         ├─ Verificación de disponibilidad (EndpointAvailabilityChecker)
         ├─ Logging automático (DocumentsEndpointLogger, FormEndpointLogger, etc.)
         └─ Circuit breaker con reintentos
```

## Patrón Template Method

`BaseAction` usa el patrón Template Method:

1. **`execute()`** - Método final que coordina el flujo
   - Llama a `ApiManager::sendRequest()`
   - Pasa resultado a `mapResponse()`

2. **`mapResponse()`** - Método abstracto que cada acción implementa
   - Transforma respuesta de ApiManager
   - Retorna estructura estándar

## Crear una Nueva Acción

### 1. Crear la clase

```php
<?php

include_once dirname(__FILE__).'/BaseAction.php';

class MyAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();

        $this->endpoint = 'api/my-endpoint';      // Endpoint de Laravel
        $this->actionType = 'my_action';           // Tipo para logger
    }

    public function doSomething($param1, $param2, array $context = [])
    {
        $payload = [
            'action' => 'do_something',
            'param1' => $param1,
            'param2' => $param2,
        ];

        return $this->execute($payload, $context);
    }

    protected function mapResponse(array $response)
    {
        $responseData = $response['response'] ?? [];

        return [
            'status' => $response['status'],        // 'success' | 'pending' | 'error'
            'request_id' => $response['request_id'] ?? null,
            'data' => [
                // Mapear campos específicos de tu acción
                'result' => $responseData['data']['result'] ?? null,
            ],
            'error' => $response['message'] ?? null,
        ];
    }
}
```

### 2. Usar en PrestaShop

```php
<?php

include_once dirname(__FILE__).'/classes/Actions/MyAction.php';

$action = new MyAction;
$result = $action->doSomething('value1', 'value2', [
    'customer_id' => $this->context->customer->id,
]);

// Resultado siempre tiene esta estructura:
if ($result['status'] === 'success') {
    // Procesado inmediatamente
    $data = $result['data'];
} elseif ($result['status'] === 'pending') {
    // Servidor no disponible, guardado en BD
    // Cron lo procesará automáticamente
    echo "Tu solicitud está en proceso";
} elseif ($result['status'] === 'error') {
    // Error
    echo $result['error'];
}
```

## Ventajas

✅ **Reutilizable** - Template Method automatiza el flujo

✅ **Consistente** - Todas las acciones retornan la misma estructura

✅ **Confiable** - ApiManager + Loggers manejan disponibilidad automáticamente

✅ **Escalable** - Agregar nuevas acciones es trivial

✅ **Testeable** - Solo necesitas testear `mapResponse()`

## Estructura de Respuesta (Estándar)

```php
[
    'status' => 'success|pending|error',      // Estado de la operación
    'request_id' => 123,                      // ID de la petición (para tracking)
    'data' => [
        // Campos específicos de cada acción
        // Mapear según la respuesta de Laravel
    ],
    'error' => 'Mensaje de error o null',     // Solo si hay error
]
```

## Ejemplos Reales

### DocumentAction

```php
$action = new DocumentAction;
$result = $action->validateToken($token, ['customer_id' => $customerId]);

// Retorna:
[
    'status' => 'success|pending|error',
    'data' => [
        'uid' => '...',
        'document_type' => 'dni|corta|rifle|...',
        'label' => 'Validación de documento',
        'required_documents' => [...],
    ],
]
```

### FormAction

```php
$action = new FormAction;
$result = $action->submitForm(['name' => 'John', 'email' => 'john@example.com'], 'contact');

// Retorna:
[
    'status' => 'success|pending|error',
    'data' => [
        'message' => 'Formulario recibido',
        'submission_id' => 456,
        'validation_errors' => [],
    ],
]
```

### ChatAction

```php
$action = new ChatAction;
$result = $action->sendMessage('Hola', $conversationId);

// Retorna:
[
    'status' => 'success|pending|error',
    'data' => [
        'conversation_id' => '...',
        'last_message_id' => 789,
    ],
]
```

## Cómo Funciona ApiManager

1. **Verifica disponibilidad** via `EndpointAvailabilityChecker`
   - Si NO disponible → return `status='pending'` (guardado en logger)
   - Si SÍ disponible → continúa

2. **Envía petición HTTP** POST a `$endpoint` con `$payload`

3. **Retorna estructura estándar**:
   ```php
   [
       'status' => 'success|error|pending',
       'message' => 'Mensaje',
       'response' => [...datos...],       // JSON decodificado
       'request_id' => int|null,
   ]
   ```

4. **Registra automáticamente** en el logger apropiado
   - `DocumentsEndpointLogger` para `type='documents'`
   - `FormEndpointLogger` para `type='forms'`
   - etc.

## Logging Automático

Cada acción es registrada automáticamente en la BD con:

- `endpoint_type` - Tipo de acción ('documents', 'forms', 'chat')
- `status` - 'success', 'pending', 'failed', 'server_unavailable'
- `payload` - Datos enviados
- `response` - Respuesta recibida
- `retry_count` - Número de reintentos
- `next_retry_at` - Cuándo reintentar si falló

Esto permite:
- Auditar todas las peticiones
- Reintentarlas automáticamente si fallan
- Rastrear errores
- Debugging

## Circuit Breaker Pattern

Si un endpoint falla consecutivamente (>3 veces):
- Se marca como `unavailable`
- Las peticiones se guardan como `pending`
- Se reintenta después de 5 minutos
- Cuando se recupera, procesa automáticamente todo lo pendiente
