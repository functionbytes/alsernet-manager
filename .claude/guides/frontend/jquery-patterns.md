# jQuery Patterns Guide

**Production-ready jQuery patterns for Alsernet.**

---

## PATTERN 1: Component Class

```javascript
class DataTableComponent {
    constructor(selector, options = {}) {
        this.$element = $(selector);
        this.options = { apiUrl: '/api/items', pageSize: 15, ...options };
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadData();
    }

    bindEvents() {
        this.$element.on('click', '.edit-btn', (e) => this.edit($(e.target)));
    }

    loadData() {
        $.get(this.options.apiUrl, (data) => this.render(data));
    }

    render(data) {
        let html = data.map(item => `<tr data-id="${item.id}"><td>${item.name}</td></tr>`).join('');
        this.$element.find('tbody').html(html);
    }
}
```

---

## PATTERN 2: Form Validation + AJAX

```javascript
$('#form').validate({
    rules: { email: { required: true, email: true } },
    submitHandler: function(form) {
        $.ajax({
            url: '/api/save',
            type: 'POST',
            data: JSON.stringify($(form).serializeArray()),
            success: () => toastr.success('Saved'),
            error: () => toastr.error('Error')
        });
        return false;
    }
});
```

---

## PATTERN 3: Modal with Form

```javascript
$(document).on('click', '.edit-btn', function() {
    let id = $(this).data('id');
    $.get(`/api/items/${id}`, (data) => {
        $('#form').find('[name="name"]').val(data.name);
        $('#modal').modal('show');
    });
});
```

---

## PATTERN 4: Dynamic Fields

```javascript
$('#add-field').on('click', function() {
    let html = '<input type="text" name="items[]" class="form-control"><button class="btn btn-danger remove-field">Remove</button>';
    $('#fields').append(html);
});

$(document).on('click', '.remove-field', function() {
    $(this).closest('.field').remove();
});
```

---

## PATTERN 5: DataTable AJAX

```javascript
let table = $('#table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '/api/list',
    columns: [
        { data: 'id' },
        { data: 'name' },
        { data: null, defaultContent: '<button class="btn btn-sm btn-primary">Edit</button>' }
    ],
    drawCallback: () => bindRowEvents()
});

function bindRowEvents() {
    $('#table').find('.btn').on('click', function() {
        let id = $(this).closest('tr').find('td:first').text();
        console.log('Edit:', id);
    });
}
```

---

## PATTERN 6: Real-time Updates

```javascript
window.Echo.channel('items')
    .listen('ItemCreated', (e) => {
        toastr.info('New item');
        table.ajax.reload();
    });
```

---

## PATTERN 7: File Upload

```javascript
$('#drop-zone').on('drop', (e) => {
    e.preventDefault();
    handleFiles(e.originalEvent.dataTransfer.files);
});

function handleFiles(files) {
    $.each(files, (i, file) => {
        let formData = new FormData();
        formData.append('file', file);
        $.ajax({
            url: '/api/upload',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: () => toastr.success('Uploaded')
        });
    });
}
```

---

## PATTERN 8: Caching

```javascript
class CacheManager {
    set(key, value, ttl = 3600) {
        localStorage.setItem(key, JSON.stringify({ value, expires: Date.now() + ttl * 1000 }));
    }

    get(key) {
        let item = JSON.parse(localStorage.getItem(key));
        if (item && Date.now() <= item.expires) return item.value;
        localStorage.removeItem(key);
        return null;
    }
}
```

---

**Full guide in:** `guides/frontend/`

**Version:** 1.0
