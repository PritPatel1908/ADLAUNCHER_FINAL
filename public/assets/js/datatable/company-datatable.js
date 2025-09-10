$(document).ready(function () {
    // Initialize column visibility from database
    let columnVisibility = {};

    // Set default sort option
    window.currentSortBy = 'newest';

    // Apply initial CSS to hide columns that should be hidden
    function applyInitialColumnVisibility() {
        // Get saved column visibility from localStorage if available
        const savedVisibility = localStorage.getItem('companyColumnVisibility');
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
            data: { table: 'companies' },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                // Initialize all columns as visible by default
                columnVisibility = {
                    'name': true,
                    'industry': true,
                    'website': true,
                    'email': true,
                    'phone': true,
                    'locations_count': true,
                    'addresses_count': true,
                    'contacts_count': true,
                    'notes_count': true,
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
                    localStorage.setItem('companyColumnVisibility', JSON.stringify(columnVisibility));
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
        localStorage.setItem('companyColumnVisibility', JSON.stringify(columnVisibility));

        // Save to server
        $.ajax({
            url: 'columns',
            type: 'POST',
            data: {
                table: 'companies',
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
                localStorage.setItem('companyColumnVisibility', JSON.stringify(columnVisibility));

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
        if ($('#companieslist').length > 0) {
            // Initialize the DataTable - use window scope to ensure it's accessible everywhere
            window.dataTable = $('#companieslist').DataTable({
                "processing": true,
                "serverSide": true,
                "bFilter": false,
                "bInfo": false,
                "ordering": true,
                "autoWidth": true,
                "order": [[0, 'asc']], // Default order by first column ascending
                "orderCellsTop": true, // Enable ordering on header cells
                "ajax": {
                    "url": "/companies/data",
                    "type": "GET",
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    "data": function (d) {
                        // Add custom filter parameters
                        d.name_filter = $('.company-filter[data-column="name"]').val();
                        d.industry_filter = $('.company-filter[data-column="industry"]').val();
                        d.website_filter = $('.company-filter[data-column="website"]').val();
                        d.email_filter = $('.company-filter[data-column="email"]').val();
                        d.phone_filter = $('.company-filter[data-column="phone"]').val();

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
                        $('#error-message').text('Failed to load company data. Please try again.');

                        // Show a more user-friendly error dialog
                        if (typeof alert !== 'undefined') {
                            alert('Failed to load company data. Please try again.');
                        }
                    }
                },
                "columns": [
                    { "data": "name", "name": "name", "orderable": true },
                    { "data": "industry", "name": "industry", "orderable": true },
                    { "data": "website", "name": "website", "orderable": true },
                    { "data": "email", "name": "email", "orderable": true },
                    { "data": "phone", "name": "phone", "orderable": true },
                    {
                        "data": "locations_count",
                        "name": "locations_count",
                        "orderable": true,
                        "render": function (data, type, row) {
                            return data ? `<span class="badge badge-pill badge-status bg-info text-white">${data}</span>` : '<span class="badge badge-pill badge-status bg-secondary text-white">0</span>';
                        }
                    },
                    {
                        "data": "addresses_count",
                        "name": "addresses_count",
                        "orderable": true,
                        "render": function (data, type, row) {
                            return data ? `<span class="badge badge-pill badge-status bg-warning text-white">${data}</span>` : '<span class="badge badge-pill badge-status bg-secondary text-white">0</span>';
                        }
                    },
                    {
                        "data": "contacts_count",
                        "name": "contacts_count",
                        "orderable": true,
                        "render": function (data, type, row) {
                            return data ? `<span class="badge badge-pill badge-status bg-primary text-white">${data}</span>` : '<span class="badge badge-pill badge-status bg-secondary text-white">0</span>';
                        }
                    },
                    {
                        "data": "notes_count",
                        "name": "notes_count",
                        "orderable": true,
                        "render": function (data, type, row) {
                            return data ? `<span class="badge badge-pill badge-status bg-dark text-white">${data}</span>` : '<span class="badge badge-pill badge-status bg-secondary text-white">0</span>';
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
                            // Support server sending string labels or numeric codes
                            var label = (typeof data === 'string') ? data.toLowerCase() : data;
                            var badgeClass = 'bg-secondary';
                            var text = 'Inactive';
                            if (label === 1 || label === '1' || label === 'active') {
                                badgeClass = 'bg-success';
                                text = 'Active';
                            } else if (label === 2 || label === '2' || label === 'inactive' || label === 'deactivate') {
                                badgeClass = 'bg-danger';
                                text = 'Inactive';
                            } else if (label === 3 || label === '3' || label === 'block' || label === 'blocked') {
                                badgeClass = 'bg-warning';
                                text = 'Block';
                            } else if (label === 0 || label === '0' || label === 'delete' || label === 'deleted') {
                                badgeClass = 'bg-danger';
                                text = 'Delete';
                            }
                            return '<span class="badge badge-pill badge-status ' + badgeClass + ' text-white">' + text + '</span>';
                        }
                    },
                    {
                        "data": "id",
                        "orderable": false,
                        "name": "action",
                        "render": function (data, type, row) {
                            let actions = [];

                            // Check permissions and add actions accordingly
                            if (window.companyPermissions && window.companyPermissions.view) {
                                actions.push(`<a class="dropdown-item" href="/company/${data}"><i class="ti ti-eye me-2"></i>View</a>`);
                            }

                            if (window.companyPermissions && window.companyPermissions.edit) {
                                actions.push(`<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit" data-id="${data}"><i class="ti ti-edit text-blue"></i> Edit</a>`);
                            }

                            if (window.companyPermissions && window.companyPermissions.delete) {
                                actions.push(`<a class="dropdown-item delete-company" href="javascript:void(0);" data-id="${data}"><i class="ti ti-trash me-2"></i>Delete</a>`);
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
                    console.log('Available headers at init:', $('#companieslist thead th').length);

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
                            console.log('Available headers:', $('#companieslist thead th').length);
                            updateSortIndicators(columnIndex, direction);
                        } else {
                            console.error('No order information available');
                        }
                    });
                }
            });

            // Store DataTable instance in window for global access
            window.companyDataTable = dataTable;

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
            $('.company-filter').on('keyup', function () {
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
                $('#companieslist thead th').removeClass('sorting_asc sorting_desc').addClass('sorting');

                // Then, add the appropriate class to the sorted column
                const $thElement = $(`#companieslist thead th:eq(${columnIndex})`);
                $thElement.removeClass('sorting');
                $thElement.addClass(direction === 'asc' ? 'sorting_asc' : 'sorting_desc');
            }

            // Handle delete company
            $(document).on('click', '.delete-company', function () {
                const companyId = $(this).data('id');
                if (confirm('Are you sure you want to delete this company?')) {
                    $.ajax({
                        url: `/company/${companyId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                // Reload the DataTable
                                dataTable.ajax.reload();
                                // Show success message
                                alert('Company deleted successfully!');
                            } else {
                                alert(response.message || 'Failed to delete company.');
                            }
                        },
                        error: function (xhr) {
                            console.error('Error deleting company:', xhr);
                            alert('Failed to delete company. Please try again.');
                        }
                    });
                }
            });

            // Handle edit company
            $(document).on('click', '[data-bs-target="#offcanvas_edit"]', function () {
                const companyId = $(this).data('id');

                // Set the company ID to the form
                $('#edit-company-form').data('company-id', companyId);
                $('#edit-company-form').attr('action', `/company/${companyId}`);

                // Show loading state using inner alert element for proper styling
                var $editAlertWrapper = $('#edit-form-alert');
                var $editAlert = $editAlertWrapper.find('.alert');
                if ($editAlert.length === 0) {
                    $editAlertWrapper.html('<div class="alert alert-info alert-dismissible fade show" role="alert">Loading company data...<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                } else {
                    $editAlert.removeClass('alert-success alert-danger').addClass('alert-info');
                    $editAlert.html('Loading company data... <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                }
                $editAlertWrapper.show();

                // Add loading state to form
                $('#edit-company-form').addClass('form-loading');

                // Fetch company data via AJAX
                $.ajax({
                    url: `/company/${companyId}/edit`,
                    type: 'GET',
                    timeout: 10000, // 10 second timeout
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success && response.company) {
                            const company = response.company;

                            // Populate form fields
                            $('#edit-name').val(company.name);
                            $('#edit-industry').val(company.industry);
                            $('#edit-website').val(company.website);
                            $('#edit-email').val(company.email);
                            $('#edit-phone').val(company.phone);
                            // Convert status integer to string
                            let statusValue = 'active';
                            if (company.status == 0) statusValue = 'delete';
                            else if (company.status == 1) statusValue = 'active';
                            else if (company.status == 2) statusValue = 'inactive';
                            else if (company.status == 3) statusValue = 'block';
                            $('#edit-status').val(statusValue);

                            // Re-initialize Select2 for edit form first
                            $('#edit-location_ids').select2({
                                theme: 'default',
                                width: '100%',
                                placeholder: 'Choose locations...',
                                allowClear: true,
                                closeOnSelect: false,
                                tags: false,
                                tokenSeparators: [',', ' ']
                            });

                            // Populate locations after Select2 is initialized
                            if (company.locations && company.locations.length > 0) {
                                const locationIds = company.locations.map(loc => loc.id);
                                $('#edit-location_ids').val(locationIds).trigger('change');
                            } else {
                                $('#edit-location_ids').val([]).trigger('change');
                            }

                            // Populate addresses
                            $('#edit-addresses-container').empty();
                            editAddressCounter = 0;
                            if (company.addresses && company.addresses.length > 0) {
                                console.log('Addresses data from server:', company.addresses);
                                company.addresses.forEach((address, index) => {
                                    console.log(`Setting address ${index + 1}:`, address);
                                    addEditAddress();
                                    const lastAddress = $('#edit-addresses-container .address-item').last();
                                    console.log(`Setting type to: "${address.type}"`);
                                    const typeSelect = lastAddress.find('select[name*="[type]"]');
                                    const typeValue = address.type ? address.type.toLowerCase() : '';
                                    console.log(`Normalized type value: "${typeValue}"`);
                                    typeSelect.val(typeValue);

                                    // If the value doesn't match any option, try to find a close match
                                    if (typeSelect.val() !== typeValue) {
                                        console.log(`Type value "${typeValue}" not found in options, trying to find match...`);
                                        const options = typeSelect.find('option');
                                        let found = false;
                                        options.each(function () {
                                            const optionValue = $(this).val().toLowerCase();
                                            if (optionValue === typeValue) {
                                                typeSelect.val($(this).val());
                                                found = true;
                                                console.log(`Found matching option: "${$(this).val()}"`);
                                                return false; // break the loop
                                            }
                                        });
                                        // If still not found, add it as a new option
                                        if (!found && typeValue) {
                                            console.log(`Adding new option for type "${typeValue}"`);
                                            const newOption = new Option(address.type, typeValue);
                                            typeSelect.append(newOption);
                                            typeSelect.val(typeValue);
                                        } else if (!found) {
                                            console.log(`No match found for type "${typeValue}", setting to empty`);
                                            typeSelect.val('');
                                        }
                                    }
                                    lastAddress.find('input[name*="[address]"]').val(address.address);
                                    lastAddress.find('input[name*="[city]"]').val(address.city);
                                    lastAddress.find('input[name*="[state]"]').val(address.state);
                                    lastAddress.find('input[name*="[country]"]').val(address.country);
                                    lastAddress.find('input[name*="[zip_code]"]').val(address.zip_code);
                                });
                            }

                            // Populate contacts
                            $('#edit-contacts-container').empty();
                            editContactCounter = 0;
                            if (company.contacts && company.contacts.length > 0) {
                                company.contacts.forEach(contact => {
                                    addEditContact();
                                    const lastContact = $('#edit-contacts-container .contact-item').last();
                                    lastContact.find('input[name*="[name]"]').val(contact.name);
                                    lastContact.find('input[name*="[email]"]').val(contact.email);
                                    lastContact.find('input[name*="[phone]"]').val(contact.phone);
                                    lastContact.find('input[name*="[designation]"]').val(contact.designation);
                                    if (contact.is_primary) {
                                        lastContact.find('input[name*="[is_primary]"]').prop('checked', true);
                                    }
                                });
                            }

                            // Populate notes
                            $('#edit-notes-container').empty();
                            editNoteCounter = 0;
                            if (company.notes && company.notes.length > 0) {
                                company.notes.forEach(note => {
                                    addEditNote();
                                    const lastNote = $('#edit-notes-container .note-item').last();
                                    lastNote.find('textarea[name*="[note]"]').val(note.note);
                                    // Set the numeric status value directly
                                    lastNote.find('select[name*="[status]"]').val(note.status);
                                });
                            }

                            // Clear any previous alerts and remove loading state
                            $('#edit-form-alert').hide();
                            $('#edit-company-form').removeClass('form-loading');

                            // Show the edit form
                            $('#offcanvas_edit').offcanvas('show');
                        } else {
                            alert('Failed to load company data. Please try again.');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error loading company data:', xhr.responseText);
                        console.error('Status:', status);
                        console.error('Error:', error);
                        console.error('Response status:', xhr.status);

                        let errorMessage = 'Failed to load company data. Please try again.';
                        if (status === 'timeout') {
                            errorMessage = 'Request timed out. Please try again.';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 404) {
                            errorMessage = 'Company not found.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Server error occurred. Please try again.';
                        }

                        // Show error in a more user-friendly way and remove loading state
                        var $editAlertWrapperErr = $('#edit-form-alert');
                        var $editAlertErr = $editAlertWrapperErr.find('.alert');
                        if ($editAlertErr.length === 0) {
                            $editAlertWrapperErr.html('<div class="alert alert-danger alert-dismissible fade show" role="alert"></div>');
                            $editAlertErr = $editAlertWrapperErr.find('.alert');
                        }
                        $editAlertErr.removeClass('alert-success alert-info').addClass('alert-danger');
                        $editAlertErr.html(`${errorMessage} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                        $editAlertWrapperErr.show();
                        $('#edit-company-form').removeClass('form-loading');
                    }
                });
            });

            // Reset forms once the offcanvas fully closes
            $(document).on('hidden.bs.offcanvas', '#offcanvas_add', function () {
                var $form = $('#create-company-form');
                if ($form.length) {
                    try {
                        $form[0].reset();
                        // Reset Select2
                        $form.find('.select2-multiple').val([]).trigger('change');
                        // Clear dynamic sections and counters
                        $('#addresses-container').empty();
                        $('#contacts-container').empty();
                        $('#notes-container').empty();
                        addressCounter = 1;
                        contactCounter = 1;
                        noteCounter = 1;
                        // Hide alerts
                        $('#create-form-alert').hide();
                        // Prepare one fresh slot for next open
                        ensureAtLeastOneAddress();
                        ensureAtLeastOneContact();
                        ensureAtLeastOneNote();
                    } catch (err) {
                        console.error('Error resetting create form after close:', err);
                    }
                }
            });

            // When the Add Company offcanvas opens, ensure one default slot exists
            $(document).on('shown.bs.offcanvas', '#offcanvas_add', function () {
                ensureAtLeastOneAddress();
                ensureAtLeastOneContact();
                ensureAtLeastOneNote();
            });

            $(document).on('hidden.bs.offcanvas', '#offcanvas_edit', function () {
                var $form = $('#edit-company-form');
                if ($form.length) {
                    try {
                        $form[0].reset();
                        $('#edit-location_ids').val([]).trigger('change');
                        $('#edit-addresses-container').empty();
                        $('#edit-contacts-container').empty();
                        $('#edit-notes-container').empty();
                        editAddressCounter = 0;
                        editContactCounter = 0;
                        editNoteCounter = 0;
                        $('#edit-form-alert').hide();
                    } catch (err) {
                        console.error('Error resetting edit form after close:', err);
                    }
                }
            });

            // Handle form submission for creating company
            $('#create-company-form').on('submit', function (e) {
                e.preventDefault();
                console.log('Company form submission started');

                // Disable submit button to prevent double submission
                var submitBtn = $(this).find('button[type="submit"]');
                var originalBtnText = submitBtn.html();
                submitBtn.html('Creating...').prop('disabled', true);

                var formData = $(this).serialize();
                console.log('Form data being sent:', formData);
                console.log('CSRF token:', $('meta[name="csrf-token"]').attr('content'));

                $.ajax({
                    url: '/company',
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
                            $createAlert.html('Company created successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                            $createAlertWrapper.show();

                            // Clear the form
                            $('#create-company-form')[0].reset();

                            // Reset Select2 dropdowns
                            $('#create-company-form .select2-multiple').val([]).trigger('change');

                            // Clear dynamic fields
                            $('#addresses-container').empty();
                            $('#contacts-container').empty();
                            $('#notes-container').empty();
                            addressCounter = 1;
                            contactCounter = 1;
                            noteCounter = 1;

                            // Add back one blank slot in each section for convenience
                            ensureAtLeastOneAddress();
                            ensureAtLeastOneContact();
                            ensureAtLeastOneNote();

                            // Reload the DataTable to show the new company
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
                            $createAlertErr.html(`Failed to create company: ${response.message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                            $createAlertWrapperErr.show();
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error creating company:', xhr.responseText);
                        console.error('Status:', status);
                        console.error('Error:', error);
                        console.error('Response status:', xhr.status);
                        console.error('Response headers:', xhr.getAllResponseHeaders());

                        let errorMessage = 'Error creating company. Please try again.';
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

            // Handle form submission for editing company
            $('#edit-company-form').on('submit', function (e) {
                e.preventDefault();
                console.log('Edit company form submission started');

                // Disable submit button to prevent double submission
                var submitBtn = $(this).find('button[type="submit"]');
                var originalBtnText = submitBtn.html();
                submitBtn.html('Updating...').prop('disabled', true);

                const companyId = $(this).data('company-id');
                const formData = $(this).serialize();
                console.log('Edit form data being sent:', formData);

                $.ajax({
                    url: `/company/${companyId}`,
                    type: 'PUT',
                    data: formData,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        console.log('Edit success response:', response);

                        if (response.success) {
                            // Show success message using inner alert element for proper styling
                            var $editAlertWrapper2 = $('#edit-form-alert');
                            var $editAlert2 = $editAlertWrapper2.find('.alert');
                            if ($editAlert2.length === 0) {
                                $editAlertWrapper2.html('<div class="alert alert-success alert-dismissible fade show" role="alert"></div>');
                                $editAlert2 = $editAlertWrapper2.find('.alert');
                            }
                            $editAlert2.removeClass('alert-danger').addClass('alert-success');
                            $editAlert2.html('Company updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
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
                            $editAlert3.html(`Failed to update company: ${response.message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                            $editAlertWrapper3.show();
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error updating company:', xhr.responseText);
                        console.error('Status:', status);
                        console.error('Error:', error);
                        console.error('Response status:', xhr.status);

                        let errorMessage = 'Failed to update company. Please try again.';
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
        }
    });

    // Global variables for form field counters
    let addressCounter = 1;
    let contactCounter = 1;
    let noteCounter = 1;
    let editAddressCounter = 0;
    let editContactCounter = 0;
    let editNoteCounter = 0;

    // Ensure at least one blank slot exists in create form
    function ensureAtLeastOneAddress() {
        const container = document.getElementById('addresses-container');
        if (container && container.children.length === 0) {
            addAddress();
        }
    }

    function ensureAtLeastOneContact() {
        const container = document.getElementById('contacts-container');
        if (container && container.children.length === 0) {
            addContact();
        }
    }

    function ensureAtLeastOneNote() {
        const container = document.getElementById('notes-container');
        if (container && container.children.length === 0) {
            addNote();
        }
    }

    // Function to add address field in create form
    window.addAddress = function () {
        const container = document.getElementById('addresses-container');
        const newAddress = document.createElement('div');
        newAddress.className = 'address-item border rounded p-3 mb-2';
        newAddress.innerHTML = `
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" name="addresses[${addressCounter}][type]" required>
                        <option value="">Select Type</option>
                        <option value="head office">Head Office</option>
                        <option value="branch">Branch</option>
                        <option value="office">Office</option>
                        <option value="warehouse">Warehouse</option>
                        <option value="factory">Factory</option>
                        <option value="store">Store</option>
                        <option value="billing">Billing</option>
                        <option value="shipping">Shipping</option>
                        <option value="home">Home</option>
                        <option value="mailing">Mailing</option>
                        <option value="corporate">Corporate</option>
                        <option value="other">Other</option>
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

    // Function to add contact field in create form
    window.addContact = function () {
        const container = document.getElementById('contacts-container');
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
                    <input type="text" class="form-control" name="contacts[${contactCounter}][designation]" placeholder="Designation">
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="contacts[${contactCounter}][is_primary]" value="1">
                        <label class="form-check-label">Primary Contact</label>
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

    // Function to add note field in create form
    window.addNote = function () {
        const container = document.getElementById('notes-container');
        const newNote = document.createElement('div');
        newNote.className = 'note-item border rounded p-3 mb-2';
        newNote.innerHTML = `
            <div class="row">
                <div class="col-md-9">
                    <textarea class="form-control" name="notes[${noteCounter}][note]" rows="3" placeholder="Note content"></textarea>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="notes[${noteCounter}][status]">
                        <option value="0">Delete</option>
                        <option value="1">Active</option>
                        <option value="2">Inactive</option>
                        <option value="3">Block</option>
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

    // Function to add address field in edit form
    window.addEditAddress = function () {
        const container = document.getElementById('edit-addresses-container');
        const newAddress = document.createElement('div');
        newAddress.className = 'address-item border rounded p-3 mb-2';
        newAddress.innerHTML = `
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" name="addresses[${editAddressCounter}][type]">
                        <option value="">Select Type</option>
                        <option value="head office">Head Office</option>
                        <option value="branch">Branch</option>
                        <option value="office">Office</option>
                        <option value="warehouse">Warehouse</option>
                        <option value="factory">Factory</option>
                        <option value="store">Store</option>
                        <option value="billing">Billing</option>
                        <option value="shipping">Shipping</option>
                        <option value="home">Home</option>
                        <option value="mailing">Mailing</option>
                        <option value="corporate">Corporate</option>
                        <option value="other">Other</option>
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

    // Function to add contact field in edit form
    window.addEditContact = function () {
        const container = document.getElementById('edit-contacts-container');
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
                    <input type="text" class="form-control" name="contacts[${editContactCounter}][designation]" placeholder="Designation">
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="contacts[${editContactCounter}][is_primary]" value="1">
                        <label class="form-check-label">Primary Contact</label>
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

    // Function to add note field in edit form
    window.addEditNote = function () {
        const container = document.getElementById('edit-notes-container');
        const newNote = document.createElement('div');
        newNote.className = 'note-item border rounded p-3 mb-2';
        newNote.innerHTML = `
            <div class="row">
                <div class="col-md-9">
                    <textarea class="form-control" name="notes[${editNoteCounter}][note]" rows="3" placeholder="Note content"></textarea>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="notes[${editNoteCounter}][status]">
                        <option value="0">Delete</option>
                        <option value="1">Active</option>
                        <option value="2">Inactive</option>
                        <option value="3">Block</option>
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
});
