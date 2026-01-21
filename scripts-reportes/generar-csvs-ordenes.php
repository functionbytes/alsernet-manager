<?php

/**
 * Script para generar CSVs de órdenes pagadas vs documentos
 *
 * Uso: php generar-csvs-ordenes.php
 *
 * IMPORTANTE: Una orden está "pagada" si ALGUNA VEZ tuvo estado 27 en aalv_order_history
 *
 * Genera:
 * - CSV_1: Órdenes pagadas (NOV 1+) con productos bloqueados
 * - CSV_2: Órdenes pagadas (NOV 1+) con documentos
 * - CSV_3: Órdenes pagadas (NOV 1+) sin documentos pero CON bloqueados
 * - CSV_4: Documentos orfandos (órdenes que NUNCA fueron pagadas en ningún período)
 */

// Detectar directorio base
$baseDir = __DIR__;
$projectRoot = dirname($baseDir); // scripts-reportes -> manager (project root)
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

echo "🔄 Generando CSVs detallados de órdenes...\n\n";

// ============ OBTENER DATOS BASE ============
$blockedProducts = DB::connection('mysql')
    ->table('document_product_blockades')
    ->distinct('product_id')
    ->pluck('product_id')
    ->toArray();

echo '✅ Productos bloqueados encontrados: '.count($blockedProducts)."\n";

// IMPORTANTE: Órdenes PAGADAS = Las que ALGUNA VEZ tuvieron estado 27 (sin filtro de fecha)
$paidOrderIds = DB::connection('prestashop')
    ->table('aalv_order_history')
    ->where('id_order_state', 27)
    ->distinct('id_order')
    ->pluck('id_order')
    ->toArray();

// Para el análisis del PERÍODO NOV 1+, subset de órdenes pagadas
$paidOrderIdsNov = DB::connection('prestashop')
    ->table('aalv_orders as o')
    ->join('aalv_order_history as oh', 'o.id_order', '=', 'oh.id_order')
    ->where('oh.id_order_state', 27)
    ->where('o.date_add', '>=', '2025-05-27')
    ->distinct('o.id_order')
    ->pluck('o.id_order')
    ->toArray();

$ordersWithDocs = DB::connection('mysql')
    ->table('documents')
    ->whereIn('order_id', $paidOrderIdsNov)
    ->distinct('order_id')
    ->pluck('order_id')
    ->toArray();

$paidOrdersInfo = DB::connection('prestashop')
    ->table('aalv_orders as o')
    ->join('aalv_order_history as oh', 'o.id_order', '=', 'oh.id_order')
    ->where('oh.id_order_state', 2)
    ->where('o.date_add', '>=', '2025-05-27')
    ->select('o.id_order', 'o.reference', 'o.date_add')
    ->distinct('o.id_order')
    ->orderBy('o.id_order', 'desc')
    ->get();

echo "🔄 Generando análisis de órdenes pagadas (estado 27)...\n";
echo '✅ Órdenes pagadas (NOV 1+, estado 27): '.count($paidOrderIdsNov)."\n";
echo '✅ Órdenes con documentos (NOV 1+): '.count($ordersWithDocs)."\n";

// ============ CSV 1: ÓRDENES CON BLOQUEADOS ============
$ordersWithBlocked = DB::connection('prestashop')
    ->table('aalv_order_detail as od')
    ->join('aalv_orders as o', 'od.id_order', '=', 'o.id_order')
    ->whereIn('od.id_order', $paidOrderIdsNov)
    ->whereIn('od.product_id', $blockedProducts)
    ->where('o.date_add', '>=', '2025-05-27')
    ->select('o.id_order', 'o.reference', 'o.date_add', 'od.product_id', 'od.product_name')
    ->distinct()
    ->orderBy('o.id_order', 'desc')
    ->get();

$csv1 = "id_order,reference,date_add,product_id,product_name\n";
foreach ($ordersWithBlocked as $row) {
    $csv1 .= "{$row->id_order},\"{$row->reference}\",\"{$row->date_add}\",{$row->product_id},\"{$row->product_name}\"\n";
}
file_put_contents($outputDir.'/CSV_1_ORDENES_PAGADAS_CON_BLOQUEADOS.csv', $csv1);
echo '✅ CSV_1 generado: '.$ordersWithBlocked->count()." registros\n";

// ============ CSV 2: ÓRDENES CON DOCUMENTOS ============
$ordersWithDocsList = DB::connection('mysql')
    ->table('documents')
    ->whereIn('order_id', $paidOrderIdsNov)
    ->select('order_id', 'id', 'uid', 'status_id', 'created_at', 'validation_status')
    ->orderBy('order_id', 'desc')
    ->get();

$paidMap = [];
foreach ($paidOrdersInfo as $o) {
    $paidMap[$o->id_order] = ['ref' => $o->reference, 'date' => $o->date_add];
}

$csv2 = "id_order,reference,date_add,doc_id,doc_uid,doc_status,doc_created_at,doc_validation_status\n";
foreach ($ordersWithDocsList as $doc) {
    if (isset($paidMap[$doc->order_id])) {
        $csv2 .= "{$doc->order_id},\"{$paidMap[$doc->order_id]['ref']}\",\"{$paidMap[$doc->order_id]['date']}\",{$doc->id},\"{$doc->uid}\",{$doc->status_id},\"{$doc->created_at}\",\"{$doc->validation_status}\"\n";
    }
}
file_put_contents($outputDir.'/CSV_2_ORDENES_PAGADAS_CON_DOCUMENTOS.csv', $csv2);
echo '✅ CSV_2 generado: '.count($ordersWithDocsList)." registros\n";

// ============ CSV 3: ÓRDENES CON BLOQUEADOS SIN DOCUMENTOS ============
$docsOrderIds = $ordersWithDocsList->pluck('order_id')->unique()->toArray();
$ordersWithBlockedNoDoc = DB::connection('prestashop')
    ->table('aalv_order_detail as od')
    ->join('aalv_orders as o', 'od.id_order', '=', 'o.id_order')
    ->whereIn('od.id_order', $paidOrderIdsNov)
    ->whereNotIn('od.id_order', $docsOrderIds)
    ->whereIn('od.product_id', $blockedProducts)
    ->where('o.date_add', '>=', '2025-05-27')
    ->select('o.id_order', 'o.reference', 'o.date_add', 'od.product_id', 'od.product_name')
    ->distinct()
    ->orderBy('o.id_order', 'desc')
    ->get();

$csv3 = "id_order,reference,date_add,product_id,product_name,necesita_documento\n";
foreach ($ordersWithBlockedNoDoc as $row) {
    $csv3 .= "{$row->id_order},\"{$row->reference}\",\"{$row->date_add}\",{$row->product_id},\"{$row->product_name}\",SÍ\n";
}
file_put_contents($outputDir.'/CSV_3_ORDENES_PAGADAS_SIN_DOCUMENTOS_CON_BLOQUEADOS.csv', $csv3);
echo '✅ CSV_3 generado: '.$ordersWithBlockedNoDoc->count()." registros\n";

// ============ CSV 4: DOCUMENTOS ORFANDOS ============
// Documentos cuyas órdenes NUNCA fueron pagadas
// Método: Obtener todos los documentos y filtrar con PHP para evitar error de placeholders

// Primero, obtener todas las órdenes que ALGUNA VEZ fueron pagadas (estado 27)
$paidOrderIds = DB::connection('prestashop')
    ->table('aalv_order_history')
    ->where('id_order_state', 27)
    ->distinct('id_order')
    ->pluck('id_order')
    ->toArray();

echo "\n✅ Órdenes pagadas TOTALES (sin filtro de fecha): ".count($paidOrderIds)."\n";

// Obtener TODOS los documentos
$allDocs = DB::connection('mysql')
    ->table('documents')
    ->where('order_id', '>', 0)
    ->select('order_id', 'id', 'uid', 'status_id', 'created_at', 'validation_status')
    ->orderBy('order_id', 'desc')
    ->get();

// Filtrar con PHP para obtener solo los orfandos
$paidOrdersSet = array_flip($paidOrderIds); // Más rápido para búsquedas
$orphanedDocs = $allDocs->filter(function ($doc) use ($paidOrdersSet) {
    return ! isset($paidOrdersSet[$doc->order_id]);
});

$csv4 = "order_id,doc_id,doc_uid,doc_status,doc_created_at,doc_validation_status\n";
foreach ($orphanedDocs as $doc) {
    $csv4 .= "{$doc->order_id},{$doc->id},\"{$doc->uid}\",{$doc->status_id},\"{$doc->created_at}\",\"{$doc->validation_status}\"\n";
}
file_put_contents($outputDir.'/CSV_4_DOCUMENTOS_ORFANDOS.csv', $csv4);
echo '✅ CSV_4 generado: '.count($orphanedDocs)." registros\n";

echo "\n✅ TODOS LOS CSVs GENERADOS CORRECTAMENTE\n";
echo "\n📁 Ubicación: $outputDir/\n";
echo "\n📊 Resumen General:\n";
echo '   • Órdenes pagadas (NOV 1+): '.count($paidOrderIdsNov)."\n";
echo '   • Órdenes pagadas TOTALES (sin filtro): '.count($paidOrderIds)."\n";
echo "\n📋 CSVs Generados:\n";
echo '   • CSV_1: '.$ordersWithBlocked->count()." órdenes con bloqueados (NOV 1+)\n";
echo '   • CSV_2: '.count($ordersWithDocsList)." órdenes con documentos (NOV 1+)\n";
echo '   • CSV_3: '.$ordersWithBlockedNoDoc->count()." órdenes bloqueadas sin docs (CRÍTICAS)\n";
echo '   • CSV_4: '.count($orphanedDocs)." documentos orfandos (órdenes NUNCA pagadas)\n\n";

echo '🚀 Próximo paso: Crear documentos para las '.$ordersWithBlockedNoDoc->count()." órdenes del CSV_3\n";
echo '   php artisan app:create-blocked-product-documents --force --limit='.$ordersWithBlockedNoDoc->count()."\n\n";
