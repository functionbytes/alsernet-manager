# jQuery Libraries - Complete Reference for Alsernet

**All jQuery libraries available in `public/managers/libs` with usage patterns and examples.**

---

## 📦 AVAILABLE JQUERY LIBRARIES

### Summary Table

| Library | Folder | Purpose | Version | Usage |
|---------|--------|---------|---------|-------|
| jQuery | `jquery/` | Core JavaScript library | 3.6+ | **Essential** |
| jQuery Validate | `jquery-validation/` | Form validation | 1.19+ | **Forms** |
| jQuery UI | `jquery-ui/` | UI widgets & interactions | 1.13+ | Dialogs, tabs |
| jQuery Steps | `jquery-steps/` | Multi-step forms | 1.1+ | Wizards |
| jQuery Repeater | `jquery.repeater/` | Dynamic field repeating | 1.2+ | Dynamic forms |
| jQuery InputMask | `inputmask/` | Input masking | 5.0+ | Phone, dates |
| Typeahead.js | `typeahead.js/` | Autocomplete | 0.11+ | Search |
| Select2 | `select2/` | Advanced select | 4.1+ | Multi-select |
| Bootstrap Tagsinput | `taginput/` | Tag input | 0.8+ | Tags |
| Bootstrap Switch | `bootstrap-switch/` | Toggle switch | 3.3+ | On/Off |
| Bootstrap Touchspin | `bootstrap-touchspin/` | Number spinner | 3.1+ | Increment |
| Date Range Picker | `daterangepicker/` | Date selection | 3.1+ | Date ranges |
| Bootstrap Datepicker | `bootstrap-datepicker/` | Date picker | 1.9+ | Single date |
| DatePicker Jalaali | `pickadate-jalaali/` | Persian dates | - | Persian dates |
| Dropzone | `dropzone/` | File upload | 5.9+ | **Uploads** |
| Cropper | `cropper/` | Image cropping | 1.5+ | Image edit |
| Magnific Popup | `magnific-popup/` | Lightbox | 1.1+ | Modals/galleries |
| SweetAlert2 | `sweetalert2/` | Alert dialogs | 11+ | **Alerts** |
| Toastr | `toastr/` | Toast notifications | 2.1+ | **Notifications** |
| CKEditor | `ckeditor/` | Rich text editor | 4.20+ | Content |
| TinyMCE | `tinymce/` | Rich text editor | 6.0+ | Content |
| Quill | `quill/` | Rich text editor | 1.3+ | Content |
| Summernote | `summernote/` | Rich text editor | 0.8+ | Content |
| DataTables | `datatables.net/` | Data tables | 1.13+ | **Tables** |
| DataTables Bootstrap | `datatables.net-bs5/` | Bootstrap theme | 1.13+ | **Tables** |
| Bootstrap Table | `bootstrap-table/` | Bootstrap tables | 1.20+ | Tables |
| ApexCharts | `apexcharts/` | Charts & graphs | 3.35+ | **Charts** |
| Fullcalendar | `fullcalendar/` | Calendar widget | 6.0+ | Events |
| Owl Carousel | `owl.carousel/` | Image carousel | 2.3+ | Sliders |
| Dragula | `dragula/` | Drag & drop | 3.7+ | **Drag/Drop** |
| Nestable | `nestable/` | Nested sorting | - | Tree sorting |
| Kanban | `kanban/` | Kanban boards | - | Kanban |

---

## 🚀 CORE LIBRARIES (Use in Every Project)

### 1. jQuery (Core)

**Path:** `public/managers/libs/jquery/`

#### Basic Usage
```javascript
// Include
<script src="/managers/libs/jquery/jquery-3.6.0.min.js"></script>

// Check jQuery loaded
console.log(jQuery().jquery); // "3.6.0"

// Use both $ and jQuery
$(document).ready(function() {
    // Code here
});

jQuery(function($) {
    // Shorter syntax with $ available
});
```

#### Common Operations
```javascript
// DOM Selection
$('#id')              // By ID
$('.class')           // By class
$('input[type="email"]')  // By attribute
$('.parent > .child') // Child selector

// DOM Manipulation
$('#el').html('<p>HTML</p>')
$('#el').text('Text')
$('#el').val('value')
$('#el').attr('data-id', '123')
$('#el').addClass('active')
$('#el').on('click', handler)
$('#el').css('color', 'red')

// Effects
$('#el').show()
$('#el').hide()
$('#el').fadeIn()
$('#el').slideDown()
$('#el').animate({opacity: 0.5})
```

---

### 2. jQuery Validate (Form Validation) ⭐

**Path:** `public/managers/libs/jquery-validation/`
**Version:** 1.19+

#### Include jQuery Validate
```html
<script src="/managers/libs/jquery/jquery-3.6.0.min.js"></script>
<script src="/managers/libs/jquery-validation/jquery.validate.min.js"></script>
<script src="/managers/libs/jquery-validation/localization/messages_es.js"></script>
```

#### Basic Form Validation

```html
<form id="warehouse-form">
    <div class="mb-3">
        <label for="name">Warehouse Name</label>
        <input type="text" class="form-control" id="name" name="name">
    </div>
    <div class="mb-3">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email">
    </div>
    <div class="mb-3">
        <label for="capacity">Capacity</label>
        <input type="number" class="form-control" id="capacity" name="capacity">
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
```

#### jQuery Validate Script

```javascript
jQuery(function($) {
    $('#warehouse-form').validate({
        rules: {
            name: {
                required: true,
                minlength: 3,
                maxlength: 100
            },
            email: {
                required: true,
                email: true
            },
            capacity: {
                required: true,
                number: true,
                min: 1
            }
        },
        messages: {
            name: {
                required: "Warehouse name is required",
                minlength: "Name must be at least 3 characters"
            },
            email: {
                required: "Email is required",
                email: "Please enter a valid email"
            },
            capacity: {
                required: "Capacity is required",
                number: "Capacity must be a number",
                min: "Capacity must be greater than 0"
            }
        },
        errorClass: "is-invalid",
        validClass: "is-valid",
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback d-block');
            element.after(error);
        },
        success: function(label, element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
        submitHandler: function(form) {
            // AJAX submission
            $.ajax({
                url: '/api/warehouses',
                type: 'POST',
                data: $(form).serialize(),
                success: function(response) {
                    toastr.success('Warehouse saved');
                },
                error: function(xhr) {
                    toastr.error('Error saving warehouse');
                }
            });
            return false;
        }
    });
});
```

#### Built-in Validation Rules

```javascript
rules: {
    // Text validation
    name: 'required',
    username: {
        required: true,
        minlength: 3,
        maxlength: 20
    },

    // Email validation
    email: {
        required: true,
        email: true
    },

    // Number validation
    age: {
        required: true,
        number: true,
        min: 18,
        max: 120
    },

    // URL validation
    website: {
        url: true
    },

    // Date validation
    birthdate: {
        date: true
    },

    // Pattern validation
    phone: {
        required: true,
        pattern: /^[0-9\-\+\(\)]+$/
    },

    // Custom regex
    customCode: {
        required: true,
        pattern: /^[A-Z]{3}[0-9]{3}$/
    },

    // Range
    price: {
        required: true,
        range: [10, 1000]
    },

    // Match other field
    password: 'required',
    confirmPassword: {
        required: true,
        equalTo: '#password'
    }
}
```

#### Spanish Localization

```javascript
// Spanish messages are in: jquery-validation/localization/messages_es.js
// Already included above, but you can customize:

$.extend($.validator.messages, {
    required: "Este campo es obligatorio",
    email: "Por favor, introduce un email válido",
    number: "Por favor, introduce un número válido",
    minlength: "Mínimo {0} caracteres",
    maxlength: "Máximo {0} caracteres"
});
```

#### Form with Server Validation

```javascript
jQuery(function($) {
    $('#warehouse-form').validate({
        rules: {
            name: {
                required: true,
                remote: {
                    url: '/api/warehouses/validate-name',
                    type: 'post',
                    data: {
                        name: function() {
                            return $('#name').val();
                        }
                    }
                }
            }
        },
        messages: {
            name: {
                remote: "This warehouse name already exists"
            }
        }
    });
});
```

#### Dynamic Form Validation

```javascript
jQuery(function($) {
    // Validate form on submit
    $('#warehouse-form').validate();

    // Add new field and validate
    $('#add-item').on('click', function() {
        let html = `<input type="text" name="items[]" class="form-control" required>`;
        $('#items-container').append(html);

        // Re-validate all forms
        $('[name="items[]"]').each(function() {
            $(this).rules('add', {
                required: true,
                minlength: 3
            });
        });
    });
});
```

---

### 3. jQuery UI (UI Widgets)

**Path:** `public/managers/libs/jquery-ui/`

#### Dialog (Modal)
```javascript
jQuery(function($) {
    $('#dialog').dialog({
        autoOpen: false,
        buttons: {
            "Ok": function() {
                $(this).dialog('close');
            },
            "Cancel": function() {
                $(this).dialog('close');
            }
        }
    });

    $('#open-dialog').on('click', function() {
        $('#dialog').dialog('open');
    });
});
```

#### Sortable (Drag & Drop)
```javascript
jQuery(function($) {
    $('#sortable').sortable({
        stop: function(event, ui) {
            console.log('New order:', $(this).sortable('toArray'));
        }
    });
});
```

#### Datepicker
```javascript
jQuery(function($) {
    $('#datepicker').datepicker({
        dateFormat: 'dd/mm/yy',
        language: 'es'
    });
});
```

---

## 📋 FORM VALIDATION LIBRARIES

### 4. jQuery Steps (Multi-step Forms)

**Path:** `public/managers/libs/jquery-steps/`

```html
<div id="wizard">
    <h3>Step 1</h3>
    <section>
        <form>
            <input type="text" name="name" required>
        </form>
    </section>

    <h3>Step 2</h3>
    <section>
        <form>
            <input type="email" name="email" required>
        </form>
    </section>

    <h3>Step 3</h3>
    <section>
        <form>
            <textarea name="comments"></textarea>
        </form>
    </section>
</div>
```

```javascript
jQuery(function($) {
    $('#wizard').steps({
        headerTag: 'h3',
        bodyTag: 'section',
        transitionEffect: 'slideLeft',
        onStepChanging: function(event, currentIndex, newIndex) {
            // Validate before moving to next step
            return true;
        },
        onFinishing: function(event, currentIndex) {
            // Validate before finishing
            return true;
        },
        onFinished: function(event, currentIndex) {
            // Submit form
            console.log('Form completed');
        }
    });
});
```

---

### 5. jQuery InputMask (Input Masking)

**Path:** `public/managers/libs/inputmask/`

```html
<!-- Phone mask -->
<input type="text" name="phone" placeholder="(999) 999-9999">

<!-- Date mask -->
<input type="text" name="birthdate" placeholder="dd/mm/yyyy">

<!-- Credit card -->
<input type="text" name="card" placeholder="9999 9999 9999 9999">
```

```javascript
jQuery(function($) {
    // Phone mask
    $('input[name="phone"]').inputmask('(999) 999-9999');

    // Date mask
    $('input[name="birthdate"]').inputmask('99/99/9999');

    // Credit card
    $('input[name="card"]').inputmask('9999 9999 9999 9999');

    // Custom patterns
    $('input[name="custom"]').inputmask({
        mask: '99-[99]-9999',
        placeholder: '_'
    });
});
```

---

### 6. jQuery Repeater (Dynamic Fields)

**Path:** `public/managers/libs/jquery.repeater/`

```html
<div class="repeater" data-repeater-list="items">
    <div data-repeater-item>
        <input type="text" name="item_name" placeholder="Item name">
        <input type="number" name="quantity" placeholder="Quantity">
        <button data-repeater-delete type="button" class="btn btn-danger">Delete</button>
    </div>
</div>
<button data-repeater-create type="button" class="btn btn-primary">Add Item</button>
```

```javascript
jQuery(function($) {
    $('.repeater').repeater({
        show: function() {
            $(this).slideDown();
        },
        hide: function(deleteElement) {
            $(this).slideUp(deleteElement);
        }
    });
});
```

---

### 7. Bootstrap Tagsinput (Tags)

**Path:** `public/managers/libs/taginput/`

```html
<input type="text" id="tags" data-role="tagsinput" value="">
```

```javascript
jQuery(function($) {
    $('#tags').tagsinput({
        allowDuplicates: false,
        trimValue: true
    });

    // Add tags programmatically
    $('#tags').tagsinput('add', 'tag1');
    $('#tags').tagsinput('add', 'tag2');

    // Get tags
    let tags = $('#tags').val(); // "tag1,tag2"
});
```

---

### 8. Bootstrap Touchspin (Number Spinner)

**Path:** `public/managers/libs/bootstrap-touchspin/`

```html
<input type="number" class="touchspin" value="5" min="0" max="100" step="1">
```

```javascript
jQuery(function($) {
    $('.touchspin').TouchSpin({
        min: 0,
        max: 100,
        step: 1,
        decimals: 0,
        boostat: 5
    });
});
```

---

### 9. Bootstrap Switch (Toggle Switch)

**Path:** `public/managers/libs/bootstrap-switch/`

```html
<input type="checkbox" name="notify" data-toggle="switch">
```

```javascript
jQuery(function($) {
    $('[data-toggle="switch"]').bootstrapSwitch({
        onText: 'On',
        offText: 'Off',
        onColor: 'success',
        offColor: 'danger'
    });

    // Check state
    let isOn = $('[name="notify"]').prop('checked');
});
```

---

## 🎨 UI & SELECTION LIBRARIES

### 10. Select2 (Advanced Select)

**Path:** `public/managers/libs/select2/`

```html
<select id="warehouse-select" class="form-select" multiple>
    <option value="">Select warehouse...</option>
</select>
```

```javascript
jQuery(function($) {
    $('#warehouse-select').select2({
        width: '100%',
        placeholder: 'Select a warehouse',
        allowClear: true,
        ajax: {
            url: '/api/warehouses/search',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(item => ({
                        id: item.id,
                        text: item.name
                    }))
                };
            }
        },
        minimumInputLength: 1
    });
});
```

---

### 11. Typeahead.js (Autocomplete)

**Path:** `public/managers/libs/typeahead.js/`

```html
<input type="text" id="search" class="typeahead" placeholder="Search warehouses...">
```

```javascript
jQuery(function($) {
    $('#search').typeahead({
        hint: true,
        highlight: true,
        minLength: 1
    }, {
        source: function(query, syncResults, asyncResults) {
            $.get('/api/warehouses/search?q=' + query, function(data) {
                asyncResults(data.map(item => item.name));
            });
        }
    });
});
```

---

### 12. Date Range Picker

**Path:** `public/managers/libs/daterangepicker/`

```html
<input type="text" id="daterange" class="form-control">
```

```javascript
jQuery(function($) {
    $('#daterange').daterangepicker({
        startDate: moment().subtract(29, 'days'),
        endDate: moment(),
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'),
                          moment().subtract(1, 'month').endOf('month')]
        }
    }, function(start, end, label) {
        console.log('Date range selected:', start.format('DD/MM/YYYY'), 'to', end.format('DD/MM/YYYY'));
    });
});
```

---

## 📤 UPLOAD & FILE LIBRARIES

### 13. Dropzone (File Upload)

**Path:** `public/managers/libs/dropzone/`

```html
<div id="dropzone-form" class="dropzone"></div>
```

```javascript
jQuery(function($) {
    Dropzone.options.dropzoneForm = {
        maxFilesize: 10, // MB
        acceptedFiles: '.jpg,.jpeg,.png,.gif,.pdf',
        paramName: 'file',
        url: '/api/upload',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(file, response) {
            toastr.success('File uploaded');
        },
        error: function(file, errorMessage) {
            toastr.error(errorMessage);
        }
    };

    // Or manual initialization
    var myDropzone = new Dropzone('#dropzone-form', {
        url: '/api/upload',
        maxFilesize: 10,
        acceptedFiles: '.jpg,.jpeg,.png'
    });

    myDropzone.on('success', function(file, response) {
        console.log('Uploaded:', response);
    });
});
```

---

### 14. Cropper (Image Cropping)

**Path:** `public/managers/libs/cropper/`

```html
<img id="image" src="/image.jpg">
<button id="crop-btn" class="btn btn-primary">Crop</button>
```

```javascript
jQuery(function($) {
    let cropper = new Cropper(document.getElementById('image'), {
        aspectRatio: 16 / 9,
        viewMode: 1,
        autoCropArea: 0.8,
        responsive: true,
        guides: true,
        grid: true,
        cropBoxMovable: true,
        cropBoxResizable: true
    });

    $('#crop-btn').on('click', function() {
        let canvas = cropper.getCroppedCanvas();
        let imageData = canvas.toDataURL('image/jpeg');

        // Send to server
        $.ajax({
            url: '/api/image/save',
            type: 'POST',
            data: {
                image: imageData
            }
        });
    });
});
```

---

## 📊 TABLES & DATA LIBRARIES

### 15. DataTables (Advanced Tables) ⭐

**Path:** `public/managers/libs/datatables.net/` + `public/managers/libs/datatables.net-bs5/`

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

```javascript
jQuery(function($) {
    $('#warehouses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/api/warehouses/list',
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'location' },
            { data: 'capacity' },
            {
                data: null,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-primary edit-btn" data-id="${data.id}">Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${data.id}">Delete</button>
                    `;
                }
            }
        ],
        language: {
            url: '/lang/es/datatables.json'
        }
    });
});
```

---

### 16. ApexCharts (Charts & Graphs)

**Path:** `public/managers/libs/apexcharts/`

```html
<div id="chart"></div>
```

```javascript
jQuery(function($) {
    let options = {
        chart: {
            type: 'bar'
        },
        series: [{
            name: 'Sales',
            data: [30, 40, 35, 50, 49, 60, 70, 91, 125]
        }],
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep']
        }
    };

    let chart = new ApexCharts(document.querySelector('#chart'), options);
    chart.render();
});
```

---

### 17. Bootstrap Table

**Path:** `public/managers/libs/bootstrap-table/`

```html
<table id="table" data-toggle="table" data-url="/api/data" data-pagination="true">
    <thead>
        <tr>
            <th data-field="id">ID</th>
            <th data-field="name">Name</th>
            <th data-field="price" data-formatter="priceFormatter">Price</th>
        </tr>
    </thead>
</table>
```

```javascript
jQuery(function($) {
    function priceFormatter(value) {
        return '$' + parseFloat(value).toFixed(2);
    }

    $('#table').bootstrapTable({
        url: '/api/data',
        method: 'get',
        pagination: true,
        pageSize: 10,
        search: true,
        showColumns: true
    });
});
```

---

## 🎬 MEDIA & GALLERY LIBRARIES

### 18. Magnific Popup (Lightbox)

**Path:** `public/managers/libs/magnific-popup/`

```html
<a class="image-popup-link" href="/images/1.jpg">
    <img src="/images/1-thumb.jpg" alt="Image 1">
</a>
```

```javascript
jQuery(function($) {
    $('.image-popup-link').magnificPopup({
        type: 'image',
        gallery: {
            enabled: true,
            navigateByImgClick: true
        }
    });
});
```

---

### 19. Fullcalendar (Calendar Widget)

**Path:** `public/managers/libs/fullcalendar/`

```html
<div id="calendar"></div>
```

```javascript
jQuery(function($) {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: '/api/events'
    });
    calendar.render();
});
```

---

### 20. Owl Carousel (Image Carousel)

**Path:** `public/managers/libs/owl.carousel/`

```html
<div class="owl-carousel">
    <div class="item"><img src="/image1.jpg"></div>
    <div class="item"><img src="/image2.jpg"></div>
    <div class="item"><img src="/image3.jpg"></div>
</div>
```

```javascript
jQuery(function($) {
    $('.owl-carousel').owlCarousel({
        loop: true,
        margin: 10,
        responsive: {
            0: { items: 1 },
            600: { items: 2 },
            1000: { items: 3 }
        }
    });
});
```

---

## 🖊️ RICH TEXT EDITOR LIBRARIES

### 21. CKEditor / TinyMCE / Quill / Summernote

```html
<!-- CKEditor -->
<textarea id="editor" name="content"></textarea>

<!-- Or TinyMCE -->
<textarea id="tinymce" name="content"></textarea>

<!-- Or Quill -->
<div id="editor-container"></div>

<!-- Or Summernote -->
<div id="summernote"></div>
```

```javascript
jQuery(function($) {
    // CKEditor
    CKEDITOR.replace('editor', {
        height: 400,
        toolbar: [
            ['Bold', 'Italic', 'Underline'],
            ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'],
            ['Link', 'Unlink', 'Image']
        ]
    });

    // Or Summernote (jQuery plugin)
    $('#summernote').summernote({
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture']]
        ]
    });
});
```

---

## 🔔 NOTIFICATION LIBRARIES

### 22. SweetAlert2 (Beautiful Alerts)

**Path:** `public/managers/libs/sweetalert2/`

```javascript
jQuery(function($) {
    // Simple alert
    Swal.fire({
        title: 'Success!',
        text: 'Warehouse saved successfully',
        icon: 'success'
    });

    // Confirm dialog
    Swal.fire({
        title: 'Delete warehouse?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            // Delete warehouse
            $.ajax({
                url: '/api/warehouses/' + id,
                type: 'DELETE',
                success: function() {
                    Swal.fire('Deleted!', 'Warehouse deleted.', 'success');
                }
            });
        }
    });
});
```

---

### 23. Toastr (Toast Notifications)

**Path:** `public/managers/libs/toastr/`

```javascript
jQuery(function($) {
    // Success
    toastr.success('Warehouse saved successfully');

    // Error
    toastr.error('Error saving warehouse');

    // Warning
    toastr.warning('Please review the data');

    // Info
    toastr.info('Operation in progress...');

    // Configure
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 5000
    };
});
```

---

## 🎯 DRAG & DROP LIBRARIES

### 24. Dragula (Drag & Drop)

**Path:** `public/managers/libs/dragula/`

```html
<div id="left" class="container">
    <div>Item 1</div>
    <div>Item 2</div>
</div>
<div id="right" class="container">
    <div>Item 3</div>
</div>
```

```javascript
jQuery(function($) {
    let drake = dragula([
        document.getElementById('left'),
        document.getElementById('right')
    ]);

    drake.on('drop', function(el, target, source, sibling) {
        console.log('Item dropped in:', target.id);
    });
});
```

---

## 📋 IMPLEMENTATION GUIDE

### Best Practice Pattern

```javascript
jQuery(function($) {
    // 1. Find form
    let $form = $('#my-form');

    // 2. Initialize jQuery Validate
    $form.validate({
        rules: {
            name: 'required',
            email: { required: true, email: true }
        },
        submitHandler: function(form) {
            // 3. Submit via AJAX
            $.ajax({
                url: '/api/save',
                type: 'POST',
                data: $(form).serialize(),
                success: function(response) {
                    // 4. Show notification
                    toastr.success('Saved successfully');

                    // 5. Update UI
                    $('#result').html(response.html);
                },
                error: function(xhr) {
                    toastr.error('Error saving');
                }
            });
            return false;
        }
    });

    // 6. Handle dynamic elements with event delegation
    $(document).on('click', '.delete-btn', function() {
        let id = $(this).data('id');

        // 7. Use SweetAlert for confirmation
        Swal.fire({
            title: 'Delete?',
            showCancelButton: true
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/delete/' + id,
                    type: 'DELETE',
                    success: function() {
                        toastr.success('Deleted');
                        location.reload();
                    }
                });
            }
        });
    });
});
```

---

## 🔑 Quick Reference: Which Library to Use

| Need | Library | Example |
|------|---------|---------|
| Form validation | jQuery Validate | `$form.validate()` |
| Input masking | InputMask | `$('input').inputmask()` |
| Dynamic fields | jQuery Repeater | `$('.repeater').repeater()` |
| Tags input | Tagsinput | `$('#tags').tagsinput()` |
| Select dropdown | Select2 | `$('#select').select2()` |
| Search/autocomplete | Typeahead | `$('#search').typeahead()` |
| File upload | Dropzone | `new Dropzone()` |
| Image crop | Cropper | `new Cropper()` |
| Data table | DataTables | `$('table').DataTable()` |
| Charts | ApexCharts | `new ApexCharts()` |
| Notification | Toastr | `toastr.success()` |
| Modal alert | SweetAlert2 | `Swal.fire()` |
| Lightbox | Magnific Popup | `$('.popup').magnificPopup()` |
| Drag & drop | Dragula | `dragula([...])` |
| Rich text | Summernote | `$('#editor').summernote()` |

---

## 📚 Files to Include in Every Project

```html
<!-- jQuery & Validate -->
<script src="/managers/libs/jquery/jquery-3.6.0.min.js"></script>
<script src="/managers/libs/jquery-validation/jquery.validate.min.js"></script>
<script src="/managers/libs/jquery-validation/localization/messages_es.js"></script>

<!-- Bootstrap & Components -->
<script src="/managers/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="/managers/libs/bootstrap/css/bootstrap.min.css">

<!-- Notifications -->
<script src="/managers/libs/toastr/toastr.min.js"></script>
<link rel="stylesheet" href="/managers/libs/toastr/toastr.min.css">

<!-- Alerts -->
<script src="/managers/libs/sweetalert2/sweetalert2.all.min.js"></script>

<!-- DataTables -->
<script src="/managers/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="/managers/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="/managers/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css">

<!-- FontAwesome Icons -->
<link rel="stylesheet" href="/managers/libs/fontawesome/css/all.min.css">
```

---

**Last Updated:** November 30, 2025
**Version:** Complete Library Reference
**For:** Alsernet Frontend Development
