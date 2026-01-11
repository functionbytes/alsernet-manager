# Component Organization - Document Module

## Overview
Components in the Document module have been reorganized by type for better maintainability and clarity.

## New Structure

### **Email Components** (`components/email/`)
Email-related components for customer communication:
- `actions-card.blade.php` - Email action buttons (send initial request, reminders, etc.)
- `email-history.blade.php` - Display email communication history
- **Modals** (`modals/`)
  - `initial-request.blade.php` - Initial document request modal
  - `reminder.blade.php` - Reminder email modal
  - `missing-docs.blade.php` - Specific missing documents modal
  - `upload-confirmation.blade.php` - Upload confirmation modal
  - `approval.blade.php` - Approval notification modal
  - `rejection.blade.php` - Rejection notification modal
  - `custom-email.blade.php` - Custom email composition modal

### **File Management Components** (`components/files/`)
File upload and attachment handling:
- `upload-section.blade.php` - Document upload form with progress tracking
- `additional-attachments.blade.php` - Additional files attachment section
- **Modals** (`modals/`)
  - `confirm-delete.blade.php` - File deletion confirmation modal

### **Validation/Workflow Components** (`components/validation/`)
Multi-stage approval workflow:
- `workflow.blade.php` - Workflow progress and approval/rejection buttons
- **Modals** (`modals/`)
  - `approve-stage.blade.php` - Stage approval modal
  - `reject-stage.blade.php` - Stage rejection modal

### **Document Notes Components** (`components/notes/`)
Internal notes and comments:
- `document-notes-sidebar.blade.php` - Notes container with add/edit/delete
- `document-notes.blade.php` - Individual note display

### **Management Components** (`components/management/`)
Document and order management:
- `document-management-card.blade.php` - Document configuration (status, origin, load type, etc.)
- `status-timeline.blade.php` - Document status history timeline
- `action-history.blade.php` - All actions performed on document
- `action-history-sidebar.blade.php` - Compact action history for sidebar
- **Modals** (`modals/`)
  - `confirm-configuration.blade.php` - Configuration change confirmation modal
  - `confirm-missing-docs.blade.php` - Missing documents warning modal

## Updated Include Paths

All view includes have been updated to reference the new organized structure:

### In `manage.blade.php`
```blade
@include('documents::components.validation.workflow-sidebar')
@include('documents::components.notes.document-notes-sidebar')
@include('documents::components.management.action-history')
@include('documents::components.email.email-history')
@include('documents::components.management.status-timeline')
@include('documents::components.files.additional-attachments')
@include('documents::components.management.modals.confirm-missing-docs')
@include('documents::components.files.modals.confirm-delete')
```

### In `actions-card.blade.php`
All email modals now use:
```blade
@include('documents::components.email.modals.{modal-name}')
```

### In `workflow.blade.php`
Validation modals now use:
```blade
@include('documents::components.validation.modals.{modal-name}')
```

### In `document-management-card.blade.php`
Configuration modal now uses:
```blade
@include('documents::components.management.modals.confirm-configuration')
```

## Benefits

1. **Better Organization** - Components grouped by functionality/type
2. **Easier Navigation** - Clear folder structure for finding related components
3. **Scalability** - Easy to add new components within each category
4. **Permission Management** - Components can be controlled by type (email-actions, validation-workflow, document-upload, etc.)
5. **Reduced Clutter** - Root components directory no longer contains all files

## Cache Clearing

After reorganization, view cache was cleared:
```bash
php artisan view:clear
```

This ensures Laravel uses the new view paths immediately.

---

**Last Updated:** 2026-01-04
**Status:** ✅ Complete
