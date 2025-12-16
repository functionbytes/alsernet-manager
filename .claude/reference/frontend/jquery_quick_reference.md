# jQuery Quick Reference for Alsernet

**Fast lookup guide for common jQuery patterns used in Alsernet.**

---

## FORM VALIDATION (jQuery Validate)

### Basic Validation
```javascript
$('#form').validate({
    rules: {
        name: 'required',
        email: { required: true, email: true }
    }
});
```
**File:** `patterns/jquery-validate-patterns.md` → Simple Validation

### With Bootstrap 5 Error Classes
```javascript
$('#form').validate({
    rules: { name: 'required' },
    errorClass: 'is-invalid',
    validClass: 'is-valid',
    errorPlacement: function(error, element) {
        error.addClass('invalid-feedback d-block');
        element.after(error);
    }
});
```
**File:** `patterns/jquery-validate-patterns.md` → Bootstrap Integration

### Form Submission Handler
```javascript
$('#form').validate({
    rules: { name: 'required' },
    submitHandler: function(form) {
        $.ajax({
            url: '/api/save',
            type: 'POST',
            data: $(form).serialize(),
            success: function() {
                toastr.success('Saved');
            }
        });
        return false;
    }
});
```
**File:** `patterns/jquery-validate-patterns.md` → AJAX Form Submission

### Server-side Validation
```javascript
rules: {
    email: {
        required: true,
        remote: {
            url: '/api/validate-email',
            type: 'post'
        }
    }
}
```
**File:** `patterns/jquery-validate-patterns.md` → Server-side Validation

---

## AJAX Operations

### GET Request
```javascript
$.get('/api/warehouses', function(data) {
    console.log(data);
});
```
**File:** `patterns/ajax-patterns.md` → Simple GET Request

### POST Form
```javascript
$.ajax({
    url: '/api/warehouses',
    type: 'POST',
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: JSON.stringify(formData),
    contentType: 'application/json'
});
```
**File:** `patterns/ajax-patterns.md` → POST with CSRF Token

### File Upload
```javascript
let formData = new FormData();
formData.append('file', file);

$.ajax({
    url: '/api/upload',
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false
});
```
**File:** `patterns/ajax-patterns.md` → File Upload with Progress

---

## Real-time Updates

### Bootstrap Echo
```javascript
import Echo from 'laravel-echo';
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY
});
```
**File:** `patterns/real-time-patterns.md` → Bootstrap Echo

### Listen to Channel
```javascript
window.Echo.channel('warehouse-updates')
    .listen('warehouse.updated', (event) => {
        console.log('Updated:', event.warehouse);
    });
```
**File:** `patterns/real-time-patterns.md` → Public Channel

### Private Channel
```javascript
window.Echo.private('user.' + userId)
    .notification((notification) => {
        toastr.info(notification.message);
    });
```
**File:** `patterns/real-time-patterns.md` → Private Channel

### Presence (Who's Online)
```javascript
window.Echo.join('warehouse-room')
    .here((users) => { /* users currently here */ })
    .joining((user) => { /* user joined */ })
    .leaving((user) => { /* user left */ });
```
**File:** `patterns/real-time-patterns.md` → Presence Channel

---

## Form Operations

### Form Submission
```javascript
$('#form').on('submit', function(e) {
    e.preventDefault();

    let data = {
        name: $('#name').val(),
        email: $('#email').val()
    };

    $.ajax({
        url: '/api/save',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json'
    });
});
```
**File:** `patterns/form-patterns.md` → Simple Form Submission

### Field Validation (As-you-type)
```javascript
$('#email').on('keyup', function() {
    let email = $(this).val();
    let isValid = /^[^\s@]+@[^\s@]+$/.test(email);

    if (isValid) {
        $(this).addClass('is-valid').removeClass('is-invalid');
    } else {
        $(this).addClass('is-invalid').removeClass('is-valid');
    }
});
```
**File:** `patterns/form-patterns.md` → Real-time Field Validation

### Add/Remove Dynamic Fields
```javascript
$('#add-item').on('click', function() {
    let html = `<div class="item-row">
        <input type="text" class="form-control item-name">
        <button type="button" class="btn btn-danger remove-item">Remove</button>
    </div>`;
    $('#items-container').append(html);
});

$(document).on('click', '.remove-item', function() {
    $(this).closest('.item-row').remove();
});
```
**File:** `patterns/form-patterns.md` → Dynamic Form Fields

### Display Server Errors
```javascript
error: function(xhr) {
    if (xhr.status === 422) {
        $.each(xhr.responseJSON.errors, function(field, messages) {
            $(`#${field}`).addClass('is-invalid');
            toastr.error(messages[0]);
        });
    }
}
```
**File:** `patterns/form-patterns.md` → Form Error Display

---

## Modals & Tables

### Open Modal
```javascript
$('#create-btn').on('click', function() {
    $('#warehouse-modal').modal('show');
});
```
**File:** `patterns/modal-table-patterns.md` → Basic Modal

### Load Data into Modal
```javascript
$(document).on('click', '.edit-btn', function() {
    let id = $(this).data('id');

    $.get(`/api/warehouses/${id}`, function(warehouse) {
        $('#name').val(warehouse.name);
        $('#location').val(warehouse.location);
        $('#warehouse-modal').modal('show');
    });
});
```
**File:** `patterns/modal-table-patterns.md` → Load Data into Modal

### Modal Events
```javascript
$('#warehouse-modal')
    .on('show.bs.modal', function() { /* before show */ })
    .on('shown.bs.modal', function() { /* after show */ })
    .on('hidden.bs.modal', function() {
        $('#warehouse-form')[0].reset();
    });
```
**File:** `patterns/modal-table-patterns.md` → Modal Events

### Initialize DataTable
```javascript
$('#warehouses-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '/api/warehouses/list',
    columns: [
        { data: 'id' },
        { data: 'name' },
        { data: 'location' }
    ]
});
```
**File:** `patterns/modal-table-patterns.md` → Basic DataTable

### Reload DataTable
```javascript
let table = $('#warehouses-table').DataTable();
table.ajax.reload(null, false); // Keep pagination
```
**File:** `patterns/modal-table-patterns.md` → Real-time Table Updates

### Confirmation Dialog
```javascript
if (confirm('Delete this warehouse?')) {
    $.ajax({
        url: `/api/warehouses/${id}`,
        type: 'DELETE',
        success: function() {
            toastr.success('Deleted');
            table.ajax.reload();
        }
    });
}
```
**File:** `patterns/modal-table-patterns.md` → Modal with Confirmation

---

## Storage & Caching

### localStorage (Persistent)
```javascript
// Save
localStorage.setItem('warehouse-id', '123');

// Get
let id = localStorage.getItem('warehouse-id');

// Remove
localStorage.removeItem('warehouse-id');
```
**File:** `patterns/cache-storage-patterns.md` → localStorage

### localStorage with JSON
```javascript
let prefs = {
    theme: 'dark',
    language: 'es'
};

localStorage.setItem('user-prefs', JSON.stringify(prefs));
let saved = JSON.parse(localStorage.getItem('user-prefs'));
```
**File:** `patterns/cache-storage-patterns.md` → Store JSON Objects

### sessionStorage (Per Tab)
```javascript
// Save form draft
sessionStorage.setItem('form-draft', JSON.stringify(formData));

// Restore on page load
let draft = sessionStorage.getItem('form-draft');
if (draft) {
    // Restore form fields
}
```
**File:** `patterns/cache-storage-patterns.md` → sessionStorage

### IndexedDB (Large Data)
```javascript
let dbService = new IndexedDBService('Alsernet', 1);
await dbService.init();

// Save
await dbService.save('warehouses', warehouse);

// Get all
let warehouses = await dbService.getAll('warehouses');

// Query
let results = await dbService.query('warehouses',
    item => item.location === 'Madrid'
);
```
**File:** `patterns/cache-storage-patterns.md` → IndexedDB Wrapper

### Cache with Real-time Sync
```javascript
window.Echo.channel('warehouse-updates')
    .listen('warehouse.updated', async (event) => {
        await dbService.save('warehouses', event.warehouse);
        let warehouses = await dbService.getAll('warehouses');
        renderWarehouses(warehouses);
    });
```
**File:** `patterns/cache-storage-patterns.md` → Cache with Real-time Updates

---

## DOM Manipulation

### Selectors
```javascript
$('#id');                           // By ID
$('.class');                        // By class
$('input[type="email"]');          // By attribute
$('tr:first');                     // Pseudo-selector
$('.parent > .child');             // Direct child
```

### Content
```javascript
$('#el').html('<p>New HTML</p>');   // Set HTML
$('#el').text('New text');          // Set text
$('#el').val('value');              // Set form value
$('#el').html();                    // Get HTML
$('#el').val();                     // Get form value
```

### Classes
```javascript
$('#el').addClass('active');
$('#el').removeClass('active');
$('#el').toggleClass('active');
$('#el').hasClass('active');
```

### Attributes
```javascript
$('#el').attr('data-id', '123');    // Set attribute
$('#el').attr('data-id');          // Get attribute
$('#el').data('id', '123');        // Set data attribute
$('#el').data('id');               // Get data attribute
```

### Visibility
```javascript
$('#el').show();
$('#el').hide();
$('#el').fadeIn();
$('#el').fadeOut();
$('#el').slideDown();
$('#el').slideUp();
$('#el').toggle();
```

### Events
```javascript
$('#btn').on('click', function() { });
$(document).on('click', '.dynamic', function() { }); // Event delegation
$('#form').on('submit', function(e) { e.preventDefault(); });
$('#input').on('keyup change', function() { });
```

**File:** `patterns/` → All files use these

---

## Notifications

### Toastr (Toast Notifications)
```javascript
toastr.success('Operation successful');
toastr.error('An error occurred');
toastr.warning('Warning message');
toastr.info('Information message');
```
**Usage:** Configure once, use everywhere
**File:** `AGENTE-FRONTEND-ESPECIFICACION-JQUERY.md`

---

## Common Workflows

### 1. Load & Display List
```javascript
$.get('/api/warehouses', function(warehouses) {
    let html = '';
    warehouses.forEach(w => {
        html += `<tr><td>${w.name}</td></tr>`;
    });
    $('#table tbody').html(html);
});
```
**File:** `patterns/ajax-patterns.md` → Simple GET Request

### 2. Save with Modal
```javascript
$('#save-btn').on('click', function() {
    $.ajax({
        url: '/api/warehouses',
        type: 'POST',
        data: JSON.stringify({
            name: $('#name').val()
        }),
        success: function() {
            $('#modal').modal('hide');
            $.get('/api/warehouses', reloadList);
        }
    });
});
```
**File:** `patterns/ajax-patterns.md` + `patterns/modal-table-patterns.md`

### 3. Live Table with Updates
```javascript
let table = $('#table').DataTable({
    ajax: '/api/list',
    serverSide: true
});

window.Echo.channel('updates')
    .listen('updated', () => table.ajax.reload());
```
**File:** `patterns/real-time-patterns.md` + `patterns/modal-table-patterns.md`

### 4. Form with Validation
```javascript
$('#form').on('submit', function(e) {
    e.preventDefault();

    let errors = validateForm();
    if (errors.length > 0) {
        errors.forEach(err => toastr.error(err));
        return;
    }

    $.ajax({ /* submit */ });
});

function validateForm() {
    let errors = [];
    if (!$('#email').val()) errors.push('Email required');
    return errors;
}
```
**File:** `patterns/form-patterns.md` → Form Validation

---

## Gotchas & Tips

### ⚠️ Event Delegation Required for Dynamic Elements
```javascript
// ❌ Won't work for dynamically added elements
$('.delete-btn').on('click', function() { });

// ✅ Use event delegation
$(document).on('click', '.delete-btn', function() { });
```

### ⚠️ Always Include CSRF Token
```javascript
headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
}
```

### ⚠️ Reset Forms After Submit
```javascript
success: function() {
    $('#form')[0].reset();  // Native reset
    $('#modal').modal('hide');
}
```

### ⚠️ Prevent Multiple Submissions
```javascript
let $btn = $('button[type="submit"]');
$btn.prop('disabled', true);

// ... ajax ...

complete: function() {
    $btn.prop('disabled', false);
}
```

### ✅ Use .closest() for Parent Elements
```javascript
// Find parent row of a button inside the row
let $row = $(this).closest('tr');
let id = $row.data('id');
```

---

## Performance Tips

1. **Cache jQuery selectors** if used multiple times
   ```javascript
   let $form = $('#my-form');
   $form.on('submit', ...);
   $form.find('#field').val();
   ```

2. **Use event delegation** for dynamic content
   ```javascript
   $(document).on('click', '.dynamic-btn', handler);
   ```

3. **Debounce search** to avoid too many requests
   ```javascript
   let timeout;
   $('#search').on('keyup', function() {
       clearTimeout(timeout);
       timeout = setTimeout(doSearch, 300);
   });
   ```

4. **Minimize DOM manipulations** - batch updates
   ```javascript
   let html = '';
   items.forEach(item => html += renderItem(item));
   $('#container').html(html); // Single update
   ```

5. **Use localStorage** to cache expensive data
   ```javascript
   let data = localStorage.getItem('cached-data');
   if (!data) {
       $.get('/api/data', function(result) {
           localStorage.setItem('cached-data', JSON.stringify(result));
       });
   }
   ```

---

## Documentation Index

| Need | File |
|------|------|
| AJAX calls | `patterns/ajax-patterns.md` |
| Real-time WebSockets | `patterns/real-time-patterns.md` |
| Forms & validation | `patterns/form-patterns.md` |
| Modals & tables | `patterns/modal-table-patterns.md` |
| Storage & caching | `patterns/cache-storage-patterns.md` |
| Full architecture | `AGENTE-FRONTEND-ESPECIFICACION-JQUERY.md` |
| UI components | `components/component-library-detailed.md` |

---

**Last Updated:** November 30, 2025
**Version:** 1.0
**For:** Alsernet Frontend Development Team
