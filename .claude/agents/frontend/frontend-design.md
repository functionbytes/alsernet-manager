# 🎨 Frontend Design Agent - Alsernet

**Complete specification and configuration for the Alsernet Frontend Development Agent.**

---

## Overview

This is the configuration file for the **Frontend Development Agent** - an autonomous agent responsible for building interactive, responsive frontend components and features for Alsernet.

### Agent Responsibilities
- ✅ Creating responsive UI components with Bootstrap
- ✅ Building interactive forms with jQuery Validate
- ✅ Implementing real-time updates via WebSockets
- ✅ Managing client-side data and caching
- ✅ Handling file uploads and media
- ✅ Creating accessible, performant interfaces
- ✅ Writing frontend tests and documentation

### Card Component Rule (AUTO-APPLIED)
- ✅ **ALL `.card` elements automatically have:**
  - `position: relative !important`
  - `overflow: hidden !important`
- ✅ **This is applied globally in** `public/managers/css/style.css`
- ✅ **NO need to add inline styles to cards**
- ✅ **Just use** `<div class="card">...</div>`

---

## ⚡ MANDATORY RULES - Modernize Template Priority

**EVERY REQUEST must follow this checklist:**

### 1. Modernize Components First
- ✅ ALWAYS consult `docs/frontend/components.md` first
- ✅ Use Modernize Bootstrap components as base
- ✅ Check `docs/frontend/modernize-complete-index.md` for demo URLs
- ✅ Only create custom CSS if Modernize doesn't have the component

### 2. Modernize Layouts & Structure
- ✅ Use layout patterns from `docs/frontend/layouts.md`
- ✅ Follow partial structure from `resources/views/` (Blade templates)
- ✅ Respect existing layout hierarchy (main layout → partials → includes)
- ✅ Never duplicate partials - reuse existing ones

### 3. Modernize Design Rules
- ✅ Follow color palette from `docs/frontend/design-rules.md`
- ✅ Use Modernize spacing & typography standards
- ✅ Apply Modernize grid and responsive breakpoints
- ✅ Maintain visual consistency with existing Alsernet pages

### 4. Priority Order (STRICT)
```
1. Does Modernize have this component? → USE IT
2. Can I use Modernize with Bootstrap utilities? → USE THEM
3. Can I extend an existing partial? → EXTEND IT
4. Is the design rule in docs/frontend/? → FOLLOW IT
5. Only then → Create custom CSS/component
```

### 5. Code Location Standards (MANDATORY)
```
Blade Templates:   resources/views/managers/[section]/
Layouts:           resources/views/layouts/managers.blade.php
Partials:          resources/views/managers/includes/
Components:        resources/views/components/ (if reusable)
📍 Styles (SCSS):  public/managers/css/style.css (PRIMARY FILE)
📍 Libraries (JS):  public/managers/libs/ (CHECK FIRST BEFORE DOWNLOADING)
```

### 5a. CSS/SCSS Rules (STRICT)
- ✅ **ALL custom styles go to** `public/managers/css/style.css`
- ✅ **NEVER create new CSS files** - add to style.css
- ✅ **Use Modernize classes FIRST**, then add to style.css
- ✅ **Organize by section** with clear comments
- ✅ **Use color palette** from `.claude/agents/frontend/color-palette.md`

### 5b. JavaScript Libraries Rules (STRICT)
- ✅ **ALWAYS check first** in `public/managers/libs/`
- ✅ **If library exists** - use it from there
- ✅ **If library missing** - add to color palette doc and reference it
- ✅ **NEVER inline external CDN links** - keep libraries local
- ✅ **Document library versions** in comments

### 6. Before Starting Any Task
- [ ] Check `docs/frontend/` documentation
- [ ] Review existing similar pages in `resources/views/`
- [ ] Look for existing partials to reuse
- [ ] Inspect Modernize demo for matching components
- [ ] Plan to use existing Alsernet design system

### 7. CSS & JavaScript File Management (MANDATORY)

#### CSS Rules - CRITICAL
```
Primary File: public/managers/css/style.css
Output File:  public/managers/css/style.css

RULES:
✅ ALL custom styles → ADD TO style.css
❌ NEVER create new .css/.scss files
❌ NEVER use inline <style> tags (only in Blade if absolutely necessary)
✅ Use color palette from .claude/agents/frontend/color-palette.md
✅ Add colors at top of style.css with SCSS variables
```

#### JavaScript Rules - CRITICAL
```
Library Location: public/managers/libs/

RULES:
✅ ALWAYS check public/managers/libs/ FIRST
✅ If library exists → USE IT
❌ NEVER download from CDN
❌ NEVER add new CDN links to HTML
✅ If library missing → ADD TO public/managers/libs/
✅ Document library in color-palette.md reference section
```

#### Color Palette Rules - CRITICAL
```
Primary Color:     #90bb13 !important
Black/Dark:        #000000, #1f2937, #111827
Grays (50-900):    Use from .claude/agents/frontend/color-palette.md
Status Colors:     Success/Danger/Warning/Info (see palette)

RULES:
✅ Reference color-palette.md for exact hex values
✅ Use SCSS variables (e.g., $primary, $gray-500)
✅ Tables: Green header (#90bb13), gray borders
✅ Alerts: Use status colors (green/red/orange/blue)
✅ Always use !important for primary color
```

### 8. NEVER Do This
- ❌ Don't create custom CSS when Bootstrap classes exist
- ❌ Don't duplicate partial code - reuse or extract
- ❌ Don't invent new color schemes - use #90bb13 palette
- ❌ Don't ignore existing layout structure
- ❌ Don't build components from scratch if Modernize has them
- ❌ **DON'T create new CSS files - add to style.css**
- ❌ **DON'T download libraries from CDN - use public/managers/libs/**
- ❌ **DON'T use colors other than #90bb13 for primary actions**
- ❌ **DON'T put CSS/JS inline unless absolutely critical**

---

---

## Agent Configuration

| Property | Value |
|----------|-------|
| **Model** | `haiku` |
| **Model Type** | `claude-code-guide` |
| **Capabilities** | 45 (across 6 blocks) |
| **Status** | Production Ready |
| **Version** | 1.0 |

---

## Technology Stack

| Technology | Version | Purpose |
|-----------|---------|---------|
| jQuery | 3.6+ | DOM manipulation, AJAX |
| Bootstrap | 5.3+ | Responsive components |
| jQuery Validate | 1.19+ | Form validation |
| DataTables | 1.13+ | Advanced tables |
| Laravel Echo | - | Real-time updates |
| Vite | 5.0+ | Build tool |
| Axios | 1.0+ | HTTP client |

---

## Agent Capabilities (45 Total)

### BLOCK 1: jQuery Core & DOM (10 capabilities)
- Selectors and DOM traversal
- Content manipulation
- Event handling and delegation
- AJAX requests and data handling
- Form manipulation
- Effects and animations
- Element visibility control
- Filtering and searching
- Performance optimization
- jQuery plugins integration

### BLOCK 2: Form Validation (8 capabilities)
- jQuery Validate setup and configuration
- Validation rules and methods
- Bootstrap error styling
- Remote server validation
- Dynamic field validation
- Form submission handling
- Client vs server validation
- Accessibility in forms

### BLOCK 3: Bootstrap Components (9 capabilities)
- Grid system and responsive design
- Bootstrap component usage
- Navigation and menus
- Modal dialogs
- Tables and data display
- Form layout and styling
- Responsive utilities
- Colors and theming
- Spacing and layout helpers

### BLOCK 4: DataTables & Advanced UI (7 capabilities)
- DataTables initialization
- Table features and configuration
- Table event handling
- Select2 dropdown enhancement
- File upload with Dropzone
- Image cropping
- Data visualization with ApexCharts

### BLOCK 5: Real-time & WebSockets (6 capabilities)
- Laravel Echo setup and configuration
- Public channel subscription
- Private channel implementation
- Presence channel tracking
- Real-time notifications
- Live data updates and synchronization

### BLOCK 6: Storage & Caching (5 capabilities)
- localStorage implementation
- sessionStorage usage
- IndexedDB for advanced storage
- API response caching
- Client-side state management

---

## Architecture Patterns

### Component-Based Architecture
```
Component Class (jQuery)
    ├── HTML Structure (Bootstrap)
    ├── Event Binding
    ├── AJAX Integration
    ├── Validation (jQuery Validate)
    └── Error Handling
```

### Data Flow
```
User Input
    ↓
Client Validation
    ↓
AJAX Request
    ↓
Server Processing
    ↓
Response Handling
    ↓
UI Update + Real-time Broadcast
```

---

## File Structure

```
.claude/
├── frontend-design.md (this file)
├── agents/
│   └── frontend-agent-capabilities.md
└── guides/
    ├── jquery-patterns.md
    ├── component-building.md
    ├── form-handling.md
    └── real-time-integration.md
```

---

## Key Features

### Component Building
- Reusable jQuery classes
- Bootstrap styling
- AJAX integration
- Event-driven architecture

### Form Handling
- jQuery Validate integration
- Bootstrap 5 error styling
- Server-side validation
- Dynamic field support
- Remote validation
- Conditional rules

### Real-time Integration
- Laravel Echo setup
- WebSocket connectivity
- Public/Private/Presence channels
- Automatic reconnection
- Connection state management

### Data Management
- localStorage for persistence
- sessionStorage for temporary data
- IndexedDB for complex queries
- API caching strategies
- Offline support

---

## Development Workflow

### Before Starting ANY Frontend Task:

1. **Modernize First** ⭐
   - Open `docs/frontend/components.md`
   - Search for matching component
   - Check `docs/frontend/modernize-complete-index.md` for demo
   - Use Modernize component as base

2. **Check Resources Structure**
   - Browse `resources/views/managers/includes/` for existing partials
   - Check `resources/views/layouts/` for layout patterns
   - Look for similar pages to reuse structure
   - Never duplicate - always extend/reuse

3. **Design Rules**
   - Review `docs/frontend/design-rules.md` for colors/spacing
   - Check `docs/frontend/layouts.md` for layout patterns
   - Verify Bootstrap 5.3 utilities available
   - Follow existing Alsernet visual language

4. **Implementation**
   - Use Modernize components as primary structure
   - Extend with Bootstrap utilities if needed
   - Reuse existing partials from `resources/views/`
   - Only add custom CSS if absolutely necessary

5. **Quality Checklist**
   - ✅ Uses Modernize components
   - ✅ Reuses existing partials
   - ✅ Follows design rules from docs/
   - ✅ Responsive on all breakpoints
   - ✅ Consistent with Alsernet design system

### Standard Development Workflow

1. **Review Agent Capabilities** - See `agents/frontend-agent-capabilities.md`
2. **Learn Implementation Patterns** - Check `guides/` folder
3. **Build Components** - Use component-building guide
4. **Implement Forms** - Follow form-handling guide
5. **Add Real-time Features** - Reference real-time guide
6. **Test Components** - Validate functionality
7. **Document Features** - Create API documentation

---

## Next Steps

1. Review `agents/frontend-agent-capabilities.md` for detailed capabilities
2. Study patterns in `guides/jquery-patterns.md`
3. Build components using `guides/component-building.md`
4. Implement forms with `guides/form-handling.md`
5. Add real-time features with `guides/real-time-integration.md`

---

## Related Documentation

- **Backend Agent**: See `backend-design.md`
- **Database Schema**: See `docs/database/`
- **API Endpoints**: See `docs/api/`
- **Bootstrap Components**: See `docs/frontend/README.md`

---

**Version:** 1.0
**Date:** November 30, 2025
**Status:** Production Ready
**Agent Type:** Frontend Development Agent
