$(document).ready(function () {
    // Initialize column visibility from database
    let columnVisibility = {};

    // Set default sort option
    window.currentSortBy = 'newest';

    // Apply initial CSS to hide columns that should be hidden
    function applyInitialColumnVisibility() {
        // Initialize all columns as visible by default
        columnVisibility = {
            'schedule_name': true,
            'device': true,
            'layout': true,
            'created_at': true,
            'action': true
        };

        // Get saved column visibility from localStorage if available
        const savedVisibility = localStorage.getItem('scheduleColumnVisibility');
        if (savedVisibility) {
            try {
                const parsed = JSON.parse(savedVisibility);
                // Only update if it's a valid object
                if (parsed && typeof parsed === 'object') {
                    // Ensure we're not mixing up columns - process each column independently
                    for (var column in parsed) {
                        if (column in columnVisibility) {
                            columnVisibility[column] = parsed[column];
                        }
                    }
                    console.log('Applied initial column visibility from localStorage:', columnVisibility);
                }

                // We'll apply CSS to hide columns after DataTable is initialized
                // Store the visibility state for now, and it will be applied when DataTable is created
                Object.keys(columnVisibility).forEach(function (column) {
                    if (!columnVisibility[column]) {
                        console.log('Column will be hidden on initialization:', column);
                    }
                });
            } catch (e) {
                console.error('Error parsing saved column visibility:', e);
            }
        }
    }

    // Apply initial visibility on page load before AJAX
    applyInitialColumnVisibility();

    function initializeSelect2() {
        $('select[data-toggle="select2"]').each(function () {
            if (!$(this).hasClass('select2-initialized')) {
                $(this).select2({
                    placeholder: 'Select...',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $(this).closest('.offcanvas-body, .modal-body, body')
                });
                $(this).addClass('select2-initialized');
            }
        });
    }

    initializeSelect2();
    $(document).on('shown.bs.offcanvas', function () {
        setTimeout(initializeSelect2, 100);
    });

    // Track whether user has explicitly selected a date range; default = not picked
    $('#reportrange').data('picked', false);

    // Function to save column visibility preference
    function saveColumnVisibility(column, isVisible) {
        console.log('Saving column visibility for:', column, 'to:', isVisible);

        // Update local state for the specific column only
        columnVisibility[column] = isVisible;

        // Save to localStorage for immediate use on next page load
        localStorage.setItem('scheduleColumnVisibility', JSON.stringify(columnVisibility));

        // Save to server
        $.ajax({
            url: 'columns',
            type: 'POST',
            data: {
                table: 'schedules',
                column_name: column,  // Only save this specific column
                is_show: isVisible ? 1 : 0  // Convert boolean to 1/0 for Laravel validation
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                console.log(`Column visibility for ${column} saved:`, response);
            },
            error: function (xhr) {
                console.error(`Error saving column visibility for ${column}:`, xhr);
                console.log('Response:', xhr.responseJSON);

                // Revert the UI change if server save fails
                $('.column-visibility-toggle[data-column="' + column + '"]').prop('checked', !isVisible);

                // Also revert the local state and localStorage
                columnVisibility[column] = !isVisible;
                localStorage.setItem('scheduleColumnVisibility', JSON.stringify(columnVisibility));

                // Revert the DataTable column visibility if available
                var columnIndex = scheduleTable ? scheduleTable.column(function (idx, data, node) {
                    return data.name === column;
                }).index() : undefined;
                if (columnIndex !== undefined) {
                    scheduleTable.column(columnIndex).visible(!isVisible);
                }

                // Show error to user
                alert(`Failed to save column preference for ${column}. Please try again.`);
            }
        });
    }

    // Function to load column visibility preferences
    function loadColumnVisibility() {
        return $.ajax({
            url: 'columns',
            type: 'GET',
            data: { table: 'schedules' },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                // Initialize all columns as visible by default
                columnVisibility = {
                    'schedule_name': true,
                    'device': true,
                    'layout': true,
                    'created_at': true,
                    'action': true
                };

                // Update with saved preferences
                if (response.success && response.columns && response.columns.length > 0) {
                    response.columns.forEach(function (col) {
                        // Convert to boolean if needed
                        let isVisible = col.is_show;
                        if (typeof isVisible !== 'boolean') {
                            isVisible = isVisible === 1 || isVisible === '1' || isVisible === true || isVisible === 'true';
                        }

                        // Ensure we're updating the correct column and not affecting others
                        if (col.column_name in columnVisibility) {
                            // Important: Only update the specific column, don't affect others
                            columnVisibility[col.column_name] = isVisible;
                        }
                    });

                    // Save to localStorage for immediate use on next page load
                    localStorage.setItem('scheduleColumnVisibility', JSON.stringify(columnVisibility));
                } else {
                    // If no server data, try to load from localStorage
                    const savedVisibility = localStorage.getItem('scheduleColumnVisibility');
                    if (savedVisibility) {
                        try {
                            const parsed = JSON.parse(savedVisibility);
                            if (parsed && typeof parsed === 'object') {
                                // Merge with defaults, only updating existing columns
                                for (var column in parsed) {
                                    if (column in columnVisibility) {
                                        columnVisibility[column] = parsed[column];
                                    }
                                }
                                console.log('Applied column visibility from localStorage:', columnVisibility);
                            }
                        } catch (e) {
                            console.error('Error parsing saved column visibility:', e);
                        }
                    }
                }

                // Update toggle switches in the UI
                updateColumnToggles();
                console.log('Loaded column visibility:', columnVisibility);
            },
            error: function (xhr) {
                console.error('Error loading column visibility:', xhr);
                console.log('Response:', xhr.responseJSON);

                // On error, try to load from localStorage as fallback
                const savedVisibility = localStorage.getItem('scheduleColumnVisibility');
                if (savedVisibility) {
                    try {
                        const parsed = JSON.parse(savedVisibility);
                        if (parsed && typeof parsed === 'object') {
                            // Merge with defaults, only updating existing columns
                            for (var column in parsed) {
                                if (column in columnVisibility) {
                                    columnVisibility[column] = parsed[column];
                                }
                            }
                            console.log('Applied column visibility from localStorage (fallback):', columnVisibility);
                        }
                    } catch (e) {
                        console.error('Error parsing saved column visibility:', e);
                    }
                }

                // Update toggle switches in the UI
                updateColumnToggles();
            }
        });
    }

    // Function to update column toggle switches based on loaded preferences
    function updateColumnToggles() {
        console.log('Updating column toggles with current state:', columnVisibility);

        // Update each toggle based on current visibility state
        // Process each column independently to avoid any cross-influence
        $('.column-visibility-toggle').each(function () {
            const column = $(this).data('column');
            if (column in columnVisibility) {
                $(this).prop('checked', columnVisibility[column]);
                console.log('Setting toggle for', column, 'to', columnVisibility[column]);
            }
        });
    }

    // Load column visibility preferences before initializing DataTable
    // Ensure the table initializes even if the columns API fails
    var loadReq = loadColumnVisibility();
    if (loadReq && typeof loadReq.always === 'function') {
        loadReq.always(function () {
            if ($('#scheduleslist').length > 0) {
                initializeDataTable();
            }
        });
    } else {
        // Fallback in case $.ajax compatibility changes
        if ($('#scheduleslist').length > 0) {
            initializeDataTable();
        }
    }

    function initializeDataTable() {
        // Show loading indicator
        $('#error-container').hide();

        // Initialize the DataTable - use window scope to ensure it's accessible everywhere
        window.scheduleTable = $('#scheduleslist').DataTable({
            processing: true,
            serverSide: true,
            bFilter: false,
            bInfo: false,
            ordering: true,
            autoWidth: true,
            order: [[0, 'asc']],
            orderCellsTop: true,
            ajax: {
                url: '/schedules/data',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function (xhr) {
                    console.log('Sending AJAX request to:', this.url);
                    console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
                },
                data: function (d) {
                    // Add custom filter parameters
                    d.name_filter = $('.schedule-filter[data-column="schedule_name"]').val();
                    d.device_filter = $('.schedule-filter[data-column="device"]').val();

                    // Add date range filter parameters only when user has picked a range
                    if ($('#reportrange').data('picked') === true) {
                        var dateRange = $('#reportrange span').text().split(' - ');
                        if (dateRange.length === 2) {
                            d.start_date = moment(dateRange[0], 'D MMM YY').format('YYYY-MM-DD');
                            d.end_date = moment(dateRange[1], 'D MMM YY').format('YYYY-MM-DD');
                        }
                    }

                    // Add sort by parameter
                    d.sort_by = window.currentSortBy || 'newest';

                    return d;
                },
                error: function (xhr, error, thrown) {
                    console.error('DataTable AJAX error:', xhr.responseText);
                    console.error('Error details:', error, thrown);

                    // Hide loading indicator
                    $('.data-loading').hide();

                    // Show error container
                    $('#error-container').show();

                    // Set error message
                    let errorMessage = 'Failed to load schedule data. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 0) {
                        errorMessage = 'Network error. Please check your connection.';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Schedules endpoint not found. Please contact administrator.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Please try again later.';
                    }

                    $('#error-message').text(errorMessage);

                    // Show a more user-friendly error dialog
                    if (typeof alert !== 'undefined') {
                        alert(errorMessage);
                    }
                }
            },
            columns: [
                { data: 'schedule_name', name: 'schedule_name', orderable: true },
                { data: 'device', name: 'device', orderable: true },
                { data: 'layout', name: 'layout', orderable: true },
                { data: 'created_at', name: 'created_at', orderable: true, render: function (data) { return data ? new Date(data).toLocaleString() : 'N/A'; } },
                {
                    data: 'id', orderable: false, name: 'action', render: function (data) {
                        let actions = [];

                        // Check permissions and add actions accordingly
                        if (window.schedulePermissions && window.schedulePermissions.view) {
                            actions.push(`<a class="dropdown-item" href="/schedule/${data}"><i class="ti ti-eye me-2"></i>View</a>`);
                        }

                        if (window.schedulePermissions && window.schedulePermissions.edit) {
                            actions.push(`<a class="dropdown-item edit-schedule" href="javascript:void(0);" data-id="${data}"><i class="ti ti-edit text-blue"></i> Edit</a>`);
                        }

                        if (window.schedulePermissions && window.schedulePermissions.delete) {
                            actions.push(`<a class="dropdown-item delete-schedule" href="javascript:void(0);" data-id="${data}"><i class="ti ti-trash me-2"></i>Delete</a>`);
                        }

                        // If no actions available, return empty
                        if (actions.length === 0) {
                            return '<span class="text-muted">No actions</span>';
                        }

                        return `
                        <div class="dropdown dropdown-action">
                            <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></a>
                            <div class="dropdown-menu dropdown-menu-end">
                                ${actions.join('')}
                            </div>
                        </div>`;
                    }
                }
            ],
            language: {
                search: ' ', sLengthMenu: '_MENU_', searchPlaceholder: 'Search', info: '_START_ - _END_ of _TOTAL_ items', lengthMenu: 'Show _MENU_ entries',
                paginate: { next: '<i class="ti ti-chevron-right"></i> ', previous: '<i class="ti ti-chevron-left"></i> ' },
            },
            initComplete: function (settings, json) {
                // Hide loading indicator
                $('.data-loading').hide();

                $('.dataTables_paginate').appendTo('.datatable-paginate');
                $('.dataTables_length').appendTo('.datatable-length');
                $('#error-container').hide();

                // Apply column visibility after DataTable is fully initialized
                setTimeout(function () {
                    Object.keys(columnVisibility).forEach(function (column) {
                        const isVisible = columnVisibility[column];

                        // Find the correct column index by matching the column name
                        let columnIndex = null;
                        scheduleTable.columns().every(function (index) {
                            const colName = this.settings()[0].aoColumns[index].name;
                            if (colName === column) {
                                columnIndex = index;
                                return false; // Break the loop
                            }
                        });

                        if (columnIndex !== null) {
                            // Set column visibility in DataTable
                            scheduleTable.column(columnIndex).visible(isVisible, false);
                            console.log('Applied column visibility in initComplete for column:', column, 'with index:', columnIndex, 'to:', isVisible);
                        } else {
                            console.error('Column not found for visibility in initComplete:', column);
                        }
                    });

                    // Redraw the table after applying all column visibility changes
                    scheduleTable.columns.adjust().draw(false);
                }, 200);

                // Initialize sort indicators for default sort
                const initialOrder = this.api().order();
                console.log('Initial order data structure:', JSON.stringify(initialOrder));
                console.log('Available headers at init:', $('#scheduleslist thead th').length);

                if (initialOrder && initialOrder.length > 0) {
                    const columnIndex = initialOrder[0][0];
                    const direction = initialOrder[0][1];
                    console.log('Initial sorting column index:', columnIndex, 'direction:', direction);

                    // Delay the indicator update slightly to ensure the DOM is ready
                    setTimeout(function () {
                        updateSortIndicators(columnIndex, direction);
                    }, 100);
                } else {
                    console.log('No initial order information available');
                }

                // Add click event for sorting after initialization
                this.api().on('order.dt', function (e, settings) {
                    // Use the API instance provided by the event context
                    const api = new $.fn.dataTable.Api(settings);
                    const order = api.order();
                    console.log('DataTable order event triggered');
                    console.log('Order data structure:', JSON.stringify(order));

                    if (order && order.length > 0) {
                        const columnIndex = parseInt(order[0][0]);
                        const direction = order[0][1];
                        console.log('Sorting column index:', columnIndex, 'direction:', direction);
                        console.log('Available headers:', $('#scheduleslist thead th').length);
                        updateSortIndicators(columnIndex, direction);
                    } else {
                        console.error('No order information available');
                    }
                });
            }
        });

        // Store DataTable instance in window for global access
        window.scheduleDataTable = scheduleTable;

        // Handle column visibility toggle
        $(document).on('change', '.column-visibility-toggle', function (e) {
            // Stop event propagation to prevent affecting other columns
            e.stopPropagation();

            const column = $(this).data('column');
            const isVisible = $(this).prop('checked');

            // Find the correct column index by matching the column name
            let columnIndex = null;
            scheduleTable.columns().every(function (index) {
                const colData = this.dataSrc();
                const colName = this.settings()[0].aoColumns[index].name;
                if (colName === column) {
                    columnIndex = index;
                    return false; // Break the loop
                }
            });

            console.log('Toggling column visibility for:', column, 'with index:', columnIndex, 'to:', isVisible);

            if (columnIndex !== null) {
                // Remove any existing style for this column
                $(`style#column-style-${column}`).remove();

                // Update DataTable column visibility using the API
                scheduleTable.column(columnIndex).visible(isVisible, false);

                // Adjust the table layout after changing visibility
                scheduleTable.columns.adjust().draw(false);

                // Save preference to database for this column only
                saveColumnVisibility(column, isVisible);
            } else {
                console.error('Column not found:', column);
            }
        });

        // Add event listeners for live filtering
        $('.schedule-filter').on('keyup', function () {
            $('#error-container').hide();
            scheduleTable.ajax.reload();
        });

        // Add event listener for date range picker
        $('#reportrange').on('apply.daterangepicker', function (ev, picker) {
            // Mark that date filter is active
            $('#reportrange').data('picked', true);
            $('#error-container').hide();
            scheduleTable.ajax.reload();
        });

        // Add event listener for sort options
        $(document).on('click', '.sort-option', function () {
            const sortBy = $(this).data('sort');
            const sortText = $(this).text();
            window.currentSortBy = sortBy;

            // Update the dropdown button text to show current sort option
            $('.dropdown-toggle.btn-outline-light').first().html(`<i class="ti ti-sort-ascending-2 me-2"></i>${sortText}`);

            // Show loading and reload the DataTable with the new sort option
            $('#error-container').hide();
            scheduleTable.ajax.reload();
        });

        // Function to update sort indicators in the table header
        function updateSortIndicators(columnIndex, direction) {
            console.log('Updating sort indicators for column:', columnIndex, 'direction:', direction);

            // First, remove all existing sort indicators
            $('#scheduleslist thead th').removeClass('sorting_asc sorting_desc').addClass('sorting');

            // Then, add the appropriate class to the sorted column
            const $thElement = $(`#scheduleslist thead th:eq(${columnIndex})`);
            $thElement.removeClass('sorting');
            $thElement.addClass(direction === 'asc' ? 'sorting_asc' : 'sorting_desc');
        }

        // Handle retry button click
        $(document).on('click', '#retry-load', function () {
            $('#error-container').hide();
            scheduleTable.ajax.reload();
        });

        // Handle create schedule with optimized upload
        $('#create-schedule-form').on('submit', function (e) {
            e.preventDefault();
            console.log('Form submission started');

            // Ensure Schedule Info section is expanded
            $('#basic').collapse('show');

            // Client-side validation
            var scheduleName = $('input[name="schedule_name"]').val();
            var startDate = null; // removed schedule-level dates
            var endDate = null;
            var deviceId = $('select[name="device_id"]').val();

            if (!scheduleName) {
                alert('Please enter a schedule name');
                return;
            }
            // schedule-level dates removed; per-media dates optional
            if (!deviceId) {
                alert('Please select a device');
                return;
            }

            console.log('All required fields validated successfully');

            var submitBtn = $(this).find('button[type="submit"]');
            var originalBtnText = submitBtn.html();
            submitBtn.html('Creating...').prop('disabled', true);

            // Show progress bar
            $('#upload-progress-container').show();
            $('#create-form-alert').hide();
            updateProgressBar(0, 'Preparing upload...');

            // Check for large video files and use chunked upload
            var hasLargeFiles = false;
            var mediaFiles = [];
            $('input[name="media_file[]"]').each(function (index) {
                if (this.files[0]) {
                    var file = this.files[0];
                    console.log('File ' + index + ':', file.name, 'Size:', file.size, 'bytes', 'Type:', file.type);

                    if (file.size > 2 * 1024 * 1024 * 1024) { // 2GB limit
                        alert('File ' + file.name + ' is too large. Maximum size is 2GB.');
                        submitBtn.html(originalBtnText).prop('disabled', false);
                        return false;
                    }

                    if (file.size > 50 * 1024 * 1024 && file.type.startsWith('video/')) { // 50MB for videos
                        hasLargeFiles = true;
                    }

                    mediaFiles.push({
                        file: file,
                        index: index,
                        title: $('input[name="media_title[]"]').eq(index).val(),
                        type: $('select[name="media_type[]"]').eq(index).val(),
                        screenId: $('select[name="media_screen_id[]"]').eq(index).val(),
                        duration: $('input[name="media_duration_seconds[]"]').eq(index).val(),
                        startDate: $('input[name="media_start_date_time[]"]').eq(index).val(),
                        endDate: $('input[name="media_end_date_time[]"]').eq(index).val(),
                        playForever: $('input[name="media_play_forever[]"]').eq(index).is(':checked')
                    });
                }
            });

            if (hasLargeFiles) {
                // Use chunked upload for large files
                uploadWithChunkedUpload(this, mediaFiles, submitBtn, originalBtnText);
            } else {
                // Use regular upload for smaller files
                uploadWithRegularMethod(this, submitBtn, originalBtnText);
            }
        });

        // Edit schedule
        $(document).on('click', '.edit-schedule', function () {
            const id = $(this).data('id');
            if (!id) return;

            // Show loading state
            $('#offcanvas_edit').offcanvas('show');

            // Fetch schedule data
            $.ajax({
                url: `/schedule/${id}/edit`,
                type: 'GET',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    console.log('Schedule edit response:', response);
                    if (response.success && response.schedule) {
                        const schedule = response.schedule;

                        // Populate form fields
                        $('#edit-schedule_name').val(schedule.schedule_name || '');

                        // Format datetime values for datetime-local input (preserve local timezone)
                        const firstMedia = (schedule.medias && schedule.medias.length > 0) ? schedule.medias[0] : null;
                        if (firstMedia && firstMedia.schedule_start_date_time) {
                            const startDate = new Date(firstMedia.schedule_start_date_time);
                            // Use local timezone formatting instead of UTC
                            const year = startDate.getFullYear();
                            const month = String(startDate.getMonth() + 1).padStart(2, '0');
                            const day = String(startDate.getDate()).padStart(2, '0');
                            const hours = String(startDate.getHours()).padStart(2, '0');
                            const minutes = String(startDate.getMinutes()).padStart(2, '0');
                            const startFormatted = `${year}-${month}-${day}T${hours}:${minutes}`;
                            $('#edit-schedule_start_date_time').val(startFormatted);
                        } else {
                            $('#edit-schedule_start_date_time').val('');
                        }

                        if (firstMedia && firstMedia.schedule_end_date_time) {
                            const endDate = new Date(firstMedia.schedule_end_date_time);
                            // Use local timezone formatting instead of UTC
                            const year = endDate.getFullYear();
                            const month = String(endDate.getMonth() + 1).padStart(2, '0');
                            const day = String(endDate.getDate()).padStart(2, '0');
                            const hours = String(endDate.getHours()).padStart(2, '0');
                            const minutes = String(endDate.getMinutes()).padStart(2, '0');
                            const endFormatted = `${year}-${month}-${day}T${hours}:${minutes}`;
                            $('#edit-schedule_end_date_time').val(endFormatted);
                        } else {
                            $('#edit-schedule_end_date_time').val('');
                        }

                        // Set device
                        if (schedule.device_id) {
                            $('#edit-device_id').val(schedule.device_id).trigger('change');
                        }

                        // Set play_forever checkbox from first media
                        if (firstMedia && firstMedia.play_forever) {
                            $('#edit-play_forever').prop('checked', true);
                        } else {
                            $('#edit-play_forever').prop('checked', false);
                        }

                        // Set form action
                        $('#edit-schedule-form').attr('action', `/schedule/${id}`);

                        // Load layouts after device is set
                        if (schedule.device_id) {
                            loadLayoutsForEdit(schedule.device_id, schedule.layout_id);
                        }

                        // Load existing media data
                        loadExistingMedia(schedule.medias || []);

                        // Load screens for existing media after a short delay to ensure DOM is ready
                        setTimeout(function () {
                            if (schedule.device_id) {
                                loadScreensForEditMedia(schedule.device_id, schedule.layout_id);
                            }
                        }, 100);
                    } else {
                        alert('Failed to load schedule data');
                        $('#offcanvas_edit').offcanvas('hide');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Schedule edit error:', xhr, status, error);
                    alert('Failed to load schedule data. Please try again.');
                    $('#offcanvas_edit').offcanvas('hide');
                }
            });
        });

        // Delete schedule
        $(document).on('click', '.delete-schedule', function () {
            const id = $(this).data('id');
            if (!id) return;
            if (confirm('Are you sure you want to delete this schedule?')) {
                $.ajax({
                    url: `/schedule/${id}`, type: 'DELETE', headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (response.success) {
                            scheduleTable.ajax.reload(null, false);
                            alert('Schedule deleted successfully!');
                        } else {
                            alert(response.message || 'Failed to delete schedule.');
                        }
                    },
                    error: function () { alert('Failed to delete schedule. Please try again.'); }
                });
            }
        });

        // Helper function to load existing media data
        function loadExistingMedia(medias) {
            const $container = $('#edit-media-container');
            $container.empty();

            // Local datetime formatter to avoid UTC shifts
            function formatLocalDateTime(dateString) {
                if (!dateString) return '';
                const d = new Date(dateString);
                if (isNaN(d.getTime())) return '';
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                const hh = String(d.getHours()).padStart(2, '0');
                const mm = String(d.getMinutes()).padStart(2, '0');
                return `${y}-${m}-${day}T${hh}:${mm}`;
            }

            if (medias && medias.length > 0) {
                medias.forEach(function (media, index) {
                    // Prefer server formatted values if provided to match DB exactly
                    const startVal = media.schedule_start_date_time_formatted || formatLocalDateTime(media.schedule_start_date_time);
                    const endVal = media.schedule_end_date_time_formatted || formatLocalDateTime(media.schedule_end_date_time);
                    const mediaHtml = `
                        <div class="media-item border rounded p-3 mb-3" data-media-index="${index}">
                            <input type="hidden" name="edit_media_id[]" value="${media.id}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Media Title</label>
                                        <input type="text" class="form-control" name="edit_media_title[]"
                                            value="${media.title || ''}" placeholder="Enter media title">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Media Type</label>
                                        <select class="form-control select2" name="edit_media_type[]"
                                            data-toggle="select2">
                                            <option value="">Select type...</option>
                                            <option value="image" ${media.media_type === 'image' ? 'selected' : ''}>Image</option>
                                            <option value="video" ${media.media_type === 'video' ? 'selected' : ''}>Video</option>
                                            <option value="audio" ${media.media_type === 'audio' ? 'selected' : ''}>Audio</option>
                                            <option value="mp4" ${media.media_type === 'mp4' ? 'selected' : ''}>MP4</option>
                                            <option value="png" ${media.media_type === 'png' ? 'selected' : ''}>PNG</option>
                                            <option value="jpg" ${media.media_type === 'jpg' ? 'selected' : ''}>JPG</option>
                                            <option value="pdf" ${media.media_type === 'pdf' ? 'selected' : ''}>PDF</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Screen</label>
                                        <select class="form-control select2" name="edit_media_screen_id[]"
                                            data-toggle="select2" data-existing-screen-id="${media.screen_id || ''}">
                                            <option value="">Select screen...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-danger btn-sm w-100 remove-media">
                                            <i class="ti ti-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Duration (seconds)</label>
                                    <input type="number" class="form-control edit-media-duration-input" name="edit_media_duration_seconds[]" min="1" max="86400" value="${media.duration_seconds || ''}" placeholder="e.g. 15">
                                </div>
                            </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Start At</label>
                                        <input type="datetime-local" class="form-control" name="edit_media_start_date_time[]" value="${startVal}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">End At</label>
                                        <input type="datetime-local" class="form-control" name="edit_media_end_date_time[]" value="${endVal}">
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-center">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="edit_media_play_forever[]" value="1" ${media.play_forever ? 'checked' : ''}>
                                        <label class="form-check-label">Play Forever</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Media File</label>
                                        <input type="file" class="form-control edit-media-file-input" name="edit_media_file[]" accept="image/*,video/*,audio/*,.mp4,.png,.jpg,.pdf">
                                        ${media.media_file ? `<small class=\"text-muted\">Current: ${media.media_file}</small>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $container.append(mediaHtml);
                });
            } else {
                // Add one empty media item if no existing media (match dynamic template)
                const mediaHtml = `
                    <div class="media-item border rounded p-3 mb-3">
                        <input type="hidden" name="edit_media_id[]" value="">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Media Title</label>
                                    <input type="text" class="form-control" name="edit_media_title[]" placeholder="Enter media title">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Media Type</label>
                                    <select class="form-control select2" name="edit_media_type[]" data-toggle="select2">
                                        <option value="">Select type...</option>
                                        <option value="image">Image</option>
                                        <option value="video">Video</option>
                                        <option value="audio">Audio</option>
                                        <option value="mp4">MP4</option>
                                        <option value="png">PNG</option>
                                        <option value="jpg">JPG</option>
                                        <option value="pdf">PDF</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Screen</label>
                                    <select class="form-control select2" name="edit_media_screen_id[]" data-toggle="select2">
                                        <option value="">Select screen...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-sm w-100 remove-media">
                                        <i class="ti ti-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Duration (seconds)</label>
                                    <input type="number" class="form-control edit-media-duration-input" name="edit_media_duration_seconds[]" min="1" max="86400" placeholder="e.g. 15">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Start At</label>
                                    <input type="datetime-local" class="form-control" name="edit_media_start_date_time[]">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">End At</label>
                                    <input type="datetime-local" class="form-control" name="edit_media_end_date_time[]">
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="edit_media_play_forever[]" value="1">
                                    <label class="form-check-label">Play Forever</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Media File</label>
                                    <input type="file" class="form-control edit-media-file-input" name="edit_media_file[]" accept="image/*,video/*,audio/*,.mp4,.png,.jpg,.pdf">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $container.append(mediaHtml);
            }

            // Initialize select2 for the new elements
            initializeSelect2();
        }

        // Helper function to load layouts for edit form
        function loadLayoutsForEdit(deviceId, selectedLayoutId) {
            var $layout = $('#edit-layout_id');

            $layout.empty().append('<option value="">Loading...</option>').trigger('change.select2');

            if (!deviceId) {
                $layout.empty().append('<option value="">Select layout...</option>').trigger('change');
                return;
            }

            $.ajax({
                url: '/device/' + deviceId + '/layouts',
                type: 'GET',
                data: { status: 1 },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    $layout.empty().append('<option value="">Select layout...</option>');
                    if (res && res.success && Array.isArray(res.layouts)) {
                        res.layouts.forEach(function (l) {
                            var selected = (l.id == selectedLayoutId) ? ' selected' : '';
                            $layout.append('<option value="' + l.id + '"' + selected + '>' + l.layout_name + '</option>');
                        });
                    }
                    $layout.trigger('change');

                },
                error: function () {
                    $layout.empty().append('<option value="">Failed to load layouts</option>');
                }
            });
        }

        // Helper function to load screens for media items
        function loadScreensForMedia(deviceId, layoutId = null) {
            if (!deviceId) return;

            console.log('Loading screens for device:', deviceId, 'layout:', layoutId);

            $('select[name="media_screen_id[]"]').each(function () {
                var $screen = $(this);
                var previouslySelected = $screen.val();
                $screen.empty().append('<option value="">Loading...</option>');

                var url = layoutId ? '/layout/' + layoutId + '/screens' : '/device/' + deviceId + '/screens';
                console.log('Loading screens from URL:', url);

                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (res) {
                        console.log('Screens loaded successfully:', res);
                        $screen.empty().append('<option value="">Select screen...</option>');
                        if (res && res.success && Array.isArray(res.screens)) {
                            var hasPrev = false;
                            res.screens.forEach(function (s) {
                                var label = 'Screen ' + s.screen_no;
                                if (s.layout && s.layout.layout_name) {
                                    label += ' - ' + s.layout.layout_name;
                                }
                                var selected = (previouslySelected && String(s.id) === String(previouslySelected)) ? ' selected' : '';
                                if (selected) { hasPrev = true; }
                                $screen.append('<option value="' + s.id + '"' + selected + '>' + label + '</option>');
                            });
                            if (!hasPrev && previouslySelected) {
                                console.log('Previously selected screen not in current list, leaving as empty.');
                            }
                            console.log('Added', res.screens.length, 'screens to dropdown');
                        } else {
                            console.log('No screens found or invalid response');
                        }
                        $screen.trigger('change');
                    },
                    error: function (xhr, status, error) {
                        console.error('Error loading screens:', error, xhr.responseText);
                        $screen.empty().append('<option value="">Failed to load screens</option>');
                    }
                });
            });
        }

        // Helper function to load screens for edit media items
        function loadScreensForEditMedia(deviceId, layoutId = null) {
            if (!deviceId) return;

            console.log('Loading screens for edit media - device:', deviceId, 'layout:', layoutId);

            $('select[name="edit_media_screen_id[]"]').each(function () {
                var $screen = $(this);
                var existingScreenId = $screen.data('existing-screen-id') || $screen.val();
                $screen.empty().append('<option value="">Loading...</option>');

                var url = layoutId ? '/layout/' + layoutId + '/screens' : '/device/' + deviceId + '/screens';
                console.log('Loading edit screens from URL:', url, 'existing screen ID:', existingScreenId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (res) {
                        console.log('Edit screens loaded successfully:', res);
                        $screen.empty().append('<option value="">Select screen...</option>');
                        if (res && res.success && Array.isArray(res.screens)) {
                            res.screens.forEach(function (s) {
                                var label = 'Screen ' + s.screen_no;
                                if (s.layout && s.layout.layout_name) {
                                    label += ' - ' + s.layout.layout_name;
                                }
                                var selected = (existingScreenId && String(s.id) === String(existingScreenId)) ? ' selected' : '';
                                $screen.append('<option value="' + s.id + '"' + selected + '>' + label + '</option>');
                            });
                            console.log('Added', res.screens.length, 'screens to edit dropdown, selected screen:', existingScreenId);
                        } else {
                            console.log('No screens found or invalid response for edit');
                        }
                        $screen.trigger('change');
                    },
                    error: function (xhr, status, error) {
                        console.error('Error loading edit screens:', error, xhr.responseText);
                        $screen.empty().append('<option value="">Failed to load screens</option>');
                    }
                });
            });
        }

        // Dependent selects: device -> layouts (active)
        $(document).on('change', 'select[name="device_id"]', function () {
            var deviceId = $(this).val();
            var $layout = $('select[name="layout_id"]');

            // Load screens for all media items (will be updated when layout is selected)
            loadScreensForMedia(deviceId);

            // Clear and reset layout options
            $layout.empty().append('<option value="">Loading...</option>');
            if ($layout.hasClass('select2-hidden-accessible')) {
                $layout.select2('destroy');
            }
            $layout.select2();

            if (!deviceId) {
                $layout.empty().append('<option value="">Select layout...</option>');
                $layout.trigger('change');
                return;
            }

            $.ajax({
                url: '/device/' + deviceId + '/layouts', type: 'GET', data: { status: 1 },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    $layout.empty().append('<option value="">Select layout...</option>');
                    if (res && res.success && Array.isArray(res.layouts)) {
                        res.layouts.forEach(function (l) {
                            $layout.append('<option value="' + l.id + '">' + l.layout_name + '</option>');
                        });
                    }
                    // Refresh Select2 after populating options
                    $layout.trigger('change');
                },
                error: function () {
                    $layout.empty().append('<option value="">Failed to load layouts</option>');
                    $layout.trigger('change');
                }
            });
        });

        // Edit form dependent selects: device -> layouts (active)
        $(document).on('change', '#edit-device_id', function () {
            var deviceId = $(this).val();
            var $layout = $('#edit-layout_id');

            // Load screens for all media items in edit form (will be updated when layout is selected)
            loadScreensForEditMedia(deviceId);

            // Clear and reset layout options
            $layout.empty().append('<option value="">Loading...</option>');
            if ($layout.hasClass('select2-hidden-accessible')) {
                $layout.select2('destroy');
            }
            $layout.select2();

            if (!deviceId) {
                $layout.empty().append('<option value="">Select layout...</option>');
                $layout.trigger('change');
                return;
            }

            $.ajax({
                url: '/device/' + deviceId + '/layouts', type: 'GET', data: { status: 1 },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    $layout.empty().append('<option value="">Select layout...</option>');
                    if (res && res.success && Array.isArray(res.layouts)) {
                        res.layouts.forEach(function (l) {
                            $layout.append('<option value="' + l.id + '">' + l.layout_name + '</option>');
                        });
                    }
                    // Refresh Select2 after populating options
                    $layout.trigger('change');
                },
                error: function () {
                    $layout.empty().append('<option value="">Failed to load layouts</option>');
                    $layout.trigger('change');
                }
            });
        });

        // Function to show notification about available screens for selected layout
        function showLayoutScreensNotification(layoutName, layoutId) {
            // Get layout info to show screen count
            $.ajax({
                url: '/layout/' + layoutId + '/screens',
                type: 'GET',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    if (res && res.success && res.layout_info) {
                        var screenCount = res.screens ? res.screens.length : 0;
                        var maxScreens = res.layout_info.max_screens;
                        var layoutType = res.layout_info.type_name;

                        var message = 'Layout "' + layoutName + '" selected! ';
                        message += 'Available screens: ' + screenCount + '/' + maxScreens + ' (' + layoutType + '). ';
                        message += 'You can now assign media to any of these screens.';

                        // Show toast notification
                        showToast('Layout Selected', message, 'info');
                    }
                },
                error: function () {
                    // Fallback notification
                    showToast('Layout Selected', 'Layout "' + layoutName + '" selected. Screens are now available for media assignment.', 'info');
                }
            });
        }

        // Function to show toast notifications
        function showToast(title, message, type = 'info') {
            // Create toast element
            var toastHtml = `
                <div class="toast align-items-center text-white bg-${type === 'info' ? 'primary' : type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong>${title}</strong><br>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;

            // Add to toast container or create one
            var $toastContainer = $('#toast-container');
            if ($toastContainer.length === 0) {
                $toastContainer = $('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
                $('body').append($toastContainer);
            }

            var $toast = $(toastHtml);
            $toastContainer.append($toast);

            // Initialize and show toast
            var toast = new bootstrap.Toast($toast[0], { delay: 5000 });
            toast.show();

            // Remove toast element after it's hidden
            $toast.on('hidden.bs.toast', function () {
                $(this).remove();
            });
        }

        // Layout change handler for create form
        $(document).on('change', 'select[name="layout_id"]', function () {
            var layoutId = $(this).val();
            var deviceId = $('select[name="device_id"]').val();
            var layoutName = $(this).find('option:selected').text();

            if (layoutId && deviceId) {
                // Load screens for the selected layout
                loadScreensForMedia(deviceId, layoutId);

                // Disabled toast notification for layout selection per UI requirement
                // showLayoutScreensNotification(layoutName, layoutId);
            } else if (deviceId) {
                // If no layout selected, load all screens for the device
                loadScreensForMedia(deviceId);
            }
        });

        // Layout change handler for edit form
        $(document).on('change', '#edit-layout_id', function () {
            var layoutId = $(this).val();
            var deviceId = $('#edit-device_id').val();
            var layoutName = $(this).find('option:selected').text();

            if (layoutId && deviceId) {
                // Load screens for the selected layout
                loadScreensForEditMedia(deviceId, layoutId);

                // Disabled toast notification for layout selection per UI requirement
                // showLayoutScreensNotification(layoutName, layoutId);
            } else if (deviceId) {
                // If no layout selected, load all screens for the device
                loadScreensForEditMedia(deviceId);
            }
        });

        // Handle edit schedule form submission
        $('#edit-schedule-form').on('submit', function (e) {
            e.preventDefault();
            var submitBtn = $(this).find('button[type="submit"]');
            var originalBtnText = submitBtn.html();
            submitBtn.html('Updating...').prop('disabled', true);

            // Pre-validate selected media files size (2GB limit)
            var tooLarge = false;
            $('input[name="edit_media_file[]"]').each(function (index) {
                if (this.files && this.files[0]) {
                    var file = this.files[0];
                    if (file.size > 2 * 1024 * 1024 * 1024) { // 2GB limit
                        alert('File ' + file.name + ' is too large. Maximum size is 2GB.');
                        tooLarge = true;
                        return false; // break .each
                    }
                }
            });
            if (tooLarge) {
                submitBtn.html(originalBtnText).prop('disabled', false);
                return;
            }

            // Use FormData for file uploads
            var formData = new FormData(this);

            // Show progress bar
            $('#edit-upload-progress-container').show();
            $('#edit-form-alert').hide();
            updateEditProgressBar(0, 'Preparing upload...');

            // Debug: Log form data
            console.log('Edit form data:');
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = Math.round(evt.loaded / evt.total * 100);
                            console.log('Edit upload progress: ' + percentComplete + '%');
                            updateEditProgressBar(percentComplete, 'Uploading files... (' + percentComplete + '%)');
                        }
                    }, false);
                    return xhr;
                },
                success: function (response) {
                    // Hide progress bar
                    $('#edit-upload-progress-container').hide();

                    if (response.success) {
                        var $wrap = $('#edit-form-alert');
                        var $alert = $wrap.find('.alert');
                        if ($alert.length === 0) {
                            $wrap.html('<div class="alert alert-success alert-dismissible fade show" role="alert"></div>');
                            $alert = $wrap.find('.alert');
                        }
                        $alert.removeClass('alert-danger').addClass('alert-success');
                        $alert.html('Schedule updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                        $wrap.show();
                        scheduleTable.ajax.reload();
                        setTimeout(function () { $('#offcanvas_edit').offcanvas('hide'); }, 1200);
                    } else {
                        showToast('Update Failed', response.message || 'Failed to update schedule', 'danger');
                    }
                },
                error: function (xhr) {
                    console.error('Schedule update error:', xhr);
                    // Hide progress bar
                    $('#edit-upload-progress-container').hide();

                    const res = xhr.responseJSON;
                    let msg = 'Error updating schedule.';
                    if (res && res.errors) {
                        msg = Object.values(res.errors)[0][0];
                    } else if (res && res.message) {
                        msg = res.message;
                    }
                    showToast('Validation Error', msg, 'danger');
                },
                complete: function () { submitBtn.html(originalBtnText).prop('disabled', false); }
            });
        });

        // Handle add media functionality for create form
        $(document).on('click', '#add-media', function () {
            const mediaHtml = `
                <div class="media-item border rounded p-3 mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Media Title</label>
                                <input type="text" class="form-control" name="media_title[]"
                                    placeholder="Enter media title">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Media Type</label>
                                <select class="form-control select2" name="media_type[]"
                                    data-toggle="select2">
                                    <option value="">Select type...</option>
                                    <option value="image">Image</option>
                                    <option value="video">Video</option>
                                    <option value="audio">Audio</option>
                                    <option value="mp4">MP4</option>
                                    <option value="png">PNG</option>
                                    <option value="jpg">JPG</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Screen</label>
                                <select class="form-control select2" name="media_screen_id[]"
                                    data-toggle="select2">
                                    <option value="">Select screen...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-danger btn-sm w-100 remove-media">
                                    <i class="ti ti-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Duration (seconds)</label>
                                <input type="number" class="form-control media-duration-input" name="media_duration_seconds[]" min="1" max="86400" placeholder="e.g. 15">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Start At</label>
                                <input type="datetime-local" class="form-control" name="media_start_date_time[]">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">End At</label>
                                <input type="datetime-local" class="form-control" name="media_end_date_time[]">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="media_play_forever[]" value="1">
                                <label class="form-check-label">Play Forever</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Media File</label>
                                <input type="file" class="form-control media-file-input" name="media_file[]"
                                    accept="image/*,video/*,audio/*,.mp4,.png,.jpg,.pdf">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#media-container').append(mediaHtml);
            initializeSelect2();

            // Load screens for the newly added media item
            var deviceId = $('select[name="device_id"]').val();
            var layoutId = $('select[name="layout_id"]').val();
            if (deviceId) {
                loadScreensForMedia(deviceId, layoutId);
            }
        });

        // Handle add media functionality for edit form
        $(document).on('click', '#edit-add-media', function () {
            const mediaHtml = `
                <div class="media-item border rounded p-3 mb-3">
                    <input type="hidden" name="edit_media_id[]" value="">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Media Title</label>
                                <input type="text" class="form-control" name="edit_media_title[]"
                                    placeholder="Enter media title">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Media Type</label>
                                <select class="form-control select2" name="edit_media_type[]"
                                    data-toggle="select2">
                                    <option value="">Select type...</option>
                                    <option value="image">Image</option>
                                    <option value="video">Video</option>
                                    <option value="audio">Audio</option>
                                    <option value="mp4">MP4</option>
                                    <option value="png">PNG</option>
                                    <option value="jpg">JPG</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Screen</label>
                                <select class="form-control select2" name="edit_media_screen_id[]"
                                    data-toggle="select2">
                                    <option value="">Select screen...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-danger btn-sm w-100 remove-media">
                                    <i class="ti ti-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Duration (seconds)</label>
                                <input type="number" class="form-control edit-media-duration-input" name="edit_media_duration_seconds[]" min="1" max="86400" placeholder="e.g. 15">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Start At</label>
                                <input type="datetime-local" class="form-control" name="edit_media_start_date_time[]">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">End At</label>
                                <input type="datetime-local" class="form-control" name="edit_media_end_date_time[]">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="edit_media_play_forever[]" value="1">
                                <label class="form-check-label">Play Forever</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Media File</label>
                                <input type="file" class="form-control edit-media-file-input" name="edit_media_file[]"
                                    accept="image/*,video/*,audio/*,.mp4,.png,.jpg,.pdf">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#edit-media-container').append(mediaHtml);
            initializeSelect2();

            // Load screens for the newly added media item
            var deviceId = $('#edit-device_id').val();
            var layoutId = $('#edit-layout_id').val();
            if (deviceId) {
                loadScreensForEditMedia(deviceId, layoutId);
            }
        });

        // Handle remove media functionality
        $(document).on('click', '.remove-media', function () {
            $(this).closest('.media-item').remove();
        });

        // Function to get video duration
        function getVideoDuration(file, callback) {
            const video = document.createElement('video');
            video.preload = 'metadata';

            video.onloadedmetadata = function () {
                window.URL.revokeObjectURL(video.src);
                callback(Math.round(video.duration));
            };

            video.onerror = function () {
                window.URL.revokeObjectURL(video.src);
                callback(null);
            };

            video.src = URL.createObjectURL(file);
        }

        // Handle video file selection for create form
        $(document).on('change', '.media-file-input', function () {
            const file = this.files[0];
            const $mediaItem = $(this).closest('.media-item');
            const $durationInput = $mediaItem.find('.media-duration-input');
            const $mediaTypeSelect = $mediaItem.find('select[name="media_type[]"]');

            if (file && file.type.startsWith('video/')) {
                // Show loading indicator
                $durationInput.prop('disabled', true).val('Loading...');

                getVideoDuration(file, function (duration) {
                    $durationInput.prop('disabled', false);

                    if (duration) {
                        $durationInput.val(duration);
                        // Auto-select video type if not already selected
                        if (!$mediaTypeSelect.val()) {
                            if (file.name.toLowerCase().endsWith('.mp4')) {
                                $mediaTypeSelect.val('mp4').trigger('change');
                            } else {
                                $mediaTypeSelect.val('video').trigger('change');
                            }
                        }

                        // Show success message
                        showToast('Video Duration', `Video duration detected: ${duration} seconds`, 'success');
                    } else {
                        $durationInput.val('').attr('placeholder', 'Could not detect duration');
                        showToast('Error', 'Could not detect video duration. Please enter manually.', 'warning');
                    }
                });
            } else if (file && file.type.startsWith('audio/')) {
                // Handle audio files
                getVideoDuration(file, function (duration) {
                    if (duration) {
                        $durationInput.val(duration);
                        if (!$mediaTypeSelect.val()) {
                            $mediaTypeSelect.val('audio').trigger('change');
                        }
                        showToast('Audio Duration', `Audio duration detected: ${duration} seconds`, 'success');
                    }
                });
            } else {
                // Clear duration for non-video/audio files
                $durationInput.val('');
            }
        });

        // Handle video file selection for edit form
        $(document).on('change', '.edit-media-file-input', function () {
            const file = this.files[0];
            const $mediaItem = $(this).closest('.media-item');
            const $durationInput = $mediaItem.find('.edit-media-duration-input');
            const $mediaTypeSelect = $mediaItem.find('select[name="edit_media_type[]"]');

            if (file && file.type.startsWith('video/')) {
                // Show loading indicator
                $durationInput.prop('disabled', true).val('Loading...');

                getVideoDuration(file, function (duration) {
                    $durationInput.prop('disabled', false);

                    if (duration) {
                        $durationInput.val(duration);
                        // Auto-select video type if not already selected
                        if (!$mediaTypeSelect.val()) {
                            if (file.name.toLowerCase().endsWith('.mp4')) {
                                $mediaTypeSelect.val('mp4').trigger('change');
                            } else {
                                $mediaTypeSelect.val('video').trigger('change');
                            }
                        }

                        // Show success message
                        showToast('Video Duration', `Video duration detected: ${duration} seconds`, 'success');
                    } else {
                        $durationInput.val('').attr('placeholder', 'Could not detect duration');
                        showToast('Error', 'Could not detect video duration. Please enter manually.', 'warning');
                    }
                });
            } else if (file && file.type.startsWith('audio/')) {
                // Handle audio files
                getVideoDuration(file, function (duration) {
                    if (duration) {
                        $durationInput.val(duration);
                        if (!$mediaTypeSelect.val()) {
                            $mediaTypeSelect.val('audio').trigger('change');
                        }
                        showToast('Audio Duration', `Audio duration detected: ${duration} seconds`, 'success');
                    }
                });
            } else {
                // Clear duration for non-video/audio files
                $durationInput.val('');
            }
        });

        // Handle refresh screens button
        $(document).on('click', '#refresh-screens', function () {
            var deviceId = $('select[name="device_id"]').val();
            var layoutId = $('select[name="layout_id"]').val();

            if (!deviceId) {
                showToast('Error', 'Please select a device first', 'danger');
                return;
            }

            if (layoutId) {
                loadScreensForMedia(deviceId, layoutId);
                showToast('Screens Refreshed', 'Screens for the selected layout have been refreshed', 'success');
            } else {
                loadScreensForMedia(deviceId);
                showToast('Screens Refreshed', 'All device screens have been refreshed', 'success');
            }
        });

        // Handle refresh edit screens button
        $(document).on('click', '#refresh-edit-screens', function () {
            var deviceId = $('#edit-device_id').val();
            var layoutId = $('#edit-layout_id').val();

            if (!deviceId) {
                showToast('Error', 'Please select a device first', 'danger');
                return;
            }

            if (layoutId) {
                loadScreensForEditMedia(deviceId, layoutId);
                showToast('Screens Refreshed', 'Screens for the selected layout have been refreshed', 'success');
            } else {
                loadScreensForEditMedia(deviceId);
                showToast('Screens Refreshed', 'All device screens have been refreshed', 'success');
            }
        });

        // Reset forms once the offcanvas fully closes
        $(document).on('hidden.bs.offcanvas', '#offcanvas_add', function () {
            var $form = $('#create-schedule-form');
            if ($form.length) {
                try {
                    $form[0].reset();
                    // Hide alerts and progress bar
                    $('#create-form-alert').hide();
                    $('#upload-progress-container').hide();
                } catch (err) {
                    console.error('Error resetting create form after close:', err);
                }
            }
        });

        $(document).on('hidden.bs.offcanvas', '#offcanvas_edit', function () {
            var $form = $('#edit-schedule-form');
            if ($form.length) {
                try {
                    $form[0].reset();
                    $('#edit-form-alert').hide();
                    $('#edit-upload-progress-container').hide();
                } catch (err) {
                    console.error('Error resetting edit form after close:', err);
                }
            }
        });
    }

    // Progress bar update functions
    function updateProgressBar(percent, status) {
        const progressBar = $('#upload-progress-bar');
        const progressText = $('#upload-progress-text');
        const statusText = $('#upload-status-text');

        // Update progress bar width
        progressBar.css('width', percent + '%').attr('aria-valuenow', percent);
        progressText.text(percent + '%');
        statusText.text(status);

        // Add dynamic color classes based on progress
        progressBar.removeClass('progress-success progress-warning progress-error');

        if (percent >= 100) {
            progressBar.addClass('progress-success');
            statusText.css('color', '#28a745');
        } else if (percent >= 75) {
            progressBar.addClass('progress-warning');
            statusText.css('color', '#ffc107');
        } else if (percent >= 50) {
            statusText.css('color', '#17a2b8');
        } else if (percent >= 25) {
            statusText.css('color', '#6c757d');
        } else {
            statusText.css('color', '#6c757d');
        }

        // Add pulsing effect for active uploads
        if (percent > 0 && percent < 100) {
            progressBar.css('animation', 'pulse 1.5s ease-in-out infinite alternate');
        } else {
            progressBar.css('animation', 'none');
        }
    }

    function updateEditProgressBar(percent, status) {
        $('#edit-upload-progress-bar').css('width', percent + '%').attr('aria-valuenow', percent);
        $('#edit-upload-progress-text').text(percent + '%');
        $('#edit-upload-status-text').text(status);
    }

    // Chunked upload function for large files
    async function uploadWithChunkedUpload(form, mediaFiles, submitBtn, originalBtnText) {
        try {
            updateProgressBar(0, 'Initializing chunked upload...');

            const uploadPromises = [];
            const uploadedFiles = [];

            for (let i = 0; i < mediaFiles.length; i++) {
                const mediaFile = mediaFiles[i];
                const file = mediaFile.file;

                if (file.size > 50 * 1024 * 1024 && file.type.startsWith('video/')) {
                    // Use chunked upload for large video files
                    const uploader = new ChunkedUploader({
                        chunkSize: 2 * 1024 * 1024, // 2MB chunks
                        maxConcurrentChunks: 3,
                        onProgress: (progress) => {
                            const totalProgress = ((i / mediaFiles.length) * 100) + ((progress.percentage / mediaFiles.length));
                            updateProgressBar(Math.round(totalProgress), `Uploading ${file.name}... (${progress.percentage}%)`);
                        },
                        onError: (error) => {
                            throw new Error(`Failed to upload ${file.name}: ${error.message}`);
                        }
                    });

                    const promise = uploader.uploadFile(file, '/schedule/chunked-upload').then(result => {
                        console.log('Chunked upload completed for:', file.name, result);
                        return {
                            ...mediaFile,
                            file_path: result.file_path,
                            file_name: result.file_name
                        };
                    });
                    uploadPromises.push(promise);

                    console.log('Added chunked upload promise for file:', file.name);
                } else {
                    // Use single upload for smaller files
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('single_upload', 'true');

                    const promise = fetch('/schedule/chunked-upload', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: formData
                    }).then(async response => {
                        if (!response.ok) {
                            throw new Error(`Failed to upload ${file.name}`);
                        }

                        const result = await response.json();
                        console.log('Single upload completed for:', file.name, result);
                        return {
                            ...mediaFile,
                            file_path: result.file_path,
                            file_name: result.file_name
                        };
                    });

                    uploadPromises.push(promise);
                }
            }

            // Wait for all uploads to complete
            console.log('Waiting for', uploadPromises.length, 'upload promises to complete...');
            const uploadResults = await Promise.all(uploadPromises);
            console.log('All uploads completed. Upload results:', uploadResults);

            // Add the upload results to uploadedFiles
            uploadedFiles.push(...uploadResults);

            updateProgressBar(90, 'Creating schedule...');

            // Now create the schedule with uploaded file paths
            const scheduleData = {
                schedule_name: $('input[name="schedule_name"]').val(),
                device_id: $('select[name="device_id"]').val(),
                layout_id: $('select[name="layout_id"]').val(),
                medias: uploadedFiles.map(media => ({
                    title: media.title,
                    media_type: media.type,
                    screen_id: media.screenId,
                    duration_seconds: media.duration,
                    start_date_time: media.startDate,
                    end_date_time: media.endDate,
                    play_forever: media.playForever,
                    media_file: media.file_path
                }))
            };

            console.log('Sending schedule data:', scheduleData);

            const response = await fetch('/schedule', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify(scheduleData)
            });

            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Response error:', errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const result = await response.json();
            console.log('Response result:', result);

            updateProgressBar(100, 'Complete!');

            if (result.success) {
                var $wrap = $('#create-form-alert');
                var $alert = $wrap.find('.alert');
                if ($alert.length === 0) {
                    $wrap.html('<div class="alert alert-success alert-dismissible fade show" role="alert"></div>');
                    $alert = $wrap.find('.alert');
                }
                $alert.removeClass('alert-danger').addClass('alert-success');
                $alert.html('Schedule created successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                $wrap.show();
                $('#create-schedule-form')[0].reset();
                scheduleTable.ajax.reload();
                setTimeout(function () { $('#offcanvas_add').offcanvas('hide'); }, 1200);
            } else {
                throw new Error(result.message || 'Failed to create schedule');
            }

        } catch (error) {
            console.error('Chunked upload error:', error);
            showFormAlert('#create-form-alert', 'danger', 'Upload failed: ' + error.message);
        } finally {
            $('#upload-progress-container').hide();
            submitBtn.html(originalBtnText).prop('disabled', false);
        }
    }

    // Regular upload function for smaller files
    function uploadWithRegularMethod(form, submitBtn, originalBtnText) {
        // Use FormData for file uploads
        var formData = new FormData(form);

        // Debug: Log form data
        console.log('Form data being sent:');
        for (var pair of formData.entries()) {
            if (pair[1] instanceof File) {
                console.log(pair[0] + ': [File] ' + pair[1].name + ' (' + pair[1].size + ' bytes)');
            } else {
                console.log(pair[0] + ': ' + pair[1]);
            }
        }

        $.ajax({
            url: '/schedule',
            type: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            timeout: 0, // no timeout for large uploads
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            beforeSend: function () {
                console.log('AJAX request being sent to /schedule');
            },
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.round(evt.loaded / evt.total * 100);
                        console.log('Upload progress: ' + percentComplete + '%');
                        updateProgressBar(percentComplete, 'Uploading files... (' + percentComplete + '%)');
                    }
                }, false);
                return xhr;
            },
            success: function (response) {
                console.log('Schedule creation response:', response);
                // Hide progress bar
                $('#upload-progress-container').hide();

                if (response.success) {
                    var $wrap = $('#create-form-alert');
                    var $alert = $wrap.find('.alert');
                    if ($alert.length === 0) { $wrap.html('<div class="alert alert-success alert-dismissible fade show" role="alert"></div>'); $alert = $wrap.find('.alert'); }
                    $alert.removeClass('alert-danger').addClass('alert-success');
                    $alert.html('Schedule created successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                    $wrap.show();
                    $('#create-schedule-form')[0].reset();
                    scheduleTable.ajax.reload();
                    setTimeout(function () { $('#offcanvas_add').offcanvas('hide'); }, 1200);
                } else {
                    showFormAlert('#create-form-alert', 'danger', response.message || 'Failed to create schedule');
                }
            },
            error: function (xhr) {
                console.error('Schedule creation error:', xhr);
                // Hide progress bar
                $('#upload-progress-container').hide();

                const res = xhr.responseJSON;
                let msg = 'Error creating schedule.';
                if (res && res.errors) {
                    // Show all validation errors
                    const errorMessages = [];
                    Object.keys(res.errors).forEach(field => {
                        errorMessages.push(`${field}: ${res.errors[field][0]}`);
                    });
                    msg = errorMessages.join('<br>');
                } else if (res && res.message) {
                    msg = res.message;
                } else {
                    // Check for common issues
                    if (xhr.status === 422) {
                        msg = 'Validation failed. Please check all required fields are filled.';
                    } else if (xhr.status === 500) {
                        msg = 'Server error. Please try again.';
                    } else if (xhr.status === 0) {
                        msg = 'Network error. Please check your connection.';
                    } else if (xhr.statusText === 'timeout') {
                        msg = 'Request timed out. Please try again.';
                    }
                }
                showFormAlert('#create-form-alert', 'danger', msg);
            },
            complete: function (xhr, status) {
                console.log('AJAX request completed with status:', status);
                submitBtn.html(originalBtnText).prop('disabled', false);
            }
        });
    }

    // Helper to show inline alerts within a wrapper (e.g., #create-form-alert or #edit-form-alert)
    function showFormAlert(wrapperSelector, type, message) {
        var $wrap = $(wrapperSelector);
        var $alert = $wrap.find('.alert');
        if ($alert.length === 0) {
            $wrap.html('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert"></div>');
            $alert = $wrap.find('.alert');
        }
        $alert.removeClass('alert-success alert-danger alert-warning alert-info').addClass('alert-' + type);
        $alert.html(message + ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
        $wrap.show();
    }
});


