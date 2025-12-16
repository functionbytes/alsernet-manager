# Bootstrap Modal & DataTables Patterns with jQuery

---

## PART 1: BOOTSTRAP MODALS

## 1. Basic Modal Structure

```html
<!-- Modal -->
<div class="modal fade" id="warehouse-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Warehouse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form id="warehouse-form">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name">
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
                <button type="submit" form="warehouse-form" class="btn btn-primary">
                    Save
                </button>
            </div>
        </div>
    </div>
</div>
```

---

## 2. Open Modal with jQuery

### Basic Open

```javascript
jQuery(function($) {
    $('#open-modal-btn').on('click', function() {
        $('#warehouse-modal').modal('show');
    });

    // Close modal programmatically
    $('#close-modal-btn').on('click', function() {
        $('#warehouse-modal').modal('hide');
    });

    // Toggle modal
    $('#toggle-modal-btn').on('click', function() {
        $('#warehouse-modal').modal('toggle');
    });
});
```

### Open Modal with Size Options

```javascript
jQuery(function($) {
    // Small modal
    $('#small-modal-btn').on('click', function() {
        // Use .modal-sm class
        let html = `<div class="modal-dialog modal-sm">...</div>`;
        // Or use data attribute
        $(this).data('bs-toggle', 'modal');
    });

    // Large modal
    $('#large-modal-btn').on('click', function() {
        $('#warehouse-modal').find('.modal-dialog').addClass('modal-lg');
        $('#warehouse-modal').modal('show');
    });

    // Full screen modal
    $('#fullscreen-modal-btn').on('click', function() {
        $('#warehouse-modal').find('.modal-dialog').addClass('modal-fullscreen');
        $('#warehouse-modal').modal('show');
    });
});
```

---

## 3. Modal Events with jQuery

```javascript
jQuery(function($) {
    let $modal = $('#warehouse-modal');

    // When modal is about to show
    $modal.on('show.bs.modal', function(event) {
        console.log('Modal is showing');
        // Can be used to load data, prevent showing, etc.
    });

    // When modal is fully shown
    $modal.on('shown.bs.modal', function(event) {
        console.log('Modal is shown');
        // Focus on first input
        $('#name').focus();
    });

    // When modal is about to hide
    $modal.on('hide.bs.modal', function(event) {
        console.log('Modal is hiding');
        // Check for unsaved changes
        if (isDirty) {
            return false; // Prevent hiding
        }
    });

    // When modal is hidden
    $modal.on('hidden.bs.modal', function(event) {
        console.log('Modal is hidden');
        // Clear form
        $('#warehouse-form')[0].reset();
    });
});
```

---

## 4. Load Data into Modal

### Fetch and Display

```javascript
jQuery(function($) {
    $(document).on('click', '.edit-warehouse', function() {
        let id = $(this).data('id');

        // Fetch data
        $.get(`/api/warehouses/${id}`, function(warehouse) {
            // Populate modal
            $('#warehouse-id').val(warehouse.id);
            $('#name').val(warehouse.name);
            $('#location').val(warehouse.location);
            $('#capacity').val(warehouse.capacity);

            // Store ID for later use
            $('#warehouse-modal').data('warehouse-id', warehouse.id);

            // Show modal
            $('#warehouse-modal').modal('show');
        });
    });
});
```

### With Loading Indicator

```javascript
jQuery(function($) {
    $(document).on('click', '.edit-warehouse', function() {
        let id = $(this).data('id');
        let $modal = $('#warehouse-modal');

        // Show modal with loading state
        $modal.modal('show');
        $modal.find('.modal-body').html(
            '<div class="text-center"><div class="spinner-border"></div></div>'
        );

        // Fetch data
        $.get(`/api/warehouses/${id}`, function(warehouse) {
            // Render form
            let html = `
                <form id="warehouse-form">
                    <input type="hidden" id="warehouse-id" value="${warehouse.id}">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" value="${warehouse.name}">
                    </div>
                </form>
            `;
            $modal.find('.modal-body').html(html);
        });
    });
});
```

---

## 5. Create vs Edit in Same Modal

```javascript
jQuery(function($) {
    // Create new
    $('#create-warehouse-btn').on('click', function() {
        $('#warehouse-modal').data('mode', 'create');
        $('#warehouse-form')[0].reset();
        $('#warehouse-modal').modal('show');
        $('#warehouse-modal').find('.modal-title').text('Create Warehouse');
    });

    // Edit existing
    $(document).on('click', '.edit-warehouse', function() {
        let id = $(this).data('id');

        $.get(`/api/warehouses/${id}`, function(warehouse) {
            $('#warehouse-id').val(warehouse.id);
            $('#name').val(warehouse.name);
            $('#location').val(warehouse.location);

            $('#warehouse-modal').data('mode', 'edit');
            $('#warehouse-modal').data('warehouse-id', warehouse.id);
            $('#warehouse-modal').find('.modal-title').text('Edit Warehouse');
            $('#warehouse-modal').modal('show');
        });
    });

    // Submit form (create or edit)
    $('#warehouse-form').on('submit', function(e) {
        e.preventDefault();

        let mode = $('#warehouse-modal').data('mode');
        let url = mode === 'create' ? '/api/warehouses' : `/api/warehouses/${$('#warehouse-id').val()}`;
        let method = mode === 'create' ? 'POST' : 'PUT';

        $.ajax({
            url: url,
            type: method,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify({
                name: $('#name').val(),
                location: $('#location').val()
            }),
            contentType: 'application/json',
            success: function(response) {
                toastr.success(mode === 'create' ? 'Created' : 'Updated');
                $('#warehouse-modal').modal('hide');
                refreshTable();
            }
        });
    });
});
```

---

## 6. Nested/Multiple Modals

```html
<!-- Primary modal -->
<div class="modal fade" id="primary-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p>Primary Modal</p>
                <button class="btn btn-primary" id="open-secondary">Open Secondary</button>
            </div>
        </div>
    </div>
</div>

<!-- Secondary modal (on top) -->
<div class="modal fade" id="secondary-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p>Secondary Modal (on top)</p>
            </div>
        </div>
    </div>
</div>
```

### jQuery Implementation

```javascript
jQuery(function($) {
    $('#open-secondary').on('click', function() {
        // Close primary first
        $('#primary-modal').modal('hide');

        // Open secondary
        setTimeout(() => {
            $('#secondary-modal').modal('show');
        }, 300);
    });

    $('#secondary-modal').on('hidden.bs.modal', function() {
        // Reopen primary
        $('#primary-modal').modal('show');
    });
});
```

---

## 7. Modal with Confirmation

```html
<div class="modal fade" id="confirm-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirm-message"></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" id="confirm-btn">Delete</button>
            </div>
        </div>
    </div>
</div>
```

### jQuery Confirmation

```javascript
jQuery(function($) {
    $(document).on('click', '.delete-warehouse', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');

        // Set message
        $('#confirm-message').text(`Are you sure you want to delete "${name}"?`);

        // Store ID
        $('#confirm-modal').data('delete-id', id);

        // Show modal
        $('#confirm-modal').modal('show');
    });

    $('#confirm-btn').on('click', function() {
        let id = $('#confirm-modal').data('delete-id');

        $.ajax({
            url: `/api/warehouses/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                toastr.success('Deleted');
                $('#confirm-modal').modal('hide');
                refreshTable();
            }
        });
    });
});
```

---

## PART 2: DATATABLES

## 8. Basic DataTable Setup

```html
<table id="warehouses-table" class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Location</th>
            <th>Capacity</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
```

### jQuery Initialization

```javascript
jQuery(function($) {
    $('#warehouses-table').DataTable({
        processing: true,           // Show "Processing..." message
        serverSide: true,          // Server-side processing
        ajax: '/api/warehouses/list', // Data source
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'location' },
            { data: 'capacity' },
            {
                data: null,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-primary edit-warehouse" data-id="${data.id}">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-warehouse" data-id="${data.id}">
                            Delete
                        </button>
                    `;
                }
            }
        ],
        language: {
            url: '/lang/es/datatables.json'  // Spanish translation
        }
    });
});
```

---

## 9. DataTable with Search, Sort, Pagination

```javascript
jQuery(function($) {
    let table = $('#warehouses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/warehouses/list',
            type: 'POST'
        },
        columns: [
            { data: 'id' },
            { data: 'name', searchable: true, orderable: true },
            { data: 'location', searchable: true },
            { data: 'capacity' },
            { data: null, searchable: false, orderable: false }
        ],
        searching: true,     // Enable search box
        ordering: true,      // Enable column sorting
        paging: true,        // Enable pagination
        pageLength: 10,      // Items per page
        lengthMenu: [5, 10, 25, 50]  // Page length options
    });

    // Custom search
    $('#custom-search').on('keyup', function() {
        table.search($(this).val()).draw();
    });
});
```

---

## 10. Real-time Table Updates

```javascript
jQuery(function($) {
    let table = $('#warehouses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/api/warehouses/list'
    });

    // Listen for updates
    window.Echo.channel('warehouse-updates')
        .listen('warehouse.updated', (event) => {
            console.log('Warehouse updated, reloading table');
            table.ajax.reload(null, false); // Keep current page
        })
        .listen('warehouse.created', (event) => {
            toastr.success('New warehouse added');
            table.ajax.reload();
        })
        .listen('warehouse.deleted', (event) => {
            toastr.info('Warehouse removed');
            table.ajax.reload();
        });

    // Manual refresh button
    $('#refresh-table').on('click', function() {
        table.ajax.reload();
    });
});
```

---

## 11. DataTable with Inline Editing

```javascript
jQuery(function($) {
    let table = $('#warehouses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/api/warehouses/list',
        columns: [
            { data: 'id' },
            {
                data: 'name',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return `<span class="editable" data-id="${row.id}" data-field="name">${data}</span>`;
                    }
                    return data;
                }
            },
            { data: 'location' },
            { data: 'capacity' }
        ]
    });

    // Make fields editable on click
    $(document).on('click', '.editable', function() {
        let current = $(this).text();
        let field = $(this).data('field');
        let id = $(this).data('id');

        let input = `<input type="text" class="form-control" value="${current}" data-id="${id}" data-field="${field}">`;
        $(this).replaceWith(input);

        // Focus and select text
        $('input').focus().select();

        // Save on blur or enter
        $('input').on('blur keypress', function(e) {
            if (e.type === 'keypress' && e.which !== 13) return;

            let newValue = $(this).val();
            let warehouseId = $(this).data('id');
            let fieldName = $(this).data('field');

            $.ajax({
                url: `/api/warehouses/${warehouseId}`,
                type: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify({ [fieldName]: newValue }),
                contentType: 'application/json',
                success: function() {
                    table.ajax.reload();
                    toastr.success('Updated');
                }
            });
        });
    });
});
```

---

## 12. DataTable with Row Selection

```html
<table id="warehouses-table" class="table table-striped">
    <thead>
        <tr>
            <th>
                <input type="checkbox" id="select-all">
            </th>
            <th>Name</th>
            <th>Location</th>
        </tr>
    </thead>
</table>

<div id="bulk-actions">
    <button class="btn btn-danger" id="delete-selected">Delete Selected</button>
</div>
```

### jQuery Implementation

```javascript
jQuery(function($) {
    let table = $('#warehouses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/api/warehouses/list',
        columns: [
            {
                data: null,
                searchable: false,
                orderable: false,
                render: function(data) {
                    return `<input type="checkbox" class="row-checkbox" value="${data.id}">`;
                }
            },
            { data: 'name' },
            { data: 'location' }
        ]
    });

    // Select all checkbox
    $('#select-all').on('change', function() {
        let isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
    });

    // Delete selected
    $('#delete-selected').on('click', function() {
        let ids = [];
        $('.row-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length === 0) {
            toastr.warning('Select items to delete');
            return;
        }

        if (confirm(`Delete ${ids.length} items?`)) {
            $.ajax({
                url: '/api/warehouses/bulk-delete',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify({ ids: ids }),
                contentType: 'application/json',
                success: function() {
                    toastr.success('Deleted');
                    table.ajax.reload();
                }
            });
        }
    });
});
```

---

## 13. DataTable Export/Print

```html
<div class="mb-3">
    <button class="btn btn-secondary" id="export-excel">Export Excel</button>
    <button class="btn btn-secondary" id="print-table">Print</button>
</div>

<table id="warehouses-table" class="table table-striped">
    <!-- ... -->
</table>
```

### jQuery Implementation

```javascript
jQuery(function($) {
    let table = $('#warehouses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/api/warehouses/list',
        dom: 'Bfrtip',  // Add export buttons
        buttons: [
            'excel',
            'pdf',
            'print'
        ]
    });

    // Custom export
    $('#export-excel').on('click', function() {
        $.get('/api/warehouses/export/excel', function() {
            toastr.success('Download started');
        });
    });

    $('#print-table').on('click', function() {
        table.print();
    });
});
```

---

## 14. DataTable Responsive

```javascript
jQuery(function($) {
    $('#warehouses-table').DataTable({
        responsive: true,  // Enable responsive mode
        processing: true,
        serverSide: true,
        ajax: '/api/warehouses/list',
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'location' },
            { data: 'capacity' }
        ]
    });
});
```

---

## 15. DataTable with Filtering

```javascript
jQuery(function($) {
    let table = $('#warehouses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/warehouses/list',
            data: function(d) {
                // Add custom filters
                d.location = $('#filter-location').val();
                d.min_capacity = $('#filter-min-capacity').val();
                return d;
            }
        }
    });

    // Refresh table when filters change
    $('#filter-location, #filter-min-capacity').on('change', function() {
        table.ajax.reload();
    });
});
```

---

## Best Practices

### DataTables
1. **Always use serverSide: true** for large datasets
2. **Set processing: true** to show loading indicator
3. **Reload carefully** - use ajax.reload(null, false) to keep page
4. **Cache table instance** - let table = $('#table').DataTable()
5. **Use responsive: true** for mobile support

### Modals
1. **Always reset form on hide** - Clear previous data
2. **Use data attributes** to pass IDs between modals
3. **Listen to events** - show.bs.modal, hidden.bs.modal
4. **Prevent scrolling** - Bootstrap handles this automatically
5. **Focus management** - Focus first input when modal opens

