# Agentes de Alsernet - Guía de Uso

**Tres agentes especializados para construir características de Alsernet**

---

## 🎯 Agentes Disponibles

### 1️⃣ Plan Agent
**Ubicación:** `.claude/agents/plan/`

**Propósito:** Planificar estrategias de implementación, desglosar características complejas en tareas manejables

**Cuándo usar:**
- Necesitas descomponer una característica grande en pasos
- Quieres diseñar la arquitectura antes de codificar
- Necesitas validar requisitos y alcance
- Quieres identificar riesgos y bloqueadores

**Cómo usarlo:**
```
"@plan-agent, planifica un sistema de gestión de clientes que..."
```

**Acceso directo en Claude Code:**
```python
Task(
    subagent_type="plan-agent",
    prompt="Planifica una característica de..."
)
```

---

### 2️⃣ Backend Agent
**Ubicación:** `.claude/agents/backend/`

**Propósito:** Implementar APIs Laravel, controladores, servicios, y lógica de negocio

**Cuándo usar:**
- Necesitas crear un endpoint de API
- Quieres implementar lógica de base de datos
- Necesitas crear un servicio o job
- Quieres integrar con sistemas externos

**Cómo usarlo:**
```
"@backend-agent, crea un endpoint POST /clientes que..."
```

**Acceso directo en Claude Code:**
```python
Task(
    subagent_type="backend-agent",
    prompt="Crea un controlador que..."
)
```

**Stack que Entiende:**
- Laravel 12.x y PHP 8.3+
- PostgreSQL, MySQL, MongoDB
- Redis y caching
- Laravel Sanctum y JWT
- Queues y Jobs
- WebSockets con Laravel Reverb

---

### 3️⃣ Frontend Agent
**Ubicación:** `.claude/agents/frontend/`

**Propósito:** Construir componentes interactivos, formularios responsivos, y características de UI

**Cuándo usar:**
- Necesitas crear un formulario
- Quieres construir un componente responsivo
- Necesitas integrar con AJAX/API
- Quieres mejorar la experiencia del usuario

**Cómo usarlo:**
```
"@frontend-agent, crea un formulario de registro que..."
```

**Acceso directo en Claude Code:**
```python
Task(
    subagent_type="frontend-agent",
    prompt="Construye una tabla data que..."
)
```

**Stack que Entiende:**
- jQuery 3.6+ (Obligatorio - NO JavaScript vanilla)
- Bootstrap 5.3+
- Select2 para dropdowns mejorados
- DataTables para tablas
- jQuery Validate para validación de formularios
- Laravel Echo para real-time
- Vite para build

**📋 REGLA OBLIGATORIA - Validación de Formularios:**
- ✅ TODOS los formularios usan jQuery Validate con rules y messages
- ✅ Los mensajes SIEMPRE en español
- ✅ Se definen rules para cada campo (required, minlength, maxlength, email, number, etc)
- ✅ Se incluye submitHandler si es necesario
- ✅ Ver `FRONTEND_AGENT_RULES.md` para especificaciones completas

---

## 📚 Documentación por Agente

### Plan Agent - Documentación
- **Plan Design:** `plan-design.md` - Especificación completa del agente
- **Capabilities:** `capabilities.md` - Lista de capacidades
- **Guides:** `guides/plan/` - Guías de uso

### Backend Agent - Documentación
- **Backend Design:** `backend-design.md` - Especificación completa del agente
- **Capabilities:** `capabilities.md` - Lista de capacidades
- **Guides:** `guides/backend/` - Guías de uso

### Frontend Agent - Documentación
- **Frontend Design:** `frontend-design.md` - Especificación completa del agente
- **Capabilities:** `capabilities.md` - Lista de capacidades
- **Guides:** `guides/frontend/` - Guías de uso

---

## 🚀 Ejemplos de Uso

### Ejemplo 1: Planificar + Backend + Frontend

**Paso 1 - Planificación:**
```
"@plan-agent, planifica un sistema de gestión de órdenes que:
1. Cree órdenes con datos de cliente
2. Verifique disponibilidad de stock
3. Integre con API de Gestión ERP
4. Sincronice con Web Álvarez"
```

**Paso 2 - Backend (basado en plan):**
```
"@backend-agent, implementa basado en este plan: [pega plan]

Específicamente:
- Crea OrderService.php que llame a Gestión API
- Implementa validación de LOPD
- Maneja códigos de error 20401, 20402
- Referencia erp-api-endpoints.md Sección 6"
```

**Paso 3 - Frontend (basado en backend):**
```
"@frontend-agent, construye la UI para este backend: [pega backend]

Crea:
- Formulario de creación de orden con múltiples pasos
- Búsqueda de producto con stock en tiempo real
- Validación contra restricciones de ERP
- Bootstrap Modernize styling
- jQuery Validate para validación de campos"
```

**⚠️ NOTA IMPORTANTE:**
Todos los formularios creados por Frontend Agent usan:
- **jQuery Validate** (NO JavaScript vanilla)
- **Rules y Messages en español** para cada campo
- **submitHandler** si es necesario
- **Select2** para dropdowns mejorados

---

## 🔗 Integración con Documentación ERP

Los agentes tienen acceso completo a la documentación de Gestión ERP:

**Ubicación:** `.claude/reference/project/erp/`

- `README.md` - Índice de navegación
- `erp-integration-overview.md` - Arquitectura
- `erp-api-endpoints.md` - 14 endpoints REST
- `erp-sync-tables.md` - 50+ tablas de base de datos
- `erp-xmlrpc-services.md` - Servicios legacy
- `AGENTS_GUIDE.md` - Guía completa de agentes

**Cómo los agentes la usan:**

Backend Agent → Referencia parámetros de API desde `erp-api-endpoints.md`
Frontend Agent → Referencia campos de datos desde `erp-sync-tables.md`
Plan Agent → Referencia flujos completos desde `erp-integration-overview.md`

---

## 📋 Checklist de Configuración

Los agentes están configurados en: `.claude/agents-config.json`

✅ **Plan Agent** - Configurado y listo
✅ **Backend Agent** - Configurado y listo
✅ **Frontend Agent** - Configurado y listo
✅ **ERP Documentation** - Accesible en `.claude/reference/project/erp/`
✅ **Guides** - Disponibles en `.claude/guides/`

---

## 🎓 Flujo de Trabajo Recomendado

### Para Características Simples:
```
1. Backend Agent → Implementa endpoint
2. Frontend Agent → Construye formulario
```

### Para Características Complejas:
```
1. Plan Agent → Diseña arquitectura
2. Backend Agent → Implementa basado en plan
3. Frontend Agent → Construye basado en backend
```

### Para Integraciones ERP:
```
1. Plan Agent → Planifica flujo de ERP
2. Backend Agent → Implementa llamadas a API de ERP
3. Frontend Agent → Construye UI que consume datos de ERP
```

---

## 💡 Mejores Prácticas

### ✅ Haz esto:
```
"@backend-agent, crea un endpoint que:
1. Valide input
2. Llame a POST /cliente/ de Gestión
3. Maneje errores 20401, 20402
4. Referencia erp-api-endpoints.md Sección 4"
```

### ❌ No hagas esto:
```
"@backend-agent, crea un endpoint"  ← Muy vago
"@frontend-agent, crea una página"   ← Sin contexto
```

---

## 📞 Contacto y Soporte

**Preguntas sobre agentes:**
- Revisa los archivos `design.md` en cada carpeta de agente
- Consulta `AGENTS_GUIDE.md` para ejemplos completos
- Usa guías específicas en `guides/{agent}/`

**Problemas con integración ERP:**
- Lee `erp-integration-overview.md` para arquitectura
- Consulta `erp-api-endpoints.md` para parámetros específicos
- Referencia `AGENTS_GUIDE.md` para flujos de trabajo

---

## 📊 Estructura de Directorios

```
.claude/
├── agents/                          ← Este archivo
│   ├── plan/
│   │   ├── plan-design.md
│   │   └── capabilities.md
│   ├── backend/
│   │   ├── backend-design.md
│   │   └── capabilities.md
│   └── frontend/
│       ├── frontend-design.md
│       └── capabilities.md
│
├── guides/                          ← Documentación detallada
│   ├── plan/
│   ├── backend/
│   └── frontend/
│
├── reference/
│   └── project/erp/                 ← Documentación ERP
│       ├── README.md
│       ├── erp-integration-overview.md
│       ├── erp-api-endpoints.md
│       ├── erp-sync-tables.md
│       ├── erp-xmlrpc-services.md
│       └── AGENTS_GUIDE.md
│
└── agents-config.json               ← Configuración de agentes
```

---

**Versión:** 1.0
**Última Actualización:** 30 de Noviembre de 2025
**Estado:** Listo para Usar ✅

Para ver cómo usar cada agente con la integración ERP, consulta:
👉 **`.claude/reference/project/erp/AGENTS_GUIDE.md`**
