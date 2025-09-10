$(document).ready(function () {
    // Initialize column visibility from database
    let columnVisibility = {};

    // Set default sort option
    window.currentSortBy = 'newest';

    // Custom notification function to replace browser alerts
    function showCustomNotification(message, type) {
        var alertClass = 'alert-success';
        var iconClass = 'ti ti-check-circle';

        if (type === 'error') {
            alertClass = 'alert-danger';
            iconClass = 'ti ti-alert-circle';
        }

        var notification = $('<div class="alert ' + alertClass + ' alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">' +
            '<i class="' + iconClass + ' me-2"></i>' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>');

        // Remove any existing notifications
        $('.alert.position-fixed').remove();

        // Append to body
        $('body').append(notification);

        // Auto dismiss after 4 seconds
        setTimeout(function () {
            notification.alert('close');
        }, 4000);
    }

    // Apply initial CSS to hide columns that should be hidden
    function applyInitialColumnVisibility() {
        // Get saved column visibility from localStorage if available
        const savedVisibility = localStorage.getItem('locationColumnVisibility');
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
            data: { table: 'locations' },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                // Initialize all columns as visible by default
                columnVisibility = {
                    'name': true,
                    'email': true,
                    'address': true,
                    'city': true,
                    'state': true,
                    'country': true,
                    'zip_code': true,
                    'created_by': true,
                    'updated_by': true,
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
                    localStorage.setItem('locationColumnVisibility', JSON.stringify(columnVisibility));
                }

                // Update toggle switches in the UI
                updateColumnToggles();
                console.log('Loaded column visibility:', columnVisibility);
            },
            error: function (xhr) {
                console.error('Error loading column visibility:', xhr);
                console.log('Response:', xhr.responseJSON);
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
        localStorage.setItem('locationColumnVisibility', JSON.stringify(columnVisibility));

        // Save to server
        $.ajax({
            url: 'columns',
            type: 'POST',
            data: {
                table: 'locations',
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
                localStorage.setItem('locationColumnVisibility', JSON.stringify(columnVisibility));

                // Revert the DataTable column visibility if available
                var columnIndex = dataTable ? dataTable.column(function (idx, data, node) {
                    return data.name === column;
                }).index() : undefined;
                if (columnIndex !== undefined) {
                    dataTable.column(columnIndex).visible(!isVisible);
                }

                // Show error to user
                showCustomNotification(`Failed to save column preference for ${column}. Please try again.`, 'error');
            }
        });
    }

    // Load column visibility preferences before initializing DataTable
    loadColumnVisibility().then(function () {
        if ($('#contactslist').length > 0) {
            // Initialize the DataTable - use window scope to ensure it's accessible everywhere
            window.dataTable = $('#contactslist').DataTable({
                "processing": true,
                "serverSide": true,
                "bFilter": false,
                "bInfo": false,
                "ordering": true,
                "autoWidth": true,
                "order": [[0, 'asc']], // Default order by first column ascending
                "orderCellsTop": true, // Enable ordering on header cells
                "ajax": {
                    "url": (typeof window.locationsDataUrl !== 'undefined' ? window.locationsDataUrl : 'locations/data'),
                    "type": "GET",
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    "data": function (d) {
                        // Add custom filter parameters
                        d.name_filter = $('.location-filter[data-column="name"]').val();
                        d.email_filter = $('.location-filter[data-column="email"]').val();
                        d.address_filter = $('.location-filter[data-column="address"]').val();
                        d.city_filter = $('.location-filter[data-column="city"]').val();
                        d.state_filter = $('.location-filter[data-column="state"]').val();
                        d.country_filter = $('.location-filter[data-column="country"]').val();
                        d.zip_code_filter = $('.location-filter[data-column="zip_code"]').val();

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
                        $('#error-container').show();
                        $('#error-message').text('Unable to load data. ' + thrown);
                    }
                },
                "columns": [
                    { "data": "name", "name": "name", "orderable": true },
                    { "data": "email", "name": "email", "orderable": true },
                    { "data": "address", "name": "address", "orderable": true },
                    { "data": "city", "name": "city", "orderable": true },
                    { "data": "state", "name": "state", "orderable": true },
                    { "data": "country", "name": "country", "orderable": true },
                    { "data": "zip_code", "name": "zip_code", "orderable": true },
                    {
                        "data": "created_by",
                        "name": "created_by",
                        "orderable": true,
                        "render": function (data, type, row) {
                            return data ? data : 'N/A';
                        },
                        "className": "column-created-by"
                    },
                    {
                        "data": "updated_by",
                        "name": "updated_by",
                        "orderable": true,
                        "render": function (data, type, row) {
                            return data ? data : 'N/A';
                        },
                        "className": "column-updated-by"
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
                            let badgeClass = 'bg-secondary';
                            if (data === 'Active') {
                                badgeClass = 'bg-success';
                            } else if (data === 'Inactive') {
                                badgeClass = 'bg-danger';
                            } else if (data === 'Blocked') {
                                badgeClass = 'bg-warning';
                            } else if (data === 'Deleted') {
                                badgeClass = 'bg-secondary';
                            }
                            return '<span class="badge badge-pill badge-status ' + badgeClass + ' text-white">' + data + '</span>';
                        }
                    },
                    {
                        "data": "id",
                        "orderable": false,
                        "name": "action",
                        "render": function (data, type, row) {
                            let actions = [];

                            // Check permissions and add actions accordingly
                            if (window.locationPermissions && window.locationPermissions.view) {
                                actions.push(`<a class="dropdown-item" href="/location/${data}"><i class="ti ti-eye me-2"></i>View</a>`);
                            }

                            if (window.locationPermissions && window.locationPermissions.edit) {
                                actions.push(`<a class="dropdown-item edit-location" href="javascript:void(0);" data-id="${data}" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit"><i class="ti ti-edit text-blue"></i> Edit</a>`);
                            }

                            if (window.locationPermissions && window.locationPermissions.delete) {
                                actions.push(`<a class="dropdown-item delete-location" href="javascript:void(0);" data-id="${data}"><i class="ti ti-trash me-2"></i>Delete</a>`);
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
                    $('.dataTables_paginate').appendTo('.datatable-paginate');
                    $('.dataTables_length').appendTo('.datatable-length');
                    $('#error-container').hide();

                    // Initialize sort indicators for default sort
                    const initialOrder = this.api().order();
                    console.log('Initial order data structure:', JSON.stringify(initialOrder));
                    console.log('Available headers at init:', $('#contactslist thead th').length);

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
                            console.log('Available headers:', $('#contactslist thead th').length);
                            updateSortIndicators(columnIndex, direction);
                        } else {
                            console.error('No order information available');
                        }
                    });
                }
            });

            // Store DataTable instance in window for global access
            window.locationDataTable = dataTable;

            // Apply initial column visibility after DataTable is initialized
            // This ensures all columns are properly hidden/shown based on saved preferences
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
                    dataTable.column(columnIndex).visible(isVisible);
                    console.log('Applied initial visibility for column:', column, 'with index:', columnIndex, 'to:', isVisible);
                } else {
                    console.error('Column not found for initial visibility:', column);
                }
            });

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
            $('.location-filter').on('keyup', function () {
                dataTable.ajax.reload();
            });

            // Add event listeners for status checkbox filtering
            $('.status-filter').on('change', function () {
                dataTable.ajax.reload();
            });

            // Add event listener for date range picker
            $('#reportrange').on('apply.daterangepicker', function (ev, picker) {
                dataTable.ajax.reload();
            });

            // Add event listener for sort options
            $(document).on('click', '.sort-option', function () {
                const sortBy = $(this).data('sort');
                const sortText = $(this).text();
                window.currentSortBy = sortBy;

                // Update the dropdown button text to show current sort option
                $('.dropdown-toggle i.ti-sort-ascending-2').closest('a').html(
                    '<i class="ti ti-sort-ascending-2 me-2"></i>Sort By: ' + sortText
                );

                dataTable.ajax.reload();
            });

            // We'll use DataTables' built-in sorting functionality
            // The click handler is removed as DataTables handles this automatically
            // We'll use the order.dt event to update our custom indicators


            // Function to update sort indicators in table headers
            function updateSortIndicators(columnIndex, direction) {
                console.log('Updating sort indicators - column:', columnIndex, 'direction:', direction);

                // Ensure columnIndex is treated as a number
                columnIndex = parseInt(columnIndex);

                // Wait a short time to ensure the DOM is fully rendered
                setTimeout(function () {
                    // Remove all existing sort indicators
                    $('.sort-indicator').remove();

                    // Make sure columnIndex is a valid number
                    if (isNaN(columnIndex) || columnIndex === undefined || columnIndex === null) {
                        console.error('Invalid column index for sort indicator:', columnIndex);
                        return;
                    }

                    // Log all available headers for debugging
                    const allHeaders = $('#contactslist thead th');
                    console.log('Total headers found:', allHeaders.length);
                    allHeaders.each(function (idx) {
                        console.log('Header', idx, 'text:', $(this).text().trim());
                    });

                    // Get the header element directly by index
                    const sortedHeader = allHeaders.eq(columnIndex);

                    if (sortedHeader.length === 0) {
                        console.error('Could not find header element for column index:', columnIndex);
                        return;
                    }

                    console.log('Found header for column', columnIndex, ':', sortedHeader.text().trim());

                    // Create indicator based on sort direction
                    const indicator = direction === 'asc' ?
                        '<span class="sort-indicator" style="display:inline-block;margin-left:5px;color:#0d6efd;"><i class="ti ti-arrow-up"></i></span>' :
                        '<span class="sort-indicator" style="display:inline-block;margin-left:5px;color:#0d6efd;"><i class="ti ti-arrow-down"></i></span>';

                    // Append the indicator to the header
                    sortedHeader.append(indicator);
                    console.log('Added sort indicator to header:', sortedHeader.text().trim());

                    // Indicator already appended above

                    console.log('Updated sort indicator for column', columnIndex, 'direction:', direction);
                }, 50);
            }

            // Add CSS for sort indicators
            $('<style>\n' +
                '#contactslist thead th { cursor: pointer; position: relative; }\n' +
                '#contactslist thead th:hover { background-color: rgba(0,0,0,0.05); }\n' +
                '.sort-indicator { display: inline-block; margin-left: 5px; }\n' +
                '</style>').appendTo('head');

            // Add direct click handler for table headers
            $(document).on('click', '#contactslist thead th', function () {
                console.log('Header clicked directly');
                const columnIndex = $(this).index();
                console.log('Clicked column index:', columnIndex);

                // Check if this column is sortable
                const column = window.locationDataTable.column(columnIndex);
                if (column && column.visible()) {
                    console.log('Sorting column via direct click');

                    // Get current order
                    const currentOrder = window.locationDataTable.order();
                    console.log('Current order before change:', JSON.stringify(currentOrder));
                    let direction = 'asc';

                    // If already sorted by this column, toggle direction
                    if (currentOrder && currentOrder.length > 0) {
                        console.log('Current sorted column:', currentOrder[0][0], 'Clicked column:', columnIndex);
                        if (parseInt(currentOrder[0][0]) === parseInt(columnIndex)) {
                            console.log('Column already sorted, current direction:', currentOrder[0][1]);
                            direction = currentOrder[0][1] === 'asc' ? 'desc' : 'asc';
                            console.log('Toggling to direction:', direction);
                        } else {
                            console.log('Different column clicked, using default direction:', direction);
                        }
                    } else {
                        console.log('No current order, using default direction:', direction);
                    }

                    // Apply sort - fix the format of the order array
                    console.log('Setting order to:', [[columnIndex, direction]]);
                    window.locationDataTable.order([[columnIndex, direction]]).draw();

                    // Force update of sort indicators after draw
                    setTimeout(function () {
                        updateSortIndicators(columnIndex, direction);
                    }, 100);
                }
            });
        }
    });

    // Delete location - Show custom modal instead of browser confirm
    $(document).on('click', '.delete-location', function () {
        var locationId = $(this).data('id');

        // Store the location ID for the delete operation
        $('#delete_location').data('location-id', locationId);

        // Show the custom delete modal
        $('#delete_location').modal('show');
    });

    // Handle delete confirmation button click
    $(document).on('click', '#confirm-delete-btn', function () {
        var locationId = $('#delete_location').data('location-id');

        if (!locationId) {
            showCustomNotification('Error: Location ID not found', 'error');
            return;
        }

        // Disable the button to prevent double clicks
        $(this).prop('disabled', true).text('Deleting...');

        $.ajax({
            url: 'location/' + locationId,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    // Close the modal
                    $('#delete_location').modal('hide');

                    // Reload the DataTable
                    $('#contactslist').DataTable().ajax.reload();

                    // Show custom success notification
                    showCustomNotification('Location deleted successfully', 'success');
                } else {
                    showCustomNotification('Error deleting location', 'error');
                }
            },
            error: function (xhr) {
                console.error('Error deleting location:', xhr);
                showCustomNotification('Error deleting location: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'), 'error');
            },
            complete: function () {
                // Re-enable the button
                $('#confirm-delete-btn').prop('disabled', false).text('Delete');
            }
        });
    });

    // Handle edit button click
    $(document).on('click', '[data-bs-target="#offcanvas_edit"]', function () {
        // Prefer the id on the clicked element; fallback to sibling actions; final fallback to row data
        var locationId = $(this).data('id');
        if (!locationId) {
            locationId = $(this).closest('.dropdown-action').find('.edit-location').data('id')
                || $(this).closest('.dropdown-action').find('.delete-location').data('id');
        }

        // Set the location ID to the form
        $('#edit-location-form').data('location-id', locationId);
        $('#edit-location-form').attr('action', '/location/' + locationId);

        // Fetch location data via AJAX
        $.ajax({
            url: 'location/' + locationId,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                // Handle both direct data and wrapped response
                var data = response.location || response;

                // Populate form fields
                $('#edit-name').val(data.name);
                $('#edit-email').val(data.email);
                $('#edit-address').val(data.address);
                $('#edit-city').val(data.city);
                $('#edit-state').val(data.state);
                $('#edit-country').val(data.country);
                $('#edit-zip_code').val(data.zip_code);
                $('#edit-status').val(data.status);
            },
            error: function (xhr) {
                console.error('Error fetching location data');
                showCustomNotification('Error loading location data. Please try again.', 'error');
            }
        });
    });

    // Handle edit form submission
    $('#edit-location-form').on('submit', function (e) {
        e.preventDefault();
        var locationId = $(this).data('location-id');
        var formData = $(this).serialize();

        $.ajax({
            url: '/location/' + locationId,
            type: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    // Show success message in the form
                    $('#edit-form-alert').show();

                    // Close the offcanvas after a short delay
                    setTimeout(function () {
                        $('#offcanvas_edit').offcanvas('hide');
                        $('#edit-form-alert').hide();
                    }, 1500);

                    // Reload the DataTable
                    $('#contactslist').DataTable().ajax.reload();
                } else {
                    showCustomNotification('Error updating location', 'error');
                }
            },
            error: function (xhr) {
                console.error('Error updating location:', xhr);

                // Handle validation errors
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = 'Validation errors:\n';

                    for (var field in errors) {
                        errorMessage += errors[field][0] + '\n';
                    }

                    showCustomNotification(errorMessage, 'error');
                } else {
                    showCustomNotification('Error updating location: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'), 'error');
                }
            }
        });
    });

    // Handle create location form submit
    $('#create-location-form').on('submit', function (e) {
        e.preventDefault();

        // Disable submit button to prevent double submission
        var submitBtn = $(this).find('button[type="submit"]');
        var originalBtnText = submitBtn.html();
        submitBtn.html('Creating...').prop('disabled', true);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                console.log('Success response:', response);

                // Show success message in the form
                $('#create-form-alert').show();

                // Clear the form
                $('#create-location-form')[0].reset();

                // Close the offcanvas after a short delay
                setTimeout(function () {
                    $('#offcanvas_add').offcanvas('hide');
                    $('#create-form-alert').hide();
                }, 1500);

                // Reload the DataTable
                $('#contactslist').DataTable().ajax.reload();
            },
            error: function (xhr, status, error) {
                console.error('Error creating location:', xhr.responseText);

                // Handle validation errors
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = 'Validation errors:\n';

                    for (var field in errors) {
                        errorMessage += errors[field][0] + '\n';
                    }

                    showCustomNotification(errorMessage, 'error');
                } else {
                    showCustomNotification('Error creating location: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'), 'error');
                }
            },
            complete: function () {
                // Re-enable submit button
                submitBtn.html(originalBtnText).prop('disabled', false);
            }
        });
    });
});
