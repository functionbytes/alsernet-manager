# Component Building Guide

**Building reusable frontend components.**

---

## DataTable Component

```javascript
class DataTableComponent {
    constructor(selector, options = {}) {
        this.$table = $(selector);
        this.options = { apiUrl: '/api/items', apiListUrl: '/api/items/list', ...options };
        this.table = null;
        this.init();
    }

    init() {
        this.initDataTable();
        this.bindGlobalEvents();
    }

    initDataTable() {
        this.table = this.$table.DataTable({
            processing: true,
            serverSide: true,
            ajax: this.options.apiListUrl,
            pageLength: this.options.pageSize || 15,
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'status' },
                { data: null, orderable: false, defaultContent: '<button class="btn btn-sm btn-primary edit-btn">Edit</button>' }
            ],
            drawCallback: () => this.bindRowEvents()
        });
    }

    bindGlobalEvents() {
        $(document).on('click', '#create-btn', () => this.showCreateModal());
    }

    bindRowEvents() {
        this.$table.find('.edit-btn').off('click').on('click', (e) => {
            let id = $(e.target).closest('tr').find('td:first').text();
            this.editRow(id);
        });
    }

    editRow(id) {
        $.get(`${this.options.apiUrl}/${id}`, (data) => {
            if (window.formComponent) window.formComponent.loadData(id);
        });
    }

    showCreateModal() {
        if (window.formComponent) window.formComponent.reset();
        $('#modal').modal('show');
    }

    reload() {
        this.table.ajax.reload(null, false);
    }
}
```

---

## Form Component

```javascript
class FormComponent {
    constructor(formSelector, options = {}) {
        this.$form = $(formSelector);
        this.options = { submitUrl: '/api/items', onSuccess: null, ...options };
        this.isEditMode = false;
        this.editingId = null;
        this.init();
    }

    init() {
        this.initValidation();
        this.bindEvents();
    }

    initValidation() {
        this.$form.validate({
            rules: {
                name: { required: true, minlength: 3 },
                email: { required: true, email: true }
            },
            errorClass: 'is-invalid',
            validClass: 'is-valid',
            submitHandler: (form) => this.submit(form)
        });
    }

    submit(form) {
        let url = this.isEditMode ? `${this.options.submitUrl}/${this.editingId}` : this.options.submitUrl;
        let type = this.isEditMode ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: type,
            data: JSON.stringify(this.getFormData()),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            },
            success: (response) => {
                toastr.success('Saved');
                this.reset();
                this.$form.closest('.modal').modal('hide');
                if (this.options.onSuccess) this.options.onSuccess(response);
            },
            error: () => toastr.error('Error saving')
        });
        return false;
    }

    getFormData() {
        let data = {};
        this.$form.serializeArray().forEach(item => { data[item.name] = item.value; });
        return data;
    }

    loadData(id) {
        $.get(`${this.options.submitUrl}/${id}`, (data) => {
            this.populateForm(data);
            this.setEditMode(id);
        });
    }

    populateForm(data) {
        $.each(data, (field, value) => {
            this.$form.find(`[name="${field}"]`).val(value);
        });
    }

    setEditMode(id) {
        this.isEditMode = true;
        this.editingId = id;
    }

    reset() {
        this.$form[0].reset();
        this.isEditMode = false;
        this.editingId = null;
    }

    bindEvents() {
        this.$form.closest('.modal').on('hidden.bs.modal', () => {
            this.reset();
        });
    }
}
```

---

## Modal Component

```javascript
class ModalComponent {
    constructor(modalSelector, options = {}) {
        this.$modal = $(modalSelector);
        this.options = { formComponent: null, ...options };
        this.init();
    }

    init() {
        this.$modal.on('hidden.bs.modal', () => {
            if (this.options.formComponent) this.options.formComponent.reset();
        });
    }

    show() {
        this.$modal.modal('show');
    }

    hide() {
        this.$modal.modal('hide');
    }
}
```

---

## Component Checklist

- [ ] HTML structure
- [ ] jQuery class
- [ ] Event binding
- [ ] AJAX calls
- [ ] Validation
- [ ] Error handling
- [ ] Loading states
- [ ] Success messages

---

**Version:** 1.0
