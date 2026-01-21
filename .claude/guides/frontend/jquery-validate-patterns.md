# jQuery Validate - Complete Implementation Guide

**Production-ready jQuery Validate patterns for Alsernet forms.**

---

## Quick Start

### 1. Include Libraries
```html
<!-- jQuery -->
<script src="/managers/libs/jquery/jquery-3.6.0.min.js"></script>

<!-- jQuery Validate -->
<script src="/managers/libs/jquery-validation/jquery.validate.min.js"></script>

<!-- Spanish messages -->
<script src="/managers/libs/jquery-validation/localization/messages_es.js"></script>
```

### 2. Basic Form HTML
```html
<form id="warehouse-form">
    <div class="mb-3">
        <label for="name" class="form-label">Warehouse Name</label>
        <input type="text" class="form-control" id="name" name="name">
        <div class="invalid-feedback"></div>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
```

### 3. Initialize Validation
```javascript
jQuery(function($) {
    $('#warehouse-form').validate({
        rules: {
            name: 'required'
        }
    });
});
```

---

## Pattern 1: Simple Validation

### HTML
```html
<form id="contact-form">
    <div class="mb-3">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email">
    </div>
    <div class="mb-3">
        <label for="phone">Phone</label>
        <input type="text" class="form-control" id="phone" name="phone">
    </div>
    <button type="submit" class="btn btn-primary">Send</button>
</form>
```

### JavaScript
```javascript
jQuery(function($) {
    $('#contact-form').validate({
        rules: {
            email: {
                required: true,
                email: true
            },
            phone: {
                required: true,
                minlength: 10
            }
        }
    });
});
```

---

## Pattern 2: Bootstrap Integration

### Bootstrap 5 Error Classes
```javascript
jQuery(function($) {
    $('#warehouse-form').validate({
        rules: {
            name: 'required',
            capacity: 'required'
        },
        errorClass: 'is-invalid',      // Bootstrap error class
        validClass: 'is-valid',         // Bootstrap valid class
        errorPlacement: function(error, element) {
            // Create error container
            error.addClass('invalid-feedback d-block');
            element.after(error);
        },
        highlight: function(element, errorClass) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function(element, errorClass) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        }
    });
});
```

### HTML with Bootstrap
```html
<form id="warehouse-form">
    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name">
        <div class="invalid-feedback"></div>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
```

---

## Pattern 3: Comprehensive Validation Rules

```javascript
jQuery(function($) {
    $('#warehouse-form').validate({
        rules: {
            // Text field
            name: {
                required: true,
                minlength: 3,
                maxlength: 100
            },

            // Email
            email: {
                required: true,
                email: true
            },

            // Number
            capacity: {
                required: true,
                number: true,
                min: 1,
                max: 100000
            },

            // Phone (with custom pattern)
            phone: {
                required: true,
                pattern: /^[0-9\-\+\(\) ]+$/
            },

            // URL
            website: {
                url: true
            },

            // Date
            established: {
                date: true
            },

            // Postal code (numbers and hyphens)
            zipcode: {
                required: true,
                pattern: /^[0-9\-]+$/
            },

            // Match other field
            password: {
                required: true,
                minlength: 8
            },
            confirmPassword: {
                required: true,
                equalTo: '#password'
            },

            // Digits only
            code: {
                required: true,
                digits: true,
                minlength: 5
            },

            // Custom validation
            customField: {
                required: true,
                pattern: /^[A-Z]{3}[0-9]{3}$/
            }
        },

        messages: {
            name: {
                required: 'Warehouse name is required',
                minlength: 'Name must be at least 3 characters',
                maxlength: 'Name cannot exceed 100 characters'
            },
            email: {
                required: 'Email is required',
                email: 'Please enter a valid email'
            },
            capacity: {
                required: 'Capacity is required',
                number: 'Capacity must be a number',
                min: 'Capacity must be at least 1',
                max: 'Capacity cannot exceed 100,000'
            },
            phone: {
                required: 'Phone is required',
                pattern: 'Phone format is invalid'
            },
            website: {
                url: 'Please enter a valid URL'
            },
            established: {
                date: 'Please enter a valid date'
            },
            confirmPassword: {
                equalTo: 'Passwords do not match'
            }
        }
    });
});
```

---

## Pattern 4: AJAX Form Submission

```javascript
jQuery(function($) {
    $('#warehouse-form').validate({
        rules: {
            name: 'required',
            location: 'required'
        },
        submitHandler: function(form) {
            // Form is valid, submit via AJAX
            let $form = $(form);
            let $submitBtn = $form.find('button[type="submit"]');
            let originalBtnText = $submitBtn.text();

            // Disable button and show loading
            $submitBtn.prop('disabled', true)
                     .html('<i class="spinner-border spinner-border-sm me-2"></i>Saving...');

            $.ajax({
                url: '/api/warehouses',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: $(form).serialize(),
                dataType: 'json',
                success: function(response) {
                    toastr.success('Warehouse saved successfully');
                    $form[0].reset();

                    // Reset validation styles
                    $form.find('.form-control').removeClass('is-valid is-invalid');

                    // Close modal if in modal
                    $('#warehouse-modal').modal('hide');

                    // Refresh table
                    let table = $('#warehouses-table').DataTable();
                    table.ajax.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Validation errors from server
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            let $field = $form.find('[name="' + field + '"]');
                            $field.addClass('is-invalid');
                            $field.after('<div class="invalid-feedback d-block">' + messages[0] + '</div>');
                            toastr.error(messages[0]);
                        });
                    } else {
                        toastr.error('Error saving warehouse');
                    }
                },
                complete: function() {
                    // Re-enable button
                    $submitBtn.prop('disabled', false).text(originalBtnText);
                }
            });

            return false; // Prevent default form submission
        }
    });
});
```

---

## Pattern 5: Server-side Validation

```javascript
jQuery(function($) {
    $('#warehouse-form').validate({
        rules: {
            name: {
                required: true,
                // Server-side check for unique name
                remote: {
                    url: '/api/warehouses/validate-name',
                    type: 'post',
                    data: {
                        name: function() {
                            return $('#name').val();
                        },
                        warehouse_id: function() {
                            return $('#warehouse-id').val() || '';
                        }
                    }
                }
            },
            email: {
                required: true,
                email: true,
                // Server-side check for unique email
                remote: {
                    url: '/api/validate-email',
                    type: 'post'
                }
            }
        },
        messages: {
            name: {
                remote: 'This warehouse name already exists'
            },
            email: {
                remote: 'This email is already registered'
            }
        }
    });
});
```

### Backend Validation Endpoint
```php
// app/Http/Controllers/WarehouseController.php
public function validateName(Request $request)
{
    $name = $request->input('name');
    $warehouseId = $request->input('warehouse_id');

    $exists = Warehouse::where('name', $name)
        ->where('id', '!=', $warehouseId)
        ->exists();

    return response()->json(!$exists); // true = valid, false = invalid
}
```

---

## Pattern 6: Dynamic Form Validation

### Add Fields Dynamically
```html
<form id="order-form">
    <div id="items-container">
        <div class="item-row mb-3">
            <input type="text" name="items[0][name]" class="form-control" placeholder="Item name">
            <input type="number" name="items[0][quantity]" class="form-control" placeholder="Quantity">
            <button type="button" class="btn btn-danger remove-item">Remove</button>
        </div>
    </div>
    <button type="button" id="add-item" class="btn btn-secondary mb-3">Add Item</button>
    <button type="submit" class="btn btn-primary">Save Order</button>
</form>
```

### jQuery with Dynamic Validation
```javascript
jQuery(function($) {
    let itemIndex = 1;

    // Initial validation
    $('#order-form').validate({
        rules: {
            'items[0][name]': 'required',
            'items[0][quantity]': {
                required: true,
                number: true,
                min: 1
            }
        }
    });

    // Add new item
    $('#add-item').on('click', function() {
        let itemHtml = `
            <div class="item-row mb-3">
                <input type="text" name="items[${itemIndex}][name]" class="form-control" placeholder="Item name">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Quantity">
                <button type="button" class="btn btn-danger remove-item">Remove</button>
            </div>
        `;
        $('#items-container').append(itemHtml);

        // Add validation for new fields
        let validator = $('#order-form').validate();
        validator.resetForm();

        // Add rules for new item
        $(`input[name="items[${itemIndex}][name]"]`).rules('add', {
            required: true
        });
        $(`input[name="items[${itemIndex}][quantity]"]`).rules('add', {
            required: true,
            number: true,
            min: 1
        });

        itemIndex++;
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').remove();
        let validator = $('#order-form').validate();
        validator.resetForm();
    });
});
```

---

## Pattern 7: Spanish Localization

```javascript
jQuery(function($) {
    // Spanish messages already loaded from messages_es.js
    // But you can customize:

    $.extend($.validator.messages, {
        required: "Este campo es obligatorio",
        remote: "Por favor, corrija este campo",
        email: "Por favor, introduce un correo electrónico válido",
        url: "Por favor, introduce una URL válida",
        date: "Por favor, introduce una fecha válida",
        dateISO: "Por favor, introduce una fecha válida (ISO)",
        number: "Por favor, introduce un número válido",
        digits: "Por favor, introduce solo dígitos",
        creditcard: "Por favor, introduce un número de tarjeta válido",
        equalTo: "Por favor, introduce el mismo valor de nuevo",
        maxlength: $.validator.format("Por favor, no escribas más de {0} caracteres"),
        minlength: $.validator.format("Por favor, escribe al menos {0} caracteres"),
        rangelength: $.validator.format("Por favor, escribe un valor entre {0} y {1} caracteres"),
        range: $.validator.format("Por favor, escribe un valor entre {0} y {1}"),
        max: $.validator.format("Por favor, escribe un valor menor o igual a {0}"),
        min: $.validator.format("Por favor, escribe un valor mayor o igual a {0}")
    });

    $('#warehouse-form').validate({
        rules: {
            name: 'required',
            capacity: 'required'
        }
    });
});
```

---

## Pattern 8: Conditional Validation

```javascript
jQuery(function($) {
    $('#form').validate({
        rules: {
            type: 'required',
            company: {
                required: '#type:checked'  // Required only if type is checked
            },
            phone: {
                required: function(element) {
                    // Required only if customer is business
                    return $('#type').val() === 'business';
                }
            }
        }
    });
});
```

---

## Pattern 9: Form in Modal

```html
<!-- Modal -->
<div class="modal fade" id="warehouse-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Warehouse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="warehouse-form">
                    <div class="mb-3">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="warehouse-form" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
```

### JavaScript
```javascript
jQuery(function($) {
    // Initialize validation
    $('#warehouse-form').validate({
        rules: {
            name: 'required'
        },
        submitHandler: function(form) {
            $.ajax({
                url: '/api/warehouses',
                type: 'POST',
                data: $(form).serialize(),
                success: function() {
                    toastr.success('Saved');
                    $('#warehouse-modal').modal('hide');
                }
            });
            return false;
        }
    });

    // Clear form and validation when modal closes
    $('#warehouse-modal').on('hidden.bs.modal', function() {
        $('#warehouse-form')[0].reset();
        let validator = $('#warehouse-form').validate();
        validator.resetForm();
        $('#warehouse-form').find('.form-control').removeClass('is-invalid is-valid');
    });

    // Open modal
    $('#create-warehouse').on('click', function() {
        $('#warehouse-form')[0].reset();
        $('#warehouse-form').validate().resetForm();
        $('#warehouse-modal').modal('show');
    });
});
```

---

## Pattern 10: Multi-step Form Validation

```html
<form id="multi-step-form">
    <!-- Step 1 -->
    <fieldset id="step1">
        <h4>Personal Info</h4>
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control">
        </div>
    </fieldset>

    <!-- Step 2 -->
    <fieldset id="step2" style="display:none;">
        <h4>Address Info</h4>
        <div class="mb-3">
            <label>Address</label>
            <input type="text" name="address" class="form-control">
        </div>
    </fieldset>

    <button type="button" id="prev" class="btn btn-secondary" style="display:none;">Previous</button>
    <button type="button" id="next" class="btn btn-primary">Next</button>
</form>
```

### JavaScript
```javascript
jQuery(function($) {
    let currentStep = 1;
    let steps = {
        1: { fields: ['name'], legend: '#step1' },
        2: { fields: ['address'], legend: '#step2' }
    };

    let validator = $('#multi-step-form').validate({
        rules: {
            name: 'required',
            address: 'required'
        },
        submitHandler: function(form) {
            $.ajax({
                url: '/api/submit',
                type: 'POST',
                data: $(form).serialize(),
                success: function() {
                    toastr.success('Submitted');
                }
            });
            return false;
        }
    });

    $('#next').on('click', function() {
        // Validate current step
        let stepFields = steps[currentStep].fields;
        let isValid = true;

        $.each(stepFields, function(i, field) {
            if (!validator.element('[name="' + field + '"]')) {
                isValid = false;
            }
        });

        if (isValid && currentStep < 2) {
            $(steps[currentStep].legend).hide();
            currentStep++;
            $(steps[currentStep].legend).show();
            $('#prev').show();
            if (currentStep === 2) {
                $('#next').text('Submit');
            }
        }
    });

    $('#prev').on('click', function() {
        $(steps[currentStep].legend).hide();
        currentStep--;
        $(steps[currentStep].legend).show();
        $('#next').text('Next');
        if (currentStep === 1) {
            $('#prev').hide();
        }
    });
});
```

---

## Best Practices

### ✅ DO
- Always validate on client AND server
- Show clear error messages
- Use Bootstrap error classes
- Reset form after successful submission
- Disable submit button during AJAX
- Use CSRF tokens with POST
- Validate on field blur (not just submit)

### ❌ DON'T
- Rely only on client validation
- Use generic error messages
- Leave validation fields unfocused
- Double-submit forms
- Forget server-side validation
- Validate inside event handlers repeatedly
- Trust user input

---

## Complete Example: Warehouse Form

```html
<!-- HTML -->
<form id="warehouse-form">
    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name">
    </div>
    <div class="mb-3">
        <label for="location" class="form-label">Location</label>
        <input type="text" class="form-control" id="location" name="location">
    </div>
    <div class="mb-3">
        <label for="capacity" class="form-label">Capacity</label>
        <input type="number" class="form-control" id="capacity" name="capacity">
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email">
    </div>
    <button type="submit" class="btn btn-primary">Save Warehouse</button>
</form>
```

```javascript
// JavaScript
jQuery(function($) {
    $('#warehouse-form').validate({
        rules: {
            name: {
                required: true,
                minlength: 3,
                maxlength: 100,
                remote: {
                    url: '/api/warehouses/validate-name',
                    type: 'post'
                }
            },
            location: {
                required: true,
                minlength: 3
            },
            capacity: {
                required: true,
                number: true,
                min: 1,
                max: 100000
            },
            email: {
                required: true,
                email: true
            }
        },
        messages: {
            name: {
                required: 'Warehouse name is required',
                minlength: 'Name must be at least 3 characters',
                remote: 'This warehouse name already exists'
            },
            location: 'Location is required',
            capacity: 'Capacity must be a number between 1 and 100,000',
            email: 'Please enter a valid email'
        },
        errorClass: 'is-invalid',
        validClass: 'is-valid',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback d-block');
            element.after(error);
        },
        submitHandler: function(form) {
            let $btn = $(form).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Saving...');

            $.ajax({
                url: '/api/warehouses',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: $(form).serialize(),
                success: function() {
                    toastr.success('Warehouse saved successfully');
                    form.reset();
                    $(form).validate().resetForm();
                    $(form).find('.form-control').removeClass('is-valid is-invalid');
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            $('[name="' + field + '"]').addClass('is-invalid').after(
                                '<div class="invalid-feedback d-block">' + messages[0] + '</div>'
                            );
                        });
                    }
                    toastr.error('Error saving warehouse');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Save Warehouse');
                }
            });
            return false;
        }
    });
});
```

