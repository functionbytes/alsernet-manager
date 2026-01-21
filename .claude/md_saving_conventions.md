# .md Files Saving Conventions

**Sistema de organización para guardar archivos .md según tipo, módulo y uso.**

---

## 📋 Tabla de Contenidos

- [Clasificación de Archivos](#clasificación-de-archivos)
- [Estructura de Carpetas](#estructura-de-carpetas)
- [Dónde Guardar Cada Tipo](#dónde-guardar-cada-tipo)
- [Convención de Nombres](#convención-de-nombres)
- [Reglas de Decisión](#reglas-de-decisión)

---

## Clasificación de Archivos

### Tipos de Contenido

```
1. GUIDES (Guías de Implementación)
   - Para ayudar a desarrolladores a implementar
   - Copy-paste ready code
   - Ejemplos reales

2. SPECIFICATIONS (Especificaciones)
   - Diseño de sistemas
   - Arquitectura
   - Esquemas

3. REFERENCES (Referencias Rápidas)
   - Quick reference
   - Checklists
   - Command lists

4. PATTERNS (Patrones)
   - Patrones de diseño
   - Best practices
   - Anti-patterns

5. DOCUMENTATION (Documentación)
   - Explicación de módulos
   - Cómo funcionan las cosas
   - Arquitectura interna

6. IMPLEMENTATION (Implementación)
   - Resultados de tareas
   - Archivos generados
   - Ejemplos específicos
```

---

## Estructura de Carpetas

### .claude/ (Sistema de Agentes)

```
.claude/
├── agents/
│   ├── plan/
│   │   ├── plan-design.md              (SPECIFICATION)
│   │   └── capabilities.md             (REFERENCE)
│   │
│   ├── frontend/
│   │   ├── frontend-design.md          (SPECIFICATION)
│   │   └── capabilities.md             (REFERENCE)
│   │
│   └── backend/
│       ├── backend-design.md           (SPECIFICATION)
│       └── capabilities.md             (REFERENCE)
│
├── guides/
│   ├── plan/
│   │   ├── plan-agent-quick-start.md           (GUIDE)
│   │   ├── feature-planning-guide.md           (GUIDE)
│   │   ├── architecture-planning-guide.md      (GUIDE)
│   │   ├── task-breakdown-guide.md             (GUIDE)
│   │   ├── risk-assessment-guide.md            (GUIDE)
│   │   └── how-to-request-changes.md           (GUIDE)
│   │
│   ├── frontend/
│   │   ├── jquery-patterns.md                  (PATTERNS)
│   │   ├── component-building.md               (GUIDE)
│   │   ├── form-handling.md                    (GUIDE)
│   │   └── real-time-integration.md            (GUIDE)
│   │
│   └── backend/
│       ├── creating-new-module.md              (GUIDE)
│       └── api-endpoint-patterns.md            (PATTERNS)
│
├── reference/
│   ├── ARTISAN_COMMANDS.md             (REFERENCE - auto-generated)
│   └── QUICK_REFERENCE.md              (REFERENCE)
│
├── setup/
│   └── hooks/
│       └── pre-commit                  (SCRIPT)
│
└── database-optimization/
    ├── DENORMALIZACION_GUIA.md         (GUIDE)
    ├── OPTIMIZACION_DB_GUIA.md         (GUIDE)
    └── WAREHOUSE_QUICK_REFERENCE.md    (REFERENCE)
```

### /docs (Documentación Temática)

```
docs/
├── guides/                             (GUIDES - Prácticas)
│   ├── database-patterns.md            (PATTERNS + GUIDE)
│   ├── api-standards.md                (PATTERNS + GUIDE)
│   ├── security-patterns.md            (PATTERNS + GUIDE)
│   └── testing-standards.md            (PATTERNS + GUIDE)
│
├── api/                                (SPECIFICATIONS)
│   └── [API specs por módulo]
│
├── backend/                            (DOCUMENTATION)
│   ├── guides/
│   └── [Docs por módulo]
│
├── frontend/                           (DOCUMENTATION)
│   ├── guides/
│   ├── components/
│   ├── patterns/
│   └── [Docs por módulo]
│
├── database/                           (DOCUMENTATION)
│   ├── [Esquemas por módulo]
│   └── [Migration guides]
│
├── devops/                             (DOCUMENTATION)
│   └── [Config guides]
│
├── reference/                          (REFERENCES)
│   └── [Quick refs]
│
├── implementation/                     (IMPLEMENTATION)
│   └── [Examples y resultados]
│
└── planning/                           (DOCUMENTATION)
    └── [Planes y diseños]
```

---

## Dónde Guardar Cada Tipo

### 1. GUIDES (Guías de Implementación)

**Ubicación:** `.claude/guides/{agent}/` o `docs/guides/`

**Cuándo crear:**
- ✅ Explicar cómo hacer algo paso a paso
- ✅ Incluyen code examples copy-paste ready
- ✅ Son usadas por los agentes para implementar

**Cuándo NO crear:**
- ❌ Si es apenas una especificación
- ❌ Si no tiene ejemplos prácticos

**Ejemplo:**
```
.claude/guides/backend/creating-new-module.md
docs/guides/api-standards.md
```

---

### 2. SPECIFICATIONS (Especificaciones)

**Ubicación:** `.claude/agents/{agent}/` o `docs/api/`

**Cuándo crear:**
- ✅ Diseño de agente o sistema
- ✅ Define qué puede hacer algo
- ✅ Arquitectura de un módulo

**Cuándo NO crear:**
- ❌ Si es implementación específica

**Ejemplo:**
```
.claude/agents/backend/backend-design.md
docs/api/products-api-spec.md
```

---

### 3. REFERENCES (Referencias Rápidas)

**Ubicación:** `.claude/reference/` o `docs/reference/`

**Cuándo crear:**
- ✅ Checklist o lista rápida
- ✅ Referencia de comandos
- ✅ Quick lookup

**Cuándo NO crear:**
- ❌ Si necesita explicación detallada

**Ejemplo:**
```
.claude/reference/QUICK_REFERENCE.md
docs/reference/artisan-commands.md
```

---

### 4. PATTERNS (Patrones)

**Ubicación:** `.claude/guides/{agent}/` o `docs/guides/`

**Cuándo crear:**
- ✅ Patrón de diseño o arquitectura
- ✅ Best practices
- ✅ Incluye ejemplos de "bien" vs "mal"

**Cuándo NO crear:**
- ❌ Si es solo una guía paso a paso

**Ejemplo:**
```
.claude/guides/frontend/jquery-patterns.md
docs/guides/database-patterns.md
```

---

### 5. DOCUMENTATION (Documentación)

**Ubicación:** `docs/{module}/`

**Cuándo crear:**
- ✅ Explicar cómo funciona un módulo existente
- ✅ Arquitectura interna
- ✅ Decisiones de diseño

**Cuándo NO crear:**
- ❌ Si es para enseñar a implementar (usa GUIDE)
- ❌ Si es histórico o no se mantiene

**Ejemplo:**
```
docs/frontend/components/
docs/backend/models-architecture.md
docs/database/schema-overview.md
```

---

### 6. IMPLEMENTATION (Implementación)

**Ubicación:** `docs/implementation/{module}/`

**Cuándo crear:**
- ✅ Resultado de una tarea completada
- ✅ Ejemplo de implementación real
- ✅ Para referencia futura

**Cuándo NO crear:**
- ❌ Si va a cambiar pronto (usar en commit message)
- ❌ Si es solo temporal

**Ejemplo:**
```
docs/implementation/products/
docs/implementation/warehouse/
```

---

## Convención de Nombres

### Por Tipo

```
GUIDES:
- {feature}-guide.md
- {topic}-guide.md
Ej: database-patterns.md, api-standards.md

SPECIFICATIONS:
- {agent/module}-design.md
- {agent/module}-spec.md
Ej: backend-design.md, api-spec.md

REFERENCES:
- QUICK_REFERENCE.md
- {TOPIC}_REFERENCE.md
Ej: ARTISAN_COMMANDS.md

PATTERNS:
- {topic}-patterns.md
Ej: database-patterns.md, jquery-patterns.md

DOCUMENTATION:
- {module}-{aspect}.md
- {module}-overview.md
Ej: products-architecture.md

IMPLEMENTATION:
- {feature}-implementation.md
- {feature}-examples.md
Ej: products-crud-implementation.md
```

---

## Reglas de Decisión

### Preguntate antes de crear un .md:

```
¿Es para enseñar a implementar algo?
  SÍ → GUIDE (.claude/guides/ o docs/guides/)
  NO → Siguiente pregunta

¿Es una especificación de agente o sistema?
  SÍ → SPECIFICATION (.claude/agents/ o docs/api/)
  NO → Siguiente pregunta

¿Es para referencia rápida/checklist?
  SÍ → REFERENCE (.claude/reference/ o docs/reference/)
  NO → Siguiente pregunta

¿Son patrones o best practices?
  SÍ → PATTERNS (docs/guides/)
  NO → Siguiente pregunta

¿Es explicación de módulo existente?
  SÍ → DOCUMENTATION (docs/{module}/)
  NO → Siguiente pregunta

¿Es resultado de una implementación?
  SÍ → IMPLEMENTATION (docs/implementation/)
  NO → ¿REALMENTE NECESITAS ESTE ARCHIVO?
       → Si es temporal: NO crear
       → Si es histórico: NO crear
       → Si es uno-off: NO crear
```

---

## Matriz de Decisión

| Tipo | Propósito | Ubicación | Ejemplo |
|------|-----------|-----------|---------|
| **GUIDE** | Enseñar a hacer | `.claude/guides/` | how-to-create-api.md |
| **SPEC** | Definir qué es | `.claude/agents/` | backend-design.md |
| **PATTERN** | Mostrar patrón | `docs/guides/` | database-patterns.md |
| **REFERENCE** | Lookup rápido | `.claude/reference/` | QUICK_REFERENCE.md |
| **DOC** | Explicar módulo | `docs/{module}/` | products-overview.md |
| **IMPL** | Ejemplo real | `docs/implementation/` | products-example.md |

---

## Reglas de No Crear

❌ **NO crear .md si:**

1. **Es histórico**
   - Fue válido hace meses pero cambió
   - No se mantiene actualmente

2. **Es duplicado**
   - Existe información igual en otro lado
   - No agrega valor nuevo

3. **Es uno-off**
   - Se usa una sola vez
   - No tiene reutilización

4. **Es muy temporal**
   - Plan que mañana cambia
   - Notas personales
   - Investigación puntual

5. **Es demasiado específico**
   - Solo aplica a una tarea
   - No es patrón o pauta general

6. **No lo van a usar los agentes**
   - No ayuda a planificar
   - No ayuda a implementar
   - No es referencia útil

---

## Flujo de Creación

```
1. Determinar TIPO
   ↓
2. Decidir si REALMENTE es necesario
   ↓
   NO → NO crear
   ↓
   SÍ → Siguiente
   ↓
3. Seleccionar UBICACIÓN según tipo
   ↓
4. Nombrar según convención
   ↓
5. Crear archivo
   ↓
6. Actualizar índices (si aplica)
   ↓
7. Commit con explicación de por qué
```

---

## Ejemplos de Decisiones

### ✅ Crear

```
"Cómo crear un API endpoint con validación"
→ GUIDE → .claude/guides/backend/creating-endpoints.md
→ ¿Valor? SÍ (reutilizable)
→ ¿Lo usarán? SÍ (Backend Agent)

"Patrones de seguridad en Laravel"
→ PATTERN → docs/guides/security-patterns.md
→ ¿Valor? SÍ (patrón general)
→ ¿Lo usarán? SÍ (todos los agentes)

"Quick reference de artisan commands"
→ REFERENCE → .claude/reference/ARTISAN_COMMANDS.md
→ ¿Valor? SÍ (lookup rápido)
→ ¿Lo usarán? SÍ (desarrolladores)
```

### ❌ NO Crear

```
"Notas de la reunión del 30 de noviembre"
→ Histórico, personal
→ NO crear .md permanente

"Cómo instalé Redis el día de hoy"
→ Muy específico, una sola vez
→ NO crear .md

"Draft de API endpoint para feature X"
→ Temporal, va a cambiar
→ Usar en commit message o branch, no .md

"Problema con warehouse que ya solucionamos"
→ Histórico, no se mantiene
→ NO crear .md
```

---

## Mantenimiento

### Actualizar Índices Cuando:

- ✅ Crear GUIDE nueva
- ✅ Crear PATTERN nueva
- ✅ Crear REFERENCE nueva
- ✅ Cambiar ubicación de archivo

### NO Actualizar Índices Para:

- ❌ Cambios internos de archivo
- ❌ Correcciones de typos
- ❌ Mejoras de contenido

---

## Checklista Antes de Crear

```
□ ¿Esto va a ser usado por algún agente?
□ ¿Otros desarrolladores lo van a reutilizar?
□ ¿Es un patrón/guía general o específico?
□ ¿Voy a mantenerlo actualizado?
□ ¿Hay documentación similar?
□ ¿Realmente necesito un .md o basta un commit message?
□ ¿Tengo ejemplos prácticos?
□ ¿Sé exactamente dónde va en la estructura?
□ ¿El nombre sigue la convención?
□ ¿Voy a linkarlo desde algún índice?

Si NO a cualquiera → Reconsiderar crear
Si SÍ a todas → Crear el .md
```

---

**Última actualización:** Noviembre 30, 2025
**Versión:** 1.0
**Status:** Production Ready ✅
