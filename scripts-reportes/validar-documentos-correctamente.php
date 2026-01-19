<?php

/**
 * Script para validar documentos CORRECTAMENTE
 *
 * Valida que cada documento cumpla:
 * 1. La orden tiene estado 27 en aalv_order_history
 * 2. Los productos (con su atributo) de la orden están en document_product_blockades
 *    para el mismo type_id del documento
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

echo "🔍 Validando documentos CORRECTAMENTE (con atributos)...\n\n";

// PASO 1: Obtener órdenes con estado 27
echo "⏳ Cargando órdenes con estado 27...\n";

$ordersWithStatus27 = DB::connection('prestashop')
    ->table('aalv_order_history')
    ->where('id_order_state', 27)
    ->where('id_order', '>', 0)
    ->distinct('id_order')
    ->pluck('id_order')
    ->toArray();
$ordersWithStatus27Set = array_flip($ordersWithStatus27);

echo '✅ Órdenes con estado 27: '.count($ordersWithStatus27)."\n";

// PASO 2: Obtener todos los documentos
echo "⏳ Cargando documentos...\n";

$allDocuments = DB::connection('mysql')
    ->table('documents')
    ->where('order_id', '>', 0)
    ->select('id', 'order_id', 'uid', 'type_id', 'status_id', 'created_at', 'validation_status')
    ->get();

echo '✅ Total de documentos: '.count($allDocuments)."\n";

// PASO 3: Obtener todas las blockades en memoria
echo "⏳ Cargando blockades...\n";

$blockades = DB::connection('mysql')
    ->table('document_product_blockades')
    ->select('product_id', 'product_attribute_id', 'document_type_id')
    ->get();

// Crear mapa de blockades aplicando la misma lógica de atributos:
// - Si product_attribute_id = 0 (producto simple): clave = "product_id|type_id"
// - Si product_attribute_id > 0 (variante): clave = "attribute_id|type_id"
// - Si product_attribute_id = NULL: crear ambas claves para máxima compatibilidad
$blockadeMap = [];
foreach ($blockades as $b) {
    $prod = $b->product_id;
    $attr = $b->product_attribute_id;
    $type = $b->document_type_id;

    if ($attr == 0 || $attr === null) {
        // Producto simple o NULL: crear clave con product_id
        $key = "prod:{$prod}|{$type}";
        $blockadeMap[$key] = true;
    } else {
        // Variante específica: crear clave con attribute_id
        $key = "attr:{$attr}|{$type}";
        $blockadeMap[$key] = true;
    }

    // Si attribute_id es NULL, también crear claves wildcard para flexibilidad
    if ($attr === null && $prod !== null) {
        // Wildcard para cualquier atributo de este producto
        $key = "prod:{$prod}|{$type}";
        $blockadeMap[$key] = true;
    }
}

echo '✅ Registros en blockades: '.count($blockades)."\n";

// PASO 4: Obtener productos de órdenes con atributos
echo "⏳ Mapeando productos con atributos...\n";

$orderProducts = DB::connection('prestashop')
    ->table('aalv_order_detail')
    ->select('id_order', 'product_id', 'product_attribute_id')
    ->get();

// Crear mapa: order_id => [(product_id, attribute_id), ...]
$orderProductMap = [];
foreach ($orderProducts as $item) {
    if (! isset($orderProductMap[$item->id_order])) {
        $orderProductMap[$item->id_order] = [];
    }
    $orderProductMap[$item->id_order][] = [
        'product_id' => $item->product_id,
        'attribute_id' => $item->product_attribute_id,
    ];
}

echo '✅ Órdenes mapeadas: '.count($orderProductMap)."\n\n";

// PASO 5: Validar documentos
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

    // Validación 2: ¿La orden tiene productos?
    if (! isset($orderProductMap[$doc->order_id])) {
        $invalidReasons['Orden no encontrada'][] = $doc;

        continue;
    }

    // Validación 3: ¿Algún producto (con atributo) de la orden está bloqueado?
    // Lógica de búsqueda en blockadeMap:
    // - Si product_attribute_id = 0 (producto simple): buscar "prod:{product_id}|{type_id}"
    // - Si product_attribute_id > 0 (variante): buscar "attr:{attribute_id}|{type_id}"
    $hasBlockedProduct = false;
    foreach ($orderProductMap[$doc->order_id] as $product) {
        $prodId = $product['product_id'];
        $attrId = $product['attribute_id'];
        $typeId = $doc->type_id;

        if ($attrId == 0) {
            // Producto simple: buscar blockade con product_id
            $key = "prod:{$prodId}|{$typeId}";
            if (isset($blockadeMap[$key])) {
                $hasBlockedProduct = true;
                break;
            }
        } else {
            // Variante (attribute_id > 0): buscar blockade con attribute_id
            $key = "attr:{$attrId}|{$typeId}";
            if (isset($blockadeMap[$key])) {
                $hasBlockedProduct = true;
                break;
            }
        }
    }

    if (! $hasBlockedProduct) {
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

// PASO 6: Generar CSVs

// CSV de documentos válidos (con product_ids)
$csv_valid = "doc_id,order_id,doc_uid,type_id,status_id,created_at,validation_status,product_ids\n";
foreach ($validDocuments as $doc) {
    // Obtener los product_ids de la orden
    $productIds = [];
    if (isset($orderProductMap[$doc->order_id])) {
        foreach ($orderProductMap[$doc->order_id] as $product) {
            $productIds[] = $product['product_id'];
        }
    }
    $productIdsStr = implode('|', $productIds);

    $csv_valid .= "{$doc->id},{$doc->order_id},\"{$doc->uid}\",{$doc->type_id},{$doc->status_id},\"{$doc->created_at}\",\"{$doc->validation_status}\",\"{$productIdsStr}\"\n";
}
file_put_contents($outputDir.'/CSV_7_DOCUMENTOS_VALIDOS_CORRECTO.csv', $csv_valid);
echo "\n✅ CSV_7 generado: ".count($validDocuments)." documentos VÁLIDOS\n";

// CSV de documentos inválidos (con product_ids)
$csv_invalid = "doc_id,order_id,doc_uid,type_id,status_id,created_at,validation_status,razon_invalido,product_ids\n";
foreach ($invalidReasons as $reason => $docs) {
    foreach ($docs as $doc) {
        // Obtener los product_ids de la orden
        $productIds = [];
        if (isset($orderProductMap[$doc->order_id])) {
            foreach ($orderProductMap[$doc->order_id] as $product) {
                $productIds[] = $product['product_id'];
            }
        }
        $productIdsStr = implode('|', $productIds);

        $csv_invalid .= "{$doc->id},{$doc->order_id},\"{$doc->uid}\",{$doc->type_id},{$doc->status_id},\"{$doc->created_at}\",\"{$doc->validation_status}\",\"$reason\",\"{$productIdsStr}\"\n";
    }
}
file_put_contents($outputDir.'/CSV_8_DOCUMENTOS_INVALIDOS_CORRECTO.csv', $csv_invalid);
echo '❌ CSV_8 generado: '.(count($allDocuments) - count($validDocuments))." documentos INVÁLIDOS\n";

echo "\n✅ Validación completada\n";
echo "📁 Ubicación: $outputDir/\n\n";
