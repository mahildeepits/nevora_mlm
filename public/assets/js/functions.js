function ajaxFormSubmit(form) {
    event.preventDefault();
    $('.invalid-feedback').removeClass('d-block text-danger').text('');
    var formData = new FormData(form[0]);
    var route = form.attr('action');
    var method = form.attr('method');
    form.find('[type=submit]').attr('disabled', true);
    $.ajax({
        url: route,
        type: method,
        data: formData,
        processData: false,
        contentType: false,
        cache: false,
        success: function (res) {
            if (res.status) {
                toastrMessage(res.message, 'success');
                if (res.modal) {
                    form.trigger('reset');
                    setTimeout(() => {
                        $(document).find('.closeModel').trigger('click');
                        $(document).find('table').DataTable().ajax.reload();
                    }, 700);
                } else if (res.redirect) {
                    setTimeout(() => {
                        window.location.href = res.redirect;
                    }, 1000);
                } else if (res.refresh || res.refresh == undefined) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } else {
                toastrMessage(res.message, 'error');
            }
            setTimeout(() => {
                form.find('[type=submit]').attr('disabled', false);
            }, 1000);
        },
        error: function (error) {
            console.log(error);
            form.find('[type=submit]').attr('disabled', false);
            $.each(error.responseJSON.errors, function (key, message) {
                var field = $('[name="' + key + '"]');
                if (field.length === 0) {
                    // Try to handle array names like otp[]
                    field = $('[name="' + key + '[]"]');
                }
                var wrapper = field.closest('.form-group');
                if (wrapper.length === 0) {
                    wrapper = field.closest('[class*="col-"]');
                }
                wrapper.find('.invalid-feedback').text(message[0]).addClass('text-danger d-block');
            });
            if (error.responseJSON.message) {
                toastrMessage(error.responseJSON.message, 'error');
            }
        }
    })
}

function toastrMessage(message, type = 'info') {
    if (typeof toastr === 'undefined') {
        return;
    }
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": 300,
        "hideDuration": 800,
        "timeOut": 4000,
        "extendedTimeOut": 1000,
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    if (type == 'success') {
        toastr.success(message);
    } else if (type == 'error') {
        toastr.error(message);
    } else if (type == 'warning') {
        toastr.warning(message);
    } else {
        toastr.info(message);
    }
}

// Auto-dismiss standard Bootstrap alerts after 4 seconds
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function (alert) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            } else {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        });
    }, 4000);

    // Responsive Mobile Sidebar & Backdrop Synchronization
    const overlay = document.getElementById('mobileSidebarOverlay');
    const sidebar = document.querySelector('.sidebar');
    const mobileToggleBtn = document.getElementById('mobileSidebarToggleBtn');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

    function openMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('mobile-open');
        sidebar.classList.remove('sidebar-mini');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('mobile-open');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (mobileToggleBtn) {
        mobileToggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (sidebar.classList.contains('mobile-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        });
    }

    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            closeMobileSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            closeMobileSidebar();
        });
    }

    // Auto close sidebar when a navigation link is clicked on mobile
    if (sidebar) {
        sidebar.querySelectorAll('.nav-link:not(.static-item):not(.dropdown-toggle)').forEach(link => {
            link.addEventListener('click', function () {
                if (window.innerWidth < 1200) {
                    closeMobileSidebar();
                }
            });
        });
    }

    // Smooth Crypto Loader Fadeout
    const loadingElem = document.getElementById('loading');
    if (loadingElem) {
        setTimeout(function () {
            loadingElem.style.transition = 'opacity 0.4s ease, visibility 0.4s ease';
            loadingElem.style.opacity = '0';
            loadingElem.style.visibility = 'hidden';
            setTimeout(function () {
                loadingElem.classList.add('d-none');
            }, 420);
        }, 450);
    }
});

/**
 * Common DataTables Initializer
 * @param {string} tableId - ID of the table to initialize
 * @param {object} customOptions - Overrides for default DataTable settings
 */
function initDataTable(tableId, customOptions = {}) {
    let selector = tableId.startsWith('#') ? tableId : '#' + tableId;
    if ($.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable().destroy();

        // Bohat rare cases vich DOM clear karan layi empty() di lod pendi hai
        // $(selector).empty(); 
    }
    let defaultOptions = {
        "pageLength": 25,
        "order": [],
        "scrollX": true,
        "scrollCollapse": true,
        "dom": "<'row mb-3'<'col-md-6 d-flex align-items-center'B><'col-md-6'f>><'row'<'col-md-12'tr>><'row'<'col-md-6'i><'col-md-6'p>>",
        "buttons": [
            {
                extend: 'csv',
                className: 'btn btn-soft-primary btn-sm fs-xs rounded-pill px-3 me-1 mb-1',
                text: '<i class="fa-solid fa-file-csv"></i> <span class="d-none d-md-inline ms-1">CSV</span>'
            },
            {
                extend: 'excel',
                className: 'btn btn-soft-success btn-sm fs-xs rounded-pill px-3 me-1 mb-1',
                text: '<i class="fa-solid fa-file-excel"></i> <span class="d-none d-md-inline ms-1">Excel</span>'
            },
            {
                extend: 'pdf',
                className: 'btn btn-soft-danger btn-sm fs-xs rounded-pill px-3 me-1 mb-1',
                text: '<i class="fa-solid fa-file-pdf"></i> <span class="d-none d-md-inline ms-1">PDF</span>'
            },
            {
                extend: 'print',
                className: 'btn btn-soft-info btn-sm fs-xs rounded-pill px-3 mb-1',
                text: '<i class="fa-solid fa-print"></i> <span class="d-none d-md-inline ms-1">Print</span>'
            }
        ],
        "language": {
            "search": "",
            "searchPlaceholder": "Search here...",
            "lengthMenu": "_MENU_ per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "paginate": {
                "next": '<i class="fa-solid fa-chevron-right fs-xs"></i>',
                "previous": '<i class="fa-solid fa-chevron-left fs-xs"></i>'
            }
        },
        "drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-sm justify-content-end mb-0');
        }
    };

    let options = $.extend(true, {}, defaultOptions, customOptions);
    return $(selector).DataTable(options);
}


function modalShow(route, title, id = 'myModal') {
    get_modal_content(route, title, id);
}

function get_modal_content(route, title, id = 'myModal') {
    let modal = document.getElementById(id);
    let modal_content = document.getElementById('modal_content');
    let modal_title = document.getElementById('modalTitle');
    console.log('Loading modal content ');
    // Clear old content
    modal_content.innerHTML = '';
    modal_title.innerHTML = title;

    // AJAX load
    $.ajax({
        url: route,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            console.log('Modal content loaded successfully');
            if (response.status && response.html) {
                modal_content.innerHTML = response.html;

                // Show modal via Bootstrap JS
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                initializeLibraries();
                // Fix Select2 inside modal
                // $('#'+id+' .select2').select2({
                //     dropdownParent: $('#' + id)
                // });
                if ($.fn.select2) {
                    $('#' + id + ' .select2').select2({
                        dropdownParent: $('#' + id)
                    });
                }
                if (response.callback != undefined) {
                    let callbackFn = window[response.callback]; // lookup global function
                    if (typeof callbackFn === "function") {
                        callbackFn();
                    }
                }
            } else {
                modal_content.innerHTML = "<p class='text-danger'>Failed to load content.</p>";
            }
        },
        error: function (error) {
            console.error('Error loading modal content:', error);
            modal_content.innerHTML = "<p class='text-danger'>Something went wrong.</p>";
            if (error?.responseJSON?.message !== undefined) {
                toasterMessage('error', error.responseJSON.message);
            }
        }
    });
}

function modalClose(id = 'myModal') {
    let modalEl = document.getElementById(id);
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
        modal.hide();
    }
}

function loader(action = 'show') {
    if (action == 'show') {
        $('#loader').show();
    } else {
        $('#loader').hide();
    }
}

function initializeLibraries() {
    // Select2
    // if ($.fn.select2) {
    //     $('.select2').select2();
    // }
    // // DatePickers
    // $('.date-range-picker').daterangepicker({
    //     autoUpdateInput: false,
    //     locale: {
    //         cancelLabel: 'Clear',
    //         format: 'YYYY-MM-DD'
    //     },
    //     ranges: {
    //         'Today': [moment(), moment()],
    //         'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
    //         'Last 7 Days': [moment().subtract(6, 'days'), moment()],
    //         'Last 30 Days': [moment().subtract(29, 'days'), moment()],
    //         'This Month': [moment().startOf('month'), moment().endOf('month')],
    //         'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    //     }
    // }).on('apply.daterangepicker', function (ev, picker) {
    //     $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    // }).on('cancel.daterangepicker', function (ev, picker) {
    //     $(this).val('');
    // });

    // $('.date-range-picker-past').daterangepicker({
    //     autoUpdateInput: false,
    //     maxDate: moment(), // Disable future dates
    //     locale: {
    //         cancelLabel: 'Clear',
    //         format: 'YYYY-MM-DD'
    //     },
    //     ranges: {
    //         'Today': [moment(), moment()],
    //         'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
    //         'Last 7 Days': [moment().subtract(6, 'days'), moment()],
    //         'Last 30 Days': [moment().subtract(29, 'days'), moment()],
    //         'This Month': [moment().startOf('month'), moment().endOf('month')],
    //         'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    //     }
    // }).on('apply.daterangepicker', function (ev, picker) {
    //     $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    // }).on('cancel.daterangepicker', function (ev, picker) {
    //     $(this).val('');
    // });

    // $('.date-range-picker-future').daterangepicker({
    //     autoUpdateInput: false,
    //     minDate: moment(), // Disable past dates
    //     locale: {
    //         cancelLabel: 'Clear',
    //         format: 'YYYY-MM-DD'
    //     }
    // }).on('apply.daterangepicker', function (ev, picker) {
    //     $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    // }).on('cancel.daterangepicker', function (ev, picker) {
    //     $(this).val('');
    // });

    // $('.date-picker').daterangepicker({
    //     singleDatePicker: true,
    //     showDropdowns: true,
    //     autoUpdateInput: false,
    //     locale: {
    //         format: 'YYYY-MM-DD'
    //     }
    // }).on('apply.daterangepicker', function (ev, picker) {
    //     $(this).val(picker.startDate.format('YYYY-MM-DD'));
    // }).on('cancel.daterangepicker', function (ev, picker) {
    //     $(this).val('');
    // });

    // $('.date-picker-future').daterangepicker({
    //     singleDatePicker: true,
    //     showDropdowns: true,
    //     minDate: moment(),   // 🚀 Only today and future
    //     locale: { format: 'YYYY-MM-DD' }
    // }).on('apply.daterangepicker', function (ev, picker) {
    //     $(this).val(picker.startDate.format('YYYY-MM-DD'));
    // }).on('cancel.daterangepicker', function (ev, picker) {
    //     $(this).val('');
    // });

    // $('.date-picker-past').daterangepicker({
    //     singleDatePicker: true,
    //     showDropdowns: true,
    //     maxDate: moment(),   // 🚀 Only today and past
    //     locale: { format: 'YYYY-MM-DD' }
    // }).on('apply.daterangepicker', function (ev, picker) {
    //     $(this).val(picker.startDate.format('YYYY-MM-DD'));
    // }).on('cancel.daterangepicker', function (ev, picker) {
    //     $(this).val('');
    // });

    // // DateTime Range Picker
    // $('.date-time-range-picker').daterangepicker({
    //     autoUpdateInput: false,
    //     timePicker: true,                // ⏰ enable time picker
    //     timePicker24Hour: true,          // use 24h format (set false for AM/PM)
    //     timePickerSeconds: true,         // optional: show seconds
    //     locale: {
    //         cancelLabel: 'Clear',
    //         format: 'YYYY-MM-DD HH:mm:ss' // date + time format
    //     }
    // }).on('apply.daterangepicker', function (ev, picker) {
    //     $(this).val(
    //         picker.startDate.format('YYYY-MM-DD HH:mm:ss') +
    //         ' - ' +
    //         picker.endDate.format('YYYY-MM-DD HH:mm:ss')
    //     );
    // }).on('cancel.daterangepicker', function (ev, picker) {
    //     $(this).val('');
    // });

    // // Single DateTime Picker
    // $('.date-time-picker').daterangepicker({
    //     singleDatePicker: true,
    //     showDropdowns: true,
    //     autoUpdateInput: false,
    //     timePicker: true,
    //     timePicker24Hour: true,
    //     timePickerSeconds: true,
    //     locale: {
    //         format: 'YYYY-MM-DD HH:mm:ss'
    //     }
    // }).on('apply.daterangepicker', function (ev, picker) {
    //     $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss'));
    // }).on('cancel.daterangepicker', function (ev, picker) {
    //     $(this).val('');
    // });
    // // 🟢 Future Only DateTime Picker
    // $('.date-time-picker-future').daterangepicker({
    //     singleDatePicker: true,
    //     showDropdowns: true,
    //     autoUpdateInput: false,
    //     timePicker: true,
    //     timePicker24Hour: true,
    //     timePickerSeconds: false,
    //     minDate: moment(),   // 🚀 only today and future
    //     locale: {
    //         format: 'YYYY-MM-DD HH:mm'
    //     }
    // }).on('apply.daterangepicker', function (ev, picker) {
    //     $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm'));
    // }).on('cancel.daterangepicker', function (ev, picker) {
    //     $(this).val('');
    // });

    // // CkEditor
    // document.querySelectorAll("textarea.ckeditor").forEach((el) => {
    //     let heightPx = calculateHeightInPxFromTextAreaRowAttr(el);
    //     if (el.classList.contains('noresize')) {
    //         CKEDITOR.replace(el, {
    //             resize_enabled: false, // disables resize handle on this editor instance
    //             height: heightPx
    //         });
    //     } else {
    //         CKEDITOR.replace(el);
    //     }
    // });

    // // Input Mask
    // $(".phone-input").inputmask({
    //     mask: "+1 (999) 999-9999",
    //     placeholder: " ",
    //     showMaskOnHover: false
    // });
}

function handleAction(model, id, action = 'delete', pageReload = false) {
    let title = '', text = '', confirmBtn = '', icon = '';

    switch (action) {
        case 'delete':
            title = 'Delete Record?';
            text = 'This will move the record to trash.';
            confirmBtn = 'Delete';
            icon = 'warning';
            break;
        case 'restore':
            title = 'Restore Record?';
            text = 'This will restore the record.';
            confirmBtn = 'Restore';
            icon = 'info';
            break;
        case 'permanent':
            title = 'Permanently Delete?';
            text = 'This action is irreversible!';
            confirmBtn = 'Delete Permanently';
            icon = 'error';
            break;
        case 'active':
            title = 'Activate Record?';
            text = 'This will make the record active.';
            confirmBtn = 'Activate';
            icon = 'success';
            break;
        case 'inactive':
            title = 'Deactivate Record?';
            text = 'This will make the record inactive.';
            confirmBtn = 'Deactivate';
            icon = 'question';
            break;
        default:
            title = 'Are you sure?';
            text = 'Please confirm the action.';
            confirmBtn = 'Confirm';
            icon = 'question';
    }

    Swal.fire({
        title, text,
        icon,
        showCancelButton: true,
        confirmButtonText: confirmBtn,
        confirmButtonColor: '#d33',
        cancelButtonText: 'Cancel',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: baseUrl() + '/ajax/handle-action',
                type: 'POST',
                data: {
                    _token: csrfToken(),
                    model: model,
                    id: id,
                    action: action
                },
                success: function (response) {
                    if (response.status) {
                        toasterMessage('success', response.message);
                    }
                    if (response.message !== undefined) {
                        toasterMessage('success', response.message);
                    }
                    refreshDataTable();

                    if (pageReload) {
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000);
                    }
                },
                error: function (error) {
                    toasterMessage('error', error?.responseJSON?.message);
                }
            });
        }
    });
}

function updateClock() {
    const now = new Date();
    const dateEl = document.getElementById('live-date');
    const timeEl = document.getElementById('live-time');

    if (!dateEl || !timeEl) return;

    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };

    dateEl.textContent = now.toLocaleDateString(undefined, dateOptions);
    timeEl.textContent = now.toLocaleTimeString(undefined, timeOptions);
}

/* Tree Dragging Functionality */
function initTreeDrag() {
    const slider = document.querySelector('.tree-wrapper');
    if (!slider) return;

    let isDown = false;
    let startX;
    let startY;
    let scrollLeft;
    let scrollTop;

    slider.addEventListener('mousedown', (e) => {
        if (e.target.tagName === 'A' || e.target.parentElement.tagName === 'A') return; // Allow clicking links
        isDown = true;
        slider.style.cursor = 'grabbing';
        startX = e.pageX - slider.offsetLeft;
        startY = e.pageY - slider.offsetTop;
        scrollLeft = slider.scrollLeft;
        scrollTop = slider.scrollTop;
    });

    slider.addEventListener('mouseleave', () => {
        isDown = false;
        slider.style.cursor = 'grab';
    });

    slider.addEventListener('mouseup', () => {
        isDown = false;
        slider.style.cursor = 'grab';
    });

    slider.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const y = e.pageY - slider.offsetTop;
        const walkX = (x - startX) * 2;
        const walkY = (y - startY) * 2;
        slider.scrollLeft = scrollLeft - walkX;
        slider.scrollTop = scrollTop - walkY;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    updateClock();
    setInterval(updateClock, 1000);
    initTreeDrag();
    initMobileSidebar();
});

/* ============================================================
   MOBILE OFF-CANVAS SIDEBAR TOGGLE (phone & tablet ≤ 1199px)
   ============================================================ */
function initMobileSidebar() {
    const MOBILE_BP = 1200; // px — matches CSS breakpoint

    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('mobileSidebarOverlay');
    const toggleBtns = document.querySelectorAll('[data-toggle="sidebar"]');

    if (!sidebar || !overlay) return;

    // Track state ourselves — don't rely on hope-ui classes
    let isOpen = false;

    function isMobile() {
        return window.innerWidth < MOBILE_BP;
    }

    function openSidebar() {
        isOpen = true;
        // Strip template classes that might cause layout shifts on mobile
        sidebar.classList.remove('sidebar-mini');
        sidebar.classList.add('mobile-open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        isOpen = false;
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    // --- Intercept ALL handlers on the toggle buttons (including hope-ui) ---
    toggleBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!isMobile()) return;

            e.stopImmediatePropagation();
            e.preventDefault();

            // Toggle
            if (isOpen) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }, true);
    });

    // Backdrop tap/click → close
    overlay.addEventListener('click', closeSidebar);

    // Resize to desktop → reset
    window.addEventListener('resize', function () {
        if (!isMobile() && isOpen) {
            closeSidebar();
        }
    });

    // Nav link click → close drawer before navigating
    sidebar.querySelectorAll('.nav-link:not(.static-item):not(.disabled)').forEach(function (link) {
        link.addEventListener('click', function () {
            if (isMobile() && isOpen) closeSidebar();
        });
    });
}

/**
 * Copies plain text to the clipboard and shows a success toast
 * @param {string} text - The text to copy
 */
function copyToClipboard(text) {
    if (!text) return;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text)
            .then(() => {
                if (typeof toastrMessage === 'function') {
                    toastrMessage("Copied to clipboard!", "success");
                } else {
                    alert("Copied to clipboard!");
                }
            })
            .catch(err => {
                console.error("Failed to copy using clipboard API: ", err);
                fallbackCopyText(text);
            });
    } else {
        fallbackCopyText(text);
    }
}

function fallbackCopyText(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.opacity = "0";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            if (typeof toastrMessage === 'function') {
                toastrMessage("Copied to clipboard!", "success");
            } else {
                alert("Copied to clipboard!");
            }
        } else {
            alert("Failed to copy. Please copy manually.");
        }
    } catch (err) {
        console.error("Fallback copy failed: ", err);
        alert("Failed to copy.");
    }

    document.body.removeChild(textArea);
}

