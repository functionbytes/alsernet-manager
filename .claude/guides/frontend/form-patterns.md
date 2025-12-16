# Form & Validation Patterns with jQuery

## Quick Reference: Common Form Patterns

---

## 1. Simple Form Submission

### HTML Structure

```html
<form id="warehouse-form">
    <div class="mb-3">
        <label for="name" class="form-label">Warehouse Name</label>
        <input type="text" class="form-control" id="name" name="name">
    </div>
    <div class="mb-3">
        <label for="location" class="form-label">Location</label>
        <input type="text" class="form-control" id="location" name="location">
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
```

### jQuery Submission

```javascript
jQuery(function($) {
    $('#warehouse-form').on('submit', function(e) {
        e.preventDefault();

        let formData = {
            name: $('#name').val(),
            location: $('#location').val()
        };

        $.ajax({
            url: '/api/warehouses',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                toastr.success('Warehouse saved');
                $('#warehouse-form')[0].reset();
                redirectOrRefresh();
            },
            error: function(xhr) {
                handleFormErrors(xhr);
            }
        });
    });
});
```

---

## 2. Form Validation - Client Side

### Custom Validation

```javascript
// resources/js/utils/validation.js
function validateWarehouseForm() {
    let errors = {};
    let name = $('#name').val().trim();
    let location = $('#location').val().trim();
    let capacity = $('#capacity').val();

    if (!name) {
        errors.name = 'Warehouse name is required';
    } else if (name.length < 3) {
        errors.name = 'Name must be at least 3 characters';
    }

    if (!location) {
        errors.location = 'Location is required';
    }

    if (!capacity || capacity <= 0) {
        errors.capacity = 'Capacity must be greater than 0';
    }

    return errors;
}

// Usage in form submission
jQuery(function($) {
    $('#warehouse-form').on('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        clearFormErrors();

        let errors = validateWarehouseForm();
        if (Object.keys(errors).length > 0) {
            displayFormErrors(errors);
            return false;
        }

        // Submit form
        submitForm();
    });

    function clearFormErrors() {
        $('input, textarea, select').removeClass('is-invalid');
        $('.invalid-feedback').empty();
    }

    function displayFormErrors(errors) {
        $.each(errors, function(field, message) {
            $(`#${field}`).addClass('is-invalid');
            $(`#${field}`).after(`<div class="invalid-feedback">${message}</div>`);
            toastr.error(message);
        });
    }
});
```

### Bootstrap Validation Classes

```html
<!-- Valid field -->
<input type="text" class="form-control is-valid" id="name">
<div class="valid-feedback">Looks good!</div>

<!-- Invalid field -->
<input type="text" class="form-control is-invalid" id="name">
<div class="invalid-feedback">Please fix this field.</div>
```

---

## 3. Real-time Field Validation

### As-you-type Validation

```javascript
jQuery(function($) {
    // Email validation
    $('#email').on('keyup', function() {
        let email = $(this).val();
        let isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

        if (email === '') {
            $(this).removeClass('is-valid is-invalid');
        } else if (isValid) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });

    // Username check via AJAX
    let usernameTimeout;
    $('#username').on('keyup', function() {
        let username = $(this).val();

        clearTimeout(usernameTimeout);

        if (username.length < 3) {
            $(this).removeClass('is-valid is-invalid');
            return;
        }

        usernameTimeout = setTimeout(() => {
            $.get('/api/check-username', { username }, function(response) {
                if (response.available) {
                    $('#username').removeClass('is-invalid').addClass('is-valid');
                    $('.valid-feedback').text('Username available');
                } else {
                    $('#username').removeClass('is-valid').addClass('is-invalid');
                    $('.invalid-feedback').text('Username taken');
                }
            });
        }, 500);
    });
});
```

---

## 4. Dynamic Form Fields

### Add/Remove Fields

```html
<div id="items-container">
    <div class="item-row mb-3">
        <input type="text" class="form-control item-name" placeholder="Item name">
        <button type="button" class="btn btn-danger remove-item">Remove</button>
    </div>
</div>
<button type="button" class="btn btn-secondary" id="add-item">Add Item</button>
```

### jQuery Implementation

```javascript
jQuery(function($) {
    let itemCount = 1;

    $('#add-item').on('click', function() {
        let html = `<div class="item-row mb-3">
            <input type="text" class="form-control item-name" placeholder="Item name">
            <button type="button" class="btn btn-danger remove-item">Remove</button>
        </div>`;
        $('#items-container').append(html);
        itemCount++;
    });

    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').fadeOut(300, function() {
            $(this).remove();
        });
    });

    // Collect all items on submit
    $('#warehouse-form').on('submit', function(e) {
        e.preventDefault();

        let items = [];
        $('.item-row').each(function() {
            items.push({
                name: $(this).find('.item-name').val()
            });
        });

        let formData = {
            name: $('#name').val(),
            items: items
        };

        // Submit...
    });
});
```

---

## 5. Select2 Integration (Advanced Selection)

### HTML

```html
<select id="warehouse-select" class="form-select" multiple>
    <option value="">Select warehouses...</option>
</select>
```

### jQuery with Select2

```javascript
jQuery(function($) {
    $('#warehouse-select').select2({
        ajax: {
            url: '/api/warehouses/search',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(item) {
                        return {
                            id: item.id,
                            text: item.name
                        };
                    })
                };
            }
        },
        minimumInputLength: 1
    });
});
```

---

## 6. File Upload Form

### HTML with Progress

```html
<form id="upload-form" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="file" class="form-label">Select File</label>
        <input type="file" class="form-control" id="file" name="file">
    </div>
    <div id="progress-bar" style="display:none;">
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Upload</button>
</form>
```

### jQuery Upload Handler

```javascript
jQuery(function($) {
    $('#upload-form').on('submit', function(e) {
        e.preventDefault();

        let fileInput = $('#file')[0];
        let file = fileInput.files[0];

        if (!file) {
            toastr.error('Please select a file');
            return;
        }

        // Validate file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            toastr.error('File too large (max 10MB)');
            return;
        }

        let formData = new FormData();
        formData.append('file', file);
        formData.append('type', $('#file-type').val());

        $('#progress-bar').show();

        $.ajax({
            url: '/api/upload',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            xhr: function() {
                let xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        let percentComplete = (e.loaded / e.total) * 100;
                        $('.progress-bar').css('width', percentComplete + '%');
                    }
                });
                return xhr;
            },
            success: function(response) {
                toastr.success('File uploaded');
                $('#upload-form')[0].reset();
                $('#progress-bar').hide();
            },
            error: function(xhr) {
                toastr.error('Upload failed');
                $('#progress-bar').hide();
            }
        });
    });
});
```

---

## 7. Edit Form (Prefilled Data)

### Modal with AJAX Data

```javascript
jQuery(function($) {
    // Open edit modal
    $(document).on('click', '.edit-btn', function() {
        let id = $(this).data('id');

        $.get(`/api/warehouses/${id}`, function(warehouse) {
            // Populate form
            $('#warehouse-id').val(warehouse.id);
            $('#name').val(warehouse.name);
            $('#location').val(warehouse.location);
            $('#capacity').val(warehouse.capacity);

            // Change form action
            $('#warehouse-form').attr('data-id', id);
            $('#warehouse-form').attr('data-action', 'update');

            // Change button text
            $('button[type="submit"]').text('Update');

            // Show modal
            $('#warehouse-modal').modal('show');
        });
    });

    // Form submission (create or update)
    $('#warehouse-form').on('submit', function(e) {
        e.preventDefault();

        let id = $(this).data('id');
        let action = $(this).data('action') || 'create';

        let formData = {
            name: $('#name').val(),
            location: $('#location').val(),
            capacity: $('#capacity').val()
        };

        let url = action === 'create' ? '/api/warehouses' : `/api/warehouses/${id}`;
        let method = action === 'create' ? 'POST' : 'PUT';

        $.ajax({
            url: url,
            type: method,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                toastr.success(action === 'create' ? 'Created' : 'Updated');
                $('#warehouse-modal').modal('hide');
                refreshTable();
            }
        });
    });

    // Reset form on modal close
    $('#warehouse-modal').on('hidden.bs.modal', function() {
        $('#warehouse-form')[0].reset();
        $('#warehouse-form').removeAttr('data-id data-action');
        $('button[type="submit"]').text('Save');
    });
});
```

---

## 8. Cascading Selects

### Country → City Selection

```html
<div class="mb-3">
    <label for="country" class="form-label">Country</label>
    <select id="country" class="form-select"></select>
</div>
<div class="mb-3">
    <label for="city" class="form-label">City</label>
    <select id="city" class="form-select" disabled></select>
</div>
```

### jQuery Implementation

```javascript
jQuery(function($) {
    // Load countries on page load
    $.get('/api/countries', function(countries) {
        let html = '<option value="">Select country...</option>';
        countries.forEach(country => {
            html += `<option value="${country.id}">${country.name}</option>`;
        });
        $('#country').html(html);
    });

    // Load cities when country selected
    $('#country').on('change', function() {
        let countryId = $(this).val();

        if (!countryId) {
            $('#city').prop('disabled', true).html('<option value="">Select city...</option>');
            return;
        }

        $.get(`/api/countries/${countryId}/cities`, function(cities) {
            let html = '<option value="">Select city...</option>';
            cities.forEach(city => {
                html += `<option value="${city.id}">${city.name}</option>`;
            });
            $('#city').prop('disabled', false).html(html);
        });
    });
});
```

---

## 9. Form Error Display from Server

### Server Response with Errors

```json
{
    "message": "Validation failed",
    "errors": {
        "name": ["Name is required"],
        "email": ["Email must be unique", "Email format invalid"]
    }
}
```

### jQuery Error Handler

```javascript
jQuery(function($) {
    $('#warehouse-form').on('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        clearFormErrors();

        $.ajax({
            url: '/api/warehouses',
            type: 'POST',
            data: JSON.stringify(getFormData()),
            contentType: 'application/json',
            success: function(response) {
                toastr.success('Saved successfully');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    displayServerErrors(xhr.responseJSON.errors);
                } else {
                    toastr.error('An error occurred');
                }
            }
        });
    });

    function clearFormErrors() {
        $('input, textarea, select').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }

    function displayServerErrors(errors) {
        $.each(errors, function(field, messages) {
            let $field = $(`#${field}`);
            if ($field.length) {
                $field.addClass('is-invalid');

                let feedback = `<div class="invalid-feedback" style="display:block;">
                    ${messages[0]}
                </div>`;
                $field.after(feedback);

                toastr.error(messages[0]);
            }
        });
    }

    function getFormData() {
        return {
            name: $('#name').val(),
            location: $('#location').val(),
            capacity: $('#capacity').val()
        };
    }
});
```

---

## 10. Form Loading State

### Disable Submit During Request

```javascript
jQuery(function($) {
    $('#warehouse-form').on('submit', function(e) {
        e.preventDefault();

        let $submit = $('button[type="submit"]', this);
        let originalText = $submit.text();

        // Disable button
        $submit.prop('disabled', true)
               .html('<i class="spinner-border spinner-border-sm me-2"></i>Saving...');

        $.ajax({
            url: '/api/warehouses',
            type: 'POST',
            data: JSON.stringify(getFormData()),
            contentType: 'application/json',
            complete: function() {
                // Re-enable button
                $submit.prop('disabled', false).text(originalText);
            },
            success: function(response) {
                toastr.success('Saved successfully');
            },
            error: function() {
                toastr.error('Save failed');
            }
        });
    });
});
```

---

## 11. Auto-save Form

### Save on Field Change

```javascript
jQuery(function($) {
    let saveTimeout;
    let isDirty = false;

    $('#name, #location, #capacity').on('change keyup', function() {
        isDirty = true;

        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            autoSave();
        }, 1000); // Save 1 second after user stops typing
    });

    function autoSave() {
        if (!isDirty) return;

        $.ajax({
            url: '/api/warehouses/' + warehouseId,
            type: 'PUT',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify({
                name: $('#name').val(),
                location: $('#location').val(),
                capacity: $('#capacity').val()
            }),
            contentType: 'application/json',
            success: function() {
                isDirty = false;
                toastr.success('Changes saved');
            }
        });
    }
});
```

---

## Best Practices

1. **Always use CSRF tokens** - Include X-CSRF-TOKEN header
2. **Validate on client AND server** - Never trust client-side validation alone
3. **Show loading state** - Disable submit button during request
4. **Clear errors before resubmit** - Don't show old validation errors
5. **Use preventDefault()** - Stop form from submitting normally
6. **Reset form after success** - Clear fields after successful submission
7. **Provide user feedback** - Always show success/error messages
8. **Handle file size limits** - Validate before upload
9. **Use JSON payloads** - More flexible than form-encoded
10. **Debounce real-time validation** - Don't check on every keystroke

