<?php

/**
 * Script para validar documentos
 *
 * Valida que cada documento cumpla:
 * 1. La orden tiene estado 27 en aalv_order_history
 * 2. Los productos de la orden están en document_product_blockades
 */
$baseDir = __DIR__;
$projectRoot = dirname($baseDir);
$outputDir = $baseDir.'/output';

if (! file_exists($outputDir)) {
    mkdir($outputDir, 0755, true);
}

require $projectRoot.'/vendor/autoload.php';
$app = require_once $projectRoot.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 Validando documentos...\n\n";

// PASO 1: Obtener datos base en memoria
echo "⏳ Cargando datos base...\n";

$blockedProducts = DB::connection('mysql')
    ->table('document_product_blockades')
    ->distinct('product_id')
    ->where('product_id', '>', 0)  // Filtrar nulls
    ->pluck('product_id')
    ->toArray();
$blockedProductsSet = array_flip($blockedProducts);

echo '✅ Productos bloqueados: '.count($blockedProducts)."\n";

// Órdenes con estado 27
$ordersWithStatus27 = DB::connection('prestashop')
    ->table('aalv_order_history')
    ->where('id_order_state', 27)
    ->where('id_order', '>', 0)  // Filtrar nulls
    ->distinct('id_order')
    ->pluck('id_order')
    ->toArray();
$ordersWithStatus27Set = array_flip($ordersWithStatus27);

echo '✅ Órdenes con estado 27: '.count($ordersWithStatus27)."\n";

// PASO 2: Obtener todos los documentos
$allDocuments = DB::connection('mysql')
    ->table('documents')
    ->where('order_id', '>', 0)
    ->select('id', 'order_id', 'uid', 'status_id', 'created_at', 'validation_status')
    ->get();

echo '✅ Total de documentos: '.count($allDocuments)."\n";

// PASO 3: Obtener todos los productos de las órdenes (en cache)
echo "⏳ Mapeando productos por orden...\n";

$orderProducts = DB::connection('prestashop')
    ->table('aalv_order_detail')
    ->select('id_order', 'product_id')
    ->get();

// Crear mapa: order_id => [product_ids]
$orderProductMap = [];
foreach ($orderProducts as $item) {
    if (! isset($orderProductMap[$item->id_order])) {
        $orderProductMap[$item->id_order] = [];
    }
    $orderProductMap[$item->id_order][] = $item->product_id;
}

echo '✅ Órdenes con productos mapeadas: '.count($orderProductMap)."\n\n";

// PASO 4: Validar documentos
echo "🔍 Validando documentos...\n\n";

$validDocuments = [];
$invalidReasons = [
    'Sin estado 27' => [],
    'Sin productos bloqueados' => [],
    'Orden no encontrada' => [],
];

foreach ($allDocuments as $doc) {
    // Validación 1: ¿La orden tuvo estado 27?
    if (! isset($ordersWithStatus27Set[$doc->order_id])) {
        $invalidReasons['Sin estado 27'][] = $doc;

        continue;
    }

    // Validación 2: ¿Los productos de la orden están bloqueados?
    if (! isset($orderProductMap[$doc->order_id])) {
        $invalidReasons['Orden no encontrada'][] = $doc;

        continue;
    }

    $hasBlockedProducts = false;
    foreach ($orderProductMap[$doc->order_id] as $productId) {
        if (isset($blockedProductsSet[$productId])) {
            $hasBlockedProducts = true;
            break;
        }
    }

    if (! $hasBlockedProducts) {
        $invalidReasons['Sin productos bloqueados'][] = $doc;

        continue;
    }

    $validDocuments[] = $doc;
}

echo "📊 VALIDACIÓN COMPLETA:\n";
echo '   ✅ Documentos VÁLIDOS: '.count($validDocuments)."\n";
echo '   ❌ Documentos INVÁLIDOS: '.(count($allDocuments) - count($validDocuments))."\n\n";

echo "Desglose de inválidos:\n";
foreach ($invalidReasons as $reason => $docs) {
    echo "   • $reason: ".count($docs)."\n";
}

// PASO 5: Generar CSVs

// CSV de documentos válidos
$csv_valid = "doc_id,order_id,doc_uid,status_id,created_at,validation_status\n";
foreach ($validDocuments as $doc) {
    $csv_valid .= "{$doc->id},{$doc->order_id},\"{$doc->uid}\",{$doc->status_id},\"{$doc->created_at}\",\"{$doc->validation_status}\"\n";
}
file_put_contents($outputDir.'/CSV_7_DOCUMENTOS_VALIDOS.csv', $csv_valid);
echo "\n✅ CSV_7 generado: ".count($validDocuments)." documentos VÁLIDOS\n";

// CSV de documentos inválidos
$csv_invalid = "doc_id,order_id,doc_uid,status_id,created_at,validation_status,razon_invalido\n";
foreach ($invalidReasons as $reason => $docs) {
    foreach ($docs as $doc) {
        $csv_invalid .= "{$doc->id},{$doc->order_id},\"{$doc->uid}\",{$doc->status_id},\"{$doc->created_at}\",\"{$doc->validation_status}\",\"$reason\"\n";
    }
}
file_put_contents($outputDir.'/CSV_8_DOCUMENTOS_INVALIDOS.csv', $csv_invalid);
echo '❌ CSV_8 generado: '.(count($allDocuments) - count($validDocuments))." documentos INVÁLIDOS\n";

echo "\n✅ Validación completada\n";
echo "📁 Ubicación: $outputDir/\n\n";
