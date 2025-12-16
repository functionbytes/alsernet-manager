# Using the AlserBackend Decision Agent

## Quick Start (30 seconds)

Ask the agent for backend design:

```
"Design a feedback module for managers where:
- Users can create and view feedback
- Feedback has status (pending, reviewed, resolved)
- Assign feedback to team members
- Send email when status changes"
```

**You'll get:**
- Migration file
- Model with relationships and scopes
- Form request with validation
- Complete CRUD controller
- Route definitions
- Required permissions
- Events and listeners (if needed)
- Explanations of each decision

## Common Request Examples

### Example 1: Simple CRUD
```
"Design a complaints module for callcenters with:
- Create and view complaints
- Update status (pending, in_progress, resolved)
- Assign to team members
- Email notifications on status change"
```

### Example 2: Complex System
```
"Design a warranty management system for Returns:
1. Track product warranties (serial numbers, coverage dates)
2. Process warranty claims with inspection
3. Validate claims against return rules
4. Generate warranty certificates PDF
5. Send expiration reminders
6. Track claim history"
```

## Permission Naming Pattern

```
{resource}:{action}

Examples:
- ticket:create
- ticket:view
- ticket:update
- ticket:delete
- returns:approve
- warehouse:export
- user:create
```

## Route Naming Pattern

```
{profile}.{resource}.{action}

Examples:
- manager.tickets.index
- callcenter.returns.show
- warehouse.products.export
- shop.subscribers.edit
```

## Decision Guidelines (WITH EXAMPLES)

### 🎯 Decision Table: Service vs Event vs Observer

| Your Need | Best Choice | Example |
|-----------|------------|---------|
| Single operation, no notifications | **Service** | CreateInvoice service |
| Multiple listeners (log + email + API) | **Event** | OrderCreated event |
| Track model lifecycle (created, updated) | **Observer** | ReturnObserver |
| Complex transaction, multiple tables | **Service** | ComplexReturnService |
| Status change + multiple notifications | **Event** | ComplaintStatusChanged |

👉 **Find the row that matches your requirement and use that pattern**

### When in Doubt - Decision Flow

1. **Is it a single operation with no side effects?** → Use **Service**
2. **Do you need multiple things to happen?** (emails, logging, API calls) → Use **Event**
3. **Do you need to track model state changes?** (auto-update fields) → Use **Observer**
4. **Do you need transactions across multiple tables?** → Use **Service** + Event if side effects needed

### Real Alsernet Examples

**✅ Use Service:**
- `ReturnService` - Complex returns with multiple tables and transactions

**✅ Use Event:**
- `ReturnCreated` - Triggers logging, notifications, ERP sync
- `ReturnStatusChanged` - Multiple listeners for different workflows

**✅ Use Observer:**
- `ReturnObserver` - Auto-update status fields when state changes

---

### 📊 Decision Table: JSON Column vs Separate Table

| Data Type | Use JSON | Use Table | Why |
|-----------|----------|-----------|-----|
| Flexible metadata | ✅ | ❌ | Don't need WHERE clauses |
| Queryable history | ❌ | ✅ | Need filtering/searching |
| Fixed structure | ❌ | ✅ | Has defined relationships |
| ERP integration data | ✅ | ❌ | Store as-is, don't query |
| User preferences | ✅ | ❌ | Optional, flexible fields |
| Approval history | ❌ | ✅ | Need to query/filter |

**Alsernet Example:**
```php
// JSON for flexible ERP data (can't query it)
$table->json('erp_data')->nullable();

// Separate table for items (need to query them)
Schema::create('return_items', ...);
```

---

### 🏷️ Decision Table: Model Traits

| Need | HasUid | LogsActivity | SoftDeletes | HasCache |
|------|--------|--------------|-------------|----------|
| External API reference | ✅ | - | - | - |
| Track who changed what | - | ✅ | - | - |
| Preserve deleted data | - | - | ✅ | - |
| Cache query results | - | - | - | ✅ |
| **Customer entity** | ✅ | ✅ | ✅ | - |
| **System table** | - | ✅ | ✅ | - |

**Rule of Thumb:**
- External entities: HasUid + LogsActivity + SoftDeletes
- Internal entities: LogsActivity + SoftDeletes
- High-query entities: Add HasCache

---

### 🔐 Decision Table: Profile & Middleware

| Profile | Use For | Permission Scope |
|---------|---------|------------------|
| **manager** | Admin control | All features |
| **callcenter** | Support tickets | Support only |
| **warehouse** | Inventory | Inventory only |
| **shop** | Store ops | Store only |
| **administrative** | Documents | Admin only |

**Always apply:** `['auth', 'check.roles.permissions:{profile}']`

---

## Best Practices Quick Reference

### ✅ DO These Things

**Migrations:**
- Always use `constrained()` for foreign keys
- Index status fields: `$table->index('status')`
- Use `softDeletes()` for important data
- Add `timestamps()` to all tables

**Models:**
- Always use `use HasFactory`
- Add `LogsActivity` for audit trail
- Use `with()` when loading relationships
- Cast JSON columns: `'metadata' => 'json'`

**Controllers:**
- Wrap writes in `DB::transaction()`
- Return proper status codes (201 for create, 204 for delete)
- Use `try-catch` for error handling
- Log errors with context

**Services:**
- Wrap all DB changes in transactions
- Dispatch events after operations
- Return consistent types (Model, Collection)
- Use constructor DI

**FormRequests:**
- Check permissions in `authorize()`
- Provide Spanish error messages
- Sanitize HTML in `prepareForValidation()`
- Use `Rule::exists()` for FK validation

### ❌ AVOID These Things

- ❌ Don't validate in controllers (use FormRequest)
- ❌ Don't do business logic in models (use Services)
- ❌ Don't use `$guarded = []` (use `$fillable`)
- ❌ Don't query relationships without `with()` (N+1 problem)
- ❌ Don't return raw model data (filter sensitive fields)
- ❌ Don't put all logic in one listener (separate concerns)

---

## Code Examples Available

The agent includes **8 complete code examples** you can reference:

1. **Migration Pattern** - With relationships, timestamps, indexes
2. **Model Pattern** - With relationships, scopes, casts
3. **FormRequest Pattern** - With validation, Spanish messages
4. **Controller Pattern** - Full CRUD with transactions
5. **Routes Pattern** - Profile-based grouping
6. **Service Pattern** - With transactions, event dispatch
7. **Event Pattern** - With rich context data
8. **Listener Pattern** - Multiple listeners for one event

👉 **Ask agent:** "Show me code examples" to see all of these

## Module Complexity Levels

### Tier 1 - Simple CRUD
- Single table, basic CRUD
- No events/services
- Example: Product reviews, FAQ

**Code:** 200-300 lines
**Time:** 10 minutes

### Tier 2 - Standard Workflow
- Single-main table + status tables
- CRUD + status management
- Simple event notifications
- Example: Tickets, simple returns

**Code:** 400-500 lines
**Time:** 20 minutes

### Tier 3 - Complex Domain
- Multiple related tables
- Complex business logic
- Event-driven architecture
- External integrations
- Example: Full returns system

**Code:** 800-1200 lines
**Time:** 40 minutes

## Key Files to Know

**Documentation:**
- `docs/backend/roles-acl.md` - RBAC guide
- `docs/backend/route-system.md` - Routes guide
- `docs/guides/system-architecture.md` - Architecture
- `docs/database/WAREHOUSE_ARCHITECTURE.md` - Schema

**Reference Code:**
- `app/Http/Controllers/Managers/Tickets/TicketsController.php`
- `app/Models/Ticket/Ticket.php`
- `app/Http/Requests/Api/V1/BaseTicketRequest.php`
- `app/Services/Return/ReturnService.php`
- `routes/managers.php`

## Model Traits to Use

| Trait | Purpose | When to Use |
|-------|---------|------------|
| `HasFactory` | Testing/seeding | Always |
| `SoftDeletes` | Logical deletion | Data preservation |
| `LogsActivity` | Audit trail | Track changes |
| `HasUid` | UUID generation | External references |
| `HasCache` | Caching | Complex queries |

## Time Savings

| Scenario | Without Agent | With Agent | Savings |
|----------|---------------|-----------|---------|
| Simple CRUD | 60 min | 10 min | 83% |
| Status workflow | 120 min | 20 min | 83% |
| Complex logic | 240 min | 40 min | 83% |

## Request Template

For best results, provide:

```markdown
## Feature: {Name}

### Context
- Profile: {manager/callcenter/warehouse/shop}
- Similar to: {existing_module}

### Requirements
- Requirement 1
- Requirement 2
- Requirement 3

### Workflow
1. First step
2. Second step
3. Third step

### Data to Track
- Field 1
- Field 2
- Field 3

### Notifications
- When? To whom?

### Integrations
- External systems?
- ERP sync needed?
```

## System Profiles

- **Manager** - Full system administration
- **Callcenter** - Customer support operations
- **Warehouse** - Inventory management
- **Shop** - Store operations
- **Administrative** - Document management

Choose based on who uses the feature.

## How to Use the Improvements (New!)

The agent now has **enhanced decision-making tools** to help you get better results:

### 1. Use Decision Tables to Make Choices
Instead of debating "should we use Service or Event?", look at the decision table:
- Find your scenario
- See the recommended pattern
- Done in 30 seconds ✓

**Example:**
```
Q: "Should I use Service or Event for this?"
A: Check the Service vs Event table → Find your use case → Implement
```

### 2. Reference Code Examples
The agent now includes 8 complete code examples you can copy:

```
Request: "Show me a model example"
Get: Complete Model class with traits, relationships, scopes, casts
Copy → Adapt → Use ✓
```

### 3. Follow Best Practices Checklist
Before requesting a feature, review the ✅ DO's and ❌ DON'Ts:
- Prevents common mistakes
- Ensures consistency
- Speeds up code review

### 4. Study Real-World Scenarios
Want to understand what code to expect?
- Simple CRUD: 200-300 lines, 1 table
- Status workflow: 400-500 lines, 2 tables, service + event
- Complex domain: 800-1200 lines, 4+ tables, multiple services/events

---

## Requesting Features - Updated Approach

### Step 1: Determine Complexity
Look at your requirements and match to a tier:
- **Tier 1 - Simple CRUD?** → Single table, basic CRUD
- **Tier 2 - Status workflow?** → Multiple tables, status tracking
- **Tier 3 - Complex domain?** → Multiple workflows, complex logic

### Step 2: Make Architectural Decisions
Use the decision tables:
- Service vs Event vs Observer?
- JSON column vs separate table?
- Which traits to use?
- Which profile?

### Step 3: Request with Confidence
```markdown
## Feature: Product Review System

### Complexity: Tier 1 (Simple CRUD)
### Profile: shop
### Architecture Decision:
- Use Event (ReviewCreated) for notifications
- Use Table (not JSON) for reviews
- Use traits: HasFactory, LogsActivity, SoftDeletes

### Requirements:
- Customers submit reviews (rating 1-5, comment)
- Managers approve/reject reviews
- Send email on new review
```

### Step 4: Review Output
Agent provides:
- Migration (validated with best practices ✓)
- Model (with correct traits ✓)
- FormRequest (Spanish messages ✓)
- Controller (with transactions ✓)
- Routes (profile-based ✓)
- Event & Listeners ✓

---

## Shortcut Commands

Instead of long requests, you can now ask:

```
"Show me the Service vs Event decision table"
↓
Agent displays the decision table instantly

"Give me a model code example"
↓
Agent shows complete Model class with patterns

"What are the best practices for migrations?"
↓
Agent shows DO's and DON'Ts for migrations

"Real-world example of a complex system"
↓
Agent shows return management system example
```

---

## Estimated Time Savings (Updated)

| Task | Before | After | Savings |
|------|--------|-------|---------|
| Make architecture decision | 20 min | 5 min | 75% |
| Find code example | 30 min | 2 min | 93% |
| Check best practices | 15 min | 3 min | 80% |
| Write complete feature | 120 min | 30 min | 75% |

**Total per module: 185 min → 40 min (78% savings)**

---

## Next Steps

1. ✅ **Read this guide** - Understand the improvements (5 min)
2. ✅ **Review decision tables** - Know your options (5 min)
3. ✅ **Study code examples** - See working patterns (10 min)
4. ✅ **Check best practices** - Avoid common mistakes (5 min)
5. 👉 **Request your first feature** - Use all the tools!

---

## Quick Navigation

**Need to decide?** → See "Decision Guidelines" section
**Need code?** → See "Code Examples Available" section
**Need to avoid mistakes?** → See "Best Practices Quick Reference" section
**Need real examples?** → See "Real-World Request Examples" section

