# Email Template System - Quick Reference Guide

**Fecha:** Noviembre 27, 2025
**Para:** Developers
**Formato:** Cheat sheet rápido

---

## 🚀 Getting Started (30 segundos)

### 1. Setup Base (first time)
```bash
php artisan migrate
php artisan db:seed --class=EmailTemplateSeeder
php artisan serve  # Visit: http://localhost/administratives/email-templates
```

### 2. Admin UI Access
```
URL: http://localhost/administratives/email-templates
Actions: Create, Edit (with CodeMirror), Preview, Test, Delete
```

### 3. Send Email (in your code)
```php
use App\Factories\DocumentEmailFactory;

// Simple way
DocumentEmailFactory::sendUploadedNotification($document);

// With custom variables
DocumentEmailFactory::sendByTemplate($document, $template, [
    'CUSTOM_FIELD' => 'value'
]);
```

---

## 📞 API Reference

### TemplateRendererService

```php
use App\Services\Mails\MailTemplateRendererService;

// Render template
$html = MailTemplateRendererService::renderEmailTemplate($template, [
    'CUSTOMER_NAME' => 'Juan',
    'ORDER_ID' => '12345'
]);

// Replace variables
$text = MailTemplateRendererService::replaceVariables(
    'Hola {CUSTOMER_NAME}',
    ['CUSTOMER_NAME' => 'Juan']
);

// Get preview
$html = MailTemplateRendererService::getPreviewHtml($template);

// Validate
$validation = MailTemplateRendererService::validateTemplate($template);

// Get stats
$stats = MailTemplateRendererService::getStats($template);
```

### DocumentEmailFactory

```php
use App\Factories\DocumentEmailFactory;

// By template key (RECOMMENDED)
DocumentEmailFactory::sendByTemplateKey($doc, 'document_uploaded');
DocumentEmailFactory::sendByTemplateKey($doc, 'document_reminder');
DocumentEmailFactory::sendByTemplateKey($doc, 'document_missing', ['MISSING' => 'Cédula']);

// By template object
$template = EmailTemplate::where('key', '...')->first();
DocumentEmailFactory::sendByTemplate($doc, $template);

// Specific methods
DocumentEmailFactory::sendUploadedNotification($doc);
DocumentEmailFactory::sendReminder($doc);
DocumentEmailFactory::sendMissingNotification($doc, 'Documentos faltantes');
DocumentEmailFactory::sendApprovedNotification($doc);

// Custom content (legacy)
DocumentEmailFactory::sendCustom(
    $doc,
    'Subject',
    '<p>Content with {CUSTOMER_NAME}</p>'
);

// Test email
DocumentEmailFactory::sendTestEmail($template, 'test@example.com');

// Get templates
$template = DocumentEmailFactory::getTemplate('document_uploaded');
$templates = DocumentEmailFactory::getAvailableTemplates();
$stats = DocumentEmailFactory::getTemplatesWithStats();
```

### DocumentCustomMail

```php
use App\Mail\Documents\DocumentCustomMail;
use Illuminate\Support\Facades\Mail;

// With template
$mail = new DocumentCustomMail($doc, null, null, $template);

// With custom content
$mail = new DocumentCustomMail($doc, 'Subject', '<p>Content</p>');

// Fluent interface
$mail = (new DocumentCustomMail($doc))
    ->setTemplate($template)
    ->setVariables(['EXTRA' => 'value']);

// Send
Mail::to($doc->customer_email)->send($mail);

// Queue
Mail::to($doc->customer_email)->queue($mail);
```

### EmailTemplate Model

```php
use App\Models\Mail\MailTemplate;

// Get by key
$template = MailTemplate::where('key', 'document_uploaded')->first();

// Get enabled templates
$templates = MailTemplate::enabled()->get();

// Get for specific module
$templates = MailTemplate::module('documents')->enabled()->get();

// Search
$templates = MailTemplate::search('uploaded')->get();

// Available variables
$vars = $template->getAvailableVariables();

// Check if complete
$isComplete = $template->isComplete();

// Get missing variables
$missing = $template->getMissingVariables();

// Check enabled
$template->is_enabled = true;
$template->save();

// Get preview
$html = $template->getPreviewHtml();
```

---

## 📧 Template Variables

### Document Variables (auto-populated)
```
{CUSTOMER_NAME}      → Juan García
{CUSTOMER_EMAIL}     → juan@example.com
{ORDER_ID}           → 12345
{ORDER_REFERENCE}    → 12345
{DOCUMENT_TYPE}      → Cédula
{UPLOAD_LINK}        → https://Alsernet.test/upload/12345
{EXPIRATION_DATE}    → 2025-12-04
```

### System Variables (predefined)
```
{SITE_NAME}          → Alsernet
{SITE_URL}           → https://Alsernet.test
{SITE_EMAIL}         → info@Alsernet.test
{CURRENT_YEAR}       → 2025
{CURRENT_MONTH}      → 11
{CURRENT_DAY}        → 27
```

### Campaign Variables (if using templates)
```
{UNSUBSCRIBE_URL}    → https://Alsernet.test/unsubscribe/xxx
{WEB_VIEW_URL}       → https://Alsernet.test/view/xxx
{SUBSCRIBER_UID}     → xxx
```

---

## 🎨 Create New Template (Code)

### Via Factory
```php
EmailTemplate::create([
    'key' => 'custom_email',
    'name' => 'Custom Email',
    'subject' => 'Subject with {VARIABLES}',
    'content' => '<p>Hello {CUSTOMER_NAME}</p>',
    'layout_id' => null,  // optional
    'is_enabled' => true,
    'variables' => ['CUSTOMER_NAME', 'ORDER_ID'],
    'module' => 'documents',
    'description' => 'Custom email for documents'
]);
```

### Via Admin UI
```
1. Go to: http://localhost/administratives/email-templates/create
2. Fill form:
   - Key: unique identifier (e.g., 'custom_email')
   - Name: Display name
   - Subject: Email subject (can have {VARIABLES})
   - Module: documents, orders, etc.
   - Layout: Optional header/footer layout
   - Content: HTML content with {VARIABLES}
   - Description: Notes
3. Click Create
4. Use immediately via factory/mailable
```

---

## 🔄 Update Template

### Via Code
```php
$template = EmailTemplate::where('key', 'document_uploaded')->first();
$template->update([
    'subject' => 'New subject',
    'content' => '<p>New content</p>',
    'variables' => ['CUSTOMER_NAME', 'ORDER_ID']
]);
```

### Via Admin UI
```
1. Go to: http://localhost/administratives/email-templates
2. Click Edit on template
3. Modify HTML in CodeMirror editor
4. Watch live preview (right panel) update
5. Modify metadata: name, subject, state
6. Press Ctrl+S or click Save
7. Changes saved immediately
```

---

## 📨 Send Email Workflows

### Workflow 1: Simple Notification
```php
// In controller, job, or event
$document = Document::find($id);
DocumentEmailFactory::sendUploadedNotification($document);
// Template loaded from DB, variables auto-filled, sent.
```

### Workflow 2: With Additional Variables
```php
DocumentEmailFactory::sendByTemplateKey(
    $document,
    'document_missing',
    ['MISSING_DOCUMENTS' => 'Cédula, Comprobante']
);
```

### Workflow 3: Batch Sending
```php
$documents = Document::where('status', 'pending')->get();

foreach ($documents as $doc) {
    DocumentEmailFactory::sendReminder($doc);
}
// Each gets individual personalized email
```

### Workflow 4: Queue for Later
```php
Mail::to($doc->customer_email)
    ->queue(new DocumentCustomMail($doc, null, null, $template));
// Email sent asynchronously via queue worker
```

### Workflow 5: Fallback to Legacy
```php
// If template not found, use custom content
$template = DocumentEmailFactory::getTemplate('some_key');

if ($template) {
    DocumentEmailFactory::sendByTemplate($doc, $template);
} else {
    DocumentEmailFactory::sendCustom($doc, 'Subject', '<p>Content</p>');
}
```

---

## 🧪 Testing Examples

### Unit Test

```php
use App\Factories\DocumentEmailFactory;use App\Models\Document\Document;use Illuminate\Support\Facades\Mail;use Tests\TestCase;

class DocumentEmailTest extends TestCase
{
    public function test_send_uploaded_notification()
    {
        Mail::fake();

        $doc = Document::factory()->create();
        $sent = DocumentEmailFactory::sendUploadedNotification($doc);

        $this->assertTrue($sent);
        Mail::assertSent(\App\Mail\Documents\DocumentCustomMail::class);
    }
}
```

### Manual Test (Tinker)
```bash
php artisan tinker

>>> $doc = \App\Models\Order\Document::first()
>>> \App\Factories\DocumentEmailFactory::sendUploadedNotification($doc)
true  # ✅ If returns true, email sent
# false # ❌ If returns false, check logs for error

>>> \Illuminate\Support\Facades\Log::tail()  # Check recent logs
```

### Browser Test
```
1. Go to: http://localhost/administratives/email-templates
2. Click "Send Test" on any template
3. Enter email address: your-email@example.com
4. Click Send
5. Check inbox in 5-10 seconds
6. Verify rendering
```

---

## 🐛 Debugging

### Check if template exists
```php
$template = EmailTemplate::where('key', 'document_uploaded')->first();
if (!$template) {
    echo "Template not found!";
} else {
    echo "Template: " . $template->name;
}
```

### Check if rendering works

```php
$html = \App\Services\Mails\MailTemplateRendererService::renderEmailTemplate(
    $template,
    ['CUSTOMER_NAME' => 'Test']
);
echo $html;  // Check output for {VARIABLE} tags (should be gone)
```

### Check logs
```bash
tail -f storage/logs/laravel.log | grep -i email
# Look for: "Email enviado", "Error al enviar", etc.
```

### Check database
```bash
php artisan tinker
>>> \App\Models\Email\EmailTemplate::all()->count()  # Should be 4+
>>> \App\Models\Email\EmailTemplate::where('key', 'document_uploaded')->first()->content
# View actual HTML content
```

---

## ⚙️ Configuration

### MAIL configuration (.env)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@Alsernet.test
MAIL_FROM_NAME="${APP_NAME}"
```

### Module Configuration (in code)
```php
// Define available modules in EmailTemplate model
public static function availableModules()
{
    return [
        'documents' => 'Document Emails',
        'orders' => 'Order Emails',
        'notifications' => 'System Notifications',
        'marketing' => 'Marketing Emails',
    ];
}
```

---

## 🚨 Common Issues & Fixes

### Issue: Template not found
```php
// Problem
$sent = DocumentEmailFactory::sendByTemplateKey($doc, 'invalid_key');
// Return false, email not sent

// Solution
$template = DocumentEmailFactory::getTemplate('invalid_key');
if (!$template) {
    // Create template or use different key
}
```

### Issue: Variables not replaced
```php
// Problem
$html = "Hello {CUSTOMER}";  // Wrong variable name
// Result: "Hello {CUSTOMER}"

// Solution
$html = "Hello {CUSTOMER_NAME}";
// Result: "Hello Juan García"
```

### Issue: Email not sending
```php
// Check MAIL config
php artisan tinker
>>> config('mail.driver')  # Should be 'smtp'
>>> config('mail.from')    # Should be configured

// Test SMTP
php artisan tinker
>>> \Illuminate\Support\Facades\Mail::raw('Test', fn($m) => $m->to('test@example.com'))

// Check logs
tail -f storage/logs/laravel.log
```

### Issue: Preview showing raw HTML
```
// Problem: Preview shows <p>Hello {CUSTOMER_NAME}</p> literally

// Solution: Refresh page or clear browser cache
// Check variables are properly defined in template.variables JSON

// In tinker:
>>> $template->variables
// Should be array, not null
```

---

## 📊 Database Queries

### Get all enabled document templates
```php
EmailTemplate::where('module', 'documents')
    ->where('is_enabled', true)
    ->orderBy('name')
    ->get();
```

### Find templates using specific variable
```php
EmailTemplate::where('is_enabled', true)
    ->where('content', 'like', '%{CUSTOMER_NAME}%')
    ->get();
```

### Get recently updated templates
```php
EmailTemplate::orderBy('updated_at', 'desc')
    ->limit(10)
    ->get();
```

### Find unused templates (not in any layout)
```php
EmailTemplate::where('layout_id', null)->get();
```

---

## 📚 File Locations

```
Core Files:
├── app/Services/Email/TemplateRendererService.php
├── app/Mail/Documents/DocumentCustomMail.php
├── app/Factories/DocumentEmailFactory.php
├── app/Models/Email/EmailTemplate.php
├── app/Http/Controllers/Administratives/Email/EmailTemplateController.php
├── database/migrations/2025_11_27_213954_create_email_templates_table.php
├── database/seeders/EmailTemplateSeeder.php

Views:
├── resources/views/administratives/email-templates/index.blade.php
├── resources/views/administratives/email-templates/create.blade.php
├── resources/views/administratives/email-templates/edit.blade.php
├── resources/views/administratives/email-templates/preview.blade.php

Documentation:
├── docs/PHASE_1_SETUP_INSTRUCTIONS.md
├── docs/PHASE_2_VIEWS_AND_UI.md
├── docs/PHASE_3_INTEGRATION.md
├── docs/TESTING_AND_VALIDATION.md
├── docs/IMPLEMENTATION_COMPLETE_SUMMARY.md
├── docs/QUICK_REFERENCE.md
```

---

## 🎯 Most Common Use Cases

### 1. Send document uploaded notification (90%)
```php
DocumentEmailFactory::sendUploadedNotification($document);
```

### 2. Send reminder for pending document (70%)
```php
DocumentEmailFactory::sendReminder($document);
```

### 3. Notify missing documents (60%)
```php
DocumentEmailFactory::sendMissingNotification($document, 'Cédula, Comprobante');
```

### 4. Send approval confirmation (50%)
```php
DocumentEmailFactory::sendApprovedNotification($document);
```

### 5. Custom email (30%)
```php
DocumentEmailFactory::sendCustom($document, $subject, $content);
```

---

## 🔐 Security Reminders

- ✅ Always validate user input before passing to template
- ✅ Variables are replaced (safe), not evaluated (code injection impossible)
- ✅ CSRF tokens on all forms
- ✅ Emailtemplate changes are logged
- ✅ Authorization checked (administratives middleware)
- ✅ HTML sanitized in preview (iframe isolation)

---

## 📞 Support

- **For SETUP issues:** See `FASE_1_SETUP_INSTRUCTIONS.md`
- **For UI issues:** See `FASE_2_VIEWS_AND_UI.md`
- **For integration:** See `FASE_3_INTEGRATION.md`
- **For testing:** See `TESTING_AND_VALIDATION.md`
- **For overview:** See `IMPLEMENTATION_COMPLETE_SUMMARY.md`

---

**Last Updated:** November 27, 2025
**Quick Ref Version:** 1.0
