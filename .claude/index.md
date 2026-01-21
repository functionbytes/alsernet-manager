# 🗂️ Alsernet Claude Code Index

**Complete guide to the .claude directory structure and where to find everything.**

---

## 📁 Directory Structure

```
.claude/
├── agents/                      # Agent specifications
│   ├── plan/                    # Plan Agent
│   ├── frontend/                # Frontend Agent
│   └── backend/                 # Backend Agent
│
├── guides/                      # Implementation guides
│   ├── plan/                    # Plan Agent guides (5 guides)
│   ├── frontend/                # Frontend Agent guides
│   ├── backend/                 # Backend Agent guides
│   └── thematic/                # Thematic guides (API, Database, Security, Testing)
│
├── reference/                   # Quick reference documentation
│   ├── frontend/                # Frontend references (jQuery, Modernize, layouts)
│   │   ├── components/          # Component library
│   │   ├── layouts/             # Page layouts
│   │   ├── modernize/           # Modernize template reference
│   │   └── jquery*.md           # jQuery patterns & quick reference
│   │
│   └── project/                 # Project documentation (consolidated from docs/)
│       ├── api/                 # API specifications
│       ├── backend/             # Backend docs (roles, routes, permissions)
│       ├── devops/              # DevOps configuration
│       └── guides/              # Setup and system guides
│
├── setup/                       # Setup and configuration
│   └── hooks/                   # Git hooks (pre-commit, etc)
│
├── database-optimization/       # Database guides
│   ├── denormalizacion_guia.md  # Denormalization guide
│   ├── optimizacion_db_guia.md  # Optimization guide
│   └── warehouse_quick_reference.md
│
├── agents.md                    # Central registry of all agents
├── agents-config.json           # Machine-readable agent configuration
├── index.md                     # This file
├── md_saving_conventions.md     # Where to save each .md file
└── ...other files
```

---

## 🎯 Quick Navigation

### For Planning Features
→ `.claude/guides/plan/`
- `plan-agent-quick-start.md` (5 min)
- `feature-planning-guide.md` (30 min)
- `architecture-planning-guide.md` (45 min)
- `task-breakdown-guide.md` (60 min)
- `risk-assessment-guide.md` (30 min)
- `how-to-request-changes.md` (workflow)

### For Frontend Development
→ `.claude/guides/frontend/`
→ `.claude/agents/frontend/frontend-design.md`

### For Backend Development
→ `.claude/guides/backend/`
→ `.claude/agents/backend/backend-design.md`

### For API Standards & Patterns
→ `.claude/guides/thematic/api-standards.md`

### For Database Patterns & Optimization
→ `.claude/guides/thematic/database-patterns.md`
→ `.claude/database-optimization/`

### For Security Patterns
→ `.claude/guides/thematic/security-patterns.md`

### For Testing Standards
→ `.claude/guides/thematic/testing-standards.md`

### For Artisan Commands
→ `.claude/reference/ARTISAN_COMMANDS.md`

### For Git Hooks & Setup
→ `.claude/setup/hooks/`

### For File Organization Conventions
→ `.claude/md_saving_conventions.md`
- Where to save each .md file
- Classification of documentation types
- Decision matrix for creating files
- What NOT to create

---

## 📚 Complete Documentation Consolidation

All documentation has been consolidated into **`.claude/`** for maximum agent accessibility:

```
CONSOLIDATED INTO .claude/:

✅ Reutilizable (Agent-focused)
├── .claude/guides/thematic/        # Patterns: API, Database, Security, Testing
├── .claude/guides/frontend/        # jQuery patterns, form handling, real-time
├── .claude/guides/backend/         # Modules, endpoints, logging
├── .claude/guides/plan/            # Planning, architecture, risk assessment
├── .claude/reference/frontend/     # jQuery, Modernize, components, layouts
└── .claude/database-optimization/  # Denormalization, optimization guides

✅ Project-Specific (Reference)
├── .claude/reference/project/api/      # API specifications
├── .claude/reference/project/backend/  # Roles, routes, permissions, compatibility
├── .claude/reference/project/devops/   # Supervisor, backups, scheduler
└── .claude/reference/project/guides/   # Setup, system status, architecture
```

**Previous `docs/` folder**: REMOVED ✅
- All useful content moved to `.claude/`
- Eliminated redundant/historical documentation
- 88 files optimized to focused, agent-accessible resources

---

## 🤖 Agent System

### Three Independent Agents

**1. Plan Agent** (inherit model)
- Purpose: Plan implementation, design architecture, breakdown tasks
- Location: `.claude/agents/plan/`
- Capabilities: 35 (planning, analysis, risk assessment)
- Use when: Starting features, designing architecture, planning workflows

**2. Frontend Agent** (haiku model)
- Purpose: Build UI components with jQuery and Bootstrap
- Location: `.claude/agents/frontend/`
- Capabilities: 45 (DOM, forms, validation, real-time)
- Use when: Creating forms, modals, tables, interactive features

**3. Backend Agent** (haiku model)
- Purpose: Create models, APIs, services, business logic
- Location: `.claude/agents/backend/`
- Capabilities: 41 (models, controllers, services, events)
- Use when: Building endpoints, creating models, implementing business logic

**Total: 121 capabilities across 3 agents**

---

## 💡 Smart Hybrid Modality

The system automatically chooses between two modes:

### ⚡ QUICK MODE (< 5 hours)
- For simple features
- Quick summary → Testing? → Execute
- 60-70% fewer tokens

### 📋 STRUCTURED MODE (> 5 hours)
- For complex features
- Full chronogram → Agent auth → Testing → Style → Execute
- Full visibility and control

---

## 📖 Central Registries

### agents.md
Complete registry of all agents with:
- Purpose and description
- Capabilities breakdown
- Technology stacks
- When to use each agent
- Links to guides and specs

### agents-config.json
Machine-readable configuration:
- Agent metadata
- Model and type settings
- Capabilities count
- Guide references
- Version information

---

## 🔄 Workflow

```
Request Change
    ↓
Smart Modality Decides (QUICK vs STRUCTURED)
    ↓
    ├─ QUICK: Summary → Testing? → Execute
    │
    └─ STRUCTURED: Chronogram → Agent? → Testing? → Style? → Execute
    ↓
Agents Implement
    ├─ Plan Agent (if needed): Analyze & design
    ├─ Frontend Agent: Build UI
    └─ Backend Agent: Build API
    ↓
Tests (if selected)
    ├─ Unit tests
    ├─ Integration tests
    └─ E2E tests
    ↓
Commit & Deploy
```

---

## 🎓 Learning Path for New Developers

1. **Start here:** README.md (root directory)
2. **Understand agents:** `.claude/agents.md`
3. **Follow workflows:** `.claude/guides/plan/how-to-request-changes.md`
4. **Learn patterns:** `docs/guides/` (api, database, security, testing)
5. **Reference guides:** Specific agent guides as needed

---

## 🔧 Common Tasks

### Create a new feature
1. Request: "Create [feature] with [requirements]"
2. System responds with chronogram
3. Authorize agents
4. Decide testing
5. Choose implementation style
6. System executes

### Review API standards
→ `docs/guides/api-standards.md`

### Design database schema
→ `docs/guides/database-patterns.md`
→ `.claude/guides/plan/architecture-planning-guide.md`

### Implement security
→ `docs/guides/security-patterns.md`
→ `.claude/guides/plan/risk-assessment-guide.md`

### Setup testing
→ `docs/guides/testing-standards.md`

### Optimize database
→ `.claude/database-optimization/OPTIMIZACION_DB_GUIA.md`

---

## ✅ Checklist for Development

```
Before Starting Feature:
□ Check agents.md for relevant agent
□ Review how-to-request-changes.md for workflow
□ Read relevant guide (API/Database/Security/Testing)
□ Plan with Plan Agent if complex
□ Follow Smart Hybrid modality

During Implementation:
□ Follow established patterns
□ Use agent capabilities
□ Validate input (see api-standards.md)
□ Implement security (see security-patterns.md)
□ Write tests (see testing-standards.md)

Before Commit:
□ Tests pass
□ Code follows patterns
□ Includes necessary documentation
□ Follows security checklist
□ Activity logging implemented
```

---

## 📞 Support & Resources

### Agent System
- **Agent Questions:** See `.claude/agents.md`
- **Request Workflow:** See `.claude/guides/plan/how-to-request-changes.md`
- **Agent Specifications:** See `.claude/agents/{plan,frontend,backend}/`

### Implementation Guides
- **API Standards:** See `.claude/guides/thematic/api-standards.md`
- **Database Patterns:** See `.claude/guides/thematic/database-patterns.md`
- **Security Patterns:** See `.claude/guides/thematic/security-patterns.md`
- **Testing Standards:** See `.claude/guides/thematic/testing-standards.md`

### Project Documentation
- **Project Setup:** See `.claude/reference/project/guides/`
- **Backend Docs:** See `.claude/reference/project/backend/`
- **API Specs:** See `.claude/reference/project/api/`
- **DevOps Config:** See `.claude/reference/project/devops/`

### File Organization
- **Where to Save .md:** See `.claude/md_saving_conventions.md`

---

## 🔗 External Integrations

### PrestaShop Integration

**Location**: `integrations/prestashop/`

```
✨ 6 custom modules for Alsernet ↔ PrestaShop synchronization:
- Alsernetauth - Authentication & SSO
- Alsernetcustomer - Customer synchronization
- Alsernetproducts - Product catalog sync
- Alsernetshopping - Order synchronization
- Alsernetcontents - CMS content sync
- Alsernetforms - Custom forms & validation
```

**Documentation**:
- **[Overview](../../integrations/prestashop/README.md)** - Architecture & modules
- **[API Connection](../../integrations/prestashop/docs/api-connection.md)** - Configuration & auth
- **[Modules Guide](../../integrations/prestashop/docs/modules-guide.md)** - Detailed guide
- **[Setup Instructions](../../integrations/prestashop/docs/setup.md)** - Installation

---

**Last Updated:** November 30, 2025
**System Version:** 3.2 - PrestaShop Integration
**Status:** Production Ready ✅
**Changes:**
- docs/ consolidated into .claude/
- PrestaShop integrated into integrations/ structure
