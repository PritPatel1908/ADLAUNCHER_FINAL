$(document).ready(function () {
    // Initialize column visibility from database
    let columnVisibility = {};

    // Set default sort option
    window.currentSortBy = 'newest';

    // Apply initial CSS to hide columns that should be hidden
    function applyInitialColumnVisibility() {
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
            data: { table: 'users' },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                // Initialize all columns as visible by default
                columnVisibility = {
                    'first_name': true,
                    'last_name': true,
                    'username': true,
                    'email': true,
                    'mobile': true,
                    'phone': true,
                    'employee_id': true,
                    'gender': true,
                    'date_of_birth': true,
                    'date_of_joining': true,
                    'companies_count': true,
                    'locations_count': true,
                    'areas_count': true,
                    'roles_count': true,
                    'is_admin': true,
                    'is_client': true,
                    'is_user': true,
                    'last_login_at': true,
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
        localStorage.setItem('userColumnVisibility', JSON.stringify(columnVisibility));

        // Save to server
        $.ajax({
            url: 'columns',
            type: 'POST',
            data: {
                table: 'users',
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
        // $('.data-loading').show();
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
                "url": "/users/data",
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
                    d.first_name_filter = $('.user-filter[data-column="first_name"]').val();
                    d.last_name_filter = $('.user-filter[data-column="last_name"]').val();
                    d.email_filter = $('.user-filter[data-column="email"]').val();
                    d.mobile_filter = $('.user-filter[data-column="mobile"]').val();
                    d.employee_id_filter = $('.user-filter[data-column="employee_id"]').val();

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
                { "data": "first_name", "name": "first_name", "orderable": true },
                { "data": "last_name", "name": "last_name", "orderable": true },
                { "data": "username", "name": "username", "orderable": true },
                { "data": "email", "name": "email", "orderable": true },
                { "data": "mobile", "name": "mobile", "orderable": true },
                { "data": "phone", "name": "phone", "orderable": true },
                { "data": "employee_id", "name": "employee_id", "orderable": true },
                {
                    "data": "gender",
                    "name": "gender",
                    "orderable": true,
                    "render": function (data, type, row) {
                        if (data === 1) return 'Male';
                        if (data === 2) return 'Female';
                        if (data === 3) return 'Other';
                        return 'N/A';
                    }
                },
                {
                    "data": "date_of_birth",
                    "name": "date_of_birth",
                    "orderable": true,
                    "render": function (data, type, row) {
                        return data ? new Date(data).toLocaleDateString() : 'N/A';
                    }
                },
                {
                    "data": "date_of_joining",
                    "name": "date_of_joining",
                    "orderable": true,
                    "render": function (data, type, row) {
                        return data ? new Date(data).toLocaleDateString() : 'N/A';
                    }
                },
                {
                    "data": "companies_count",
                    "name": "companies_count",
                    "orderable": true,
                    "render": function (data, type, row) {
                        return data ? `<span class="badge badge-pill badge-status bg-primary text-white">${data}</span>` : '<span class="badge badge-pill badge-status bg-secondary text-white">0</span>';
                    }
                },
                {
                    "data": "locations_count",
                    "name": "locations_count",
                    "orderable": true,
                    "render": function (data, type, row) {
                        return data ? `<span class="badge badge-pill badge-status bg-info text-white">${data}</span>` : '<span class="badge badge-pill badge-status bg-secondary text-white">0</span>';
                    }
                },
                {
                    "data": "areas_count",
                    "name": "areas_count",
                    "orderable": true,
                    "render": function (data, type, row) {
                        return data ? `<span class="badge badge-pill badge-status bg-warning text-white">${data}</span>` : '<span class="badge badge-pill badge-status bg-secondary text-white">0</span>';
                    }
                },
                {
                    "data": "roles_count",
                    "name": "roles_count",
                    "orderable": true,
                    "render": function (data, type, row) {
                        return data ? `<span class="badge badge-pill badge-status bg-info text-white">${data}</span>` : '<span class="badge badge-pill badge-status bg-secondary text-white">0</span>';
                    }
                },
                {
                    "data": "is_admin",
                    "name": "is_admin",
                    "orderable": true,
                    "render": function (data, type, row) {
                        return data ? '<span class="badge badge-pill badge-status bg-success text-white">Yes</span>' : '<span class="badge badge-pill badge-status bg-secondary text-white">No</span>';
                    }
                },
                {
                    "data": "is_client",
                    "name": "is_client",
                    "orderable": true,
                    "render": function (data, type, row) {
                        return data ? '<span class="badge badge-pill badge-status bg-info text-white">Yes</span>' : '<span class="badge badge-pill badge-status bg-secondary text-white">No</span>';
                    }
                },
                {
                    "data": "is_user",
                    "name": "is_user",
                    "orderable": true,
                    "render": function (data, type, row) {
                        return data ? '<span class="badge badge-pill badge-status bg-primary text-white">Yes</span>' : '<span class="badge badge-pill badge-status bg-secondary text-white">No</span>';
                    }
                },
                {
                    "data": "last_login_at",
                    "name": "last_login_at",
                    "orderable": true,
                    "render": function (data, type, row) {
                        return data ? new Date(data).toLocaleString() : 'N/A';
                    }
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
                                return '<span class="badge badge-pill badge-status bg-warning text-white">Deactivate</span>';
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
                        if (window.userPermissions && window.userPermissions.view) {
                            actions.push(`<a class="dropdown-item" href="/user/${data}"><i class="ti ti-eye me-2"></i>View</a>`);
                        }

                        if (window.userPermissions && window.userPermissions.edit) {
                            actions.push(`<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit" data-id="${data}"><i class="ti ti-edit text-blue"></i> Edit</a>`);
                        }

                        if (window.userPermissions && window.userPermissions.delete) {
                            actions.push(`<a class="dropdown-item delete-user" href="javascript:void(0);" data-id="${data}"><i class="ti ti-trash me-2"></i>Delete</a>`);
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

        // Handle delete user
        $(document).on('click', '.delete-user', function () {
            const userId = $(this).data('id');
            if (confirm('Are you sure you want to delete this user?')) {
                $.ajax({
                    url: `/user/${userId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            // Reload the DataTable
                            dataTable.ajax.reload();
                            // Show success message
                            alert('User deleted successfully!');
                        } else {
                            alert(response.message || 'Failed to delete user.');
                        }
                    },
                    error: function (xhr) {
                        console.error('Error deleting user:', xhr);
                        alert('Failed to delete user. Please try again.');
                    }
                });
            }
        });

        // Handle edit user
        $(document).on('click', '[data-bs-target="#offcanvas_edit"]', function () {
            const userId = $(this).data('id');

            // Set the user ID to the form
            $('#edit-user-form').data('user-id', userId);
            $('#edit-user-form').attr('action', `/user/${userId}`);

            // Fetch user data via AJAX
            $.ajax({
                url: `/user/${userId}/edit`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success && response.user) {
                        const user = response.user;
                        console.log('User data received:', user);
                        console.log('Date of birth:', user.date_of_birth);
                        console.log('Date of joining:', user.date_of_joining);

                        // Populate form fields
                        $('#edit-first_name').val(user.first_name);
                        $('#edit-middle_name').val(user.middle_name);
                        $('#edit-last_name').val(user.last_name);
                        $('#edit-email').val(user.email);
                        $('#edit-mobile').val(user.mobile);
                        $('#edit-phone').val(user.phone);
                        $('#edit-employee_id').val(user.employee_id);
                        $('#edit-gender').val(user.gender);

                        // Format dates for HTML date input (YYYY-MM-DD)
                        function formatDateForInput(dateValue) {
                            if (!dateValue) return '';

                            try {
                                // Handle different date formats
                                let date;
                                if (typeof dateValue === 'string') {
                                    // If it's already in YYYY-MM-DD format, use it directly
                                    if (/^\d{4}-\d{2}-\d{2}$/.test(dateValue)) {
                                        return dateValue;
                                    }
                                    // Try to parse the date
                                    date = new Date(dateValue);
                                } else if (dateValue instanceof Date) {
                                    date = dateValue;
                                } else {
                                    return '';
                                }

                                if (!isNaN(date.getTime())) {
                                    // Use local date formatting to avoid timezone issues
                                    const year = date.getFullYear();
                                    const month = String(date.getMonth() + 1).padStart(2, '0');
                                    const day = String(date.getDate()).padStart(2, '0');
                                    return `${year}-${month}-${day}`;
                                }
                                return '';
                            } catch (e) {
                                console.error('Error formatting date:', e);
                                return '';
                            }
                        }

                        const formattedBirthDate = formatDateForInput(user.date_of_birth);
                        $('#edit-date_of_birth').val(formattedBirthDate);
                        console.log('Original birth date:', user.date_of_birth, 'Formatted:', formattedBirthDate);

                        const formattedJoiningDate = formatDateForInput(user.date_of_joining);
                        $('#edit-date_of_joining').val(formattedJoiningDate);
                        console.log('Original joining date:', user.date_of_joining, 'Formatted:', formattedJoiningDate);
                        // Convert status integer to string
                        let statusValue = 'active';
                        if (user.status == 0) statusValue = 'delete';
                        else if (user.status == 1) statusValue = 'active';
                        else if (user.status == 2) statusValue = 'deactivate';
                        else if (user.status == 3) statusValue = 'block';
                        $('#edit-status').val(statusValue);

                        // Populate companies
                        if (user.companies && user.companies.length > 0) {
                            const companyIds = user.companies.map(company => company.id);
                            $('#edit-company_ids').val(companyIds).trigger('change');
                        } else {
                            $('#edit-company_ids').val([]).trigger('change');
                        }

                        // Populate locations
                        if (user.locations && user.locations.length > 0) {
                            const locationIds = user.locations.map(location => location.id);
                            $('#edit-location_ids').val(locationIds).trigger('change');
                        } else {
                            $('#edit-location_ids').val([]).trigger('change');
                        }

                        // Populate areas
                        if (user.areas && user.areas.length > 0) {
                            const areaIds = user.areas.map(area => area.id);
                            $('#edit-area_ids').val(areaIds).trigger('change');
                        } else {
                            $('#edit-area_ids').val([]).trigger('change');
                        }

                        // Populate roles
                        if (user.roles && user.roles.length > 0) {
                            const roleIds = user.roles.map(role => role.id);
                            $('#edit-role_ids').val(roleIds).trigger('change');
                        } else {
                            $('#edit-role_ids').val([]).trigger('change');
                        }

                        // Re-initialize Select2 for edit form
                        $('#edit-company_ids, #edit-location_ids, #edit-area_ids, #edit-role_ids').select2({
                            theme: 'default',
                            width: '100%',
                            placeholder: 'Choose...',
                            allowClear: true,
                            closeOnSelect: false,
                            tags: false,
                            tokenSeparators: [',', ' ']
                        });

                        // Show the edit form
                        $('#offcanvas_edit').offcanvas('show');
                    } else {
                        alert('Failed to load user data. Please try again.');
                    }
                },
                error: function (xhr) {
                    console.error('Error loading user data:', xhr);
                    console.error('Response text:', xhr.responseText);
                    console.error('Status:', xhr.status);

                    let errorMessage = 'Failed to load user data. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    alert(errorMessage);
                }
            });
        });

        // Handle form submission for creating user
        $('#create-user-form').on('submit', function (e) {
            e.preventDefault();
            console.log('User form submission started');

            // Disable submit button to prevent double submission
            var submitBtn = $(this).find('button[type="submit"]');
            var originalBtnText = submitBtn.html();
            submitBtn.html('Creating...').prop('disabled', true);

            var formData = $(this).serialize();
            console.log('Form data being sent:', formData);
            console.log('CSRF token:', $('meta[name="csrf-token"]').attr('content'));

            $.ajax({
                url: '/user',
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
                        $createAlert.html('User created successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                        $createAlertWrapper.show();

                        // Clear the form
                        $('#create-user-form')[0].reset();

                        // Reset Select2 dropdowns
                        $('#create-user-form .select2-multiple').val([]).trigger('change');

                        // Reload the DataTable to show the new user
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
                        $createAlertErr.html(`Failed to create user: ${response.message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                        $createAlertWrapperErr.show();
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error creating user:', xhr.responseText);
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response status:', xhr.status);
                    console.error('Response headers:', xhr.getAllResponseHeaders());

                    let errorMessage = 'Error creating user. Please try again.';
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

        // Handle form submission for editing user
        $('#edit-user-form').on('submit', function (e) {
            e.preventDefault();
            console.log('Edit user form submission started');

            // Disable submit button to prevent double submission
            var submitBtn = $(this).find('button[type="submit"]');
            var originalBtnText = submitBtn.html();
            submitBtn.html('Updating...').prop('disabled', true);

            const userId = $(this).data('user-id');
            const formData = $(this).serialize();

            $.ajax({
                url: `/user/${userId}`,
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
                        $editAlert2.html('User updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
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
                        $editAlert3.html(`Failed to update user: ${response.message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                        $editAlertWrapper3.show();
                    }
                },
                error: function (xhr) {
                    console.error('Error updating user:', xhr);

                    let errorMessage = 'Failed to update user. Please try again.';
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

        // Reset forms once the offcanvas fully closes (mirror company behavior)
        $(document).on('hidden.bs.offcanvas', '#offcanvas_add', function () {
            var $form = $('#create-user-form');
            if ($form.length) {
                try {
                    $form[0].reset();
                    // Reset Select2
                    $form.find('.select2-multiple').val([]).trigger('change');
                    // Hide alerts
                    $('#create-form-alert').hide();
                } catch (err) {
                    console.error('Error resetting create form after close:', err);
                }
            }
        });

        $(document).on('hidden.bs.offcanvas', '#offcanvas_edit', function () {
            var $form = $('#edit-user-form');
            if ($form.length) {
                try {
                    $form[0].reset();
                    $('#edit-company_ids').val([]).trigger('change');
                    $('#edit-location_ids').val([]).trigger('change');
                    $('#edit-area_ids').val([]).trigger('change');
                    $('#edit-role_ids').val([]).trigger('change');
                    $('#edit-form-alert').hide();
                } catch (err) {
                    console.error('Error resetting edit form after close:', err);
                }
            }
        });
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
