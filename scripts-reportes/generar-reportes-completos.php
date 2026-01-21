<?php

/**
 * Script para generar reportes COMPLETOS de órdenes vs documentos
 *
 * Uso: php generar-reportes-completos.php
 *
 * Genera 6 CSVs:
 * - CSV_1: TODAS las órdenes (desde 2025-05-27)
 * - CSV_2: Órdenes con estado 27 (PAGADAS)
 * - CSV_3: Órdenes con productos bloqueados (ARMAS)
 * - CSV_4: Órdenes con bloqueados + DOCUMENTOS creados
 * - CSV_5: Órdenes con bloqueados SIN DOCUMENTOS (CRÍTICAS)
 * - CSV_6: Documentos creados que NO tienen órdenes con bloqueados (ORFANDOS POR BLOQUEO)
 */

// Detectar directorio base
$baseDir = __DIR__;
$projectRoot = dirname($baseDir);
$outputDir = $baseDir.'/output';

// Crear directorio de output si no existe
if (! file_exists($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Cargar Laravel
require $projectRoot.'/vendor/autoload.php';
$app = require_once $projectRoot.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Generando reportes COMPLETOS de órdenes...\n\n";

// ============ OBTENER DATOS BASE ============
$blockedProducts = DB::connection('mysql')
    ->table('document_product_blockades')
    ->distinct('product_id')
    ->pluck('product_id')
    ->toArray();

echo '✅ Productos bloqueados encontrados: '.count($blockedProducts)."\n";

// TODAS las órdenes desde 2025-05-27
$allOrders = DB::connection('prestashop')
    ->table('aalv_orders')
    ->where('date_add', '>=', '2025-05-27')
    ->select('id_order', 'reference', 'date_add')
    ->orderBy('id_order', 'desc')
    ->get();

echo '✅ Órdenes totales (desde 2025-05-27): '.count($allOrders)."\n";

// Órdenes con estado 27 (PAGADAS)
$paidOrderIds = DB::connection('prestashop')
    ->table('aalv_order_history')
    ->where('id_order_state', 27)
    ->distinct('id_order')
    ->pluck('id_order')
    ->toArray();

echo '✅ Órdenes pagadas (estado 27): '.count($paidOrderIds)."\n";

$paidOrdersFromDate = DB::connection('prestashop')
    ->table('aalv_orders as o')
    ->join('aalv_order_history as oh', 'o.id_order', '=', 'oh.id_order')
    ->where('oh.id_order_state', 27)
    ->where('o.date_add', '>=', '2025-05-27')
    ->select('o.id_order', 'o.reference', 'o.date_add')
    ->distinct('o.id_order')
    ->orderBy('o.id_order', 'desc')
    ->get();

echo '✅ Órdenes pagadas (2025-05-27+): '.count($paidOrdersFromDate)."\n";

// Órdenes con productos bloqueados
$ordersWithBlocked = DB::connection('prestashop')
    ->table('aalv_order_detail as od')
    ->join('aalv_orders as o', 'od.id_order', '=', 'o.id_order')
    ->whereIn('od.product_id', $blockedProducts)
    ->where('o.date_add', '>=', '2025-05-27')
    ->select('o.id_order', 'o.reference', 'o.date_add', 'od.product_id', 'od.product_name')
    ->distinct()
    ->orderBy('o.id_order', 'desc')
    ->get();

echo '✅ Órdenes con bloqueados (2025-05-27+): '.count($ordersWithBlocked)."\n";

// Documentos
$allDocs = DB::connection('mysql')
    ->table('documents')
    ->whereIn('order_id', collect($ordersWithBlocked)->pluck('id_order')->toArray())
    ->select('order_id', 'id', 'uid', 'status_id', 'created_at', 'validation_status')
    ->get();

echo '✅ Documentos para órdenes con bloqueados: '.count($allDocs)."\n\n";

// ============ CSV 1: TODAS LAS ÓRDENES ============
$csv1 = "id_order,reference,date_add\n";
foreach ($allOrders as $row) {
    $csv1 .= "{$row->id_order},\"{$row->reference}\",\"{$row->date_add}\"\n";
}
file_put_contents($outputDir.'/CSV_1_TODAS_ORDENES.csv', $csv1);
echo '✅ CSV_1 generado: '.count($allOrders)." registros (TODAS las órdenes)\n";

// ============ CSV 2: ÓRDENES PAGADAS (ESTADO 27) ============
$csv2 = "id_order,reference,date_add\n";
foreach ($paidOrdersFromDate as $row) {
    $csv2 .= "{$row->id_order},\"{$row->reference}\",\"{$row->date_add}\"\n";
}
file_put_contents($outputDir.'/CSV_2_ORDENES_PAGADAS_ESTADO_27.csv', $csv2);
echo '✅ CSV_2 generado: '.count($paidOrdersFromDate)." registros (PAGADAS estado 27)\n";

// ============ CSV 3: ÓRDENES CON PRODUCTOS BLOQUEADOS ============
$csv3 = "id_order,reference,date_add,product_id,product_name\n";
foreach ($ordersWithBlocked as $row) {
    $csv3 .= "{$row->id_order},\"{$row->reference}\",\"{$row->date_add}\",{$row->product_id},\"{$row->product_name}\"\n";
}
file_put_contents($outputDir.'/CSV_3_ORDENES_CON_BLOQUEADOS.csv', $csv3);
echo '✅ CSV_3 generado: '.count($ordersWithBlocked)." registros (CON BLOQUEADOS)\n";

// ============ CSV 4: ÓRDENES CON BLOQUEADOS + DOCUMENTOS ============
$ordersWithBlockedIds = collect($ordersWithBlocked)->pluck('id_order')->unique()->toArray();
$docsOrderIds = collect($allDocs)->pluck('order_id')->unique()->toArray();
$ordersWithBlockedAndDocs = collect($ordersWithBlocked)->filter(function ($order) use ($docsOrderIds) {
    return in_array($order->id_order, $docsOrderIds);
});

$csv4 = "id_order,reference,date_add,product_id,product_name,doc_id,doc_uid,doc_status,doc_created_at\n";
foreach ($ordersWithBlockedAndDocs as $order) {
    // Buscar documentos de esta orden
    $orderDocs = $allDocs->filter(function ($doc) use ($order) {
        return $doc->order_id == $order->id_order;
    });

    if ($orderDocs->count() > 0) {
        foreach ($orderDocs as $doc) {
            $csv4 .= "{$order->id_order},\"{$order->reference}\",\"{$order->date_add}\",{$order->product_id},\"{$order->product_name}\",{$doc->id},\"{$doc->uid}\",{$doc->status_id},\"{$doc->created_at}\"\n";
        }
    } else {
        $csv4 .= "{$order->id_order},\"{$order->reference}\",\"{$order->date_add}\",{$order->product_id},\"{$order->product_name}\",,,,\n";
    }
}
file_put_contents($outputDir.'/CSV_4_BLOQUEADOS_CON_DOCUMENTOS.csv', $csv4);
echo '✅ CSV_4 generado: '.$ordersWithBlockedAndDocs->count()." registros (BLOQUEADOS + DOCS)\n";

// ============ CSV 5: ÓRDENES CON BLOQUEADOS SIN DOCUMENTOS (CRÍTICAS) ============
$ordersWithBlockedNoDocs = collect($ordersWithBlocked)->filter(function ($order) use ($docsOrderIds) {
    return ! in_array($order->id_order, $docsOrderIds);
});

$csv5 = "id_order,reference,date_add,product_id,product_name,necesita_documento\n";
foreach ($ordersWithBlockedNoDocs as $row) {
    $csv5 .= "{$row->id_order},\"{$row->reference}\",\"{$row->date_add}\",{$row->product_id},\"{$row->product_name}\",SÍ\n";
}
file_put_contents($outputDir.'/CSV_5_BLOQUEADOS_SIN_DOCUMENTOS_CRITICAS.csv', $csv5);
echo '✅ CSV_5 generado: '.$ordersWithBlockedNoDocs->count()." registros (BLOQUEADOS SIN DOCS - CRÍTICAS)\n";

// ============ CSV 6: DOCUMENTOS SIN ÓRDENES CON BLOQUEADOS (ORFANDOS POR BLOQUEO) ============
// Documentos cuya orden NO tiene productos bloqueados
$allDocuments = DB::connection('mysql')
    ->table('documents')
    ->where('order_id', '>', 0)
    ->select('order_id', 'id', 'uid', 'status_id', 'created_at', 'validation_status')
    ->get();

$docsOfOrdersWithBlocked = $allDocs;
$docsOrphanedByBlockade = $allDocuments->filter(function ($doc) use ($ordersWithBlockedIds) {
    return ! in_array($doc->order_id, $ordersWithBlockedIds);
});

$csv6 = "order_id,doc_id,doc_uid,doc_status,doc_created_at,doc_validation_status\n";
foreach ($docsOrphanedByBlockade as $doc) {
    $csv6 .= "{$doc->order_id},{$doc->id},\"{$doc->uid}\",{$doc->status_id},\"{$doc->created_at}\",\"{$doc->validation_status}\"\n";
}
file_put_contents($outputDir.'/CSV_6_DOCUMENTOS_SIN_BLOQUEADOS.csv', $csv6);
echo '✅ CSV_6 generado: '.$docsOrphanedByBlockade->count()." registros (DOCS SIN BLOQUEADOS)\n";

echo "\n✅ TODOS LOS CSVs GENERADOS CORRECTAMENTE\n";
echo "\n📁 Ubicación: $outputDir/\n";
echo "\n📊 Resumen:\n";
echo '   • CSV_1: '.count($allOrders)." TODAS las órdenes\n";
echo '   • CSV_2: '.count($paidOrdersFromDate)." órdenes PAGADAS (estado 27)\n";
echo '   • CSV_3: '.count($ordersWithBlocked)." órdenes con BLOQUEADOS\n";
echo '   • CSV_4: '.$ordersWithBlockedAndDocs->count()." BLOQUEADOS + DOCUMENTOS\n";
echo '   • CSV_5: '.$ordersWithBlockedNoDocs->count()." BLOQUEADOS SIN DOCS (CRÍTICAS)\n";
echo '   • CSV_6: '.$docsOrphanedByBlockade->count()." DOCUMENTOS SIN BLOQUEADOS\n\n";

echo '🚀 Próximo paso: Crear documentos para las '.$ordersWithBlockedNoDocs->count()." órdenes del CSV_5\n";
echo '   php artisan app:create-blocked-product-documents --force --limit='.$ordersWithBlockedNoDocs->count()."\n\n";
