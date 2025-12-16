# Plan Agent - Task Breakdown & Sequencing Guide

**Break down complex features into manageable, sequential tasks.**

---

## Task Breakdown Process

```
Feature
    ↓
Identify Components
    ↓
Sequence Tasks
    ↓
Estimate Effort
    ↓
Create Timeline
    ↓
Ready to Execute
```

---

## SECTION 1: Task Identification

### Method: Break by Implementation Layer

```
Frontend
    ↓
API/Backend
    ↓
Database
    ↓
Integration
    ↓
Testing
```

### Example: Feedback Module

```
LAYER 1: DATABASE
└── Create feedback table
└── Add indexes

LAYER 2: MODEL & SERVICE
└── Create Feedback model
└── Create FeedbackService
└── Add validation

LAYER 3: API
└── Create FeedbackController
└── Implement CRUD endpoints
└── Add permissions

LAYER 4: EVENTS
└── Create FeedbackSubmitted event
└── Create listeners
└── Setup email notifications

LAYER 5: FRONTEND
└── Create feedback form
└── Create feedback list
└── Add real-time updates

LAYER 6: TESTING
└── Unit tests
└── Integration tests
└── E2E tests

LAYER 7: DOCUMENTATION
└── API documentation
└── User guide
```

---

## SECTION 2: Task Sequencing

### Dependency Rules

```
Rule 1: Database comes first
Rule 2: Model depends on Database
Rule 3: Service depends on Model
Rule 4: API depends on Service
Rule 5: Frontend depends on API
Rule 6: Testing needs all layers
```

### Sequential Task Order

```
1️⃣  Database Migration
2️⃣  Model & Relationships
3️⃣  Service Layer
4️⃣  API Endpoints
5️⃣  Events & Listeners
6️⃣  Frontend Components
7️⃣  Unit Tests
8️⃣  Integration Tests
9️⃣  E2E Tests
🔟  Documentation
```

### Parallel Tasks

```
Can run in parallel:
├── Task 4 (API) while Task 6 (Frontend) with mocked API
├── Task 7 (Unit Tests) while implementing services
└── Task 9 (E2E Tests) while frontend is being built

Critical Path (must be sequential):
└── Task 1 → Task 2 → Task 3 → Task 4
```

---

## SECTION 3: Task Template

### Task Definition Template

```markdown
## Task: [Task Name]

**Duration:** [X hours]
**Depends on:** [Previous task]
**Blocks:** [Next task]
**Skills needed:** [Laravel/JavaScript/etc]

### What to Deliver:
- [ ] Deliverable 1
- [ ] Deliverable 2
- [ ] Deliverable 3

### Steps:
1. Step 1
2. Step 2
3. Step 3

### Code to Create/Modify:
- New: `app/Models/ClassName.php`
- New: `app/Services/ServiceName.php`
- Modify: `routes/api.php`

### Testing:
- Run: `php artisan test`
- Test: [Specific test case]

### Notes:
- [Important considerations]
```

### Real Example

```markdown
## Task: Create Feedback Model & Relationships

**Duration:** 30 minutes
**Depends on:** Database Migration
**Blocks:** Service Layer
**Skills needed:** Laravel, Eloquent

### What to Deliver:
- [x] Feedback model with relationships
- [x] Scopes for filtering
- [x] Casts for data types
- [x] Factory for testing

### Steps:
1. Create Feedback model: `php artisan make:model Feedback`
2. Add relationships to Product and User
3. Define scopes: approved(), pending(), recent()
4. Add attribute casts for rating
5. Create factory: `php artisan make:factory FeedbackFactory`

### Code to Create/Modify:
- New: `app/Models/Feedback.php`
- New: `database/factories/FeedbackFactory.php`
- Modify: `app/Models/Product.php` (add relationship)
- Modify: `app/Models/User.php` (add relationship)

### Testing:
- Run: `php artisan tinker`
- Test: `Feedback::factory()->create()`
- Test: `$product->feedback()->count()`

### Notes:
- Use UUID for primary key
- Add soft deletes for archive functionality
- Add timestamps automatically
```

---

## SECTION 4: Complete Task Plan Example

### Feature: Customer Feedback Module

#### Task 1: Database Migration
```
Duration: 1 hour
Deliverables:
- feedback table created
- indexes added
- foreign keys set up

Command: php artisan make:migration create_feedback_table
```

#### Task 2: Feedback Model
```
Duration: 30 minutes
Deliverables:
- Feedback model
- Relationships defined
- Scopes created
- Factory created

Files: Feedback.php, FeedbackFactory.php
```

#### Task 3: User & Product Models Update
```
Duration: 20 minutes
Deliverables:
- User model: hasFeedback() relationship
- Product model: feedback() relationship

Files: User.php, Product.php
```

#### Task 4: Feedback Service
```
Duration: 1.5 hours
Deliverables:
- FeedbackService class
- submitFeedback() method
- respondToFeedback() method
- Validation logic
- Event dispatch

Files: FeedbackService.php
```

#### Task 5: Form Request Validation
```
Duration: 30 minutes
Deliverables:
- StoreFeedbackRequest
- UpdateFeedbackRequest
- Validation rules
- Spanish error messages

Files: Requests/StoreFeedbackRequest.php, UpdateFeedbackRequest.php
```

#### Task 6: API Controller
```
Duration: 2 hours
Deliverables:
- FeedbackController
- store() - submit feedback
- index() - list feedback
- show() - get single feedback
- update() - user edit own feedback
- destroy() - user delete own feedback
- respond() - manager respond to feedback
- moderate() - admin delete/hide feedback

Files: FeedbackController.php
```

#### Task 7: API Routes
```
Duration: 30 minutes
Deliverables:
- POST /api/products/{id}/feedback
- GET /api/products/{id}/feedback
- PUT /api/feedback/{id}
- DELETE /api/feedback/{id}
- POST /api/feedback/{id}/response
- PUT /api/feedback/{id}/moderate

Files: routes/api.php
```

#### Task 8: Events & Listeners
```
Duration: 1.5 hours
Deliverables:
- FeedbackSubmitted event
- FeedbackResponded event
- NotifyManagerListener
- NotifyUserListener
- LogActivityListener

Files: Events/, Listeners/
```

#### Task 9: Email Notifications
```
Duration: 1 hour
Deliverables:
- FeedbackSubmittedMail
- FeedbackResponseMail
- Templates in Markdown

Files: Mail/, resources/views/emails/
```

#### Task 10: Frontend Form Component
```
Duration: 2 hours
Deliverables:
- FeedbackForm component (jQuery)
- Validation with jQuery Validate
- Star rating selector
- Comment textarea
- Success/error messages

Files: resources/views/components/feedback-form.blade.php, public/js/feedback-form.js
```

#### Task 11: Frontend List Component
```
Duration: 2 hours
Deliverables:
- FeedbackList component (jQuery)
- Display with rating stars
- Pagination
- Filter options
- Real-time updates via Echo

Files: resources/views/components/feedback-list.blade.php, public/js/feedback-list.js
```

#### Task 12: Admin Dashboard
```
Duration: 1.5 hours
Deliverables:
- Dashboard table
- Moderation actions
- Response modal
- Filters and search

Files: resources/views/admin/feedback.blade.php, public/js/feedback-admin.js
```

#### Task 13: Unit Tests
```
Duration: 1.5 hours
Deliverables:
- FeedbackServiceTest
- FeedbackModelTest
- FeedbackValidationTest

Files: tests/Unit/
```

#### Task 14: Integration Tests
```
Duration: 2 hours
Deliverables:
- FeedbackControllerTest
- API endpoint tests
- Permission tests
- Event dispatch tests

Files: tests/Integration/
```

#### Task 15: E2E Tests
```
Duration: 1.5 hours
Deliverables:
- User submits feedback flow
- Manager reviews feedback flow
- Admin moderation flow

Files: tests/E2E/
```

#### Task 16: Documentation
```
Duration: 1 hour
Deliverables:
- API documentation
- Database schema diagram
- User guide

Files: docs/feedback-module.md
```

### Timeline Summary

```
Total Duration: 21.5 hours

Sequential Path:
Task 1 (1h)
  → Task 2-3 (1h)
  → Task 4-5 (2h)
  → Task 6-7 (2.5h)
  → Task 8-9 (2.5h)
  → Task 10-12 (5.5h) [Frontend can start after Task 7]
  → Task 13-15 (5h)
  → Task 16 (1h)

Parallel Opportunities:
- Frontend (Tasks 10-12) while backend events are being implemented
- Unit tests (Task 13) while implementing services

Estimated Completion: 3 days (7-8 hours per day) with parallelization
```

---

## SECTION 5: Task Estimation Guide

### Time Estimation Template

```
Simple Task = 1-2 hours
├── Simple CRUD endpoint
├── Single model
└── Basic validation

Medium Task = 3-6 hours
├── Complex logic
├── Multiple relationships
└── Integration with events

Complex Task = 8-16 hours
├── Multi-step workflow
├── Real-time features
└── Advanced integrations

Very Complex = 16+ hours
├── New domain/pattern
├── Performance optimization
└── Multiple integrations
```

### Add Buffer Time

```
Initial estimate: 20 hours
Buffer (25%): 5 hours
Final estimate: 25 hours

Use buffer for:
- Unexpected issues
- Code review feedback
- Testing edge cases
- Documentation improvements
```

---

## SECTION 6: Task Tracking Template

### Weekly Task Plan

```markdown
# Week 1 - Feedback Module Development

## Monday (8 hours)
- [x] Task 1: Database Migration (1h)
- [x] Task 2-3: Models & Relationships (1h)
- [x] Task 4: Feedback Service (2h)
- [x] Task 5: Form Requests (0.5h)
- [ ] Task 6: API Controller (2.5h)

## Tuesday (8 hours)
- [x] Task 6: API Controller (2h)
- [x] Task 7: API Routes (0.5h)
- [x] Task 8-9: Events & Email (2.5h)
- [x] Task 13: Unit Tests (2h)
- [ ] Task 14: Integration Tests (1h)

## Wednesday (8 hours)
- [x] Task 14: Integration Tests (2h)
- [x] Task 10: Feedback Form (2h)
- [x] Task 11: Feedback List (2h)
- [ ] Task 15: E2E Tests (1.5h)

## Thursday (6 hours)
- [x] Task 15: E2E Tests (1.5h)
- [x] Task 12: Admin Dashboard (1.5h)
- [x] Task 16: Documentation (1h)
- [ ] Code review & fixes (1.5h)

Progress: 95% Complete
```

---

## Task Breakdown Checklist

```
TASK IDENTIFICATION:
□ All components identified
□ Tasks are 1-4 hours each
□ Clear deliverables for each task
□ Dependencies mapped

TASK SEQUENCING:
□ Database tasks first
□ Models before services
□ Services before API
□ API before frontend
□ Testing throughout

ESTIMATION:
□ Realistic time estimates
□ 25% buffer included
□ Parallel opportunities identified
□ Critical path identified

DOCUMENTATION:
□ Each task has template filled
□ Dependencies clear
□ Deliverables listed
□ Success criteria defined
```

---

**Version:** 1.0
**Updated:** November 30, 2024
