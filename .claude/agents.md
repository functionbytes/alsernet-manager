# 🤖 Alsernet Agents Registry

**Central registry of all available development agents for Alsernet.**

---

## Available Agents

### 1. Frontend Agent
**Purpose:** Build interactive, responsive frontend components and features

**Location:** `.claude/agents/frontend/`

**Specification:** `frontend-design.md`

**Model Configuration:** Haiku with claude-code-guide

**Capabilities:** 45 comprehensive frontend development capabilities

**Files:**
- `frontend-design.md` - Main specification and overview
- `capabilities.md` - Detailed 45 capabilities in 6 blocks
- `guides/` - Implementation guides
  - `jquery-patterns.md` - 8 jQuery patterns
  - `component-building.md` - Building reusable components
  - `form-handling.md` - Form validation and submission
  - `real-time-integration.md` - WebSockets and real-time features

**Technology Stack:**
- jQuery 3.6+
- Bootstrap 5.3+
- jQuery Validate
- DataTables
- Laravel Echo
- Vite

**Agent Capabilities:**
- Block 1: jQuery Core & DOM (10 caps)
- Block 2: Form Validation (8 caps)
- Block 3: Bootstrap Components (9 caps)
- Block 4: DataTables & Advanced UI (7 caps)
- Block 5: Real-time & WebSockets (6 caps)
- Block 6: Storage & Caching (5 caps)

**When to Use:**
- Building UI components
- Creating forms with validation
- Implementing real-time features
- Managing frontend state
- Handling file uploads

---

### 2. Backend Agent
**Purpose:** Generate Laravel models, controllers, APIs, and business logic

**Location:** `.claude/agents/backend/`

**Specification:** `backend-design.md` (in docs/backend/)

**Model Configuration:** Haiku with claude-code-guide

**Capabilities:** 41 comprehensive backend development capabilities

**Files:**
- `backend-design.md` - Main specification and overview
- `capabilities.md` - Detailed 41 capabilities in 5 blocks
- `guides/` - Implementation guides
  - `creating-new-module.md` - Step-by-step module creation (12 steps)
  - `api-endpoint-patterns.md` - REST API patterns and standards

**Technology Stack:**
- Laravel 12.x
- PHP 8.3+
- PostgreSQL 14+
- Redis 6+
- Laravel Sanctum
- Laravel Reverb

**Agent Capabilities:**
- Block 1: Model & Database (12 caps)
- Block 2: Controllers & Routing (10 caps)
- Block 3: Business Logic & Services (8 caps)
- Block 4: Real-time Features (6 caps)
- Block 5: Data Management (5 caps)

**When to Use:**
- Creating models and migrations
- Building REST APIs
- Implementing business logic
- Setting up authentication
- Managing database operations

---

### 3. Plan Agent
**Purpose:** Plan implementation strategy, break down features, and create development roadmaps

**Location:** `.claude/agents/plan/`

**Specification:** `plan-design.md`

**Model Configuration:** Inherit with plan-agent

**Capabilities:** 35 comprehensive planning capabilities

**Files:**
- `plan-design.md` - Main specification and overview
- `capabilities.md` - Detailed 35 capabilities in 5 blocks
- `guides/` - Implementation guides
  - `plan-agent-quick-start.md` - Get started in 5 minutes
  - `feature-planning-guide.md` - Detailed feature planning with real examples
  - `architecture-planning-guide.md` - Database, API, and system design patterns
  - `task-breakdown-guide.md` - Sequential task planning and templates
  - `risk-assessment-guide.md` - Risk identification and mitigation strategies

**Agent Capabilities:**
- Block 1: Feature Analysis & Decomposition (8 caps)
- Block 2: Architecture Planning (7 caps)
- Block 3: Task Breakdown & Sequencing (8 caps)
- Block 4: Risk & Validation (7 caps)
- Block 5: Testing & Deployment Strategy (5 caps)

**When to Use:**
- Starting a new feature or module
- Planning complex multi-step features
- Breaking down large tasks
- Designing database schema
- Planning API structure
- Creating development roadmap
- Identifying risks and dependencies
- Planning testing strategy

**Guides:**
1. **Quick Start** (5 min) - Get started immediately
2. **Feature Planning** (15-30 min) - Detailed feature analysis with examples
3. **Architecture Planning** (30-45 min) - Design database, API, and services
4. **Task Breakdown** (30-60 min) - Create sequential task plans
5. **Risk Assessment** (20-30 min) - Identify and mitigate risks

---

## Agent Selection Guide

### Choose Frontend Agent When:
✅ Building user interfaces
✅ Creating interactive components
✅ Implementing form validation
✅ Adding real-time updates
✅ Managing client-side state
✅ Handling file uploads
✅ Creating responsive layouts

### Choose Backend Agent When:
✅ Creating database models
✅ Building API endpoints
✅ Implementing business logic
✅ Managing authentication
✅ Processing data
✅ Setting up background jobs
✅ Configuring real-time broadcasting

### Choose Plan Agent When:
✅ Starting new features or modules
✅ Need to break down complex tasks
✅ Planning architecture
✅ Creating development roadmaps
✅ Analyzing requirements
✅ Identifying risks and dependencies
✅ Planning testing strategies
✅ Designing deployment plans

---

## How to Use Agents

### 1. Review Agent Documentation
Start by reading the agent's main specification file:
- Frontend: `.claude/agents/frontend/frontend-design.md`
- Backend: `.claude/agents/backend/backend-design.md`

### 2. Review Capabilities
Check the capabilities file to understand what the agent can do:
- Frontend: `.claude/agents/frontend/capabilities.md`
- Backend: `.claude/agents/backend/capabilities.md`
- Plan: `.claude/agents/plan/capabilities.md`

### 3. Follow Guides
Use the guides for step-by-step implementation:
- Frontend: `.claude/agents/frontend/guides/*.md`
- Backend: `.claude/agents/backend/guides/*.md`
- Plan: No guides (planning-specific patterns in spec)

### 4. Request Tasks
Ask Claude Code to perform tasks using the agents:

**Plan Agent Examples:**
```
"Plan the implementation of a customer feedback module"
"Break down the return management feature into tasks"
"Create a development roadmap for the ticketing system"
"Design the database schema for a new module"
```

**Frontend Examples:**
```
"Create a modal form with validation using the Frontend Agent"
"Build a real-time table that updates via WebSockets"
"Implement a file upload component with progress tracking"
```

**Backend Examples:**
```
"Create a new Warehouse module with the Backend Agent"
"Generate a REST API endpoint for product management"
"Set up a broadcasting event for real-time updates"
```

---

## Recommended Agent Workflow

```
1. Plan Agent
   ↓ (Creates detailed plan)
   └─→ Design & Requirements

2. Frontend Agent + Backend Agent (in parallel)
   ├─ Frontend: Build UI components
   └─ Backend: Build API & business logic

3. Integration & Testing
   └─→ Combine frontend and backend

4. Deployment
   └─→ Deploy following plan
```

---

## Agent Independence

Both agents are **completely independent**:
- ✅ Separate specifications
- ✅ Separate capabilities
- ✅ Separate guides
- ✅ Different technology stacks
- ✅ Different responsibilities

This allows them to evolve independently and be used simultaneously on different aspects of the project.

---

## File Structure

```
.claude/
├── agents.md (this file)
├── agents/
│   ├── frontend/
│   │   ├── frontend-design.md
│   │   ├── capabilities.md
│   │   └── guides/
│   │       ├── jquery-patterns.md
│   │       ├── component-building.md
│   │       ├── form-handling.md
│   │       └── real-time-integration.md
│   └── backend/
│       ├── backend-design.md
│       ├── capabilities.md
│       └── guides/
│           ├── creating-new-module.md
│           └── api-endpoint-patterns.md
```

---

## Quick Reference

| Aspect | Frontend Agent | Backend Agent |
|--------|---|---|
| **Main File** | `agents/frontend/frontend-design.md` | `agents/backend/backend-design.md` |
| **Capabilities File** | `agents/frontend/capabilities.md` | `agents/backend/capabilities.md` |
| **Total Capabilities** | 45 | 41 |
| **Number of Guides** | 4 | 2 |
| **Tech Stack** | jQuery, Bootstrap, Echo | Laravel, PHP, PostgreSQL |
| **Focus** | UI/UX, Interactivity | API, Database, Logic |

---

## Next Steps

1. **Review Agent Specifications**
   - Read frontend-design.md for UI work
   - Read backend-design.md for API work

2. **Study Capabilities**
   - Review capabilities.md for each agent

3. **Follow Implementation Guides**
   - Use guides/ folder for step-by-step instructions

4. **Request Tasks**
   - Ask Claude Code to create components/modules using agents

---

**Version:** 1.0
**Date:** November 30, 2025
**Status:** Production Ready
**For:** Alsernet Development Team
