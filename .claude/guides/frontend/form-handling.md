# Form Handling & Validation

**jQuery Validate integration for Alsernet forms.**

---

## Basic Validation

```html
<form id="form">
    <input type="text" name="name" required>
    <input type="email" name="email" required>
    <button type="submit">Save</button>
</form>
```

```javascript
$('#form').validate({
    rules: {
        name: { required: true, minlength: 3 },
        email: { required: true, email: true }
    },
    messages: {
        name: 'Name is required (min 3 chars)',
        email: 'Please enter a valid email'
    }
});
```

---

## Bootstrap Styling

```javascript
$('#form').validate({
    rules: {
        email: { required: true, email: true }
    },
    errorClass: 'is-invalid',
    validClass: 'is-valid',
    errorPlacement: (error, element) => {
        error.addClass('invalid-feedback d-block');
        element.after(error);
    },
    highlight: (element) => {
        $(element).removeClass('is-valid').addClass('is-invalid');
    },
    unhighlight: (element) => {
        $(element).removeClass('is-invalid').addClass('is-valid');
    }
});
```

---

## Custom Rules

```javascript
$.validator.addMethod('phone', function(value, element) {
    return this.optional(element) || /^[\d\s\-\+\(\)]{10,}$/.test(value);
}, 'Please enter a valid phone number');

$('#form').validate({
    rules: { phone: { required: true, phone: true } }
});
```

---

## Remote Validation

```javascript
$('#form').validate({
    rules: {
        email: {
            required: true,
            email: true,
            remote: {
                url: '/api/validate-email',
                type: 'POST'
            }
        }
    },
    messages: {
        email: {
            remote: 'This email is already registered'
        }
    }
});
```

---

## Conditional Validation

```javascript
$('#form').validate({
    rules: {
        userType: { required: true },
        companyName: {
            required: function() {
                return $('#userType').val() === 'business';
            }
        }
    }
});

$('#userType').on('change', function() {
    let type = $(this).val();
    if (type === 'business') {
        $('#businessFields').show();
    } else {
        $('#businessFields').hide();
    }
});
```

---

## Dynamic Fields

```javascript
$('#add-field').on('click', function() {
    let html = '<input type="text" name="items[]" class="form-control">';
    $('#fields').append(html);
});

$(document).on('click', '.remove-field', function() {
    $(this).closest('.field').remove();
});
```

---

## Form in Modal

```javascript
$(document).on('click', '.edit-btn', function() {
    let id = $(this).data('id');
    $.get(`/api/items/${id}`, (data) => {
        $('#form').find('[name="name"]').val(data.name);
        $('#modal').modal('show');
    });
});

$('#form').on('submit', function(e) {
    e.preventDefault();
    if ($(this).valid()) {
        $.ajax({
            url: '/api/items',
            type: 'POST',
            data: JSON.stringify(getFormData('#form')),
            headers: { 'X-CSRF-TOKEN': $('[name="csrf-token"]').attr('content') },
            success: () => {
                toastr.success('Saved');
                $('#modal').modal('hide');
            }
        });
    }
});
```

---

## Best Practices

1. Always validate client-side for UX
2. Always validate server-side for security
3. Show clear error messages
4. Use Bootstrap error classes
5. Handle loading states
6. Prevent double submission
7. Make forms accessible

---

**Version:** 1.0
