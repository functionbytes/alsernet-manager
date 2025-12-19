<?php

namespace App\Http\Controllers\Administratives\Documents;

use App\Events\Document\DocumentCreated;
use App\Events\Document\DocumentStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Managers\Settings\Documents\DocumentConfigurationController;
use App\Jobs\Documents\MailTemplateJob;
use App\Models\Document\Document;
use App\Models\Document\DocumentLoad;
use App\Models\Document\DocumentNote;
use App\Models\Document\DocumentSource;
use App\Models\Document\DocumentStatus;
use App\Models\Document\DocumentStatusTransition;
use App\Models\Document\DocumentSync;
use App\Models\Document\DocumentType;
use App\Models\Document\DocumentUploadType;
use App\Models\Mail\MailTemplate;
use App\Models\Prestashop\Orders\Order as PrestashopOrder;
use App\Models\Prestashop\Orders\OrderSendErp;
use App\Models\Setting;
use App\Services\Documents\DocumentActionService;
use App\Services\Documents\DocumentEmailService;
use App\Services\Documents\DocumentTypeService;
use App\Services\Integrations\ErpService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DocumentsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim(strtolower($request->get('search')));
        $statusId = $request->get('status_id');
        $loadId = $request->get('load_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $perPage = paginationNumber();

        $documents = Document::filterListing($search, null, $dateFrom, $dateTo)
            ->when($statusId, fn ($q) => $q->where('status_id', $statusId))
            ->when($loadId, fn ($q) => $q->where('load_id', $loadId))
            ->paginate($perPage);

        // Get statuses and loads for filters
        $statuses = DocumentStatus::where('is_active', true)->orderBy('order')->get();
        $loads = DocumentLoad::where('is_active', true)->orderBy('order')->get();

        return view('administratives.views.documents.index')->with([
            'documents' => $documents,
            'searchKey' => $search,
            'statusId' => $statusId,
            'loadId' => $loadId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'statuses' => $statuses,
            'loads' => $loads,
        ]);
    }

    /**
     * Mostrar solo documentos pendientes
     */
    public function pending(Request $request)
    {
        $search = trim(strtolower($request->get('search')));
        $loadId = $request->get('load_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $perPage = paginationNumber();

        // Get pending status ID
        $pendingStatus = DocumentStatus::where('key', 'pending')->first();

        // Filter only documents with pending status
        $documents = Document::filterListing($search, null, $dateFrom, $dateTo)
            ->where('status_id', $pendingStatus?->id)
            ->when($loadId, fn ($q) => $q->where('load_id', $loadId))
            ->paginate($perPage);

        // Get statuses and loads for filters
        $statuses = DocumentStatus::where('is_active', true)->orderBy('order')->get();
        $loads = DocumentLoad::where('is_active', true)->orderBy('order')->get();

        return view('administratives.views.documents.pending')->with([
            'documents' => $documents,
            'searchKey' => $search,
            'loadId' => $loadId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'statuses' => $statuses,
            'loads' => $loads,
        ]);
    }

    /**
     * Show import options index page
     */
    public function importIndex()
    {
        return view('administratives.views.documents.import.index');
    }

    /**
     * Show form to import orders from PrestaShop API
     */
    public function importApi()
    {
        return view('administratives.views.documents.import.api');
    }

    /**
     * Show form to import orders from ERP
     */
    public function importErp()
    {
        return view('administratives.views.documents.import.erp');
    }

    /**
     * Import orders from ERP and create documents
     * Consumes ERP API directly using serie + npedidocli
     */
    public function syncFromErp(Request $request)
    {
        // Check if ERP integration is enabled
        if (Setting::get('erp_integration_enabled') !== 'yes') {
            return response()->json([
                'status' => 'failed',
                'message' => 'ERP integration service is not enabled. Please contact your administrator.',
            ], 403);
        }

        // Check if document import is enabled
        if (Setting::get('erp_import_documents') !== 'yes') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Document import from ERP is disabled. Please contact your administrator.',
            ], 403);
        }

        // Get serie and npedidocli from request
        $serie = $request->input('serie') ?? $request->query('serie');
        $npedidocli = $request->input('npedidocli') ?? $request->query('npedidocli');

        if (! $serie || ! $npedidocli) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Missing serie or npedidocli parameter',
            ], 400);
        }

        // Create unique order identifier
        $orderIdentifier = "{$serie}/{$npedidocli}";

        try {
            // Get ERP source, load, and sync IDs
            $erpSource = DocumentSource::where('key', 'erp')->first();
            $erpSourceId = $erpSource?->id;

            $erpLoad = DocumentLoad::where('key', 'erp')->first();
            $erpLoadId = $erpLoad?->id;

            // Manual sync (imported from admin panel)
            $manualSync = DocumentSync::where('key', 'manual')->first();
            $manualSyncId = $manualSync?->id;

            // Check if document already exists by order_reference or order_id + source_id
            $existingDoc = Document::where('order_reference', $orderIdentifier)
                ->orWhere(function ($query) use ($npedidocli, $erpSourceId) {
                    $query->where('order_id', $npedidocli)
                        ->where('source_id', $erpSourceId);
                })
                ->first();

            if ($existingDoc) {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Pedido {$orderIdentifier} ya existe como documento.",
                    'data' => [
                        'order_id' => $orderIdentifier,
                        'document_uid' => $existingDoc->uid,
                    ],
                ], 400);
            }

            // Get ERP service to fetch order data using serie + npedidocli
            $erpService = app(ErpService::class);
            $orderData = $erpService->recuperarPedido($npedidocli, $serie);

            if (! $orderData) {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Pedido {$orderIdentifier} no encontrado en el ERP.",
                ], 404);
            }

            // Create new document from ERP data
            $document = $this->createDocumentFromErpData($orderIdentifier, $orderData, $erpSourceId, $erpLoadId, $manualSyncId);

            if (! $document) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Error al crear documento desde datos del ERP',
                ], 500);
            }

            // Load products
            $productsCount = $document->products()->count();

            // Get total from order data
            $resource = $orderData['resource'] ?? $orderData;
            $total = $resource['total_con_impuestos'] ?? '0.00';

            return response()->json([
                'status' => 'success',
                'message' => "Pedido {$orderIdentifier} importado correctamente del ERP.",
                'data' => [
                    'order_id' => $document->order_id,
                    'document_uid' => $document->uid,
                    'synced' => 1,
                    'failed' => 0,
                    'products_count' => $productsCount,
                    'customer_name' => trim($document->customer_firstname.' '.$document->customer_lastname),
                    'order_reference' => $document->order_reference,
                    'total' => $total,
                ],
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error importing from ERP: '.$e->getMessage(), [
                'serie' => $serie,
                'npedidocli' => $npedidocli,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'failed',
                'message' => 'Import failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a Document from ERP order data
     * Maps the XML structure from Gestión ERP API
     *
     * Expected structure:
     * - resource.cliente.nombre, resource.cliente.apellidos
     * - resource.cliente.email, resource.cliente.cif
     * - resource.envio.telefono, resource.envio.calle, etc.
     * - resource.fpedido, resource.idpedidocli, resource.npedidocli
     * - resource.lineas_pedido_cliente.resource[].articulo
     */
    private function createDocumentFromErpData(string $orderIdentifier, array $orderData, ?int $sourceId = null, ?int $loadId = null, ?int $syncId = null): ?Document
    {
        try {
            // The API returns data wrapped in 'resource' key
            $resource = $orderData['resource'] ?? $orderData;

            // Extract customer info from ERP structure
            $cliente = $resource['cliente'] ?? [];
            $envio = $resource['envio'] ?? [];

            $firstName = $cliente['nombre'] ?? 'Unknown';
            $lastName = $cliente['apellidos'] ?? '';
            $customerEmail = $cliente['email'] ?? null;
            $customerDni = $cliente['cif'] ?? null;
            $customerPhone = $envio['telefono'] ?? null;

            // Extract order info
            $idPedidoCli = $resource['idpedidocli'] ?? null;
            $npedidocli = $resource['npedidocli'] ?? null;
            $fpedido = $resource['fpedido'] ?? now()->format('Y-m-d');
            $total = $resource['total_con_impuestos'] ?? 0;
            $serie = $resource['serie']['descripcorta'] ?? date('Y');

            // Extract shipping address
            $shippingAddress = trim(implode(', ', array_filter([
                $envio['calle'] ?? '',
                $envio['num'] ?? '',
                $envio['cp'] ?? '',
                $envio['localidad'] ?? '',
                $envio['provincia'] ?? '',
                $envio['pais'] ?? '',
            ])));

            // Create document using existing fields
            // source_id differentiates ERP from PrestaShop orders
            // load_id indicates the loading method (erp import)
            // sync_id indicates manual sync (from admin panel)
            $document = new Document;
            $document->order_id = $idPedidoCli;
            $document->customer_id = $cliente['idcliente'] ?? null;
            $document->type = 'order';
            $document->source_id = $sourceId; // ERP source ID

            // Set source_id to 'api' (data from PrestaShop API)
            $apiSource = DocumentSource::where('key', 'api')->first();
            $document->source_id = $apiSource?->id;

            // Set load_id to 'api' (loaded from PrestaShop)
            $apiLoad = DocumentLoad::where('key', 'erp')->first();
            $document->load_id = $apiLoad?->id;

            // Set sync_id to 'automatic'
            $automaticSync = DocumentSync::where('key', 'automatic')->first();
            $document->sync_id = $automaticSync?->id;

            // Set upload_id to 'manual' (admin uploads files)
            $manualUpload = DocumentUploadType::where('key', 'automatic')->first();
            $document->upload_id = $manualUpload?->id;

            // Set initial status to 'pending'
            $pendingStatus = DocumentStatus::where('key', 'pending')->first();
            $document->status_id = $pendingStatus?->id;

            $document->lang_id = 1; // español
            $document->proccess = 0;
            $document->customer_firstname = $firstName;
            $document->customer_lastname = $lastName;
            $document->customer_email = $customerEmail;
            $document->customer_cellphone = $customerPhone;
            $document->customer_dni = $customerDni;
            $document->order_reference = $orderIdentifier;
            $document->order_date = $fpedido;
            $document->save();

            // Create products from ERP data
            $this->createDocumentProductsFromErpData($document, $resource);

            // Detect document type based on products
            $document->type = $document->detectDocumentType();
            $document->save();

            // Fire event
            DocumentCreated::dispatch($document);

            \Log::info('Document created from ERP', [
                'document_uid' => $document->uid,
                'order_identifier' => $orderIdentifier,
                'erp_order_id' => $idPedidoCli,
                'customer' => $firstName.' '.$lastName,
            ]);

            return $document;
        } catch (\Exception $e) {
            \Log::error('Error creating document from ERP: '.$e->getMessage(), [
                'order_identifier' => $orderIdentifier,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Create document products from ERP data
     * Maps the lineas_pedido_cliente structure from Gestión ERP API
     *
     * Expected structure:
     * lineas_pedido_cliente.resource[].articulo.codigo
     * lineas_pedido_cliente.resource[].articulo.descripcion
     * lineas_pedido_cliente.resource[].unidades
     * lineas_pedido_cliente.resource[].total_con_impuestos
     */
    private function createDocumentProductsFromErpData(Document $document, array $orderData): void
    {
        try {
            // Get lineas_pedido_cliente from ERP structure
            $lineas = $orderData['lineas_pedido_cliente'] ?? [];

            // Handle empty or missing lines
            if (empty($lineas)) {
                \Log::warning('No product lines found in ERP order', [
                    'document_uid' => $document->uid,
                ]);

                return;
            }

            // Get the resource array (could be single item or array)
            $resources = $lineas['resource'] ?? $lineas;

            // Ensure it's an array of products
            if (! is_array($resources)) {
                return;
            }

            // If single product (has 'articulo' key directly), wrap in array
            if (isset($resources['articulo'])) {
                $resources = [$resources];
            }

            $productCount = 0;
            foreach ($resources as $linea) {
                if (! is_array($linea)) {
                    continue;
                }

                // Extract articulo data
                $articulo = $linea['articulo'] ?? [];

                $code = $articulo['codigo'] ?? '';
                $name = $articulo['descripcion'] ?? '';
                $quantity = (float) ($linea['unidades'] ?? 1);
                $totalPrice = (float) ($linea['total_con_impuestos'] ?? 0);

                // Calculate unit price from total
                $unitPrice = $quantity > 0 ? $totalPrice / $quantity : $totalPrice;

                // Skip empty products or promotional items with 0 price (optional)
                if (! $code || ! $name) {
                    continue;
                }

                $document->products()->create([
                    'product_id' => $articulo['idarticulo'] ?? null,
                    'product_reference' => $code,
                    'product_name' => $name,
                    'price' => round($unitPrice, 2),
                    'quantity' => (int) $quantity,
                ]);

                $productCount++;
            }

            \Log::info('Products created from ERP order', [
                'document_uid' => $document->uid,
                'products_count' => $productCount,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating products from ERP: '.$e->getMessage(), [
                'document_uid' => $document->uid ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Obtiene órdenes disponibles para el Select2 dinámico
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableOrders(Request $request)
    {
        $search = $request->query('search', '');

        try {
            $query = PrestashopOrder::query();

            if (! empty($search)) {
                $query->where('id_order', 'LIKE', "%{$search}%")
                    ->orWhere('reference', 'LIKE', "%{$search}%");
            }

            $orders = $query->select('id_order', 'reference')
                ->orderBy('id_order', 'DESC')
                ->limit(50)
                ->get()
                ->map(fn ($order) => [
                    'id' => $order->id_order,
                    'text' => "#{$order->id_order} - {$order->reference}",
                ]);

            return response()->json([
                'results' => $orders,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($uid)
    {
        $document = Document::with(['notes.author', 'status', 'source', 'documentLoad', 'sync', 'uploadType'])
            ->whereUid($uid)
            ->firstOrFail();
        $products = $document->products;

        return view('administratives.views.documents.show')->with([
            'document' => $document,
            'products' => $products,
        ]);
    }

    public function summary($uid)
    {
        $document = Document::findByUid($uid);

        $mediaItems = $document->media;

        $pdfDocs = $mediaItems->filter(fn ($media) => $media->mime_type === 'application/pdf');

        $imageDocs = $mediaItems->filter(function ($media) {
            if (! Str::startsWith($media->mime_type, 'image/')) {
                return false;
            }
            $path = $media->getPath();

            return file_exists($path) && is_readable($path) && filesize($path) > 0;
        });

        $pdf = new Fpdi;

        // Insertar imágenes
        foreach ($imageDocs as $media) {
            $path = $media->getPath();

            // 🛡️ Verificamos y preparamos la imagen
            try {
                $safePath = $this->prepareImageForPDF($path); // <- helper que normaliza a PNG válido
            } catch (Exception $e) {
                // Si hay error, saltamos la imagen
                $pdf->AddPage();
                $pdf->Cell(0, 10, 'Error con imagen: '.basename($path));

                continue;
            }

            // ✅ Ahora trabajamos con una imagen válida
            [$width, $height] = getimagesize($safePath);
            $orientation = $width > $height ? 'L' : 'P';

            $pdf->AddPage($orientation);

            // Márgenes: 10px de borde
            $maxWidth = $orientation === 'L' ? 277 : 190;
            $maxHeight = $orientation === 'L' ? 190 : 277;

            // Escalamos manteniendo proporción
            $ratio = min($maxWidth / $width, $maxHeight / $height);

            $newWidth = $width * $ratio;
            $newHeight = $height * $ratio;

            // Centrar en la página
            $x = (($orientation === 'L' ? 297 : 210) - $newWidth) / 2;
            $y = (($orientation === 'L' ? 210 : 297) - $newHeight) / 2;

            $pdf->Image($safePath, $x, $y, $newWidth, $newHeight);
        }

        // Insertar PDFs
        foreach ($pdfDocs as $media) {
            $filePath = $media->getPath();
            if (! file_exists($filePath) || ! is_readable($filePath)) {
                continue;
            }

            try {
                $pageCount = $pdf->setSourceFile($filePath);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $tpl = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($tpl);

                    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpl);
                }
            } catch (\Exception $e) {
                // \Log::warning("Error al procesar PDF: {$filePath} — {$e->getMessage()}");
            }
        }

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename=documento_'.$document->uid.'.pdf');
    }

    private function prepareImageForPDF($srcPath, $destDir = __DIR__.'/../../../../../storage/app/imageDocs')
    {
        if (! file_exists($srcPath) || filesize($srcPath) === 0) {
            throw new \Exception('Imagen no encontrada o vacía: '.$srcPath);
        }

        if (! is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }

        $info = getimagesize($srcPath);
        if ($info === false) {
            throw new \Exception('Archivo no es una imagen válida: '.$srcPath);
        }

        $mime = $info['mime'];

        switch ($mime) {
            case 'image/png':
                return $srcPath; // ya es PNG válido
            case 'image/jpeg':
                $image = imagecreatefromjpeg($srcPath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($srcPath);
                break;
            default:
                throw new \Exception('Formato no soportado: '.$mime);
        }

        $fileName = pathinfo($srcPath, PATHINFO_FILENAME).'_fixed.png';
        $destPath = $destDir.'/'.$fileName;

        imagepng($image, $destPath);
        imagedestroy($image);

        return $destPath;
    }

    public function update(Request $request)
    {
        $document = Document::findByUid($request->uid);
        $oldStatusId = $document->status_id;
        $document->proccess = $request->proccess;

        // Actualizar source_id si se proporciona
        if ($request->has('source_id')) {
            $document->source_id = $request->source_id ?: null;
        }

        // Actualizar load_id si se proporciona
        if ($request->has('load_id')) {
            $document->load_id = $request->load_id ?: null;
        }

        // Actualizar sync_id si se proporciona
        if ($request->has('sync_id')) {
            $document->sync_id = $request->sync_id ?: null;
        }

        // Actualizar upload_id si se proporciona
        if ($request->has('upload_id')) {
            $document->upload_id = $request->upload_id ?: null;
        }

        // Actualizar status si se proporciona
        // Los administradores pueden cambiar el estado directamente sin validación de transiciones
        if ($request->has('status_id') && ! empty($request->status_id)) {
            $document->status_id = $request->status_id;
        }

        $document->save();

        // Fire DocumentStatusChanged event if status was actually changed
        if ($oldStatusId !== $document->status_id && $document->status_id) {
            $oldStatus = DocumentStatus::find($oldStatusId);
            $newStatus = DocumentStatus::find($document->status_id);

            if ($oldStatus && $newStatus) {
                // Verify transition is valid
                $transition = DocumentStatusTransition::where('from_status_id', $oldStatusId)
                    ->where('to_status_id', $document->status_id)
                    ->active()
                    ->first();

                DocumentStatusChanged::dispatch(
                    $document,
                    $oldStatus,
                    $newStatus,
                    'Manual status change via admin panel'
                );
            }
        }

        if ($request->proccess == 1) {
            OrderSendErp::create([
                'id_order' => $document->order_id,
                'posible_enviar' => 1,
                'motivo_no_enviar' => '',
                'fecha_envio' => null,
                'error_gestion' => '',
                'id_pedido_gestion' => '',
                'id_usuario_gestion' => '',
                'force_type' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'slack' => $document->uid,
            'message' => 'Se actualizo la clase correctamente',
        ]);

    }

    public function resendReminderEmail($uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Document not found.',
                ], 404);
            }

            // Validar que el cliente tiene email
            $recipient = $document->customer_email ?? $document->customer?->email;
            if (! $recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar: documento sin email de cliente',
                ], 400);
            }

            // Despachar job para enviar email en background
            MailTemplateJob::dispatch($document, 'reminder');

            $document->reminder_at = now();
            $document->save();

            return response()->json([
                'success' => true,
                'message' => 'Email de recordatorio en cola para envío',
                'recipient' => $recipient,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar email: '.$e->getMessage(),
            ], 500);
        }
    }

    public function confirmDocumentUpload($uid)
    {
        $document = Document::findByUid($uid);

        if (! $document) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Document not found.',
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

        // Registrar la acción
        DocumentActionService::logUploadConfirmation($document);

        return response()->json([
            'success' => true,
            'message' => 'Carga de documento confirmada correctamente',
        ]);
    }

    public function upload(Request $request)
    {

        $document = Document::findByUid($request->uid);
        $type = 'documents';

        $document->clearMediaCollection($type);

        $media = $document->addMediaFromRequest('file')->toMediaCollection($type);

        // Asegurar que el archivo es accesible al servidor web
        $mediaPath = $media->getPath();
        if (file_exists($mediaPath)) {
            @chmod($mediaPath, 0644);
        }
        $mediaDir = dirname($mediaPath);
        if (is_dir($mediaDir)) {
            @chmod($mediaDir, 0755);
        }

        // Procesar upload: cambiar status a "received" y enviar confirmación
        app(DocumentEmailService::class)->processDocumentUpload($document);

        return response()->json([
            'status' => 'success',
            'statement_id' => $document->id,
            'media' => [
                'id' => $media->id,
                'uuid' => $media->uuid,
                'file' => $media->file_name,
                'size' => $media->size,
                'path' => $media->getUrl(),
            ],
        ]);

    }

    public function getFile($document, $type)
    {
        $document = Document::findByUid($document);
        $media = $document->getMedia($type)->first();

        if (! $media) {
            return response()->json([]);
        }

        return response()->json([[
            'id' => $media->id,
            'uuid' => $media->uuid,
            'file' => $media->file_name,
            'size' => $media->size,
            'path' => $media->getUrl(),
        ]]);
    }

    public function deleteFile($id)
    {
        $media = Media::find($id);

        if ($media) {
            $media->delete();

            return response()->json(['status' => 'deleted']);
        }

        return response()->json(['status' => 'not_found'], 404);
    }

    public function destroy($uid)
    {
        $document = Document::findByUid($uid);
        $document->delete();

        return redirect()->route('administrative.documents');
    }

    /**
     * Sincroniza todos los documentos con los datos de sus órdenes
     * Incluye importación de productos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncAllDocuments()
    {
        try {
            $synced = 0;
            $failed = 0;
            $errors = [];

            $documents = Document::get();

            if ($documents->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No documents to synchronize.',
                    'data' => [
                        'synced' => 0,
                        'failed' => 0,
                        'total' => 0,
                    ],
                ], 200);
            }

            foreach ($documents as $document) {
                try {
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
     * Sincroniza documentos de una orden específica
     * Crea un nuevo documento si no existe, o sincroniza los existentes
     * Incluye importación de productos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncByOrderId(Request $request)
    {
        $orderId = $request->input('order_id') ?? $request->query('order_id');

        if (! $orderId) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Missing order_id parameter',
            ], 400);
        }

        try {
            $order = PrestashopOrder::find($orderId);

            if (! $order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Order not found in Prestashop.',
                ], 404);
            }

            $documents = Document::where('order_id', $orderId)->get();

            // Validar que la orden no exista
            if (! $documents->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Orden {$orderId} ya existe.",
                    'data' => [
                        'order_id' => $orderId,
                        'existing_documents' => $documents->count(),
                    ],
                ], 400);
            }

            // Si no existe documento, crear uno nuevo
            if ($documents->isEmpty()) {
                try {
                    $document = new Document;
                    $document->order_id = $orderId;
                    $document->type = 'order';

                    // Set source_id to 'api' (data from PrestaShop API)
                    $apiSource = DocumentSource::where('key', 'api')->first();
                    $document->source_id = $apiSource?->id;

                    // Set load_id to 'api' (loaded from PrestaShop)
                    $apiLoad = DocumentLoad::where('key', 'api')->first();
                    $document->load_id = $apiLoad?->id;

                    // Set sync_id to 'automatic'
                    $automaticSync = DocumentSync::where('key', 'manual')->first();
                    $document->sync_id = $automaticSync?->id;

                    // Set upload_id to 'manual' (admin uploads files)
                    $manualUpload = DocumentUploadType::where('key', 'automatic')->first();
                    $document->upload_id = $manualUpload?->id;

                    // Set initial status to 'pending'
                    $pendingStatus = DocumentStatus::where('key', 'pending')->first();
                    $document->status_id = $pendingStatus?->id;

                    $document->proccess = 0;
                    $document->save();

                    // Sincronizar documento con datos de la orden primero
                    if (! $this->syncDocumentWithOrder($document, $order)) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Failed to sync document with order',
                        ], 500);
                    }

                    // AHORA disparar evento después de que el documento esté sincronizado
                    DocumentCreated::dispatch($document);

                    $documents = collect([$document]);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Failed to create document: '.$e->getMessage(),
                    ], 500);
                }
            }

            $synced = 0;
            $failed = 0;
            $errors = [];

            $productsCount = 0;

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
                    // Contar productos del documento
                    $productsCount = $document->products()->count();
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
                    'uid' => $documents->first()?->uid,
                    'order_id' => $orderId,
                    'type' => $documents->first()?->type,
                    'lang_id' => $documents->first()?->lang_id,
                    'synced' => $synced,
                    'products_count' => $productsCount,
                    'customer_name' => $order->customer ? "{$order->customer->firstname} {$order->customer->lastname}" : null,
                    'order_reference' => $order->reference,
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
     * Muestra la vista de gestión detallada del documento
     * Con datos del cliente, orden y opciones de carga
     */
    public function manage($uid)
    {
        $document = Document::findByUid($uid);

        if (! $document) {
            abort(404, 'Documento no encontrado');
        }

        // Cargar relaciones necesarias para la vista
        $document->load('actions.performer');

        $products = $document->products;
        $sources = ['email', 'api', 'whatsapp', 'wp', 'manual'];

        // Get all active statuses except 'pending'
        $statuses = DocumentStatus::where('is_active', true)
            ->orderBy('order')
            ->get();

        // Get all document sources for dropdown
        $documentSources = DocumentSource::where('is_active', true)->orderBy('order')->get();

        // Get all document loads for dropdown
        $documentLoads = DocumentLoad::where('is_active', true)->orderBy('order')->get();

        // Get all document syncs for dropdown
        $documentSyncs = DocumentSync::where('is_active', true)->orderBy('order')->get();

        // Get all upload types for dropdown
        $uploadTypes = DocumentUploadType::where('is_active', true)->orderBy('order')->get();

        // Get global document configuration settings
        $configController = new DocumentConfigurationController;
        $globalSettings = $configController->getGlobalSettings();

        // Get the custom email template if configured
        $customEmailTemplate = null;
        $customEmailTemplateId = Setting::get('documents.mail_template_custom_email_id');
        if ($customEmailTemplateId) {
            $customEmailTemplate = MailTemplate::find($customEmailTemplateId);
        }

        $documentConfig = [
            'initial_request_description' => 'Envía un email al cliente solicitándole que cargue los documentos requeridos.',
            'missing_docs_description' => 'Solicita al cliente que reenvíe documentos concretos que falten o necesiten corrección.',
            'reminder_description' => 'Envía un recordatorio al cliente si aún no ha completado la carga de documentos.',
            'custom_email_description' => 'Envía un correo con contenido personalizado al cliente.',
            'enable_initial_request' => $globalSettings['enable_initial_request'] ?? true,
            'enable_reminder' => $globalSettings['enable_reminder'] ?? true,
            'enable_missing_docs' => $globalSettings['enable_missing_docs'] ?? true,
            'enable_custom_email' => $globalSettings['enable_custom_email'] ?? false,
            'enable_approval' => $globalSettings['enable_approval'] ?? true,
            'enable_rejection' => $globalSettings['enable_rejection'] ?? true,
        ];

        return view('administratives.views.documents.manage')->with([
            'document' => $document,
            'products' => $products,
            'sources' => $sources,
            'statuses' => $statuses,
            'documentSources' => $documentSources,
            'documentLoads' => $documentLoads,
            'documentSyncs' => $documentSyncs,
            'uploadTypes' => $uploadTypes,
            'documentConfig' => $documentConfig,
            'globalSettings' => $globalSettings,
            'customEmailTemplate' => $customEmailTemplate,
        ]);

    }

    /**
     * Envía email de notificación inicial al cliente
     * Solicita que cargue la documentación
     */
    public function sendNotificationEmail($uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // Verificar si está habilitado en configuración global
            if (Setting::get('documents.enable_initial_request', 'yes') !== 'yes') {
                return response()->json([
                    'success' => false,
                    'message' => 'La solicitud inicial de documentos está deshabilitada en la configuración.',
                ], 403);
            }

            // Validar que el documento tiene email
            $recipient = $document->customer_email ?? $document->customer?->email;
            if (! $recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar: documento sin email de cliente',
                    'document_email' => $document->customer_email,
                ], 400);
            }

            // Despachar job para enviar email en background
            MailTemplateJob::dispatch($document, 'request');

            return response()->json([
                'success' => true,
                'message' => 'Email de notificación en cola para envío',
                'recipient' => $recipient,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar solicitud: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Envía email de recordatorio al cliente
     * Recordatorio para cargar documentación (solo si no se cargó)
     */
    public function sendReminderEmail($uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // Verificar si está habilitado en configuración global
            if (Setting::get('documents.enable_reminder', 'yes') !== 'yes') {
                return response()->json([
                    'success' => false,
                    'message' => 'Los recordatorios automáticos están deshabilitados en la configuración.',
                ], 403);
            }

            // Verificar que el cliente tiene email
            $recipient = $document->customer_email ?? $document->customer?->email;
            if (! $recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar: documento sin email de cliente',
                ], 400);
            }

            // Despachar job para enviar email en background
            MailTemplateJob::dispatch($document, 'reminder');

            return response()->json([
                'success' => true,
                'message' => 'Email de recordatorio en cola para envío',
                'recipient' => $recipient,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar email: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permite al administrador cargar documentos en nombre del cliente
     * Soporta múltiples archivos con tipos específicos (dni_frontal, dni_trasera, licencia, etc)
     */
    public function adminUploadDocument(Request $request, $uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // Validar que venga al menos un archivo
            $request->validate([
                'documents.*' => 'nullable|file|max:10240', // Máximo 10MB por archivo
            ]);

            $uploadedCount = 0;
            $uploadedFiles = [];
            $type = 'documents';

            // Log del inicio de la carga
            $documentsArray = $request->file('documents') ?? [];
            \Log::info('adminUploadDocument START', [
                'uid' => $uid,
                'document_id' => $document->id,
                'files_received' => count($documentsArray),
                'array_keys' => is_array($documentsArray) ? array_keys($documentsArray) : [],
                'array_structure' => is_array($documentsArray) ? array_map(function ($f) {
                    return [
                        'name' => $f instanceof \Illuminate\Http\UploadedFile ? $f->getClientOriginalName() : 'N/A',
                        'type' => $f instanceof \Illuminate\Http\UploadedFile ? get_class($f) : gettype($f),
                    ];
                }, $documentsArray) : [],
            ]);

            // Procesar cada archivo del array documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $docType => $file) {
                    if ($file && $file->isValid()) {
                        \Log::info('Processing file', [
                            'docType' => $docType,
                            'fileName' => $file->getClientOriginalName(),
                            'fileSize' => $file->getSize(),
                        ]);

                        // Recargar media del documento para asegurar que tenemos la versión más reciente
                        $document->load('media');

                        // Eliminar archivo anterior del mismo tipo si existe
                        $existingMedia = null;
                        foreach ($document->media as $media) {
                            $storedType = $media->getCustomProperty('document_type');
                            \Log::info('Checking existing media', [
                                'mediaId' => $media->id,
                                'storedDocType' => $storedType,
                                'lookingFor' => $docType,
                                'match' => $storedType === $docType,
                            ]);

                            if ($storedType === $docType) {
                                $existingMedia = $media;
                                break;
                            }
                        }

                        if ($existingMedia) {
                            \Log::info('Deleting existing media', ['mediaId' => $existingMedia->id]);
                            $existingMedia->delete();
                        }

                        // Agregar nueva media con propiedad custom para identificar el tipo
                        $media = $document->addMedia($file)
                            ->withCustomProperties(['document_type' => $docType])
                            ->toMediaCollection($type);

                        // Verificar que el custom property se guardó correctamente
                        $savedProperty = $media->getCustomProperty('document_type');
                        \Log::info('Media uploaded and verified', [
                            'mediaId' => $media->id,
                            'fileName' => $media->file_name,
                            'docType' => $docType,
                            'savedProperty' => $savedProperty,
                            'propertyMatch' => $savedProperty === $docType,
                        ]);

                        // Asegurar que el archivo es accesible al servidor web
                        $mediaPath = $media->getPath();
                        if (file_exists($mediaPath)) {
                            @chmod($mediaPath, 0644);
                        }
                        // También cambiar permisos del directorio si es necesario
                        $mediaDir = dirname($mediaPath);
                        if (is_dir($mediaDir)) {
                            @chmod($mediaDir, 0755);
                        }

                        $uploadedFiles[] = [
                            'id' => $media->id,
                            'uuid' => $media->uuid,
                            'file' => $media->file_name,
                            'size' => $media->size,
                            'path' => $media->getUrl(),
                            'type' => $docType,
                        ];

                        $uploadedCount++;
                    }
                }
            }

            \Log::info('adminUploadDocument END', [
                'uploadedCount' => $uploadedCount,
                'totalFiles' => count($uploadedFiles),
            ]);

            if ($uploadedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibieron archivos válidos',
                ], 400);
            }

            // Sincronizar JSON de documentos subidos con los archivos media actuales
            $document->syncUploadedDocumentsJson();

            // Actualizar timestamp de confirmación
            $document->update([
                'confirmed_at' => now(), // El admin confirma implícitamente
            ]);

            // NO enviar correo automático cuando el admin sube documentos
            // app(DocumentEmailService::class)->processDocumentUpload($document);

            return response()->json([
                'success' => true,
                'message' => 'Documento(s) cargado(s) correctamente por el administrador',
                'uploaded_count' => $uploadedCount,
                'statement_id' => $document->id,
                'files' => $uploadedFiles,
                'uploaded_documents' => $document->uploaded_documents,
                'missing_documents' => $document->getMissingDocuments(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar documento: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sincroniza los campos required_documents y uploaded_documents de todos los documentos
     * Útil para migrar documentos antiguos o corregir inconsistencias
     * Puede filtrar por tipo específico si se proporciona
     */
    public function syncAllDocumentFields(Request $request)
    {
        try {
            $type = $request->query('type');
            $force = $request->query('force', false);

            if ($type) {
                $documents = Document::where('type', $type)->get();
            } else {
                $documents = Document::all();
            }

            if ($documents->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron documentos para sincronizar',
                ], 404);
            }

            $synced = 0;
            $skipped = 0;

            foreach ($documents as $document) {
                // Si ya está sincronizado y no es force, omitir
                if (! $force && ! empty($document->required_documents) && ! empty($document->uploaded_documents)) {
                    $skipped++;

                    continue;
                }

                // 1. Establecer tipo por defecto si no existe
                if (! $document->type) {
                    $document->type = 'general';
                }

                // 2. Generar required_documents desde DocumentTypeService
                $requiredDocs = DocumentTypeService::getRequiredDocuments($document->type);
                $document->required_documents = $requiredDocs;

                // 3. Generar uploaded_documents desde media actual
                $uploadedDocs = [];
                foreach ($document->getMedia('documents') as $media) {
                    $docType = $media->getCustomProperty('document_type', 'documento');
                    $uploadedDocs[$docType] = [
                        'id' => $media->id,
                        'file_name' => $media->file_name,
                        'size' => $media->size,
                        'url' => $media->getUrl(),
                        'created_at' => $media->created_at->format('Y-m-d H:i:s'),
                    ];
                }
                $document->uploaded_documents = $uploadedDocs;

                // 4. Guardar el documento
                $document->save();

                $synced++;
            }

            return response()->json([
                'success' => true,
                'message' => "Sincronización completada: {$synced} sincronizados, {$skipped} omitidos",
                'data' => [
                    'total_documents' => $documents->count(),
                    'synced' => $synced,
                    'skipped' => $skipped,
                    'type_filter' => $type ?? 'todos',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sincronizando documentos: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene el estado completo del documento en formato JSON
     * Retorna: required_documents, uploaded_documents, missing_documents, estado de completitud
     * Usado para actualizaciones dinámicas sin recargar la página
     */
    public function getDocumentState($uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // Obtener documentos requeridos y faltantes desde el modelo
            $requiredDocuments = $document->getRequiredDocuments();
            $missingDocuments = $document->getMissingDocuments();
            $uploadedDocuments = $document->uploaded_documents ?? [];
            $uploadedDocumentsDetails = $document->getUploadedDocumentsWithDetails();

            return response()->json([
                'success' => true,
                'document' => [
                    'uid' => $document->uid,
                    'type' => $document->type,
                    'confirmed_at' => $document->confirmed_at?->format('d/m/Y H:i'),
                ],
                'required_documents' => $requiredDocuments,
                'uploaded_documents' => $uploadedDocuments, // Array simple: ["doc_1", "doc_2"]
                'uploaded_documents_details' => $uploadedDocumentsDetails, // Con detalles completos de archivos
                'missing_documents' => $missingDocuments,
                'all_uploaded' => empty($missingDocuments),
                'stats' => [
                    'total_required' => count($requiredDocuments),
                    'total_uploaded' => count($uploadedDocuments),
                    'total_missing' => count($missingDocuments),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estado del documento: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene los documentos faltantes actualizados después de una carga
     * Usado para actualizar el modal sin recargar la página
     */
    public function getMissingDocuments($uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // Obtener documentos cargados organizados por tipo
            $uploadedDocs = [];
            foreach ($document->media as $media) {
                $docType = $media->getCustomProperty('document_type', 'documento');
                $uploadedDocs[$docType] = [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'size' => $media->size,
                    'url' => $media->getUrl(),
                    'created_at' => $media->created_at->format('d/m/Y H:i'),
                ];
            }

            // Obtener documentos faltantes
            $requiredDocuments = DocumentTypeService::getRequiredDocuments($document->type);
            $missingDocs = DocumentTypeService::getMissingDocuments($document->type, $uploadedDocs);
            $allUploaded = DocumentTypeService::allDocumentsUploaded($document->type, $uploadedDocs);

            return response()->json([
                'success' => true,
                'required_documents' => $requiredDocuments,
                'uploaded_documents' => $uploadedDocs,
                'missing_documents' => $missingDocs,
                'all_uploaded' => $allUploaded,
                'total_required' => count($requiredDocuments),
                'total_uploaded' => count($uploadedDocs),
                'total_missing' => count($missingDocs),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener documentos: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Devuelve el HTML renderizado de la sección de carga de documentos
     * Permite refrescar completamente la sección sin recargar la página
     */
    public function refreshDocumentsSection($uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // Obtener tipo de documento desde la base de datos (mismo método que manage.blade.php)
            $documentType = DocumentType::where('slug', $document->type)->with('requirements')->first();
            $requiredDocuments = $documentType?->getRequiredDocuments() ?? [];

            // Obtener documentos ya cargados organizados por tipo (recargando relación media)
            $document->load('media');

            $uploadedDocs = [];
            foreach ($document->media as $media) {
                $docType = $media->getCustomProperty('document_type', 'documento');
                $uploadedDocs[$docType] = $media;
            }

            // Calcular documentos faltantes
            $missingDocs = array_diff_key($requiredDocuments, $uploadedDocs);
            $allUploaded = empty($missingDocs);

            // Renderizar solo la sección de carga de documentos
            $html = view('administratives.views.documents.partials.upload-section', [
                'document' => $document,
                'requiredDocuments' => $requiredDocuments,
                'uploadedDocs' => $uploadedDocs,
                'missingDocs' => $missingDocs,
                'allUploaded' => $allUploaded,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al refrescar sección de documentos', [
                'uid' => $uid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al refrescar la sección: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresca la sección del historial de acciones
     */
    public function refreshActionHistory($uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // Cargar las relaciones necesarias
            $document->load('actions.performer');

            // Renderizar el componente de historial de acciones
            $html = view('administratives.views.documents.includes.action-history', [
                'document' => $document,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al refrescar historial de acciones', [
                'uid' => $uid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al refrescar el historial: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina un documento individual por su media_id
     */
    public function deleteSingleDocument(Request $request, $uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            $mediaId = $request->input('media_id');

            if (! $mediaId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de media no proporcionado',
                ], 400);
            }

            $media = Media::find($mediaId);

            if (! $media || $media->model_id !== $document->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado o no pertenece a este documento',
                ], 404);
            }

            $media->delete();

            // Sincronizar JSON de documentos subidos
            $document->syncUploadedDocumentsJson();

            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado correctamente',
                'uploaded_documents' => $document->uploaded_documents,
                'missing_documents' => $document->getMissingDocuments(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar documento: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Envía email solicitando documentos específicos faltantes
     */
    public function sendMissingDocumentsEmail(Request $request, $uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            // Verificar si está habilitado en configuración global
            if (Setting::get('documents.enable_missing_docs', 'yes') !== 'yes') {
                return response()->json([
                    'success' => false,
                    'message' => 'La solicitud de documentos específicos está deshabilitada en la configuración.',
                ], 403);
            }

            // Validar entrada (la validación de si está habilitado se hace en el job)
            $request->validate([
                'missing_docs' => 'required|array|min:1',
                'notes' => 'nullable|string',
            ]);

            $missingDocs = $request->input('missing_docs');
            $notes = $request->input('notes');
            $recipient = $document->customer_email ?? $document->customer?->email;

            if (! $recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar: documento sin email de cliente',
                ], 400);
            }

            // Despachar job para enviar email en background
            MailTemplateJob::dispatch($document, 'missing', [
                'missing_docs' => $missingDocs,
                'notes' => $notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email de solicitud en cola para envío',
                'recipient' => $recipient,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar email: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Agregar una nota al documento
     */
    public function addNote(Request $request, $uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            $request->validate([
                'content' => 'required|string|max:5000',
            ]);

            $adminId = auth()->check() ? auth()->id() : 0;

            // Agregar la nota usando el servicio
            $note = DocumentActionService::addNote(
                $document,
                $adminId,
                $request->input('content'),
                true // is_internal
            );

            // Cargar relación de autor
            $note->load('author');

            return response()->json([
                'success' => true,
                'message' => 'Nota agregada correctamente',
                'note' => [
                    'id' => $note->id,
                    'content' => $note->content,
                    'created_at' => $note->created_at,
                    'author' => $note->author ? [
                        'firstname' => $note->author->firstname ?? '',
                        'lastname' => $note->author->lastname ?? '',
                        'full_name' => $note->author->full_name ?? '',
                    ] : null,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar nota: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar una nota del documento
     */
    public function updateNote(Request $request, $uid, $noteId)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            $note = DocumentNote::find($noteId);

            if (! $note || $note->document_id !== $document->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nota no encontrada.',
                ], 404);
            }

            // Verificar que el usuario autenticado sea el autor de la nota
            if ($note->created_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para editar esta nota.',
                ], 403);
            }

            $request->validate([
                'content' => 'required|string|max:5000',
            ]);

            $note->update([
                'content' => $request->input('content'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nota actualizada correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar nota: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar una nota del documento
     */
    public function deleteNote(Request $request, $uid, $noteId)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            $note = DocumentNote::find($noteId);

            if (! $note || $note->document_id !== $document->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nota no encontrada.',
                ], 404);
            }

            // Verificar que el usuario autenticado sea el autor de la nota
            if ($note->created_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar esta nota.',
                ], 403);
            }

            $note->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nota eliminada correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar nota: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Envía un correo personalizado al cliente
     */
    public function sendCustomEmail(Request $request, $uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            $request->validate([
                'subject' => 'required|string|max:255',
                'content' => 'required|string|max:10000',
            ]);

            $subject = $request->input('subject');
            $content = $request->input('content');
            $recipient = $document->customer_email ?? $document->customer?->email;

            if (! $recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar: documento sin email de cliente',
                ], 400);
            }

            // Obtener admin ID del usuario autenticado
            $adminId = auth()->check() ? auth()->id() : null;

            // Despachar job para enviar email en background
            // La plantilla se obtiene automáticamente desde settings en DocumentEmailTemplateService
            MailTemplateJob::dispatch($document, 'custom', [
                'subject' => $subject,
                'content' => $content,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Correo en cola para envío',
                'recipient' => $recipient,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar correo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send upload confirmation email
     */
    public function sendUploadConfirmationEmail(Request $request, $uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            if (Setting::get('documents.enable_upload_confirmation') !== 'yes') {
                return response()->json([
                    'success' => false,
                    'message' => 'La notificación de confirmación de subida está deshabilitada.',
                ], 403);
            }

            $recipient = $document->customer_email ?? $document->customer?->email;

            if (! $recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar: documento sin email de cliente',
                ], 400);
            }

            $notes = $request->input('notes');

            MailTemplateJob::dispatch($document, 'upload', [
                'notes' => $notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email de confirmación en cola para envío',
                'recipient' => $recipient,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error sending upload confirmation email', [
                'uid' => $uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar correo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send approval email
     */
    public function sendApprovalEmail(Request $request, $uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            if (Setting::get('documents.enable_approval') !== 'yes') {
                return response()->json([
                    'success' => false,
                    'message' => 'La notificación de aprobación está deshabilitada.',
                ], 403);
            }

            $recipient = $document->customer_email ?? $document->customer?->email;

            if (! $recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar: documento sin email de cliente',
                ], 400);
            }

            $notes = $request->input('notes');

            MailTemplateJob::dispatch($document, 'approval');

            return response()->json([
                'success' => true,
                'message' => 'Email de aprobación en cola para envío',
                'recipient' => $recipient,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error sending approval email', [
                'uid' => $uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar correo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send rejection email
     */
    public function sendRejectionEmail(Request $request, $uid)
    {
        try {
            $document = Document::findByUid($uid);

            if (! $document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado.',
                ], 404);
            }

            if (Setting::get('documents.enable_rejection') !== 'yes') {
                return response()->json([
                    'success' => false,
                    'message' => 'La notificación de rechazo está deshabilitada.',
                ], 403);
            }

            $request->validate([
                'reason' => 'required|string|max:5000',
                'rejected_docs' => 'nullable|array',
                'rejected_docs.*' => 'string',
            ]);

            $recipient = $document->customer_email ?? $document->customer?->email;

            if (! $recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar: documento sin email de cliente',
                ], 400);
            }

            $reason = $request->input('reason');
            $rejectedDocs = $request->input('rejected_docs', []);

            MailTemplateJob::dispatch($document, 'rejection', [
                'reason' => $reason,
                'rejected_docs' => $rejectedDocs,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email de rechazo en cola para envío',
                'recipient' => $recipient,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error sending rejection email', [
                'uid' => $uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar correo: '.$e->getMessage(),
            ], 500);
        }
    }

    private function syncDocumentWithOrder(Document $document, PrestashopOrder $order): bool
    {
        $customer = $order->customer;

        if (! $customer) {
            return false;
        }

        $langId = null;

        // If iso_code is provided, map to Laravel lang_id
        if (isset($order->lang?->iso_code) && ! empty($order->lang?->iso_code)) {
            $isoCode = strtolower(trim($order->lang?->iso_code));

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

        $document->lang_id = $langId;  // Assign language
        $document->order_reference = $order->reference ?? $document->order_reference;
        $document->order_date = $order->date_add ?? $document->order_date;

        // Traer cart_id de la orden
        $document->cart_id = $order->id_cart ?? $document->cart_id;

        // Obtener dirección de envío
        $deliveryAddress = $order->deliveryAddress;
        $document->customer_id = $customer->id_customer;
        // Nombre y apellido vienen de la dirección de envío
        $document->customer_firstname = $customer->firstname;
        $document->customer_lastname = $customer->lastname;
        $document->customer_email = $customer->email;
        // DNI/SIRET vienen de la dirección de envío
        $document->customer_dni = $deliveryAddress?->dni ?? $deliveryAddress?->vat_number ?? null;
        // Empresa viene de la dirección de envío
        $document->customer_company = $deliveryAddress?->company ?? null;
        // Teléfono celular viene de la dirección de envío
        $document->customer_cellphone = $deliveryAddress?->phone ?? null;

        // Guardar primero el documento
        $document->save();

        // Luego capturar los productos
        $document->captureProducts();

        // Finalmente detectar el tipo basándose en los productos capturados
        $document->type = $document->detectDocumentType();
        $document->save();

        return true;
    }

    /**
     * Show email history for a document
     */
    public function emailHistory($uid)
    {
        $document = Document::findByUid($uid);

        if (! $document) {
            return redirect()
                ->route('administrative.documents')
                ->with('error', 'Documento no encontrado');
        }

        $mails = $document->mails()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('administratives.views.documents.emails.index', compact('document', 'mails'));
    }

    /**
     * Preview a specific email
     */
    public function emailPreview($mailUid)
    {
        $mail = \App\Models\Document\DocumentMail::where('uid', $mailUid)->firstOrFail();
        $document = $mail->document;

        return view('administratives.views.documents.emails.preview', compact('mail', 'document'));
    }
}
