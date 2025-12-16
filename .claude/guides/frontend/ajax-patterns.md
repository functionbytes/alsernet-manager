# jQuery AJAX Patterns for Alsernet

## Quick Reference

### 1. Simple GET Request

```javascript
$.get('/api/warehouses', function(data) {
    console.log(data);
});
```

### 2. GET with Error Handling

```javascript
$.get('/api/warehouses')
    .done(function(data) {
        console.log('Success:', data);
    })
    .fail(function(xhr, status, error) {
        console.error('Error:', error);
    });
```

### 3. POST with CSRF Token

```javascript
$.post('/api/warehouses', {
    name: 'New Warehouse',
    location: 'Madrid'
}, function(response) {
    console.log('Created:', response);
});
```

With explicit headers:

```javascript
$.ajax({
    url: '/api/warehouses',
    type: 'POST',
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'
    },
    data: JSON.stringify({
        name: 'New Warehouse',
        location: 'Madrid'
    }),
    contentType: 'application/json',
    success: function(response) {
        console.log('Success:', response);
    },
    error: function(xhr) {
        console.log('Error:', xhr.responseJSON);
    }
});
```

### 4. File Upload with Progress

```javascript
jQuery(function($) {
    $('#file-input').on('change', function() {
        let file = this.files[0];
        let formData = new FormData();
        formData.append('file', file);

        $.ajax({
            url: '/api/warehouse/import',
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
                        let percent = (e.loaded / e.total) * 100;
                        $('#progress-bar').css('width', percent + '%');
                    }
                });
                return xhr;
            },
            success: function(response) {
                toastr.success('File uploaded successfully');
            },
            error: function(xhr) {
                toastr.error('Upload failed');
            }
        });
    });
});
```

### 5. Handling Validation Errors

```javascript
$.ajax({
    url: '/api/warehouses',
    type: 'POST',
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: JSON.stringify(formData),
    contentType: 'application/json',
    success: function(response) {
        toastr.success('Saved successfully');
    },
    error: function(xhr) {
        if (xhr.status === 422) {
            // Validation errors
            let errors = xhr.responseJSON.errors;
            $.each(errors, function(field, messages) {
                // Show field error
                $(`#${field}`).addClass('is-invalid');
                $(`#${field}-error`).text(messages[0]);
                toastr.error(messages[0]);
            });
        } else if (xhr.status === 401) {
            toastr.error('Unauthorized');
        } else {
            toastr.error('An error occurred');
        }
    }
});
```

### 6. Chain Multiple AJAX Calls

```javascript
// Sequential calls
$.get('/api/users/1', function(user) {
    $.get('/api/warehouses/' + user.default_warehouse_id, function(warehouse) {
        console.log('User:', user);
        console.log('Warehouse:', warehouse);
    });
});

// Better: Using promises
$.get('/api/users/1')
    .then(function(user) {
        return $.get('/api/warehouses/' + user.default_warehouse_id);
    })
    .then(function(warehouse) {
        console.log('Warehouse:', warehouse);
    })
    .fail(function(error) {
        toastr.error('Error loading data');
    });
```

### 7. Timeout and Retry

```javascript
let retryCount = 0;
let maxRetries = 3;

function makeRequest() {
    $.ajax({
        url: '/api/data',
        type: 'GET',
        timeout: 5000, // 5 seconds
        success: function(data) {
            console.log('Success:', data);
        },
        error: function(xhr, status, error) {
            if (status === 'timeout' && retryCount < maxRetries) {
                retryCount++;
                console.log('Timeout, retrying (' + retryCount + ')...');
                makeRequest();
            } else {
                toastr.error('Request failed');
            }
        }
    });
}

makeRequest();
```

### 8. Update HTML with Response

```javascript
$.get('/api/warehouses/list', function(html) {
    // Replace entire content
    $('#warehouse-container').html(html);

    // Or append to container
    $('#warehouse-list').append(html);

    // Or insert before/after
    $('#warehouse-item').after(html);
});
```

### 9. Batch Requests

```javascript
let requests = [
    $.get('/api/warehouses'),
    $.get('/api/returns'),
    $.get('/api/tickets')
];

$.when.apply($, requests).done(function(warehouses, returns, tickets) {
    console.log('All data loaded');
    console.log('Warehouses:', warehouses[0]);
    console.log('Returns:', returns[0]);
    console.log('Tickets:', tickets[0]);
});
```

### 10. Abort Request

```javascript
let xhr = $.ajax({
    url: '/api/long-running-operation',
    type: 'POST'
});

// Abort after 5 seconds
setTimeout(function() {
    xhr.abort();
    toastr.warning('Request cancelled');
}, 5000);
```

---

## Advanced Patterns

### Global AJAX Setup

```javascript
// resources/js/config/ajax-setup.js
jQuery(function($) {
    // Setup defaults
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        }
    });

    // Global error handler
    $(document).ajaxError(function(event, xhr, settings, error) {
        console.error('AJAX Error:', error);

        if (xhr.status === 401) {
            window.location.href = '/login';
        } else if (xhr.status === 403) {
            toastr.error('Access denied');
        }
    });

    // Show loading indicator
    $(document).ajaxStart(function() {
        $('#loading-indicator').show();
    }).ajaxStop(function() {
        $('#loading-indicator').hide();
    });
});
```

### API Service Wrapper

```javascript
// resources/js/api/warehouse-api.js
class WarehouseAPI {
    static getList(page = 1) {
        return $.get('/api/warehouses', { page });
    }

    static getOne(id) {
        return $.get(`/api/warehouses/${id}`);
    }

    static create(data) {
        return $.ajax({
            url: '/api/warehouses',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json'
        });
    }

    static update(id, data) {
        return $.ajax({
            url: `/api/warehouses/${id}`,
            type: 'PUT',
            data: JSON.stringify(data),
            contentType: 'application/json'
        });
    }

    static delete(id) {
        return $.ajax({
            url: `/api/warehouses/${id}`,
            type: 'DELETE'
        });
    }
}

// Usage
WarehouseAPI.getList().done(function(data) {
    console.log(data);
});
```

### Pagination Helper

```javascript
jQuery(function($) {
    function loadPage(page) {
        $.get('/api/warehouses', { page }, function(response) {
            renderTable(response.data);
            updatePagination(response);
        });
    }

    function updatePagination(response) {
        let html = '';
        for (let i = 1; i <= response.last_page; i++) {
            html += `<li class="page-item ${i === response.current_page ? 'active' : ''}">
                <a class="page-link load-page" href="#" data-page="${i}">${i}</a>
            </li>`;
        }
        $('#pagination').html(html);
    }

    $(document).on('click', '.load-page', function(e) {
        e.preventDefault();
        loadPage($(this).data('page'));
    });

    // Load first page
    loadPage(1);
});
```

### Debounced Search

```javascript
jQuery(function($) {
    let searchTimeout;

    $('#search-input').on('keyup', function() {
        let query = $(this).val();

        clearTimeout(searchTimeout);

        if (query.length < 2) {
            $('#search-results').empty();
            return;
        }

        searchTimeout = setTimeout(function() {
            $.get('/api/warehouses/search', { q: query }, function(results) {
                displayResults(results);
            });
        }, 300); // Wait 300ms after user stops typing
    });
});
```

---

## Error Handling Patterns

### Comprehensive Error Handler

```javascript
jQuery(function($) {
    function handleAjaxError(xhr, fallbackMessage = 'An error occurred') {
        let message = fallbackMessage;

        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
            message = Object.values(xhr.responseJSON.errors)
                .flat()
                .join(', ');
        }

        switch (xhr.status) {
            case 401:
                toastr.error('Session expired');
                setTimeout(() => location.href = '/login', 2000);
                break;
            case 403:
                toastr.error('Access denied');
                break;
            case 404:
                toastr.error('Resource not found');
                break;
            case 422:
                toastr.error('Validation failed: ' + message);
                break;
            case 500:
                toastr.error('Server error');
                break;
            default:
                toastr.error(message);
        }
    }

    // Usage
    $.ajax({
        url: '/api/data',
        error: function(xhr) {
            handleAjaxError(xhr, 'Failed to load data');
        }
    });
});
```

---

## Performance Tips

1. **Use $.get for read-only**: Simpler than $.ajax
2. **Cache responses**: Use localStorage for non-critical data
3. **Debounce search**: Wait for user to stop typing
4. **Batch requests**: Load multiple resources together
5. **Avoid nested callbacks**: Use promises/deferred
6. **Set timeout**: Prevent hanging requests
7. **Limit concurrent requests**: Use queue system for many uploads
8. **Minimize payload**: Only send needed fields

