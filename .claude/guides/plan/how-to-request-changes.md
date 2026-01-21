# Plan Agent - How to Request Changes & Implementations

**Complete guide on how to request features and changes using the Alsernet agent system.**

---

## The New Workflow

When you request a change or implementation, here's exactly how it will work:

```
1️⃣  You Request Change
    ↓
2️⃣  I Create Chronogram (with phases/etapas)
    ↓
3️⃣  I Identify Agents That Can Help
    ↓
4️⃣  I Ask: "Use this Agent?" ← You Authorize ✓
    ↓
5️⃣  I Show: Phase-by-Phase OR All-at-Once? ← You Choose
    ↓
6️⃣  I Ask: "Include Testing?" ← You Decide YES/NO
    ↓
7️⃣  I Execute Implementation
    ↓
8️⃣  Done ✓
```

---

## SECTION 1: Requesting a Change

### Format 1: Simple Request

```
"Add a star rating system to product reviews"
```

### Format 2: Detailed Request

```
"Add a star rating system to product reviews where:
- Users can rate 1-5 stars
- Manager can see average rating
- Admin can delete inappropriate ratings
- Email notification to product owner"
```

### Format 3: Complex Request

```
"Create a complete returns management system:
- Customers request return with reason
- Warehouse validates physical return
- Generate return label and shipping
- Process refund after validation
- Real-time tracking for customer
- Analytics dashboard for management"
```

---

## SECTION 2: What You'll Get Back

### Example Response to Your Request:

```
✅ REQUEST: "Add a star rating system to product reviews"

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📋 CHRONOGRAM OF IMPLEMENTATION

Phase 1: PLANNING (1 hour)
├── Run Plan Agent to analyze requirements
├── Design database schema
├── List API endpoints needed
└── Break down into tasks
Time: 1 hour | Agent: Plan Agent

Phase 2: BACKEND (3 hours)
├── Database migration (ratings table)
├── Rating model with relationships
├── RatingService with business logic
├── API endpoints (create, list, delete)
└── Admin moderation endpoints
Time: 3 hours | Agent: Backend Agent

Phase 3: FRONTEND (2 hours)
├── Star rating input component
├── Display ratings with average
├── Admin moderation interface
└── Real-time update integration
Time: 2 hours | Agent: Frontend Agent

Phase 4: TESTING (1.5 hours) ← YOU DECIDE
├── Unit tests for service
├── Integration tests for API
└── E2E tests for user flow
Time: 1.5 hours | Optional: YES/NO

─────────────────────────────────────────────────────
TOTAL ESTIMATED TIME: 7.5 hours (or 6 hours without testing)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🤖 AGENT IDENTIFICATION

Phase 1: Plan Agent?
► "Should I use Plan Agent to design this feature?"
   ✓ (Waiting for your authorization)

Phase 2: Backend Agent?
► "Should I use Backend Agent to create:
   - Database migration
   - Model & Service
   - API endpoints"
   ✓ (Waiting for your authorization)

Phase 3: Frontend Agent?
► "Should I use Frontend Agent to build:
   - Star rating component
   - Display component
   - Admin interface"
   ✓ (Waiting for your authorization)

Phase 4: Testing?
► "Include testing phase?"
   Options:
   ☐ YES - Run full tests
   ☐ NO - Skip testing
   ☐ LATER - Test manually later
   (Waiting for your choice)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⚙️ IMPLEMENTATION STYLE

How would you like to proceed?

Option A: PHASE BY PHASE (More control)
├── Phase 1 complete → Review
├── Phase 2 complete → Review
├── Phase 3 complete → Review
└── Phase 4 complete → Review
Benefit: You see each phase result before next starts

Option B: ALL AT ONCE (Faster)
├── All phases run sequentially
├── Get final result
└── Single review
Benefit: Faster, but less control

Option C: HYBRID (Custom)
├── Phase 1-2 together (Backend)
├── Phase 3 separate (Frontend)
├── Phase 4 optional (Testing)
Benefit: Custom approach

Your choice: A / B / C?

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## SECTION 3: Your Authorization Checkpoints

### Checkpoint 1: Agent Authorization

```
I will ask:
"Should I use [Agent Name] for [specific task]?"

You respond:
✓ YES → Proceed with that agent
✗ NO → I'll do it manually
? QUESTIONS → Ask clarifications
```

### Checkpoint 2: Testing Decision

```
I will ask:
"Include testing phase?

Options:
☐ YES - Full unit, integration, E2E tests
☐ NO - Skip all tests
☐ MANUAL - I'll test manually later
"

You respond: Choose one
```

### Checkpoint 3: Implementation Style

```
I will ask:
"How to implement?

Options:
☐ Phase by Phase (most control)
☐ All at Once (fastest)
☐ Custom (choose which phases together)
"

You respond: Choose one
```

---

## SECTION 4: Real Examples

### Example 1: Simple Feature

**Your Request:**
```
"Add a wishlist feature"
```

**My Response:**
```
📋 CHRONOGRAM

Phase 1: Planning (1h)
Phase 2: Backend (2.5h)
Phase 3: Frontend (2h)
Phase 4: Testing (1h) - YOU DECIDE

Total: 6.5h

🤖 AGENTS
- Use Plan Agent? ► (Waiting authorization)
- Use Backend Agent? ► (Waiting authorization)
- Use Frontend Agent? ► (Waiting authorization)

📋 TESTING
- Include testing? ► (Waiting your decision)

⚙️ STYLE
- Phase-by-phase, all-at-once, or hybrid? ► (Waiting)
```

---

### Example 2: Complex Feature

**Your Request:**
```
"Create a complete returns management system with:
- Return requests from customers
- Warehouse inspection workflow
- Return label generation
- Refund processing
- Real-time tracking
- Admin analytics"
```

**My Response:**
```
📋 CHRONOGRAM

Phase 1: Planning (2h)
├── Analyze 6 workflows
├── Design complex schema
├── 15+ API endpoints
└── Risk assessment

Phase 2: Backend (10h)
├── 5 database tables
├── Return workflow service
├── PDF label generation
├── Refund processing service
├── Analytics queries
├── Events & listeners

Phase 3: Frontend (6h)
├── Customer return request form
├── Warehouse inspection interface
├── Real-time tracking widget
├── Admin analytics dashboard

Phase 4: Testing (3h) - YOU DECIDE
├── Complex workflow tests
├── Payment processing tests
├── Real-time sync tests

Total: 21h (or 18h without testing)

🤖 AGENTS
- Use Plan Agent for planning? ► (Waiting)
- Use Backend Agent for services? ► (Waiting)
- Use Frontend Agent for UI? ► (Waiting)

📋 TESTING
- Include testing? ► (Waiting)

⚙️ STYLE
- Phase approach? ► (Waiting)
```

---

## SECTION 5: What Happens After You Authorize

### If You Say "YES" to Agent

```
You: "✓ YES - Use Backend Agent"

I:
1. Use Backend Agent capabilities
2. Generate code following Alsernet patterns
3. Create migrations, models, services, endpoints
4. Implement validation and security
5. Add event handling
6. Show you the completed phase
7. Ask if you want to proceed to next phase
```

### If You Say "NO" to Agent

```
You: "✗ NO - Don't use Backend Agent"

I:
1. Still explain what needs to be done
2. You decide approach:
   a) I do it manually
   b) You'll do it yourself
   c) Skip this phase
3. Continue with your choice
```

### If You Skip Testing

```
You: "☐ NO - Skip testing"

I:
1. Skip the testing phase completely
2. You can manually test later
3. Focus on implementation only
4. Save ~1.5-3 hours
```

---

## SECTION 6: Phase-by-Phase Workflow Example

### If You Choose "Phase by Phase"

**Phase 1 Completes:**
```
✅ PHASE 1 COMPLETE: Planning

Results:
├── Feature analysis
├── Database schema designed
├── API endpoints listed (10 endpoints)
├── Task breakdown (8 tasks)
└── Risk assessment

Ready for Phase 2? Continue or Review?
☐ Continue → Phase 2: Backend
☐ Review → Ask questions
☐ Modify → Change approach
```

**Phase 2 Completes:**
```
✅ PHASE 2 COMPLETE: Backend

Delivered:
├── Database migration ✓
├── Rating model ✓
├── RatingService ✓
├── API controller (5 endpoints) ✓
├── Validation & authorization ✓
├── Events (RatingCreated) ✓
└── 3 test files ✓

Ready for Phase 3? Continue or Review?
☐ Continue → Phase 3: Frontend
☐ Review → Ask questions
☐ Test Manually First → Then Phase 3
```

**Phase 3 Completes:**
```
✅ PHASE 3 COMPLETE: Frontend

Delivered:
├── Rating form component ✓
├── Rating display component ✓
├── Admin interface ✓
├── Real-time updates via Echo ✓
└── Validation & error handling ✓

Ready for Phase 4? Or Done?
☐ Testing → Phase 4: Tests
☐ Done → Feature complete
☐ Test Manually → You test, I write tests later
```

---

## SECTION 7: All-at-Once Workflow Example

### If You Choose "All at Once"

```
🚀 STARTING FULL IMPLEMENTATION

Phase 1: Planning... ⏳
Phase 2: Backend... ⏳
Phase 3: Frontend... ⏳
Phase 4: Testing... (if enabled)

[Working...]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✅ ALL PHASES COMPLETE

Delivered:
✅ Plan & design
✅ Backend (migrations, models, API)
✅ Frontend (components, UI)
✅ Tests (if included)

All changes committed and ready to review.

Anything to adjust? Changes needed?
```

---

## SECTION 8: Your Decision Points Summary

### Decision Point 1: Agent Usage
```
Question: "Use this agent?"
Your Options:
✓ YES → Agent does the work
✗ NO → Manual approach
? HELP → Ask clarifications
```

### Decision Point 2: Testing Inclusion
```
Question: "Include testing?"
Your Options:
☐ YES → Full tests included
☐ NO → Skip testing completely
☐ LATER → Manual testing later
```

### Decision Point 3: Implementation Speed
```
Question: "Implementation style?"
Your Options:
☐ PHASE-BY-PHASE → Most control, see each phase
☐ ALL-AT-ONCE → Fastest, see final result
☐ HYBRID → Custom combination
```

---

## SECTION 9: Quick Reference

### How to Request Changes

**SIMPLE:**
```
"Add [feature name]"
```

**DETAILED:**
```
"Add [feature] with:
- [requirement 1]
- [requirement 2]
- [requirement 3]"
```

**COMPLEX:**
```
"Create [system] where:
- [workflow 1]
- [workflow 2]
- [integration]
- [special requirement]"
```

### How to Respond to My Questions

**Agent Question:**
```
My: "Use Backend Agent?"
You: "✓ YES" or "✗ NO" or "? HELP"
```

**Testing Question:**
```
My: "Include testing?"
You: "☐ YES" or "☐ NO" or "☐ LATER"
```

**Style Question:**
```
My: "Which approach?"
You: "☐ PHASE-BY-PHASE" or "☐ ALL-AT-ONCE" or "☐ HYBRID"
```

---

## SECTION 10: Important Notes

### What Happens to Your TODO List?

Your TODO list will:
1. Show all tasks/phases being worked on
2. Update as each phase completes
3. Mark tasks as completed immediately
4. Give you visibility of progress

### File Commits

Each phase will be committed separately:
```
- Phase 1: Planning → Commit
- Phase 2: Backend → Commit
- Phase 3: Frontend → Commit
- Phase 4: Testing → Commit
```

So you can see exactly what was added at each stage.

### Agent Output

When agents work:
- They follow Alsernet patterns
- They generate production-ready code
- They include comments and documentation
- They add proper error handling
- They respect existing code style

---

## WORKFLOW QUICK VISUAL

```
REQUEST
   ↓
CHRONOGRAM (phases & time) ← You see this
   ↓
AGENT QUESTIONS ← You authorize
   ↓
TESTING QUESTION ← You decide
   ↓
STYLE QUESTION ← You choose approach
   ↓
IMPLEMENTATION ← Happens automatically
   ↓
COMPLETE ✓ ← Ready for review
```

---

**Version:** 1.0
**Effective:** Immediately
**This is your new standard workflow for all requests**
