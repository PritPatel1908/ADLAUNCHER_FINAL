$(document).ready(function () {
    // Initialize column visibility from database
    let columnVisibility = {};

    // Set default sort option
    window.currentSortBy = 'newest';

    // Apply initial CSS to hide columns that should be hidden
    function applyInitialColumnVisibility() {
        // Initialize all columns as visible by default
        columnVisibility = {
            'name': true,
            'unique_id': true,
            'company': true,
            'location': true,
            'area': true,
            'ip': true,
            // 'layouts': false,
            'layouts_count': true,
            'screens_count': true,
            'created_at': true,
            'updated_at': true,
            'status': true,
            'action': true
        };

        // Get saved column visibility from localStorage if available
        const savedVisibility = localStorage.getItem('userColumnVisibility');
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

    // Function to load column visibility preferences
    function loadColumnVisibility() {
        return $.ajax({
            url: 'columns',
            type: 'GET',
            data: { table: 'devices' },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                // Initialize all columns as visible by default
                columnVisibility = {
                    'name': true,
                    'unique_id': true,
                    'company': true,
                    'location': true,
                    'area': true,
                    'ip': true,
                    // 'layouts': false,
                    'layouts_count': true,
                    'screens_count': true,
                    'created_at': true,
                    'updated_at': true,
                    'status': true,
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
                    localStorage.setItem('userColumnVisibility', JSON.stringify(columnVisibility));
                } else {
                    // If no server data, try to load from localStorage
                    const savedVisibility = localStorage.getItem('userColumnVisibility');
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
                const savedVisibility = localStorage.getItem('userColumnVisibility');
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

    // Function to save column visibility preference
    function saveColumnVisibility(column, isVisible) {
        console.log('Saving column visibility for:', column, 'to:', isVisible);

        // Update local state for the specific column only
        columnVisibility[column] = isVisible;

        // Save to localStorage for immediate use on next page load
        localStorage.setItem('userColumnVisibility', JSON.stringify(columnVisibility));

        // Save to server
        $.ajax({
            url: 'columns',
            type: 'POST',
            data: {
                table: 'devices',
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
                localStorage.setItem('userColumnVisibility', JSON.stringify(columnVisibility));

                // Revert the DataTable column visibility if available
                var columnIndex = dataTable ? dataTable.column(function (idx, data, node) {
                    return data.name === column;
                }).index() : undefined;
                if (columnIndex !== undefined) {
                    dataTable.column(columnIndex).visible(!isVisible);
                }

                // Show error to user
                alert(`Failed to save column preference for ${column}. Please try again.`);
            }
        });
    }

    // Load column visibility preferences before initializing DataTable
    // Ensure the table initializes even if the columns API fails
    var loadReq = loadColumnVisibility();
    if (loadReq && typeof loadReq.always === 'function') {
        loadReq.always(function () {
            if ($('#userslist').length > 0) {
                initializeDataTable();
            }
        });
    } else {
        // Fallback in case $.ajax compatibility changes
        if ($('#userslist').length > 0) {
            initializeDataTable();
        }
    }

    function initializeDataTable() {
        // Show loading indicator
        // // $('.data-loading').show();
        $('#error-container').hide();

        // Initialize the DataTable - use window scope to ensure it's accessible everywhere
        window.dataTable = $('#userslist').DataTable({
            "processing": true,
            "serverSide": true,
            "bFilter": false,
            "bInfo": false,
            "ordering": true,
            "autoWidth": true,
            "order": [[0, 'asc']], // Default order by first column ascending
            "orderCellsTop": true, // Enable ordering on header cells
            "ajax": {
                "url": "/devices/data",
                "type": "GET",
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                "beforeSend": function (xhr) {
                    console.log('Sending AJAX request to:', this.url);
                    console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
                },
                "data": function (d) {
                    // Add custom filter parameters
                    d.name_filter = $('.device-filter[data-column="name"]').val();
                    d.unique_id_filter = $('.device-filter[data-column="unique_id"]').val();
                    d.ip_filter = $('.device-filter[data-column="ip"]').val();

                    // Add date range filter parameters
                    var dateRange = $('#reportrange span').text().split(' - ');
                    if (dateRange.length === 2) {
                        d.start_date = moment(dateRange[0], 'D MMM YY').format('YYYY-MM-DD');
                        d.end_date = moment(dateRange[1], 'D MMM YY').format('YYYY-MM-DD');
                    }

                    // Add sort by parameter
                    d.sort_by = window.currentSortBy || 'newest';

                    // Handle status checkboxes
                    var statusValues = [];
                    $('.status-filter:checked').each(function () {
                        statusValues.push($(this).val());
                    });
                    d.status_filter = statusValues.length > 0 ? statusValues : null;

                    return d;
                },
                "error": function (xhr, error, thrown) {
                    console.error('DataTable AJAX error:', xhr.responseText);
                    console.error('Error details:', error, thrown);

                    // Hide loading indicator
                    $('.data-loading').hide();

                    // Show error container
                    $('#error-container').show();

                    // Set error message
                    let errorMessage = 'Failed to load user data. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 0) {
                        errorMessage = 'Network error. Please check your connection.';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Users endpoint not found. Please contact administrator.';
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
            "columns": [
                { "data": "name", "name": "name", "orderable": true },
                { "data": "unique_id", "name": "unique_id", "orderable": true },
                { "data": "company", "name": "company", "orderable": true },
                { "data": "location", "name": "location", "orderable": true },
                { "data": "area", "name": "area", "orderable": true },
                { "data": "ip", "name": "ip", "orderable": true },
                // {
                //     "data": "layouts",
                //     "name": "layouts",
                //     "orderable": false,
                //     "render": function (data, type, row) {
                //         if (data && data.length > 0) {
                //             let layoutBadges = data.map(layout => {
                //                 const typeMap = {
                //                     0: 'Full Screen',
                //                     1: 'Split Screen',
                //                     2: 'Three Grid Screen',
                //                     3: 'Four Grid Screen'
                //                 };
                //                 return `<span class="badge badge-soft-info me-1">${typeMap[layout.layout_type] || 'Unknown'}</span>`;
                //             }).join('');
                //             return layoutBadges;
                //         }
                //         return '<span class="text-muted">No layouts</span>';
                //     },
                //     "className": "column-layouts"
                // },
                {
                    "data": "layouts_count",
                    "name": "layouts_count",
                    "orderable": true,
                    "render": function (data, type, row) {
                        const totalCount = data || 0;
                        const activeCount = row.active_layouts_count || 0;
                        return `
                            <div class="d-flex flex-column">
                                <span class="badge badge-soft-primary mb-1">Total: ${totalCount}</span>
                                <span class="badge badge-soft-success">Active: ${activeCount}</span>
                            </div>
                        `;
                    },
                    "className": "column-layouts-count"
                },
                {
                    "data": "screens_count",
                    "name": "screens_count",
                    "orderable": true,
                    "render": function (data, type, row) {
                        const totalScreens = data || 0;
                        return `<span class="badge badge-soft-info">Screens: ${totalScreens}</span>`;
                    },
                    "className": "column-screens-count"
                },
                {
                    "data": "created_at",
                    "name": "created_at",
                    "orderable": true,
                    "render": function (data, type, row) {
                        return data ? new Date(data).toLocaleString() : 'N/A';
                    },
                    "className": "column-created-at"
                },
                {
                    "data": "updated_at",
                    "name": "updated_at",
                    "orderable": true,
                    "render": function (data, type, row) {
                        return data ? new Date(data).toLocaleString() : 'N/A';
                    },
                    "className": "column-updated-at"
                },
                {
                    "data": "status",
                    "name": "status",
                    "orderable": true,
                    "render": function (data, type, row) {
                        // Normalize to integer
                        var val = data;
                        if (typeof val === 'string') {
                            if (val === 'delete') val = 0;
                            else if (val === 'active') val = 1;
                            else if (val === 'deactivate') val = 2;
                            else if (val === 'block') val = 3;
                            else val = parseInt(val, 10);
                        }
                        if (val === true) val = 1;

                        switch (val) {
                            case 0:
                                return '<span class="badge badge-pill badge-status bg-secondary text-white">Delete</span>';
                            case 1:
                                return '<span class="badge badge-pill badge-status bg-success text-white">Active</span>';
                            case 2:
                                return '<span class="badge badge-pill badge-status bg-warning text-white">Inactive</span>';
                            case 3:
                                return '<span class="badge badge-pill badge-status bg-danger text-white">Block</span>';
                            default:
                                return '<span class="badge badge-pill badge-status bg-secondary text-white">Unknown</span>';
                        }
                    }
                },
                {
                    "data": "id",
                    "orderable": false,
                    "name": "action",
                    "render": function (data, type, row) {
                        let actions = [];

                        // Check permissions and add actions accordingly
                        if (window.devicePermissions && window.devicePermissions.view) {
                            actions.push(`<a class="dropdown-item" href="/device/${data}"><i class="ti ti-eye me-2"></i>View</a>`);
                        }

                        if (window.devicePermissions && window.devicePermissions.edit) {
                            actions.push(`<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit" data-id="${data}"><i class="ti ti-edit text-blue"></i> Edit</a>`);
                            actions.push(`<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_layout_management" data-device-id="${data}"><i class="ti ti-layout-grid me-2"></i>Manage Layouts</a>`);
                            actions.push(`<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_screen_management" data-device-id="${data}"><i class="ti ti-device-desktop me-2"></i>Manage Screens</a>`);
                        }

                        if (window.devicePermissions && window.devicePermissions.delete) {
                            actions.push(`<a class="dropdown-item delete-device" href="javascript:void(0);" data-id="${data}"><i class="ti ti-trash me-2"></i>Delete</a>`);
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
                                </div>
                            `;
                    }
                }
            ],
            "language": {
                search: ' ',
                sLengthMenu: '_MENU_',
                searchPlaceholder: "Search",
                info: "_START_ - _END_ of _TOTAL_ items",
                "lengthMenu": "Show _MENU_ entries",
                paginate: {
                    next: '<i class="ti ti-chevron-right"></i> ',
                    previous: '<i class="ti ti-chevron-left"></i> '
                },
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
                        dataTable.columns().every(function (index) {
                            const colName = this.settings()[0].aoColumns[index].name;
                            if (colName === column) {
                                columnIndex = index;
                                return false; // Break the loop
                            }
                        });

                        if (columnIndex !== null) {
                            // Set column visibility in DataTable
                            dataTable.column(columnIndex).visible(isVisible, false);
                            console.log('Applied column visibility in initComplete for column:', column, 'with index:', columnIndex, 'to:', isVisible);
                        } else {
                            console.error('Column not found for visibility in initComplete:', column);
                        }
                    });

                    // Redraw the table after applying all column visibility changes
                    dataTable.columns.adjust().draw(false);
                }, 200);

                // Initialize sort indicators for default sort
                const initialOrder = this.api().order();
                console.log('Initial order data structure:', JSON.stringify(initialOrder));
                console.log('Available headers at init:', $('#userslist thead th').length);

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
                        console.log('Available headers:', $('#userslist thead th').length);
                        updateSortIndicators(columnIndex, direction);
                    } else {
                        console.error('No order information available');
                    }
                });
            }
        });

        // Store DataTable instance in window for global access
        window.userDataTable = dataTable;

        // Handle column visibility toggle
        $(document).on('change', '.column-visibility-toggle', function (e) {
            // Stop event propagation to prevent affecting other columns
            e.stopPropagation();

            const column = $(this).data('column');
            const isVisible = $(this).prop('checked');

            // Find the correct column index by matching the column name
            let columnIndex = null;
            dataTable.columns().every(function (index) {
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
                dataTable.column(columnIndex).visible(isVisible, false);

                // Adjust the table layout after changing visibility
                dataTable.columns.adjust().draw(false);

                // Save preference to database for this column only
                saveColumnVisibility(column, isVisible);
            } else {
                console.error('Column not found:', column);
            }
        });

        // Add event listeners for live filtering
        $('.user-filter').on('keyup', function () {
            // $('.data-loading').show();
            $('#error-container').hide();
            dataTable.ajax.reload();
        });

        // Add event listeners for status checkbox filtering
        $('.status-filter').on('change', function () {
            // $('.data-loading').show();
            $('#error-container').hide();
            dataTable.ajax.reload();
        });

        // Add event listener for date range picker
        $('#reportrange').on('apply.daterangepicker', function (ev, picker) {
            // $('.data-loading').show();
            $('#error-container').hide();
            dataTable.ajax.reload();
        });

        // Add event listener for sort options
        $(document).on('click', '.sort-option', function () {
            const sortBy = $(this).data('sort');
            const sortText = $(this).text();
            window.currentSortBy = sortBy;

            // Update the dropdown button text to show current sort option
            $('.dropdown-toggle.btn-outline-light').first().html(`<i class="ti ti-sort-ascending-2 me-2"></i>${sortText}`);

            // Show loading and reload the DataTable with the new sort option
            // $('.data-loading').show();
            $('#error-container').hide();
            dataTable.ajax.reload();
        });

        // Function to update sort indicators in the table header
        function updateSortIndicators(columnIndex, direction) {
            console.log('Updating sort indicators for column:', columnIndex, 'direction:', direction);

            // First, remove all existing sort indicators
            $('#userslist thead th').removeClass('sorting_asc sorting_desc').addClass('sorting');

            // Then, add the appropriate class to the sorted column
            const $thElement = $(`#userslist thead th:eq(${columnIndex})`);
            $thElement.removeClass('sorting');
            $thElement.addClass(direction === 'asc' ? 'sorting_asc' : 'sorting_desc');
        }

        // Handle retry button click
        $(document).on('click', '#retry-load', function () {
            // $('.data-loading').show();
            $('#error-container').hide();
            dataTable.ajax.reload();
        });

        // Handle delete device
        $(document).on('click', '.delete-device', function () {
            const deviceId = $(this).data('id');
            if (confirm('Are you sure you want to delete this device?')) {
                $.ajax({
                    url: `/device/${deviceId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            // Reload the DataTable
                            dataTable.ajax.reload();
                            // Show success message
                            alert('Device deleted successfully!');
                        } else {
                            alert(response.message || 'Failed to delete device.');
                        }
                    },
                    error: function (xhr) {
                        console.error('Error deleting device:', xhr);
                        alert('Failed to delete device. Please try again.');
                    }
                });
            }
        });

        // Handle edit device
        $(document).on('click', '[data-bs-target="#offcanvas_edit"]', function () {
            const deviceId = $(this).data('id');

            // Set the device ID to the form
            $('#edit-device-form').data('device-id', deviceId);
            $('#edit-device-form').attr('action', `/device/${deviceId}`);

            // Fetch device data via AJAX
            $.ajax({
                url: `/device/${deviceId}/edit`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    console.log('Device data received:', response);
                    if (response.success && response.device) {
                        const device = response.device;
                        console.log('Device object:', device);

                        // Check if form fields exist
                        console.log('Form field elements:', {
                            name: $('#edit-name').length,
                            unique_id: $('#edit-unique_id').length,
                            ip: $('#edit-ip').length,
                            company_id: $('#edit-company_id').length,
                            location_id: $('#edit-location_id').length,
                            area_id: $('#edit-area_id').length,
                            status: $('#edit-status').length
                        });

                        // Just show the form first, populate later

                        // Show the edit form
                        $('#offcanvas_edit').offcanvas('show');

                        // Use setTimeout to ensure DOM is ready
                        setTimeout(function () {
                            console.log('Populating fields after timeout...');

                            // Check if form fields exist
                            console.log('Form field elements after timeout:', {
                                name: $('#edit-name').length,
                                unique_id: $('#edit-unique_id').length,
                                ip: $('#edit-ip').length,
                                company_id: $('#edit-company_id').length,
                                location_id: $('#edit-location_id').length,
                                area_id: $('#edit-area_id').length,
                                status: $('#edit-status').length
                            });

                            // Re-populate form fields
                            $('#edit-name').val(device.name);
                            $('#edit-unique_id').val(device.unique_id);
                            $('#edit-ip').val(device.ip);

                            // Convert status integer to string
                            let statusValue = '1';
                            if (device.status == 0) statusValue = '0';
                            else if (device.status == 1) statusValue = '1';
                            else if (device.status == 2) statusValue = '2';
                            else if (device.status == 3) statusValue = '3';
                            $('#edit-status').val(statusValue);

                            // Initialize Select2 for edit form fields BEFORE setting values
                            if (!$('#edit-company_id').hasClass('select2-hidden-accessible')) {
                                $('#edit-company_id').select2({
                                    placeholder: 'Select company...',
                                    allowClear: true,
                                    width: '100%',
                                    dropdownParent: $('#offcanvas_edit')
                                });
                            }
                            if (!$('#edit-location_id').hasClass('select2-hidden-accessible')) {
                                $('#edit-location_id').select2({
                                    placeholder: 'Select location...',
                                    allowClear: true,
                                    width: '100%',
                                    dropdownParent: $('#offcanvas_edit')
                                });
                            }
                            if (!$('#edit-area_id').hasClass('select2-hidden-accessible')) {
                                $('#edit-area_id').select2({
                                    placeholder: 'Select area...',
                                    allowClear: true,
                                    width: '100%',
                                    dropdownParent: $('#offcanvas_edit')
                                });
                            }

                            // Populate single select fields AFTER initialization
                            $('#edit-company_id').val(device.company_id || '').trigger('change.select2');
                            $('#edit-location_id').val(device.location_id || '').trigger('change.select2');
                            $('#edit-area_id').val(device.area_id || '').trigger('change.select2');

                            // Update layout count badge
                            if (device.layouts_count !== undefined) {
                                $('#edit-layout-count-badge').text(device.layouts_count + ' layouts');
                            }

                            // Load device layouts for preview
                            if (device.id) {
                                if (typeof window.loadDeviceLayoutsForEdit === 'function') {
                                    window.loadDeviceLayoutsForEdit(device.id);
                                } else if (typeof loadDeviceLayoutsForEdit === 'function') {
                                    loadDeviceLayoutsForEdit(device.id);
                                } else {
                                    console.error('loadDeviceLayoutsForEdit function is not available');
                                }

                                // Set device ID for layout management button
                                $('#offcanvas_edit [data-bs-target="#offcanvas_layout_management"]').attr('data-device-id', device.id);
                            }

                            // Ensure values are reflected in the Select2 UI
                            $('#edit-company_id').trigger('change.select2');
                            $('#edit-location_id').trigger('change.select2');
                            $('#edit-area_id').trigger('change.select2');

                            console.log('Fields populated after timeout');
                        }, 1000);
                    } else {
                        alert('Failed to load device data. Please try again.');
                    }
                },
                error: function (xhr) {
                    console.error('Error loading device data:', xhr);
                    console.error('Response text:', xhr.responseText);
                    console.error('Status:', xhr.status);

                    let errorMessage = 'Failed to load device data. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    alert(errorMessage);
                }
            });
        });

        // Handle form submission for creating device
        $('#create-device-form').on('submit', function (e) {
            e.preventDefault();
            console.log('Device form submission started');

            // Disable submit button to prevent double submission
            var submitBtn = $(this).find('button[type="submit"]');
            var originalBtnText = submitBtn.html();
            submitBtn.html('Creating...').prop('disabled', true);

            var formData = $(this).serialize();
            console.log('Form data being sent:', formData);
            console.log('CSRF token:', $('meta[name="csrf-token"]').attr('content'));

            $.ajax({
                url: '/device',
                type: 'POST',
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    console.log('Success response:', response);

                    if (response.success) {
                        // Show success message using inner alert element for proper styling
                        var $createAlertWrapper = $('#create-form-alert');
                        var $createAlert = $createAlertWrapper.find('.alert');
                        if ($createAlert.length === 0) {
                            $createAlertWrapper.html('<div class="alert alert-success alert-dismissible fade show" role="alert"></div>');
                            $createAlert = $createAlertWrapper.find('.alert');
                        }
                        $createAlert.removeClass('alert-danger').addClass('alert-success');
                        $createAlert.html('Device created successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                        $createAlertWrapper.show();

                        // Clear the form
                        $('#create-device-form')[0].reset();

                        // Reload the DataTable to show the new device
                        dataTable.ajax.reload();

                        // Close the offcanvas after a delay
                        setTimeout(function () {
                            $('#offcanvas_add').offcanvas('hide');
                        }, 2000);
                    } else {
                        var $createAlertWrapperErr = $('#create-form-alert');
                        var $createAlertErr = $createAlertWrapperErr.find('.alert');
                        if ($createAlertErr.length === 0) {
                            $createAlertWrapperErr.html('<div class="alert alert-danger alert-dismissible fade show" role="alert"></div>');
                            $createAlertErr = $createAlertWrapperErr.find('.alert');
                        }
                        $createAlertErr.removeClass('alert-success').addClass('alert-danger');
                        $createAlertErr.html(`Failed to create device: ${response.message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                        $createAlertWrapperErr.show();
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error creating device:', xhr.responseText);
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response status:', xhr.status);
                    console.error('Response headers:', xhr.getAllResponseHeaders());

                    let errorMessage = 'Error creating device. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = '<ul>';
                        for (const field in xhr.responseJSON.errors) {
                            errorMessage += `<li>${xhr.responseJSON.errors[field][0]}</li>`;
                        }
                        errorMessage += '</ul>';
                    }

                    var $createAlertWrapperFail = $('#create-form-alert');
                    var $createAlertFail = $createAlertWrapperFail.find('.alert');
                    if ($createAlertFail.length === 0) {
                        $createAlertWrapperFail.html('<div class="alert alert-danger alert-dismissible fade show" role="alert"></div>');
                        $createAlertFail = $createAlertWrapperFail.find('.alert');
                    }
                    $createAlertFail.removeClass('alert-success').addClass('alert-danger');
                    $createAlertFail.html(`${errorMessage} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                    $createAlertWrapperFail.show();
                },
                complete: function () {
                    // Re-enable submit button
                    submitBtn.html(originalBtnText).prop('disabled', false);
                }
            });
        });

        // Handle form submission for editing device
        $('#edit-device-form').on('submit', function (e) {
            e.preventDefault();
            console.log('Edit device form submission started');

            // Disable submit button to prevent double submission
            var submitBtn = $(this).find('button[type="submit"]');
            var originalBtnText = submitBtn.html();
            submitBtn.html('Updating...').prop('disabled', true);

            const deviceId = $(this).data('device-id');

            // Convert form data and map status values
            var formArray = $(this).serializeArray();
            var statusMap = { '0': 'delete', '1': 'active', '2': 'deactivate', '3': 'block' };
            for (var i = 0; i < formArray.length; i++) {
                if (formArray[i].name === 'status') {
                    var v = String(formArray[i].value);
                    if (statusMap.hasOwnProperty(v)) {
                        formArray[i].value = statusMap[v];
                    }
                    break;
                }
            }
            const formData = $.param(formArray);

            $.ajax({
                url: `/device/${deviceId}`,
                type: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        // Show success message using inner alert element for proper styling
                        var $editAlertWrapper2 = $('#edit-form-alert');
                        var $editAlert2 = $editAlertWrapper2.find('.alert');
                        if ($editAlert2.length === 0) {
                            $editAlertWrapper2.html('<div class="alert alert-success alert-dismissible fade show" role="alert"></div>');
                            $editAlert2 = $editAlertWrapper2.find('.alert');
                        }
                        $editAlert2.removeClass('alert-danger').addClass('alert-success');
                        $editAlert2.html('Device updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                        $editAlertWrapper2.show();

                        // Reload the DataTable
                        dataTable.ajax.reload();

                        // Close the offcanvas after a delay
                        setTimeout(function () {
                            $('#offcanvas_edit').offcanvas('hide');
                        }, 2000);
                    } else {
                        var $editAlertWrapper3 = $('#edit-form-alert');
                        var $editAlert3 = $editAlertWrapper3.find('.alert');
                        if ($editAlert3.length === 0) {
                            $editAlertWrapper3.html('<div class="alert alert-danger alert-dismissible fade show" role="alert"></div>');
                            $editAlert3 = $editAlertWrapper3.find('.alert');
                        }
                        $editAlert3.removeClass('alert-success').addClass('alert-danger');
                        $editAlert3.html(`Failed to update device: ${response.message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                        $editAlertWrapper3.show();
                    }
                },
                error: function (xhr) {
                    console.error('Error updating device:', xhr);

                    let errorMessage = 'Failed to update device. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = '<ul>';
                        for (const field in xhr.responseJSON.errors) {
                            errorMessage += `<li>${xhr.responseJSON.errors[field][0]}</li>`;
                        }
                        errorMessage += '</ul>';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    var $editAlertWrapper4 = $('#edit-form-alert');
                    var $editAlert4 = $editAlertWrapper4.find('.alert');
                    if ($editAlert4.length === 0) {
                        $editAlertWrapper4.html('<div class="alert alert-danger alert-dismissible fade show" role="alert"></div>');
                        $editAlert4 = $editAlertWrapper4.find('.alert');
                    }
                    $editAlert4.removeClass('alert-success').addClass('alert-danger');
                    $editAlert4.html(`${errorMessage} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                    $editAlertWrapper4.show();
                },
                complete: function () {
                    // Re-enable submit button
                    submitBtn.html(originalBtnText).prop('disabled', false);
                }
            });
        });

        // Reset forms once the offcanvas fully closes
        $(document).on('hidden.bs.offcanvas', '#offcanvas_add', function () {
            var $form = $('#create-device-form');
            if ($form.length) {
                try {
                    $form[0].reset();
                    // Hide alerts
                    $('#create-form-alert').hide();
                } catch (err) {
                    console.error('Error resetting create form after close:', err);
                }
            }
        });

        $(document).on('hidden.bs.offcanvas', '#offcanvas_edit', function () {
            var $form = $('#edit-device-form');
            if ($form.length) {
                try {
                    $form[0].reset();
                    $('#edit-form-alert').hide();
                } catch (err) {
                    console.error('Error resetting edit form after close:', err);
                }
            }
        });

        // ========== Device Screen Management (Index Page) ==========
        // Set device id and load layouts/screens when opening screen management
        $(document).on('click', '[data-bs-target="#offcanvas_screen_management"]', function () {
            const deviceId = $(this).data('device-id');
            if (!deviceId) {
                showAlert('warning', 'Please select a device first to manage its screens.');
                return false;
            }
            $('#screen-device-id').val(deviceId);
            // Actual population happens in offcanvas show handler to avoid duplicate parallel requests
        });

        // When the offcanvas actually opens, ensure device id is set from trigger and then load
        $('#offcanvas_screen_management').on('show.bs.offcanvas', function (e) {
            try {
                const $trigger = $(e.relatedTarget);
                const triggeredDeviceId = $trigger && $trigger.data('device-id');
                if (triggeredDeviceId) {
                    $('#screen-device-id').val(triggeredDeviceId);
                    // Populate layout options for the selected device on first open
                    populateLayoutOptionsForDevice(triggeredDeviceId);
                }
            } catch (err) {
                // no-op
            }

            loadDeviceScreensForIndex();
            if ($('#screen-form-alert').length) {
                $('#screen-form-alert').hide().empty();
            }
        });

        // Submit screen form (create/update)
        $(document).on('submit', '#screen-form', function (e) {
            e.preventDefault();

            // Frontend validation
            const screenHeight = $('#screen-height').val();
            const screenWidth = $('#screen-width').val();
            const layoutId = $('#screen-layout-id').val();
            const screenId = $('#screen-id').val();
            const isEdit = screenId !== '';

            // Check if layout is selected
            if (!layoutId) {
                showScreenAlert('danger', 'Please select a layout first.');
                return;
            }

            // Check for duplicate dimensions (basic frontend check)
            if (!isEdit) {
                const existingScreens = $('.screen-row');
                let hasConflict = false;
                existingScreens.each(function () {
                    const existingHeight = $(this).find('.screen-height').text();
                    const existingWidth = $(this).find('.screen-width').text();
                    if (existingHeight === screenHeight && existingWidth === screenWidth) {
                        hasConflict = true;
                        return false; // break loop
                    }
                });

                if (hasConflict) {
                    showScreenAlert('danger', `Screen dimensions ${screenHeight}x${screenWidth} already exist. Please use different dimensions.`);
                    return;
                }
            }

            const formData = new FormData(this);
            const url = isEdit ? `/device-screen/${screenId}` : '/device-screen';
            if (isEdit) {
                formData.append('_method', 'PUT');
            }
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    const $alertWrap = $('#screen-form-alert');
                    $alertWrap.show().html(`
                        <div class="alert alert-${response.success ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
                            ${response.message || (response.success ? 'Success' : 'Failed')}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                    if (response.success) {
                        $('#screen-form')[0].reset();
                        $('#screen-id').val('');
                        $('#screen-submit-btn').text('Add Screen');
                        $('#screen-cancel-btn').hide();
                        $('#screen-form-title').text('Add New Screen');
                        loadDeviceScreensForIndex();
                        // Refresh main devices DataTable to update screens_count without full reload
                        if (window.dataTable) {
                            window.dataTable.ajax.reload(null, false);
                        }
                        // Hide alert after a short delay but keep offcanvas open
                        setTimeout(function () {
                            $alertWrap.hide().empty();
                        }, 2000);
                    }
                },
                error: function (xhr) {
                    const response = xhr.responseJSON;
                    $('#screen-form-alert').show().html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${(response && (response.message || (response.errors && Object.values(response.errors)[0][0]))) || 'An error occurred'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                }
            });
        });

        // Edit screen from list
        $(document).on('click', '.edit-screen-btn', function () {
            const screenId = $(this).data('screen-id');
            const screenNo = $(this).data('screen-no');
            const screenHeight = $(this).data('screen-height');
            const screenWidth = $(this).data('screen-width');
            const layoutId = $(this).data('layout-id');
            $('#screen-id').val(screenId);
            $('#screen-no').val(screenNo);
            $('#screen-height').val(screenHeight);
            $('#screen-width').val(screenWidth);
            $('#screen-layout-id').val(layoutId);
            $('#screen-submit-btn').text('Update Screen');
            $('#screen-cancel-btn').show();
            $('#screen-form-title').text('Edit Screen');
            $('#offcanvas_screen_management').offcanvas('show');
        });

        // Delete screen from list
        $(document).on('click', '.delete-screen-btn', function () {
            const screenId = $(this).data('screen-id');
            if (confirm('Are you sure you want to delete this screen?')) {
                $.ajax({
                    url: `/device-screen/${screenId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            loadDeviceScreensForIndex();
                            // Refresh main devices DataTable to update screens_count
                            if (window.dataTable) {
                                window.dataTable.ajax.reload(null, false);
                            }
                        } else {
                            alert(response.message || 'Failed to delete screen');
                        }
                    },
                    error: function () {
                        alert('Failed to delete screen');
                    }
                });
            }
        });

        // Cancel edit
        $(document).on('click', '#screen-cancel-btn', function () {
            $('#screen-form')[0].reset();
            $('#screen-id').val('');
            $('#screen-submit-btn').text('Add Screen');
            $('#screen-cancel-btn').hide();
            $('#screen-form-title').text('Add New Screen');
        });

        function populateLayoutOptionsForDevice(deviceId) {
            const $select = $('#screen-layout-id');
            $.ajax({
                // Only fetch Active (status=1) layouts for the selected device
                url: `/device/${deviceId}/layouts?status=1`,
                type: 'GET',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    // Clear options on success just before appending to avoid race duplicates
                    $select.find('option:not(:first)').remove();
                    if (response && response.success && Array.isArray(response.layouts)) {
                        response.layouts.forEach(function (layout) {
                            const typeMap = { 0: 'Full Screen', 1: 'Split Screen', 2: 'Three Grid Screen', 3: 'Four Grid Screen' };
                            const text = `${layout.layout_name} (${typeMap[layout.layout_type] || 'Unknown'})`;
                            $select.append(new Option(text, layout.id));
                        });
                    }
                }
            });
        }

        // Helper function for screen alerts
        function showScreenAlert(type, message) {
            $('#screen-form-alert').show().html(`
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);
        }

        function loadDeviceScreensForIndex() {
            const deviceId = $('#screen-device-id').val();
            if (!deviceId) return;
            $.ajax({
                url: `/device/${deviceId}/screens`,
                type: 'GET',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    const tbody = $('#screen-table tbody');
                    tbody.empty();
                    if (!response || !response.success || !Array.isArray(response.screens) || response.screens.length === 0) {
                        tbody.append('<tr><td colspan="6" class="text-center text-muted">No screens found</td></tr>');
                        return;
                    }
                    response.screens.forEach(function (s) {
                        const createdAt = s.created_at ? new Date(s.created_at).toLocaleString() : '';
                        const row = `
                            <tr>
                                <td>${s.screen_no ?? ''}</td>
                                <td>${s.screen_height ?? ''}</td>
                                <td>${s.screen_width ?? ''}</td>
                                <td>${(s.layout && s.layout.layout_name) ? s.layout.layout_name : ''}</td>
                                <td>${createdAt}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary edit-screen-btn"
                                            data-screen-id="${s.id}"
                                            data-screen-no="${s.screen_no}"
                                            data-screen-height="${s.screen_height}"
                                            data-screen-width="${s.screen_width}"
                                            data-layout-id="${s.layout_id}">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-screen-btn" data-screen-id="${s.id}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                        tbody.append(row);
                    });
                }
            });
        }
    }
});

// Global variables for form field counters (if needed for future dynamic fields)
let addressCounter = 1;
let contactCounter = 1;
let noteCounter = 1;
let editAddressCounter = 0;
let editContactCounter = 0;
let editNoteCounter = 0;

// Function to add address field in create form (if needed for future use)
window.addAddress = function () {
    const container = document.getElementById('addresses-container');
    if (!container) return; // Only if container exists

    const newAddress = document.createElement('div');
    newAddress.className = 'address-item border rounded p-3 mb-2';
    newAddress.innerHTML = `
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" name="addresses[${addressCounter}][type]" required>
                        <option value="">Select Type</option>
                        <option value="Home">Home</option>
                        <option value="Work">Work</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control" name="addresses[${addressCounter}][address]" placeholder="Address">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="addresses[${addressCounter}][city]" placeholder="City">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="addresses[${addressCounter}][state]" placeholder="State">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="addresses[${addressCounter}][country]" placeholder="Country">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="addresses[${addressCounter}][zip_code]" placeholder="Zip Code">
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="this.parentElement.remove()">
                <i class="ti ti-trash me-1"></i>Remove
            </button>
        `;
    container.appendChild(newAddress);
    addressCounter++;
};

// Function to add contact field in create form (if needed for future use)
window.addContact = function () {
    const container = document.getElementById('contacts-container');
    if (!container) return; // Only if container exists

    const newContact = document.createElement('div');
    newContact.className = 'contact-item border rounded p-3 mb-2';
    newContact.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="contacts[${contactCounter}][name]" placeholder="Contact Name">
                </div>
                <div class="col-md-4">
                    <input type="email" class="form-control" name="contacts[${contactCounter}][email]" placeholder="Email">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="contacts[${contactCounter}][phone]" placeholder="Phone">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="contacts[${contactCounter}][relationship]" placeholder="Relationship">
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="contacts[${contactCounter}][is_emergency]" value="1">
                        <label class="form-check-label">Emergency Contact</label>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="this.parentElement.remove()">
                <i class="ti ti-trash me-1"></i>Remove
            </button>
        `;
    container.appendChild(newContact);
    contactCounter++;
};

// Function to add note field in create form (if needed for future use)
window.addNote = function () {
    const container = document.getElementById('notes-container');
    if (!container) return; // Only if container exists

    const newNote = document.createElement('div');
    newNote.className = 'note-item border rounded p-3 mb-2';
    newNote.innerHTML = `
            <div class="row">
                <div class="col-md-9">
                    <textarea class="form-control" name="notes[${noteCounter}][note]" rows="3" placeholder="Note content"></textarea>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="notes[${noteCounter}][status]">
                        <option value="delete">Delete</option>
                        <option value="active">Active</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="block">Block</option>
                    </select>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="this.parentElement.remove()">
                <i class="ti ti-trash me-1"></i>Remove
            </button>
        `;
    container.appendChild(newNote);
    noteCounter++;
};

// Function to add address field in edit form (if needed for future use)
window.addEditAddress = function () {
    const container = document.getElementById('edit-addresses-container');
    if (!container) return; // Only if container exists

    const newAddress = document.createElement('div');
    newAddress.className = 'address-item border rounded p-3 mb-2';
    newAddress.innerHTML = `
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" name="addresses[${editAddressCounter}][type]">
                        <option value="">Select Type</option>
                        <option value="Home">Home</option>
                        <option value="Work">Work</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control" name="addresses[${editAddressCounter}][address]" placeholder="Address">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="addresses[${editAddressCounter}][city]" placeholder="City">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="addresses[${editAddressCounter}][state]" placeholder="State">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="addresses[${editAddressCounter}][country]" placeholder="Country">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="addresses[${editAddressCounter}][zip_code]" placeholder="Zip Code">
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="this.parentElement.remove()">
                <i class="ti ti-trash me-1"></i>Remove
            </button>
        `;
    container.appendChild(newAddress);
    editAddressCounter++;
};

// Function to add contact field in edit form (if needed for future use)
window.addEditContact = function () {
    const container = document.getElementById('edit-contacts-container');
    if (!container) return; // Only if container exists

    const newContact = document.createElement('div');
    newContact.className = 'contact-item border rounded p-3 mb-2';
    newContact.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="contacts[${editContactCounter}][name]" placeholder="Contact Name">
                </div>
                <div class="col-md-4">
                    <input type="email" class="form-control" name="contacts[${editContactCounter}][email]" placeholder="Email">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="contacts[${editContactCounter}][phone]" placeholder="Phone">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="contacts[${editContactCounter}][relationship]" placeholder="Relationship">
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="contacts[${editContactCounter}][is_emergency]" value="1">
                        <label class="form-check-label">Emergency Contact</label>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="this.parentElement.remove()">
                <i class="ti ti-trash me-1"></i>Remove
            </button>
        `;
    container.appendChild(newContact);
    editContactCounter++;
};

// Function to add note field in edit form (if needed for future use)
window.addEditNote = function () {
    const container = document.getElementById('edit-notes-container');
    if (!container) return; // Only if container exists

    const newNote = document.createElement('div');
    newNote.className = 'note-item border rounded p-3 mb-2';
    newNote.innerHTML = `
            <div class="row">
                <div class="col-md-9">
                    <textarea class="form-control" name="notes[${editNoteCounter}][note]" rows="3" placeholder="Note content"></textarea>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="notes[${editNoteCounter}][status]">
                        <option value="delete">Delete</option>
                        <option value="active">Active</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="block">Block</option>
                    </select>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="this.parentElement.remove()">
                <i class="ti ti-trash me-1"></i>Remove
            </button>
        `;
    container.appendChild(newNote);
    editNoteCounter++;
};

// Device Layout Management Functions
$(document).ready(function () {
    // Load device layouts when layout management offcanvas is shown
    $('#offcanvas_layout_management').on('show.bs.offcanvas', function () {
        loadDeviceLayouts();
    });

    // Handle opening layout management for a specific device
    $(document).on('click', '[data-bs-target="#offcanvas_layout_management"]', function () {
        const deviceId = $(this).data('device-id');
        if (deviceId) {
            $('#layout-device-id').val(deviceId);
            console.log('Set device ID for layout management:', deviceId);
        } else {
            showAlert('warning', 'Please select a device first to manage its layouts.');
            return false;
        }
    });

    // Handle layout form submission
    $('#layout-form').on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const layoutId = $('#layout-id').val();
        const isEdit = layoutId !== '';

        // Ensure device_id is set
        const deviceId = $('#layout-device-id').val();
        if (!deviceId && !isEdit) {
            showAlert('danger', 'Device ID is required. Please select a device first.');
            return;
        }

        // Add device_id if not present
        if (!formData.has('device_id') && deviceId) {
            formData.append('device_id', deviceId);
        }

        const url = isEdit ? `/device-layout/${layoutId}` : '/device-layout';
        const method = isEdit ? 'PUT' : 'POST';

        // Add _method field for PUT requests
        if (isEdit) {
            formData.append('_method', 'PUT');
        }

        console.log('Layout form submission:', {
            url: url,
            method: method,
            deviceId: deviceId,
            formData: Object.fromEntries(formData.entries())
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                const $alertWrap = $('#layout-form-alert');
                $alertWrap.show().html(`
                    <div class="alert alert-${response.success ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
                        ${response.message || (response.success ? 'Success' : 'Failed')}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);

                if (response.success) {
                    $('#layout-form')[0].reset();
                    $('#layout-id').val('');
                    $('#layout-submit-btn').text('Add Layout');
                    $('#layout-cancel-btn').hide();
                    $('#layout-form-title').text('Add New Layout');
                    loadDeviceLayouts();

                    // refresh devices datatable to update counts
                    if (window.dataTable) {
                        window.dataTable.ajax.reload(null, false);
                    }

                    // Hide alert after a short delay but keep offcanvas open
                    setTimeout(function () {
                        $alertWrap.hide().empty();
                    }, 2000);
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                const message = (response && (response.message || (response.errors && Object.values(response.errors)[0][0]))) || 'An error occurred';
                $('#layout-form-alert').show().html(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
            }
        });
    });

    // Handle edit layout button click
    $(document).on('click', '.edit-layout-btn', function () {
        const layoutId = $(this).data('layout-id');
        const layoutName = $(this).data('layout-name');
        const layoutType = $(this).data('layout-type');
        const layoutStatus = $(this).data('layout-status');

        $('#layout-id').val(layoutId);
        $('#layout-name').val(layoutName);
        $('#layout-type').val(layoutType);
        $('#layout-status').val(layoutStatus);
        $('#layout-submit-btn').text('Update Layout');
        $('#layout-cancel-btn').show();
        $('#layout-form-title').text('Edit Layout');

        $('#offcanvas_layout_management').offcanvas('show');
    });

    // Handle delete layout button click
    $(document).on('click', '.delete-layout-btn', function () {
        const layoutId = $(this).data('layout-id');

        if (confirm('Are you sure you want to delete this layout?')) {
            $.ajax({
                url: `/device-layout/${layoutId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        loadDeviceLayouts();
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function (xhr) {
                    const response = xhr.responseJSON;
                    showAlert('danger', response?.message || 'An error occurred');
                }
            });
        }
    });

    // Handle cancel edit button
    $('#layout-cancel-btn').on('click', function () {
        $('#layout-form')[0].reset();
        $('#layout-id').val('');
        $('#layout-submit-btn').text('Add Layout');
        $('#layout-cancel-btn').hide();
        $('#layout-form-title').text('Add New Layout');
    });

    // Function to load device layouts
    function loadDeviceLayouts() {
        $.ajax({
            url: '/device-layouts',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    updateLayoutTable(response.layouts);
                }
            },
            error: function (xhr) {
                console.error('Error loading layouts:', xhr);
            }
        });
    }

    // Function to update layout table
    function updateLayoutTable(layouts) {
        const tbody = $('#layout-table tbody');
        tbody.empty();

        if (layouts.length === 0) {
            tbody.append('<tr><td colspan="5" class="text-center text-muted">No layouts found</td></tr>');
            return;
        }

        layouts.forEach(function (layout) {
            const typeMap = {
                0: 'Full Screen',
                1: 'Split Screen',
                2: 'Three Grid Screen',
                3: 'Four Grid Screen'
            };

            const statusMap = {
                0: { class: 'badge-soft-secondary', icon: 'ti-trash', text: 'Delete' },
                1: { class: 'badge-soft-success', icon: 'ti-check', text: 'Active' },
                2: { class: 'badge-soft-warning', icon: 'ti-player-pause', text: 'Inactive' },
                3: { class: 'badge-soft-danger', icon: 'ti-lock', text: 'Block' }
            };

            const status = statusMap[layout.status] || { class: 'badge-soft-secondary', icon: 'ti-circle', text: 'Unknown' };

            const row = `
                <tr>
                    <td>${layout.layout_name}</td>
                    <td><span class="badge badge-soft-info">${typeMap[layout.layout_type] || 'Unknown'}</span></td>
                    <td>${(layout.device && (layout.device.name || layout.device.unique_id)) || '-'}</td>
                    <td><span class="badge ${status.class}"><i class="ti ${status.icon} me-1"></i>${status.text}</span></td>
                    <td>${new Date(layout.created_at).toLocaleDateString()}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-primary edit-layout-btn"
                                    data-layout-id="${layout.id}"
                                    data-layout-name="${layout.layout_name}"
                                    data-layout-type="${layout.layout_type}"
                                    data-layout-status="${layout.status}">
                                <i class="ti ti-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-layout-btn"
                                    data-layout-id="${layout.id}">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Function to show alerts
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="min-width: 300px; max-width: 500px;">
                <i class="ti ti-${type === 'success' ? 'check-circle' : type === 'danger' ? 'alert-circle' : type === 'warning' ? 'alert-triangle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        // Remove existing alerts from alert container
        $('#alert-container .alert').remove();

        // Add new alert to the alert container
        $('#alert-container').html(alertHtml);

        // Auto-hide after 5 seconds
        setTimeout(function () {
            $('#alert-container .alert').fadeOut(500, function () {
                $(this).remove();
            });
        }, 5000);
    }

    // Function to load device layouts for edit form (exposed globally)
    window.loadDeviceLayoutsForEdit = function (deviceId) {
        $.ajax({
            url: `/device/${deviceId}/layouts`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response, status, xhr) {
                // If server returned HTML (e.g., login page due to 302), show an error instead of hanging
                const contentType = xhr.getResponseHeader('Content-Type') || '';
                if (contentType.indexOf('application/json') === -1) {
                    $('#edit-device-layouts-preview').html(`
                        <i class="ti ti-alert-circle fs-1 text-warning"></i>
                        <p class="mt-2 text-warning">Unable to load layouts. Please refresh or re-login.</p>
                    `);
                    return;
                }

                if (response && response.success) {
                    updateEditLayoutPreview(response.layouts || [], response.counts || {});
                } else {
                    $('#edit-device-layouts-preview').html(`
                        <i class="ti ti-alert-circle fs-1 text-warning"></i>
                        <p class="mt-2 text-warning">No layouts found or failed to load.</p>
                    `);
                }
            },
            error: function (xhr) {
                console.error('Error loading device layouts for edit:', xhr);
                $('#edit-device-layouts-preview').html(`
                    <i class="ti ti-alert-circle fs-1 text-warning"></i>
                    <p class="mt-2 text-warning">Error loading layouts</p>
                `);
            }
        });
    }

    // Function to update edit layout preview
    function updateEditLayoutPreview(layouts, counts) {
        const preview = $('#edit-device-layouts-preview');

        if (layouts.length === 0) {
            preview.html(`
                <i class="ti ti-layout-grid fs-1"></i>
                <p class="mt-2">No layouts found for this device</p>
            `);
            return;
        }

        let layoutHtml = '<div class="row">';
        layouts.forEach(function (layout) {
            const typeMap = {
                0: 'Full Screen',
                1: 'Split Screen',
                2: 'Three Grid Screen',
                3: 'Four Grid Screen'
            };

            const statusMap = {
                0: { class: 'badge-soft-secondary', icon: 'ti-trash', text: 'Delete' },
                1: { class: 'badge-soft-success', icon: 'ti-check', text: 'Active' },
                2: { class: 'badge-soft-warning', icon: 'ti-player-pause', text: 'Inactive' },
                3: { class: 'badge-soft-danger', icon: 'ti-lock', text: 'Block' }
            };

            const status = statusMap[layout.status] || { class: 'badge-soft-secondary', icon: 'ti-circle', text: 'Unknown' };

            layoutHtml += `
                <div class="col-md-6 mb-2">
                    <div class="border rounded p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${layout.layout_name}</h6>
                                <span class="badge badge-soft-info">${typeMap[layout.layout_type] || 'Unknown'}</span>
                            </div>
                            <span class="badge ${status.class}">
                                <i class="ti ${status.icon} me-1"></i>${status.text}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        });
        layoutHtml += '</div>';

        // Add counts summary
        if (counts) {
            layoutHtml += `
                <div class="mt-3">
                    <div class="d-flex gap-2 justify-content-center">
                        <span class="badge badge-soft-primary">Total: ${counts.total}</span>
                        <span class="badge badge-soft-success">Active: ${counts.active}</span>
                        <span class="badge badge-soft-warning">Inactive: ${counts.inactive}</span>
                        <span class="badge badge-soft-danger">Blocked: ${counts.blocked}</span>
                    </div>
                </div>
            `;
        }

        preview.html(layoutHtml);
    }
});
