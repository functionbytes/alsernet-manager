# Document System - Quick Developer Reference

Fast lookup guide for working with the document management system.

---

## 📋 What's Where

### Documentation Files
- **DOCUMENT_SYSTEM_ARCHITECTURE.md** - Complete system design, data flow, state machine, audit trail
- **IMPLEMENTATION_PLAN.md** - Phase-by-phase implementation steps, services, jobs, tests
- **MAILERS_DOCUMENTS_INTEGRATION.md** - Email template setup, language support, variable mapping

### Code Organization
```
app/
├── Models/Order/
│   ├── Document.php                      (core document model)
│   ├── DocumentStatus.php                (status definitions)
│   ├── DocumentStatusHistory.php         (audit trail)
│   ├── DocumentStatusTransition.php      (state machine)
│   ├── DocumentSlaPolicy.php            (SLA configuration)
│   ├── DocumentSlaBreach.php            (SLA violation tracking)
│   ├── DocumentConfiguration.php         (required documents per type)
│   └── DocumentAction.php               (actions performed)
│
├── Http/Controllers/Managers/Settings/
│   └── DocumentSlaPoliciesController.php (SLA admin interface)
│
├── Services/Documents/
│   ├── DocumentEmailService.php         (email dispatch) [CREATE]
│   ├── DocumentStatusService.php        (status transitions) [CREATE]
│   └── DocumentActionService.php        (audit logging) [UPDATE]
│
└── Jobs/Document/
    └── SendDocumentEmailJob.php         (queue email sending) [CREATE]

database/
├── migrations/
│   ├── ...create_document_statuses_table.php
│   ├── ...create_document_status_histories_table.php
│   ├── ...create_document_status_transitions_table.php
│   ├── ...add_status_id_to_documents_table.php
│   ├── ...create_document_sla_policies_table.php
│   └── ...create_document_sla_breaches_table.php
│
└── seeders/
    ├── DocumentStatusSeeder.php
    └── DocumentStatusTransitionSeeder.php

routes/
└── managers.php                          (routes under manager.settings.documents.sla-policies.*)

resources/views/managers/views/settings/documents/
├── index.blade.php                       (nav to global config + types)
├── sla-policies/
│   ├── index.blade.php                  (list with stats & search)
│   ├── create.blade.php                 (create form)
│   └── edit.blade.php                   (edit form)
├── configurations/
│   └── index.blade.php                  (global settings form)
└── types/
    ├── index.blade.php                  (list types & required docs)
    ├── create.blade.php                 (create new type)
    └── edit.blade.php                   (edit type documents)
```

---

## ⚡ Quick Tasks

### Add a New Email Template Type

1. **Create template in Mailers UI**
   ```
   URL: /manager/settings/mailers/templates
   - Key: document_new_type
   - Module: documents
   - Language: Spanish (+ other languages as needed)
   - Subject: Email subject
   - Content: HTML with {VARIABLES}
   ```

2. **Add method to DocumentEmailService**
   ```php
   // app/Services/Documents/DocumentEmailService.php

   public function sendNewTypeEmail(Document $document): void
   {
       $variables = $this->buildVariables($document);
       $variables['CUSTOM_VARIABLE'] = 'value';

       SendDocumentEmailJob::dispatch(
           $document,
           'document_new_type',  // Must match template key
           $variables
       );
   }
   ```

3. **Trigger the email**
   ```php
   // From a controller or event listener
   $emailService = app(DocumentEmailService::class);
   $emailService->sendNewTypeEmail($document);
   ```

### Change Default SLA Times

**Location:** `app/Http/Controllers/Managers/Settings/DocumentSlaPoliciesController.php`

```php
// In store() or update() method - set defaults
'upload_request_time' => 120,      // minutes
'review_time' => 480,              // minutes
'approval_time' => 1440,           // minutes (24 hours)
```

**Or:** Create new policy in `/manager/settings/documents/sla-policies/create` UI

### Modify Document Status Transitions

**Location:** `app/Models/Order/DocumentStatusTransition.php`

1. Add transition in database seeder or manually:
   ```php
   DocumentStatusTransition::create([
       'from_status_id' => DocumentStatus::getByKey('awaiting_documents')->id,
       'to_status_id' => DocumentStatus::getByKey('approved')->id,
       'permission' => 'documents.approve',     // optional: require permission
       'requires_all_documents_uploaded' => true,
       'auto_transition_after_days' => null,    // optional: auto-transition
       'is_active' => true,
   ]);
   ```

2. Validate transitions:
   ```php
   $transition = DocumentStatusTransition::getValidTransitions($currentStatus);
   // Returns array of valid next statuses
   ```

### Add Document Type with Custom Requirements

**Location:** `/manager/settings/documents/types/create`

1. Go to UI and create new type (e.g., "motorcycle")
2. Add required documents (e.g., "Registration Certificate", "License Copy")
3. System automatically applies to all emails showing required documents

### Check SLA Breach Status

```php
// In tinker or code
$document = Document::find($id);

// Get all breaches for document
$breaches = $document->slaBreaches;

// Get unresolved breaches
$breaches = $document->slaBreaches()->unresolved()->get();

// Get specific breach type
$breaches = $document->slaBreaches()->byBreachType('approval')->get();

// Escalate a breach
$breach = $document->slaBreaches()->first();
$breach->escalate();  // Sets escalated=true, escalated_at=now()
```

---

## 🔄 Status Transition Flow

Valid transitions in database (13 total):

```
pending
├─→ incomplete      (admin uploads manually)
├─→ awaiting_documents
└─→ cancelled       (admin cancels)

incomplete
├─→ awaiting_documents
├─→ rejected        (requires 'documents.reject' permission)
└─→ cancelled

awaiting_documents
├─→ approved        (requires 'documents.approve' + all_uploaded=true)
├─→ incomplete      (admin rejects)
└─→ cancelled

approved
├─→ completed       (requires 'documents.complete')
└─→ rejected        (requires 'documents.reject')

rejected
├─→ awaiting_documents
└─→ cancelled

completed / cancelled
└─ (terminal states, no outbound transitions)
```

---

## 📧 Email Triggers Summary

| Trigger | Global Setting | Template Key | When Sent |
|---------|-----------------|--------------|-----------|
| Initial Request | `enable_initial_request` | `document_initial_request` | Document created |
| Reminder | `enable_reminder` | `document_reminder` | After `reminder_days` |
| Missing Docs | `enable_missing_docs` | `document_missing_documents` | Admin requests |
| Approved | - | `document_approved` | Status → APPROVED |
| Rejected | - | `document_rejected` | Status → INCOMPLETE |
| Completed | - | `document_completed` | Status → COMPLETED |
| Escalation | SLA `enable_escalation` | Custom | SLA threshold met |

### Setting Location
- **Stored in:** Settings table with `documents.*` prefix
- **Admin UI:** `/manager/settings/documents/configurations`
- **Defaults:**
  - `enable_initial_request` = yes
  - `enable_reminder` = yes
  - `reminder_days` = 7
  - `enable_missing_docs` = yes

---

## 🌍 Language Support

### How It Works

1. **Customer language:** Check `customers.lang_id`
2. **Template lookup:** Find EmailTemplate with `lang_id` + `key` + `module`
3. **Fallback:** Use `Lang::first()->id` if language template not found
4. **Render:** Replace {VARIABLES} with values
5. **Apply layout:** Wrap with header/footer/wrapper

### Setting Up New Language

1. **Create email templates** for each language in Mailers UI
   - Same key (`document_initial_request`)
   - Different `lang_id`
   - Translated content

2. **Create layout components** if not already done
   - header with `lang_id`
   - footer with `lang_id`
   - wrapper with `lang_id`

3. **Test:**
   ```php
   // In tinker
   $doc = Document::with('customer')->find(1);
   $doc->customer->lang_id = 2;  // Change language
   $emailService = app(DocumentEmailService::class);
   $emailService->sendInitialRequest($doc);
   ```

---

## 🗄️ Database Schema Quick Reference

### Key Tables

**document_statuses**
```
id, key (unique), label, description, color (hex), icon, is_active, order, timestamps
```

**document_status_transitions**
```
id, from_status_id (FK), to_status_id (FK), permission, requires_all_documents_uploaded,
auto_transition_after_days, is_active, timestamps
UNIQUE (from_status_id, to_status_id)
```

**document_sla_policies**
```
id, name, upload_request_time, review_time, approval_time,
business_hours_only, business_hours (JSON), timezone,
document_type_multipliers (JSON: corta→0.75, dni→0.5, etc.),
enable_escalation, escalation_threshold_percent (default 80), escalation_recipients (JSON),
active, is_default, timestamps
```

**document_sla_breaches**
```
id, document_id (FK), sla_policy_id (FK),
breach_type (enum: upload_request|review|approval),
minutes_over, escalated, escalated_at, resolved, resolved_at, notes, timestamps
```

**request_document_configurations**
```
id, document_type (unique), document_type_label, required_documents (JSON), timestamps
```

---

## 🔧 Common Code Patterns

### Get Document with All Relations

```php
$document = Document::with([
    'status',
    'statusHistories' => fn($q) => $q->latest(),
    'slaPolicy',
    'slaBreaches',
    'actions' => fn($q) => $q->latest(),
    'customer',
])->find($id);
```

### Check if Transition is Valid

```php
$currentStatus = $document->status;
$desiredStatus = DocumentStatus::getByKey('approved');

$transition = DocumentStatusTransition::query()
    ->where('from_status_id', $currentStatus->id)
    ->where('to_status_id', $desiredStatus->id)
    ->where('is_active', true)
    ->first();

if (!$transition) {
    throw new \Exception("Cannot transition from {$currentStatus->key} to {$desiredStatus->key}");
}

if ($transition->requires_all_documents_uploaded && !$document->hasAllDocumentsUploaded()) {
    throw new \Exception("All documents must be uploaded before approval");
}
```

### Calculate Actual SLA Deadline

```php
$sla = $document->slaPolicy;
$multiplier = $sla->getMultiplierForDocumentType($document->document_type);
$slaMinutes = $sla->approval_time * $multiplier;

// If business hours only
$deadline = $sla->business_hours_only
    ? calculateBusinessHoursDeadline($document->created_at, $slaMinutes, $sla->timezone)
    : $document->created_at->addMinutes($slaMinutes);
```

### Log Document Action

```php
DocumentAction::create([
    'document_id' => $document->id,
    'action_type' => 'status_changed',
    'description' => "Status changed from pending to approved",
    'performed_by' => auth('managers')->id(),
    'metadata' => [
        'from_status' => 'pending',
        'to_status' => 'approved',
        'reason' => 'All documents validated',
    ],
]);
```

### Send Email with Logging

```php
$emailService = app(DocumentEmailService::class);

try {
    $emailService->sendInitialRequest($document);
} catch (\Exception $e) {
    \Log::error("Failed to send document email", [
        'document_id' => $document->id,
        'error' => $e->getMessage(),
    ]);
}
```

---

## 📊 Key Statistics & Queries

### SLA Compliance

```php
// Documents within SLA
$inSla = Document::whereHas('slaBreaches', fn($q) =>
    $q->whereNull('resolved')
)->count();

// Overdue documents
$overdue = Document::doesntHave('slaBreaches')->count();

// Escalated breaches
$escalated = DocumentSlaBreach::where('escalated', true)
    ->where('resolved', false)
    ->count();
```

### Email Statistics

```php
// Emails sent per type
DocumentAction::where('action_type', 'like', 'email_sent_%')
    ->groupBy('action_type')
    ->selectRaw('action_type, COUNT(*) as total')
    ->get();

// Failed emails
DocumentAction::where('action_type', 'like', 'email_failed_%')
    ->count();
```

---

## 🚀 Performance Tips

### Eager Load Relations
```php
// ✅ Good - avoids N+1
Document::with('status', 'slaPolicy', 'customer')->get();

// ❌ Bad - N+1 query problem
Document::all();
foreach ($documents as $doc) {
    echo $doc->status->label;  // Separate query per document
}
```

### Index Important Columns
```sql
-- Already in migrations, but key ones:
CREATE INDEX idx_document_status_id ON documents(status_id);
CREATE INDEX idx_document_sla_policy_id ON documents(sla_policy_id);
CREATE UNIQUE INDEX unique_status_transition ON document_status_transitions(from_status_id, to_status_id);
```

### Cache Document Types
```php
// Avoid repeated DB queries
$types = \Cache::remember('document_types', 3600, function() {
    return DocumentConfiguration::pluck('document_type_label', 'document_type')->toArray();
});
```

---

## 🧪 Testing Commands

```bash
# Run document-related tests
php artisan test tests/Feature/Documents

# Run specific test
php artisan test tests/Feature/Documents/DocumentStatusTransitionTest

# Test email job
php artisan test tests/Feature/Documents/SendDocumentEmailJobTest

# Test with specific filter
php artisan test --filter=testDocumentStatusTransition
```

---

## 📝 Useful Artisan Commands

```bash
# Seed statuses and transitions
php artisan db:seed --class=DocumentStatusSeeder
php artisan db:seed --class=DocumentStatusTransitionSeeder

# Create new service
php artisan make:class Services/Documents/DocumentStatusService

# Create new job
php artisan make:job Document/SendDocumentReminderJob

# List available commands
php artisan list --filter=document
```

---

## ⚠️ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Forgetting language_id when querying email template | Always include fallback to default language |
| Not checking SLA multiplier for document type | Use `$sla->getMultiplierForDocumentType()` |
| Modifying DocumentStatusTransition without checking `is_active` | Always filter by `where('is_active', true)` |
| Not logging actions to DocumentAction | Create action record for audit trail |
| Sending email without queueing | Always use `SendDocumentEmailJob::dispatch()` |
| Assuming all documents have customer_id | Check for NULL and handle gracefully |
| Overdue SLA check not accounting for business hours | Use dedicated method: `calculateBusinessHoursDeadline()` |

---

## 📞 Support References

### Configuration Files
- `.env` - Mail driver settings
- `config/documents.php` - Document system config
- `config/mail.php` - Email configuration

### Key Service Providers
- DocumentServiceProvider (if exists, register services here)
- MailServiceProvider - Email configuration

### Related Documentation
- Laravel Mail: https://laravel.com/docs/mail
- Laravel Queues: https://laravel.com/docs/queues
- Laravel Events: https://laravel.com/docs/events
- Eloquent Relations: https://laravel.com/docs/eloquent-relationships

