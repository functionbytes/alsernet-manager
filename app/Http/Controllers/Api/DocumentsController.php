<?php

namespace App\Http\Controllers\Api;

use App\Events\Documents\DocumentReminderRequested;
use App\Events\Documents\DocumentUploaded;
use App\Models\Document\Document;
use App\Models\Prestashop\Order\Order as PrestashopOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DocumentsController extends ApiController
{
    /**
     * Sincroniza un documento con los datos de su orden e importa productos
     * Método helper reutilizable para sincronización de datos y productos
     */
    private function syncDocumentWithOrder(Document $document, PrestashopOrder $order): bool
    {
        // Obtener el cliente
        $customer = $order->customer;

        if (! $customer) {
            return false;
        }

        // Llenar los datos desnormalizados de la orden y cliente
        $document->order_reference = $order->reference ?? $document->order_reference;
        $document->order_date = $order->date_add ?? $document->order_date;

        // Obtener dirección de envío
        $deliveryAddress = $order->deliveryAddress;

        $document->customer_id = $customer->id_customer;
        // Nombre y apellido vienen de la dirección de envío
        $document->customer_firstname = $deliveryAddress?->firstname ?? $customer->firstname;
        $document->customer_lastname = $deliveryAddress?->lastname ?? $customer->lastname;
        $document->customer_email = $customer->email;
        // DNI/SIRET vienen de la dirección de envío
        $document->customer_dni = $deliveryAddress?->dni ?? $deliveryAddress?->vat_number ?? null;
        // Empresa viene de la dirección de envío
        $document->customer_company = $deliveryAddress?->company ?? null;
        // Teléfono celular viene de la dirección de envío
        $document->customer_cellphone = $deliveryAddress?->phone_mobile ?? null;

        $document->save();

        // Importar productos del carrito
        $document->captureProducts();

        return true;
    }

    public function process(Request $request)
    {

        $action = $request->input('action');

        $data = $request->all();

        switch ($action) {
            case 'verification':
                return $this->documentVerification($data);
            case 'validate':
                return $this->documentValidates($data);
            case 'request':
                return $this->documentRequests($data);
            case 'upload':
                return $this->documentUpload($request);
            case 'delete':
                return $this->documentDelete($data);
            default:
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Invalid action type',
                ], 400);
        }
    }

    public function documentRequests($data)
    {
        try {
            // Obtener order_id (compatible con múltiples formatos)
            $orderId = $data['order_id'] ?? $data['order'] ?? null;

            // Validar que existe order_id
            if (! $orderId) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Missing order_id parameter',
                ], 400);
            }

            // Validar que no existe un documento duplicado
            $existingDocument = Document::where('order_id', $orderId)->first();
            if ($existingDocument) {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Order {$orderId} already has a document request",
                    'data' => [
                        'uid' => $existingDocument->uid,
                    ],
                ], 409);  // Conflict
            }

            // ===== Language Mapping Logic =====
            $langId = null;

            // If iso_code is provided, map to Laravel lang_id
            if (isset($data['iso_code']) && ! empty($data['iso_code'])) {
                $isoCode = strtolower(trim($data['iso_code']));

                // Find Laravel language by iso_code
                $laravelLang = \App\Models\Lang::iso($isoCode);

                if ($laravelLang) {
                    $langId = $laravelLang->id;
                    \Log::info("Language mapped: PrestaShop iso_code '{$isoCode}' → Laravel lang_id {$langId}");
                } else {
                    // Log warning and fallback to default
                    \Log::warning("Language mapping failed: iso_code '{$isoCode}' not found in Laravel langs table");
                    $defaultLang = \App\Models\Lang::iso('es');
                    $langId = $defaultLang ? $defaultLang->id : null;
                }
            }

            // Crear nuevo documento
            $document = new Document;
            $document->order_id = $orderId;
            $document->type = $data['type'] ?? 'general';
            $document->source = 'api';  // Origen: API Prestashop
            $document->proccess = 0;    // Estado inicial: pendiente
            $document->lang_id = $langId;  // Assign language

            // Obtener datos del cliente si vienen en el payload
            if (isset($data['customer']) && is_array($data['customer'])) {
                $document->customer_id = $data['customer']['id_customer'] ?? $data['customer']['id'] ?? null;
                $document->customer_firstname = $data['customer']['firstname'] ?? null;
                $document->customer_lastname = $data['customer']['lastname'] ?? null;
                $document->customer_email = $data['customer']['email'] ?? null;
                // DNI/SIRET viene de la dirección en Prestashop
                $document->customer_dni = $data['customer']['siret'] ?? $data['customer']['document_type'] ?? null;
                $document->customer_company = $data['customer']['company'] ?? null;
                // Teléfono viene de la dirección de envío
                $document->customer_cellphone = $data['customer']['phone_mobile'] ?? $data['customer']['phone'] ?? null;
            }

            // Obtener datos de la orden
            $document->cart_id = $data['cart_id'] ?? $data['cart'] ?? null;
            $document->order_reference = $data['reference'] ?? null;
            $document->order_date = $data['date_add'] ?? null;

            // Guardar documento
            $document->save();

            // Guardar productos si vienen en el payload
            $productsCount = 0;
            if (isset($data['products']) && is_array($data['products'])) {
                foreach ($data['products'] as $product) {
                    // Mapear campos de Prestashop correctamente
                    $productId = $product['product_id'] ?? $product['id'] ?? null;
                    $productName = $product['product_name'] ?? $product['name'] ?? null;
                    $productReference = $product['product_reference'] ?? $product['reference'] ?? null;
                    $quantity = (int) ($product['product_quantity'] ?? $product['quantity'] ?? 0);
                    $price = (float) ($product['unit_price_tax_incl'] ?? $product['price'] ?? 0);

                    if ($productId && $productName) {
                        $document->products()->create([
                            'product_id' => $productId,
                            'product_name' => $productName,
                            'product_reference' => $productReference,
                            'quantity' => $quantity,
                            'price' => $price,
                        ]);
                        $productsCount++;
                    }
                }
            }

            // Detectar tipo de documento basado en los productos guardados
            if ($productsCount > 0) {
                $detectedType = $document->detectDocumentType();
                $document->type = $detectedType;
                $document->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => "Document request created successfully for order {$orderId}",
                'data' => [
                    'uid' => $document->uid,
                    'order_id' => $document->order_id,
                    'type' => $document->type,
                    'lang_id' => $document->lang_id,
                    'iso_code' => $data['iso_code'] ?? null,
                    'synced' => 1,
                    'products_count' => $productsCount,
                    'customer_name' => trim(($document->customer_firstname ?? '').' '.($document->customer_lastname ?? '')) ?: 'N/A',
                ],
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating document request: '.$e->getMessage(), [
                'exception' => $e,
                'data' => $data,
            ]);

            return response()->json([
                'status' => 'failed',
                'message' => 'Error creating document request: '.$e->getMessage(),
            ], 500);
        }
    }

    public function documentVerification($data)
    {

        $document = Document::orders($data['order']);

        return response()->json([
            'status' => 'success',
            'message' => 'You have document from general emails.',
            'data' => [
                'uid' => $document->uid,
                'reference' => $document->label,
                'type' => $document->type,
            ],

        ], 200);

    }

    public function documentValidates($data)
    {
        $uid = $data['uid'] ?? null;

        if (! $uid) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Missing uid parameter',
            ], 400);
        }

        $document = Document::uid($uid);

        if (! $document) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Document not found',
            ], 404);
        }

        // Actualizar JSON de documentos requeridos si no existe
        if (empty($document->required_documents)) {
            $document->updateRequiredDocumentsJson();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Document validation successful',
            'data' => [
                'uid' => $document->uid,
                'type' => $document->type ?? 'general',
                'label' => $document->order_reference ?? $document->order_id,
                'can_upload' => is_null($document->confirmed_at),
                'required_documents' => $document->getRequiredDocuments(),
                'uploaded_documents' => $document->getUploadedDocumentTypes(),
                'missing_documents' => $document->getMissingDocuments(),
                'is_complete' => $document->hasAllRequiredDocuments(),
            ],
        ], 200);
    }

    public function documentUpload(Request $request)
    {
        try {
            $uid = $request->input('uid');

            if (! $uid) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Missing uid parameter',
                ], 400);
            }

            $document = Document::uid($uid);

            if (! $document) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Document not found',
                ], 404);
            }

            // Obtener archivos y tipos de documento
            $files = $request->file('file');
            $documentTypes = $request->input('document_types', []);

            if (! $files) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No files provided',
                ], 400);
            }

            // Convertir a array si es archivo único
            if (! is_array($files)) {
                $files = [$files];
                $documentTypes = is_array($documentTypes) ? $documentTypes : [$documentTypes];
            }

            // Validar que hay tipos para cada archivo
            if (count($files) !== count($documentTypes)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Each file must have a document type specified',
                ], 400);
            }

            // Procesar cada archivo con su tipo
            foreach ($files as $index => $file) {
                $docType = $documentTypes[$index] ?? 'documento';

                // Eliminar archivo previo del mismo tipo si existe
                $existingMedia = $document->getMedia('documents')
                    ->filter(fn ($media) => $media->getCustomProperty('document_type') === $docType)
                    ->first();

                if ($existingMedia) {
                    $existingMedia->delete();
                }

                // Subir nuevo archivo con tipo identificado
                $document->addMedia($file)
                    ->withCustomProperties(['document_type' => $docType])
                    ->toMediaCollection('documents');
            }

            // Actualizar JSON de documentos subidos
            $document->syncUploadedDocumentsJson();

            // Marcar como confirmado solo si todos los docs están completos
            if ($document->hasAllRequiredDocuments() && ! $document->confirmed_at) {
                $document->confirmed_at = Carbon::now()->setTimezone('Europe/Madrid');
            }

            $document->source = $request->input('source', 'api');
            $document->save();

            // Disparar evento
            event(new DocumentUploaded($document));

            return response()->json([
                'status' => 'success',
                'message' => 'Documents uploaded successfully',
                'data' => [
                    'uploaded_documents' => $document->uploaded_documents,
                    'missing_documents' => $document->getMissingDocuments(),
                    'is_complete' => $document->hasAllRequiredDocuments(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error uploading documents: '.$e->getMessage(),
            ], 500);
        }
    }

    public function documentDelete($data)
    {
        try {
            $uid = $data['uid'] ?? null;
            $docType = $data['doc_type'] ?? null;

            if (! $uid || ! $docType) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Missing uid or doc_type parameter',
                ], 400);
            }

            $document = Document::uid($uid);

            if (! $document) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Document not found',
                ], 404);
            }

            // Encontrar y eliminar archivo del tipo especificado
            $mediaToDelete = $document->getMedia('documents')
                ->filter(fn ($media) => $media->getCustomProperty('document_type') === $docType)
                ->first();

            if (! $mediaToDelete) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Document type not found',
                ], 404);
            }

            // Eliminar el archivo
            $mediaToDelete->delete();

            // Actualizar JSON de documentos subidos
            $document->syncUploadedDocumentsJson();

            return response()->json([
                'status' => 'success',
                'message' => 'Document deleted successfully',
                'data' => [
                    'uploaded_documents' => $document->uploaded_documents,
                    'missing_documents' => $document->getMissingDocuments(),
                    'is_complete' => $document->hasAllRequiredDocuments(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error deleting document: '.$e->getMessage(),
            ], 500);
        }
    }

    public function resendDocumentReminder(Request $request)
    {
        $request->validate([
            'uid' => 'required|string',
        ]);

        $document = Document::uid($request->input('uid'));

        if (! $document) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No document found with this UID.',
            ], 404);
        }

        event(new DocumentReminderRequested($document));

        $document->reminder_at = now();
        $document->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Document reminder email sent successfully.',
        ], 200);
    }

    public function confirmDocumentUpload(Request $request)
    {
        $request->validate([
            'uid' => 'required|string',
        ]);

        $document = Document::uid($request->input('uid'));

        if (! $document) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No document found with this UID.',
            ], 404);
        }

        if (! $document->confirmed_at || $document->media->count() === 0) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Document has not been uploaded yet.',
            ], 400);
        }

        $document->confirmed_at = now();
        $document->proccess = 1;
        $document->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Document upload confirmed successfully.',
        ], 200);
    }

    /**
     * Obtiene datos de la orden y cliente para llenar el documento
     * Consulta los datos en Prestashop y los devuelve para desnormalización
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderData(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
        ]);

        $orderId = $request->input('order_id');

        // Obtener la orden de Prestashop
        $order = PrestashopOrder::find($orderId);

        if (! $order) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Order not found in Prestashop.',
            ], 404);
        }

        // Obtener el cliente
        $customer = $order->customer;

        if (! $customer) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Customer associated with order not found.',
            ], 404);
        }

        // Retornar todos los datos necesarios para desnormalización
        return response()->json([
            'status' => 'success',
            'message' => 'Order data retrieved successfully.',
            'data' => [
                // Datos de la orden
                'order_id' => $order->id_order,
                'order_reference' => $order->reference,
                'order_date' => $order->date_add,
                'order_cart_id' => $order->id_cart,

                // Datos del cliente
                'customer_id' => $customer->id_customer,
                'customer_firstname' => $order->deliveryAddress?->firstname ?? $customer->firstname,
                'customer_lastname' => $order->deliveryAddress?->lastname ?? $customer->lastname,
                'customer_email' => $customer->email,
                'customer_dni' => $order->deliveryAddress?->dni ?? $order->deliveryAddress?->vat_number ?? null,
                'customer_company' => $order->deliveryAddress?->company ?? null,
                'customer_cellphone' => $order->deliveryAddress?->phone_mobile ?? null,
            ],
        ], 200);
    }

    /**
     * Llena automáticamente los datos desnormalizados de un documento
     * usando los datos de la orden y cliente de Prestashop
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fillDocumentWithOrderData(Request $request)
    {
        $request->validate([
            'uid' => 'required|string',
            'order_id' => 'required|integer',
        ]);

        $uid = $request->input('uid');
        $orderId = $request->input('order_id');

        // Obtener el documento
        $document = Document::uid($uid);

        if (! $document) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Document not found.',
            ], 404);
        }

        // Obtener la orden
        $order = PrestashopOrder::find($orderId);

        if (! $order) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Order not found in Prestashop.',
            ], 404);
        }

        // Obtener el cliente
        $customer = $order->customer;

        if (! $customer) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Customer not found.',
            ], 404);
        }

        // Llenar los datos desnormalizados
        $document->order_reference = $order->reference;
        $document->order_id = $order->id_order;
        $document->order_date = $order->date_add;

        // Obtener dirección de envío
        $deliveryAddress = $order->deliveryAddress;

        $document->customer_id = $customer->id_customer;
        // Nombre y apellido vienen de la dirección de envío
        $document->customer_firstname = $deliveryAddress?->firstname ?? $customer->firstname;
        $document->customer_lastname = $deliveryAddress?->lastname ?? $customer->lastname;
        $document->customer_email = $customer->email;
        // DNI/SIRET vienen de la dirección de envío
        $document->customer_dni = $deliveryAddress?->dni ?? $deliveryAddress?->vat_number ?? null;
        // Empresa viene de la dirección de envío
        $document->customer_company = $deliveryAddress?->company ?? null;
        // Teléfono celular viene de la dirección de envío
        $document->customer_cellphone = $deliveryAddress?->phone_mobile ?? null;

        $document->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Document filled with order data successfully.',
            'data' => [
                'uid' => $document->uid,
                'order_reference' => $document->order_reference,
                'customer_name' => "{$document->customer_firstname} {$document->customer_lastname}",
                'customer_email' => $document->customer_email,
            ],
        ], 200);
    }

    /**
     * Sincroniza todos los documentos existentes con los datos de sus órdenes
     * Busca documentos sin datos desnormalizados y los llena desde Prestashop
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncAllDocumentsWithOrders()
    {
        try {
            $synced = 0;
            $failed = 0;
            $errors = [];

            // Obtener documentos sin datos desnormalizados
            $documents = Document::get();

            if ($documents->isEmpty()) {

                return response()->json([
                    'status' => 'success',
                    'message' => 'No documents need synchronization.',
                    'data' => [
                        'synced' => 0,
                        'failed' => 0,
                        'total' => 0,
                    ],
                ], 200);
            }

            foreach ($documents as $document) {
                try {
                    // Obtener la orden
                    $order = PrestashopOrder::find($document->order_id);

                    if (! $order) {
                        $failed++;
                        $errors[] = [
                            'uid' => $document->uid,
                            'order_id' => $document->order_id,
                            'reason' => 'Order not found in Prestashop',
                        ];

                        continue;
                    }

                    // Sincronizar datos de la orden y productos usando el helper
                    if (! $this->syncDocumentWithOrder($document, $order)) {
                        $failed++;
                        $errors[] = [
                            'uid' => $document->uid,
                            'order_id' => $document->order_id,
                            'reason' => 'Customer not found',
                        ];

                        continue;
                    }

                    $synced++;

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'uid' => $document->uid,
                        'order_id' => $document->order_id,
                        'reason' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => "Synchronization completed. {$synced} documents synced, {$failed} failed.",
                'data' => [
                    'synced' => $synced,
                    'failed' => $failed,
                    'total' => $documents->count(),
                    'errors' => $failed > 0 ? $errors : [],
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Synchronization failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sincroniza documentos de una orden específica por query parameter
     * Recibe order_id como parámetro query e importa todos los datos y productos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncDocumentsByOrderQuery(Request $request)
    {
        $orderId = $request->query('id_order');

        if (! $orderId) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Missing required query parameter: id_order',
            ], 400);
        }

        try {
            // Obtener documentos asociados a la orden
            $documents = Document::where('order_id', $orderId)
                ->get();

            if ($documents->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No documents found for this order ID.',
                ], 404);
            }

            // Obtener la orden
            $order = PrestashopOrder::find($orderId);

            if (! $order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Order not found in Prestashop.',
                ], 404);
            }

            $synced = 0;
            $failed = 0;
            $errors = [];

            // Sincronizar todos los documentos de esta orden
            foreach ($documents as $document) {
                try {
                    if (! $this->syncDocumentWithOrder($document, $order)) {
                        $failed++;
                        $errors[] = [
                            'uid' => $document->uid,
                            'reason' => 'Customer not found',
                        ];

                        continue;
                    }
                    $synced++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'uid' => $document->uid,
                        'reason' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => "Successfully synced {$synced} document(s) for order {$orderId}.",
                'data' => [
                    'order_id' => $orderId,
                    'synced' => $synced,
                    'failed' => $failed,
                    'total' => $documents->count(),
                    'order_reference' => $order->reference,
                    'customer_name' => $order->customer ? "{$order->customer->firstname} {$order->customer->lastname}" : null,
                    'errors' => $failed > 0 ? $errors : [],
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Synchronization failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sincroniza un documento específico con los datos de su orden
     * Busca por order_id y llena todos los datos desnormalizados
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncDocumentByOrderId(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
        ]);

        $orderId = $request->input('order_id');

        try {
            // Obtener documentos asociados a la orden
            $documents = Document::where('order_id', $orderId)
                ->get();

            if ($documents->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No documents found for this order ID.',
                ], 404);
            }

            // Obtener la orden
            $order = PrestashopOrder::find($orderId);

            if (! $order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Order not found in Prestashop.',
                ], 404);
            }

            // Obtener el cliente
            $customer = $order->customer;

            if (! $customer) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Customer not found.',
                ], 404);
            }

            $synced = 0;

            // Sincronizar todos los documentos de esta orden
            foreach ($documents as $document) {
                $document->order_reference = $order->reference ?? $document->order_reference;
                $document->order_date = $order->date_add ?? $document->order_date;

                // Obtener dirección de envío
                $deliveryAddress = $order->deliveryAddress;

                $document->customer_id = $customer->id_customer;
                // Nombre y apellido vienen de la dirección de envío
                $document->customer_firstname = $deliveryAddress?->firstname ?? $customer->firstname;
                $document->customer_lastname = $deliveryAddress?->lastname ?? $customer->lastname;
                $document->customer_email = $customer->email;
                // DNI/SIRET vienen de la dirección de envío
                $document->customer_dni = $deliveryAddress?->dni ?? $deliveryAddress?->vat_number ?? null;
                // Empresa viene de la dirección de envío
                $document->customer_company = $deliveryAddress?->company ?? null;
                // Teléfono celular viene de la dirección de envío
                $document->customer_cellphone = $deliveryAddress?->phone_mobile ?? null;

                $document->save();
                $synced++;
            }

            return response()->json([
                'status' => 'success',
                'message' => "Successfully synced {$synced} document(s) for order {$orderId}.",
                'data' => [
                    'order_id' => $orderId,
                    'synced' => $synced,
                    'order_reference' => $order->reference,
                    'customer_name' => "{$customer->firstname} {$customer->lastname}",
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Synchronization failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function orderPaid(Request $request)
    {
        $payload = $request->validate([
            'order_id' => 'required|integer',
            'document_type' => 'nullable|string|max:100',
            'force_reminder' => 'sometimes|boolean',
        ]);

        $order = PrestashopOrder::find($payload['order_id']);

        if (! $order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found in Prestashop database.',
            ], 404);
        }

        $document = Document::firstOrNew(['order_id' => $order->id_order]);
        $document->customer_id = $document->customer_id ?? $order->id_customer;
        $document->cart_id = $document->cart_id ?? $order->id_cart;
        $document->type = $payload['document_type'] ?? $document->type ?? 'general';
        $document->reference = $order->reference ?? $document->reference;

        if (! $document->exists) {
            $document->save();
        } else {
            $document->save();
        }

        if ($document->confirmed_at) {
            return response()->json([
                'status' => 'success',
                'message' => 'Document already uploaded. No further action needed.',
                'data' => [
                    'uid' => $document->uid,
                    'order_id' => $document->order_id,
                ],
            ], 200);
        }

        $forceReminder = (bool) ($payload['force_reminder'] ?? false);

        if (! $forceReminder && $document->reminder_at) {
            return response()->json([
                'status' => 'success',
                'message' => 'Reminder already sent previously.',
                'data' => [
                    'uid' => $document->uid,
                    'order_id' => $document->order_id,
                ],
            ], 200);
        }

        event(new DocumentReminderRequested($document));

        $document->reminder_at = now();
        $document->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Document reminder queued successfully.',
            'data' => [
                'uid' => $document->uid,
                'order_id' => $document->order_id,
            ],
        ], 200);
    }
}
