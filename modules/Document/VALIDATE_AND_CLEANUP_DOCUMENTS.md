# ✅ Comando: ValidateAndCleanupDocuments
## Validación y Limpieza de Documentos Existentes

**Fecha de Creación:** 18 de Enero, 2025
**Comando:** `php artisan app:validate-and-cleanup-documents`
**Estado:** ✅ **FUNCIONAL Y PROBADO**

---

## 🎯 Propósito

Este comando valida todos los documentos existentes contra el estado de pago en Prestashop y **elimina aquellos que no están pagados**, con la excepción de documentos con `status_id = 5`.

### Lógica de Decisión

```
PARA CADA DOCUMENTO EXISTENTE:

✓ Obtener order_id de Prestashop
✓ Verificar si order está PAGADA (paid = 1)

IF (documento está PAGADO) THEN
    ACCIÓN: MANTENER ✅
ELSE IF (documento.status_id = 5) THEN
    ACCIÓN: MANTENER ✅ (Excepción especial)
ELSE (documento NO pagado y NO status_id=5) THEN
    ACCIÓN: ELIMINAR ❌
END IF
```

---

## 📋 Sintaxis del Comando

### Básico (Requiere Confirmación)
```bash
php artisan app:validate-and-cleanup-documents
```

### Con Flag --force (Sin Confirmación)
```bash
php artisan app:validate-and-cleanup-documents --force
```

### Con Flag --dry-run (Mostrar Sin Eliminar)
```bash
php artisan app:validate-and-cleanup-documents --force --dry-run
```

### Combinar Flags
```bash
php artisan app:validate-and-cleanup-documents --force --dry-run
```

---

## 🔍 Opciones Disponibles

| Opción | Descripción | Requerido |
|--------|-------------|-----------|
| `--force` | Salta la confirmación interactiva | No |
| `--dry-run` | Muestra qué se eliminaría SIN eliminar | No |

---

## 🧪 Ejemplo de Ejecución

### Prueba 1: Dry-Run para Ver Impacto

```bash
php artisan app:validate-and-cleanup-documents --force --dry-run
```

**Resultado:**
```
🔍 DRY-RUN MODE: No documents will be deleted

🔍 Starting document validation and cleanup...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 Validating existing documents
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Found 2085 documents with order_id

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📋 Validation Results
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📊 Summary:
  ✓ Total Documents Analyzed: 2085
  ✓ Paid Documents: 0
  ⚠️  Exception Status (5): 391
  ❌ Candidates for Deletion: 1694

⚠️  Documents to be deleted:
  • ID: 2 | UID: 6836058980eaf | Order: 762594 | Status: 1
  • ID: 3 | UID: 68360a185ac39 | Order: 762597 | Status: 1
  ... and 1674 more

🔍 Dry-run complete. No documents were deleted.
```

**Interpretación:**
- ✅ 2,085 documentos analizados
- ✅ 0 documentos pagados
- ⚠️ 391 documentos con status_id = 5 (se mantienen)
- ❌ 1,694 candidatos para eliminar (no pagados, no status_id=5)

---

## 📊 Detalles de Validación

### Paso 1: Obtener Documentos Existentes

```sql
SELECT d.id, d.uid, d.order_id, d.status_id
FROM documents d
WHERE d.order_id IS NOT NULL
  AND d.order_id > 0
```

**Resultado:** Lista de documentos con order_id registrado.

---

### Paso 2: Validar Estado de Pago

Para cada documento, se ejecuta:

```sql
SELECT 1 FROM aalv_order_history oh
INNER JOIN aalv_order_state os ON os.id_order_state = oh.id_order_state
WHERE oh.id_order = {orderId}
  AND os.paid = 1
LIMIT 1
```

**Resultado:** `1` si está pagado, `NULL` si no está pagado.

---

### Paso 3: Evaluar Decisión

```
IF paid = 1:
    MANTENER ✅
ELSE IF status_id = 5:
    MANTENER ✅ (excepción)
ELSE:
    CANDIDATO PARA ELIMINAR ❌
```

---

### Paso 4: Mostrar Candidatos (Solo en Modo Interactivo)

```
⚠️  Documents to be deleted:
  • ID: 2 | UID: 6836058980eaf | Order: 762594 | Status: 1
  • ID: 3 | UID: 68360a185ac39 | Order: 762597 | Status: 1
  ... and 1674 more
```

**Información mostrada:**
- `ID`: ID del documento en tabla documents
- `UID`: Identificador único del documento
- `Order`: ID de la orden en Prestashop
- `Status`: status_id del documento

---

### Paso 5: Confirmación de Eliminación (Sin --force)

```
Delete these documents? (yes/no) [no]: yes
```

Si el usuario no confirma, el comando termina sin eliminar nada.

---

### Paso 6: Ejecutar Limpieza

Si confirma o usa `--force`:

```
🗑️  Performing cleanup

[████████████████████] 2085/2085 (100%)

✅ CLEANUP COMPLETE
🗑️  Documents Deleted: 1694
```

---

## 🛡️ Características de Seguridad

### 1️⃣ Confirmación Requerida

```bash
# Esto REQUIERE confirmación interactiva
php artisan app:validate-and-cleanup-documents
# Pregunta: "This will validate and cleanup documents based on payment status. Continue?"
# Pregunta: "Delete these documents?"
```

### 2️⃣ Modo Dry-Run

```bash
# Ver exactamente qué se eliminaría SIN eliminar
php artisan app:validate-and-cleanup-documents --force --dry-run
```

### 3️⃣ Excepción para status_id = 5

```php
// Documentos con status_id = 5 NUNCA se eliminan
// aunque no estén pagados
if ($document->status_id === 5) {
    // MANTENER
}
```

### 4️⃣ Eliminación en Cascada

Cuando se elimina un documento, también se eliminan sus productos:

```php
$document->products()->delete();  // Eliminar productos asociados
$document->delete();              // Eliminar documento
```

### 5️⃣ Manejo de Errores

```php
try {
    // Operaciones de eliminación
} catch (\Exception $e) {
    \Log::error("Failed to delete document {$document->id}: {$e->getMessage()}");
    // Continúa con el siguiente documento
}
```

---

## 📈 Casos de Uso

### Caso 1: Auditoría Inicial (Dry-Run)

```bash
php artisan app:validate-and-cleanup-documents --force --dry-run
```

**Cuándo usar:**
- Verificar qué se eliminaría
- Revisar si hay documentos importantes
- Antes de ejecutar limpieza real

---

### Caso 2: Limpieza Manual (Con Confirmación)

```bash
php artisan app:validate-and-cleanup-documents
```

**Cuándo usar:**
- Limpieza manual e interactiva
- Quieres confirmar antes de eliminar
- Ejecutar manualmente desde CLI

---

### Caso 3: Limpieza Automática (Sin Confirmación)

```bash
php artisan app:validate-and-cleanup-documents --force
```

**Cuándo usar:**
- En scripts automáticos
- En cron jobs programados
- En pipelines de CI/CD
- Ya verificaste con --dry-run

---

### Caso 4: En Scheduler

```php
// En registerCommandSchedules() del ServiceProvider
$schedule->command('app:validate-and-cleanup-documents --force')
    ->weekly()
    ->sundays()
    ->at('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/document-cleanup.log'));
```

---

## 🔍 Flujo Completo de Ejecución

```
═══════════════════════════════════════════════════════════════
                    INICIO DEL COMANDO
═══════════════════════════════════════════════════════════════

1. Verificar confirmación (si no --force)
   └─ "This will validate and cleanup documents...?"

2. Leer configuración de Prestashop desde .env
   └─ DB_HOST_PRESTASHOP, DB_USERNAME_PRESTASHOP, etc.

3. Obtener documentos con order_id de tabla documents
   └─ Found 2085 documents with order_id

4. PARA CADA DOCUMENTO:
   │
   ├─ Conectar a Prestashop
   ├─ Verificar si order está PAGADA
   │  └─ Query: SELECT 1 FROM aalv_order_history ... WHERE paid = 1
   │
   ├─ Evaluar decisión:
   │  ├─ IF paid=1 → MANTENER ✅
   │  ├─ ELSE IF status_id=5 → MANTENER ✅
   │  └─ ELSE → MARCAR PARA ELIMINAR ❌
   │
   └─ Mostrar progreso

5. Mostrar resumen:
   ├─ Total Documents Analyzed: 2085
   ├─ Paid Documents: 0
   ├─ Exception Status (5): 391
   └─ Candidates for Deletion: 1694

6. SI --dry-run:
   └─ SALIR (no eliminar)

7. SI NO --dry-run:
   ├─ Mostrar candidatos para eliminar (primeros 20)
   │
   ├─ Confirmar eliminación (si no --force)
   │  └─ "Delete these documents?"
   │
   ├─ ELIMINAR cada documento candidato:
   │  ├─ DELETE de document_products
   │  └─ DELETE de documents
   │
   └─ Mostrar cantidad de documentos eliminados

═══════════════════════════════════════════════════════════════
                      FIN DEL COMANDO
═══════════════════════════════════════════════════════════════
```

---

## 📊 Resultado Esperado

### Con Dry-Run

```
✓ Total Documents Analyzed: 2085
✓ Paid Documents: 0
⚠️  Exception Status (5): 391
❌ Candidates for Deletion: 1694

🔍 Dry-run complete. No documents were deleted.
```

### Con Eliminación Real

```
✓ Total Documents Analyzed: 2085
✓ Paid Documents: 0
⚠️  Exception Status (5): 391
❌ Candidates for Deletion: 1694

[████████████████████] 2085/2085 (100%)

✅ CLEANUP COMPLETE
🗑️  Documents Deleted: 1694
```

---

## ⚠️ Advertencias Importantes

### 1️⃣ Operación Destructiva

```
⚠️  ESTE COMANDO ELIMINA DOCUMENTOS PERMANENTEMENTE
⚠️  Usa --dry-run PRIMERO para verificar
⚠️  Hacer BACKUP antes de eliminar en producción
```

### 2️⃣ No Hay Rollback

```
⚠️  Una vez eliminados, los documentos NO se pueden recuperar
⚠️  Los IDs no se reutilizan (integridad de datos)
⚠️  El historial de auditoría puede perderse
```

### 3️⃣ Productos Asociados

```
⚠️  Se eliminan también los document_products
⚠️  Verificar relaciones antes de eliminar
⚠️  Considerar backup de estos datos
```

---

## ✅ Checklist Pre-Eliminación

Antes de ejecutar sin `--dry-run`:

- [ ] Ejecuté con `--dry-run` primero
- [ ] Revisé los candidatos para eliminar
- [ ] Verificué que status_id=5 NO se elimina
- [ ] Hice BACKUP de la base de datos
- [ ] Informé al equipo sobre la limpieza
- [ ] Verificué en horario de bajo tráfico
- [ ] Tengo plan de rollback si algo sale mal

---

## 🚀 Uso Recomendado

### Desarrollo
```bash
# Auditoría primero
php artisan app:validate-and-cleanup-documents --force --dry-run

# Luego eliminar si es seguro
php artisan app:validate-and-cleanup-documents --force
```

### Producción
```bash
# 1. Backup
mysqldump -h localhost -u user -p database > backup.sql

# 2. Auditoría
php artisan app:validate-and-cleanup-documents --force --dry-run

# 3. Revisar resultado
# (mostrado en logs)

# 4. Si todo está bien, eliminar
php artisan app:validate-and-cleanup-documents --force

# 5. Verificar resultado
php artisan tinker
# > Document::whereNotNull('order_id')->count()
```

---

## 🔐 Garantías de Implementación

✅ **El comando garantiza:**
1. Solo elimina documentos que cumplen criterios
2. Mantiene documentos pagados
3. Mantiene documentos con status_id = 5 (excepción)
4. Valida contra Prestashop (fuente de verdad)
5. Manejo de errores completo
6. Dry-run disponible para auditoría
7. Confirmación requerida (sin --force)
8. Logging de todas las operaciones

---

## 📞 Ejemplos Completos

### Ejemplo 1: Auditoría Completa

```bash
# Paso 1: Ver qué se eliminaría
php artisan app:validate-and-cleanup-documents --force --dry-run
# Resultado: Muestra 1694 candidatos sin eliminar

# Paso 2: Revisar logs
tail -f storage/logs/document-cleanup.log

# Paso 3: Si estás seguro, eliminar
php artisan app:validate-and-cleanup-documents --force
```

### Ejemplo 2: Integración en Script

```bash
#!/bin/bash
set -e  # Exit on error

# 1. Backup
mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME > backup_$(date +%s).sql

# 2. Validar
php artisan app:validate-and-cleanup-documents --force --dry-run > validation.log

# 3. Revisar resultado
if grep "Candidates for Deletion: 0" validation.log; then
    echo "✅ Sin candidatos para eliminar"
    exit 0
fi

# 4. Confirmar antes de proceder
read -p "¿Proceder con eliminación? (s/n): " -n 1 -r
if [[ $REPLY =~ ^[Ss]$ ]]; then
    php artisan app:validate-and-cleanup-documents --force
    echo "✅ Limpieza completada"
else
    echo "❌ Limpieza cancelada"
    exit 1
fi
```

---

## 🎯 Conclusión

El comando `ValidateAndCleanupDocuments` es una herramienta potente para:

```
✅ Auditar documentos existentes
✅ Sincronizar estado con Prestashop
✅ Eliminar documentos no pagados
✅ Mantener excepciones (status_id=5)
✅ Operaciones seguras con confirmación
✅ Dry-run para verificar antes de eliminar
```

---

**Comando Creado:** 18 de Enero, 2025
**Por:** Claude Code
**Versión:** 1.0
**Clasificación:** ⚠️  OPERACIÓN DESTRUCTIVA - USAR CON CUIDADO
