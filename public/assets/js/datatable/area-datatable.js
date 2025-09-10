$(document).ready(function () {
    // Initialize column visibility from database
    let columnVisibility = {};

    // Set default sort option
    window.currentSortBy = 'newest';

    // Apply initial CSS to hide columns that should be hidden
    function applyInitialColumnVisibility() {
        // Get saved column visibility from localStorage if available
        const savedVisibility = localStorage.getItem('areaColumnVisibility');
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
            data: { table: 'areas' },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                // Initialize all columns as visible by default
                columnVisibility = {
                    'name': true,
                    'description': true,
                    'code': true,
                    'locations_count': true,
                    'companies_count': true,
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
                    localStorage.setItem('areaColumnVisibility', JSON.stringify(columnVisibility));
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
        localStorage.setItem('areaColumnVisibility', JSON.stringify(columnVisibility));

        // Save to server
        $.ajax({
            url: 'columns',
            type: 'POST',
            data: {
                table: 'areas',
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
                localStorage.setItem('areaColumnVisibility', JSON.stringify(columnVisibility));

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
    loadColumnVisibility().then(function () {
        if ($('#areaslist').length > 0) {
            // Initialize the DataTable - use window scope to ensure it's accessible everywhere
            window.dataTable = $('#areaslist').DataTable({
                "processing": true,
                "serverSide": true,
                "bFilter": false,
                "bInfo": false,
                "ordering": true,
                "autoWidth": true,
                "order": [[0, 'asc']], // Default order by first column ascending
                "orderCellsTop": true, // Enable ordering on header cells
                "ajax": {
                    "url": "/areas/data",
                    "type": "GET",
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    "data": function (d) {
                        // Add custom filter parameters
                        d.name_filter = $('.area-filter[data-column="name"]').val();
                        d.description_filter = $('.area-filter[data-column="description"]').val();
                        d.code_filter = $('.area-filter[data-column="code"]').val();

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
                        $('#error-container').show();
                        $('#error-message').text('Failed to load area data. Please try again.');

                        // Show a more user-friendly error dialog
                        if (typeof alert !== 'undefined') {
                            alert('Failed to load area data. Please try again.');
                        }
                    }
                },
                "columns": [
                    { "data": "name", "name": "name", "orderable": true },
                    { "data": "description", "name": "description", "orderable": true },
                    { "data": "code", "name": "code", "orderable": true },
                    {
                        "data": "locations_count",
                        "name": "locations_count",
                        "orderable": true,
                        "render": function (data, type, row) {
                            return data ? `<span class="badge badge-pill badge-status bg-info text-white">${data}</span>` : '<span class="badge badge-pill badge-status bg-secondary text-white">0</span>';
                        }
                    },
                    {
                        "data": "companies_count",
                        "name": "companies_count",
                        "orderable": true,
                        "render": function (data, type, row) {
                            return data ? `<span class="badge badge-pill badge-status bg-warning text-white">${data}</span>` : '<span class="badge badge-pill badge-status bg-secondary text-white">0</span>';
                        }
                    },
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
                            // Use status_value for numeric comparison and status for display text
                            const statusValue = row.status_value !== undefined ? row.status_value : data;

                            if (type === 'display') {
                                switch (parseInt(statusValue)) {
                                    case 1:
                                        return '<span class="badge badge-pill badge-status bg-success text-white">Activate</span>';
                                    case 2:
                                        return '<span class="badge badge-pill badge-status bg-warning text-white">Inactive</span>';
                                    case 3:
                                        return '<span class="badge badge-pill badge-status bg-danger text-white">Block</span>';
                                    case 0:
                                        return '<span class="badge badge-pill badge-status bg-secondary text-white">Delete</span>';
                                    default:
                                        return '<span class="badge badge-pill badge-status bg-secondary text-white">Unknown</span>';
                                }
                            }
                            return data;
                        }
                    },
                    {
                        "data": "id",
                        "orderable": false,
                        "name": "action",
                        "render": function (data, type, row) {
                            let actions = [];

                            // Check permissions and add actions accordingly
                            if (window.areaPermissions && window.areaPermissions.view) {
                                actions.push(`<a class="dropdown-item" href="/area/${data}"><i class="ti ti-eye me-2"></i>View</a>`);
                            }

                            if (window.areaPermissions && window.areaPermissions.edit) {
                                actions.push(`<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit" data-id="${data}"><i class="ti ti-edit text-blue"></i> Edit</a>`);
                            }

                            if (window.areaPermissions && window.areaPermissions.delete) {
                                actions.push(`<a class="dropdown-item delete-area" href="javascript:void(0);" data-id="${data}"><i class="ti ti-trash me-2"></i>Delete</a>`);
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
                    console.log('Available headers at init:', $('#areaslist thead th').length);

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
                            console.log('Available headers:', $('#areaslist thead th').length);
                            updateSortIndicators(columnIndex, direction);
                        } else {
                            console.error('No order information available');
                        }
                    });
                }
            });

            // Store DataTable instance in window for global access
            window.areaDataTable = dataTable;

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
            $('.area-filter').on('keyup', function () {
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
                $('.dropdown-toggle.btn-outline-light').first().html(`<i class="ti ti-sort-ascending-2 me-2"></i>${sortText}`);

                // Reload the DataTable with the new sort option
                dataTable.ajax.reload();
            });

            // Function to update sort indicators in the table header
            function updateSortIndicators(columnIndex, direction) {
                console.log('Updating sort indicators for column:', columnIndex, 'direction:', direction);

                // First, remove all existing sort indicators
                $('#areaslist thead th').removeClass('sorting_asc sorting_desc').addClass('sorting');

                // Then, add the appropriate class to the sorted column
                const $thElement = $(`#areaslist thead th:eq(${columnIndex})`);
                $thElement.removeClass('sorting');
                $thElement.addClass(direction === 'asc' ? 'sorting_asc' : 'sorting_desc');
            }

            // Handle delete area
            $(document).on('click', '.delete-area', function () {
                const areaId = $(this).data('id');
                if (confirm('Are you sure you want to delete this area?')) {
                    $.ajax({
                        url: `/area/${areaId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                // Reload the DataTable
                                dataTable.ajax.reload();
                                // Show success message
                                alert('Area deleted successfully!');
                            } else {
                                alert(response.message || 'Failed to delete area.');
                            }
                        },
                        error: function (xhr) {
                            console.error('Error deleting area:', xhr);
                            alert('Failed to delete area. Please try again.');
                        }
                    });
                }
            });

            // Handle edit area
            $(document).on('click', '[data-bs-target="#offcanvas_edit"]', function () {
                const areaId = $(this).data('id');

                // Set the area ID to the form
                $('#edit-area-form').data('area-id', areaId);
                $('#edit-area-form').attr('action', `/area/${areaId}`);

                // Fetch area data via AJAX
                $.ajax({
                    url: `/area/${areaId}/edit`,
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success && response.area) {
                            const area = response.area;

                            // Populate form fields
                            $('#edit-name').val(area.name);
                            $('#edit-description').val(area.description);
                            $('#edit-code').val(area.code);
                            $('#edit-status').val(area.status.toString());

                            // Populate locations
                            if (area.locations && area.locations.length > 0) {
                                const locationIds = area.locations.map(loc => loc.id);
                                $('#edit-location_ids').val(locationIds).trigger('change');
                            } else {
                                $('#edit-location_ids').val([]).trigger('change');
                            }

                            // Re-initialize Select2 for edit form
                            $('#edit-location_ids').select2({
                                theme: 'default',
                                width: '100%',
                                placeholder: 'Choose locations...',
                                allowClear: true,
                                closeOnSelect: false,
                                tags: false,
                                tokenSeparators: [',', ' ']
                            });

                            // Populate companies
                            if (area.companies && area.companies.length > 0) {
                                const companyIds = area.companies.map(comp => comp.id);
                                $('#edit-company_ids').val(companyIds).trigger('change');
                            } else {
                                $('#edit-company_ids').val([]).trigger('change');
                            }

                            // Re-initialize Select2 for companies
                            $('#edit-company_ids').select2({
                                theme: 'default',
                                width: '100%',
                                placeholder: 'Choose companies...',
                                allowClear: true,
                                closeOnSelect: false,
                                tags: false,
                                tokenSeparators: [',', ' ']
                            });

                            // Show the edit form
                            $('#offcanvas_edit').offcanvas('show');
                        } else {
                            alert('Failed to load area data. Please try again.');
                        }
                    },
                    error: function (xhr) {
                        console.error('Error loading area data:', xhr);
                        console.error('Response text:', xhr.responseText);
                        console.error('Status:', xhr.status);

                        let errorMessage = 'Failed to load area data. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        alert(errorMessage);
                    }
                });
            });

            // Handle form submission for creating area
            $('#create-area-form').on('submit', function (e) {
                e.preventDefault();
                console.log('Area form submission started');

                // Disable submit button to prevent double submission
                var submitBtn = $(this).find('button[type="submit"]');
                var originalBtnText = submitBtn.html();
                submitBtn.html('Creating...').prop('disabled', true);

                var formData = $(this).serialize();
                console.log('Form data being sent:', formData);
                console.log('CSRF token:', $('meta[name="csrf-token"]').attr('content'));

                $.ajax({
                    url: '/area',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        console.log('Success response:', response);

                        if (response.success) {
                            // Show success message
                            $('#create-form-alert').removeClass('alert-danger').addClass('alert-success');
                            $('#create-form-alert').html('Area created successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                            $('#create-form-alert').show();

                            // Clear the form
                            $('#create-area-form')[0].reset();

                            // Reset Select2 dropdowns
                            $('#create-area-form .select2-multiple').val([]).trigger('change');

                            // Reload the DataTable to show the new area
                            dataTable.ajax.reload();

                            // Close the offcanvas after a delay
                            setTimeout(function () {
                                $('#offcanvas_add').offcanvas('hide');
                            }, 2000);
                        } else {
                            $('#create-form-alert').removeClass('alert-success').addClass('alert-danger');
                            $('#create-form-alert').html(`Failed to create area: ${response.message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                            $('#create-form-alert').show();
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error creating area:', xhr.responseText);
                        console.error('Status:', status);
                        console.error('Error:', error);
                        console.error('Response status:', xhr.status);
                        console.error('Response headers:', xhr.getAllResponseHeaders());

                        let errorMessage = 'Error creating area. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = '<ul>';
                            for (const field in xhr.responseJSON.errors) {
                                errorMessage += `<li>${xhr.responseJSON.errors[field][0]}</li>`;
                            }
                            errorMessage += '</ul>';
                        }

                        $('#create-form-alert').removeClass('alert-success').addClass('alert-danger');
                        $('#create-form-alert').html(`${errorMessage} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                        $('#create-form-alert').show();
                    },
                    complete: function () {
                        // Re-enable submit button
                        submitBtn.html(originalBtnText).prop('disabled', false);
                    }
                });
            });

            // Handle form submission for editing area (only on index page)
            $('#edit-area-form').on('submit', function (e) {
                // Only handle form submission on the index page (where DataTable exists)
                if (typeof dataTable === 'undefined') {
                    return; // Let the show page handler take over
                }

                e.preventDefault();

                const areaId = $(this).data('area-id');
                const formData = $(this).serialize();

                $.ajax({
                    url: `/area/${areaId}`,
                    type: 'PUT',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            // Show success message
                            $('#edit-form-alert').removeClass('alert-danger').addClass('alert-success');
                            $('#edit-form-alert').html('Area updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                            $('#edit-form-alert').show();

                            // Reload the DataTable
                            dataTable.ajax.reload();

                            // Close the offcanvas after a delay
                            setTimeout(function () {
                                $('#offcanvas_edit').offcanvas('hide');
                            }, 2000);
                        } else {
                            $('#edit-form-alert').removeClass('alert-success').addClass('alert-danger');
                            $('#edit-form-alert').html(`Failed to update area: ${response.message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                            $('#edit-form-alert').show();
                        }
                    },
                    error: function (xhr) {
                        console.error('Error updating area:', xhr);

                        let errorMessage = 'Failed to update area. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = '<ul>';
                            for (const field in xhr.responseJSON.errors) {
                                errorMessage += `<li>${xhr.responseJSON.errors[field][0]}</li>`;
                            }
                            errorMessage += '</ul>';
                        }

                        $('#edit-form-alert').removeClass('alert-success').addClass('alert-danger');
                        $('#edit-form-alert').html(`${errorMessage} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                        $('#edit-form-alert').show();
                    }
                });
            });
        }
    });
});
