# Plan Agent - Feature Planning Guide

**Detailed guide for planning features from requirements to implementation.**

---

## Feature Planning Process

```
1. Requirement Analysis
   ↓
2. Feature Breakdown
   ↓
3. User Stories & Acceptance Criteria
   ↓
4. Dependencies & Constraints
   ↓
5. Ready for Implementation
```

---

## REAL EXAMPLE: Customer Feedback Module

### Step 1: Request to Plan Agent

```
Plan a customer feedback module with:
- Users can submit feedback (1-5 stars, comments)
- Feedback visible on product pages
- Managers can filter and respond to feedback
- Admin can moderate and delete inappropriate feedback
- Email notification when feedback is submitted
- Reports showing feedback trends
```

### Step 2: Requirement Analysis Output

```
FUNCTIONAL REQUIREMENTS:
✅ User can submit feedback with rating and comment
✅ User can view feedback on product page
✅ Manager can view feedback dashboard
✅ Manager can respond to feedback
✅ Admin can delete/hide feedback
✅ Email notification on new feedback
✅ Generate feedback reports

NON-FUNCTIONAL REQUIREMENTS:
✅ Page load time < 2 seconds
✅ Support 1000 concurrent users
✅ Archive feedback after 2 years
✅ Spanish language support
✅ Mobile responsive

ACCEPTANCE CRITERIA:
✅ User can submit 1-5 star rating
✅ Comment is required (min 10 chars)
✅ Email sent within 5 minutes
✅ Dashboard shows last 30 days
✅ Reports export to PDF
```

### Step 3: Feature Breakdown Output

```
DATABASE DESIGN:
└── feedback table
    ├── id (UUID)
    ├── product_id (FK)
    ├── user_id (FK)
    ├── rating (1-5)
    ├── title (string)
    ├── comment (text)
    ├── status (pending, approved, deleted)
    ├── manager_response (text)
    ├── manager_id (FK)
    ├── helpful_count (integer)
    ├── created_at
    └── updated_at

API ENDPOINTS:
└── POST /api/products/{id}/feedback
    (submit feedback)
└── GET /api/products/{id}/feedback
    (list feedback)
└── PUT /api/feedback/{id}
    (user edit)
└── DELETE /api/feedback/{id}
    (user delete own)
└── POST /api/feedback/{id}/response
    (manager respond)
└── PUT /api/feedback/{id}/status
    (admin moderate)
└── GET /api/reports/feedback
    (feedback reports)

SERVICES:
└── FeedbackService
    ├── submitFeedback()
    ├── respondToFeedback()
    ├── generateReport()
    └── cleanupOldFeedback()

EVENTS:
└── FeedbackSubmitted → NotifyManagerListener
└── FeedbackResponded → NotifyUserListener
```

### Step 4: Implementation Tasks Output

```
TASK BREAKDOWN (Sequential):

1. DATABASE MIGRATION (1-2 hours)
   └── Create feedback table
   └── Add indexes on product_id, user_id
   └── Add constraints (cascade delete)

2. MODEL & RELATIONSHIPS (30 min)
   └── Create Feedback model
   └── Define relations to Product and User
   └── Add scopes (approved, pending)

3. SERVICE LAYER (1-2 hours)
   └── Create FeedbackService
   └── Implement business logic
   └── Add validation

4. API ENDPOINTS (2-3 hours)
   └── POST /feedback (submit)
   └── GET /feedback (list)
   └── PUT /feedback/{id} (update)
   └── DELETE /feedback/{id} (delete)
   └── POST /feedback/{id}/response

5. EVENTS & NOTIFICATIONS (1-2 hours)
   └── Create FeedbackSubmitted event
   └── Create listeners for email
   └── Add to queue for sending

6. FRONTEND COMPONENTS (3-4 hours)
   └── Feedback form component
   └── Feedback list component
   └── Admin dashboard component
   └── Manager response modal

7. TESTING (2-3 hours)
   └── Unit tests for service
   └── Integration tests for API
   └── E2E tests for user flow

TOTAL TIME: 11-17 hours
CRITICAL PATH: API → Frontend (dependent)
```

### Step 5: Risk Analysis Output

```
IDENTIFIED RISKS:

1. SPAM/ABUSE
   Risk Level: HIGH
   Mitigation:
   - Implement rate limiting (1 feedback per user per day)
   - Add content moderation (flagged for admin review)
   - User reputation system

2. PERFORMANCE
   Risk Level: MEDIUM
   Mitigation:
   - Index product_id and user_id
   - Implement pagination (15 per page)
   - Cache feedback count on product

3. DATA RETENTION
   Risk Level: MEDIUM
   Mitigation:
   - Archive feedback after 2 years
   - Create scheduled job for cleanup
   - Backup before deletion

4. CONCURRENT MODIFICATIONS
   Risk Level: LOW
   Mitigation:
   - Use optimistic locking
   - Check updated_at timestamp
   - Handle conflict gracefully
```

### Step 6: Deployment Plan Output

```
PRE-DEPLOYMENT CHECKLIST:
□ Run migrations on staging
□ Run all tests (unit, integration, E2E)
□ Performance test with 1000 concurrent users
□ Security review (input validation)
□ Database backup before migration
□ Update documentation
□ Notify support team of new feature

DEPLOYMENT STEPS:
1. Database migration (on main server)
2. Deploy API code
3. Deploy frontend code
4. Run smoke tests
5. Monitor error logs for 1 hour
6. Announce feature to users

ROLLBACK PLAN:
If critical issues:
1. Revert deployment
2. Rollback database migration
3. Restore from backup
4. Update status page
```

---

## Feature Planning Checklist

Use this checklist when planning ANY feature:

```
BEFORE YOU START:
□ Read existing feature documentation
□ Check for similar existing features
□ Identify stakeholders and get their input
□ List known constraints

REQUIREMENTS:
□ Functional requirements defined
□ Non-functional requirements listed
□ Acceptance criteria clear
□ Edge cases identified

ARCHITECTURE:
□ Database schema designed
□ API endpoints listed
□ Service structure defined
□ Events/listeners identified
□ Authentication/authorization clear

IMPLEMENTATION:
□ Tasks broken down into 1-4 hour chunks
□ Dependencies identified
□ Parallel work identified
□ Estimates realistic (add 25% buffer)

RISKS:
□ Major risks identified
□ Mitigation strategies defined
□ Fallback plans created
□ Performance implications assessed

DEPLOYMENT:
□ Deployment strategy clear
□ Testing plan complete
□ Rollback procedure defined
□ Monitoring strategy defined
```

---

## Common Patterns

### Pattern 1: CRUD Feature
```
Database table with 5 columns
API endpoints (Create, Read, Update, Delete)
Frontend form + list
Service with validation
Total: 8-12 hours
```

### Pattern 2: Real-time Feature
```
Database table
API endpoints
Events + Listeners
WebSocket broadcasting
Frontend with real-time updates
Total: 16-24 hours
```

### Pattern 3: Workflow Feature
```
Multiple database tables (with relationships)
API endpoints for each entity
Service layer managing workflow
Events for state changes
Frontend forms for each step
Total: 20-32 hours
```

### Pattern 4: Reporting Feature
```
Database query optimization
API endpoint for report generation
Service layer for calculations
Frontend dashboard/charts
Scheduled job for exports
Total: 12-18 hours
```

---

## Tips for Better Planning

✅ **TIP 1: Be specific about requirements**
```
❌ Bad: "Users can manage products"
✅ Good: "Managers can create, edit, and delete products with up to 5 images each"
```

✅ **TIP 2: Include business constraints**
```
❌ Bad: "Add product search"
✅ Good: "Add product search that works with 100k+ products and returns results in < 500ms"
```

✅ **TIP 3: Mention integrations**
```
❌ Bad: "Process payments"
✅ Good: "Process payments via Stripe with webhook notifications and auto-reconciliation"
```

✅ **TIP 4: Specify user roles**
```
❌ Bad: "Users can view orders"
✅ Good: "Customers can view their orders, Managers can view all orders, Admin can view and export all orders"
```

---

## After Planning

### Next Step: Use Frontend Agent
```
"Build the feedback submission form using the plan:
- Title: required
- Rating: 1-5 stars dropdown
- Comment: textarea (min 10 chars)
- Submit button
- Success message"
```

### Next Step: Use Backend Agent
```
"Create the feedback API endpoints using the plan:
- Database migration with feedback table
- Feedback model with relationships
- FeedbackController with CRUD
- FeedbackService with business logic
- Email notification event and listener"
```

---

## Real Feature Examples

See `.claude/examples/` for complete real-world feature plans:
- `customer-feedback-example/`
- `return-management-example/`
- `wishlist-example/`

---

**Version:** 1.0
**Updated:** November 30, 2024
