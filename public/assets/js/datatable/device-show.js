$(document).ready(function () {
    // Initialize Select2 with proper configuration
    $('.select2-multiple').each(function () {
        $(this).select2({
            theme: 'default',
            width: '100%',
            placeholder: 'Choose locations...',
            allowClear: true,
            closeOnSelect: false,
            tags: false,
            tokenSeparators: [',', ' '],
            language: {
                noResults: function () {
                    return "No locations found";
                },
                searching: function () {
                    return "Searching...";
                }
            }
        });
    });

    // Reset form when offcanvas is opened
    $('#offcanvas_edit').on('show.bs.offcanvas', function () {
        // Reset submit button text and state
        var submitBtn = $('#edit-device-form').find('button[type="submit"]');
        submitBtn.html('Update Device').prop('disabled', false);

        // Hide any previous alerts
        $('#edit-form-alert').hide();
    });

    // Re-initialize Select2 after dynamic content is added
    $(document).on('shown.bs.offcanvas', function () {
        $('.select2-multiple').each(function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    theme: 'default',
                    width: '100%',
                    placeholder: 'Choose locations...',
                    allowClear: true,
                    closeOnSelect: false,
                    tags: false,
                    tokenSeparators: [',', ' ']
                });
            }
        });
    });

    // Handle form submission for editing device
    $('#edit-device-form').on('submit', function (e) {
        e.preventDefault();

        // Get form data and action URL (map numeric status to backend strings)
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
        const actionUrl = $(this).attr('action');

        // Disable submit button to prevent double submission
        var submitBtn = $(this).find('button[type="submit"]');
        var originalBtnText = submitBtn.html();
        submitBtn.html('Updating...').prop('disabled', true);

        $.ajax({
            url: actionUrl,
            type: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                console.log('Device update response:', response);
                if (response.success) {
                    // Show success message using inner alert element (keeps background styles)
                    var $wrapper = $('#edit-form-alert');
                    var $alert = $wrapper.find('.alert');
                    if ($alert.length === 0) {
                        $wrapper.html('<div class="alert alert-success alert-dismissible fade show" role="alert"></div>');
                        $alert = $wrapper.find('.alert');
                    }
                    $alert.removeClass('alert-danger').addClass('alert-success');
                    $alert.html('Device updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                    $wrapper.show();

                    // Update the page content with the new device data
                    console.log('Updating device details with:', response.device);
                    updateDeviceDetails(response.device);

                    // Close the offcanvas after a delay (matching Index page behavior)
                    setTimeout(function () {
                        $('#offcanvas_edit').offcanvas('hide');
                    }, 2000);
                } else {
                    var $wrapper2 = $('#edit-form-alert');
                    var $alert2 = $wrapper2.find('.alert');
                    if ($alert2.length === 0) {
                        $wrapper2.html('<div class="alert alert-danger alert-dismissible fade show" role="alert"></div>');
                        $alert2 = $wrapper2.find('.alert');
                    }
                    $alert2.removeClass('alert-success').addClass('alert-danger');
                    $alert2.html(`Failed to update device: ${response.message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                    $wrapper2.show();
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
                }

                var $wrapper3 = $('#edit-form-alert');
                var $alert3 = $wrapper3.find('.alert');
                if ($alert3.length === 0) {
                    $wrapper3.html('<div class="alert alert-danger alert-dismissible fade show" role="alert"></div>');
                    $alert3 = $wrapper3.find('.alert');
                }
                $alert3.removeClass('alert-success').addClass('alert-danger');
                $alert3.html(`${errorMessage} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                $wrapper3.show();
            },
            complete: function () {
                // Re-enable submit button
                submitBtn.html(originalBtnText).prop('disabled', false);
            }
        });
    });

    // Function to update device details on the page without reloading
    function updateDeviceDetails(device) {
        console.log('updateDeviceDetails called with device:', device);

        // Update device name in the top red box section (Device Header Card)
        const deviceNameElement = $('.card-body .d-flex .d-flex h5.mb-1');
        console.log('Device name element found:', deviceNameElement.length);
        deviceNameElement.text(device.name);

        // Update unique ID in the top red box section
        const uniqueIdElement = $('.card-body .d-flex .d-flex p.mb-2');
        console.log('Unique ID element found:', uniqueIdElement.length);
        uniqueIdElement.text(device.unique_id);

        // Update status badge
        var statusClass = 'badge-soft-secondary';
        var statusIcon = 'ti-circle';
        var statusText = 'Unknown';
        if (device.status == 0) {
            statusClass = 'badge-soft-secondary';
            statusIcon = 'ti-trash';
            statusText = 'Delete';
        } else if (device.status == 1) {
            statusClass = 'badge-soft-success';
            statusIcon = 'ti-check';
            statusText = 'Active';
        } else if (device.status == 2) {
            statusClass = 'badge-soft-warning';
            statusIcon = 'ti-player-pause';
            statusText = 'Inactive';
        } else if (device.status == 3) {
            statusClass = 'badge-soft-danger';
            statusIcon = 'ti-lock';
            statusText = 'Block';
        }
        // Update header status badge by id
        $('#header-status-badge')
            .removeClass('badge-soft-success badge-soft-danger badge-soft-warning badge-soft-secondary')
            .addClass(statusClass)
            .html(`<i class="ti ${statusIcon} me-1"></i>${statusText}`);

        // Update IP address in the top red box section
        const ipElement = $('.card-body .d-flex .d-flex .d-flex .d-inline-flex');
        console.log('IP element found:', ipElement.length);
        if (device.ip) {
            ipElement.html(`<i class="ti ti-network text-info me-1"></i> ${device.ip}`);
        } else {
            ipElement.remove();
        }

        // Update device overview section (green box)
        const overviewSection = $('.card-header:contains("Device Overview")').next('.card-body');
        console.log('Device Overview section found:', overviewSection.length);

        overviewSection.find('.col-md-6 .mb-4:contains("Device Name") p').text(device.name);
        overviewSection.find('.col-md-6 .mb-4:contains("Unique ID") p').text(device.unique_id);
        overviewSection.find('.col-md-6 .mb-4:contains("IP Address") p').text(device.ip || 'N/A');
        overviewSection.find('.col-md-6 .mb-4:contains("Company") p').text(device.company ? device.company.name : 'N/A');
        overviewSection.find('.col-md-6 .mb-4:contains("Location") p').text(device.location ? device.location.name : 'N/A');

        // Update status in overview
        $('#overview-status-badge')
            .removeClass('badge-soft-success badge-soft-danger badge-soft-warning badge-soft-secondary')
            .addClass(statusClass)
            .text(statusText);

        // Update Area in Device Information section
        $('.card-header:contains("Device Information")').next('.card-body').find('.col-md-6 .mb-4:contains("Area") p').text(device.area ? device.area.name : 'N/A');

        // Update Company Details section
        if (device.company) {
            console.log('Updating company details:', device.company);
            // Update company name
            $('.card-header:contains("Company Details")').next('.card-body').find('h6.fw-semibold').text(device.company.name);

            // Update company industry
            $('.card-header:contains("Company Details")').next('.card-body').find('.ti-building').parent().text(device.company.industry);

            // Update company email
            if (device.company.email) {
                $('.card-header:contains("Company Details")').next('.card-body').find('.ti-mail').parent().text(device.company.email);
            }

            // Update company phone
            if (device.company.phone) {
                $('.card-header:contains("Company Details")').next('.card-body').find('.ti-phone').parent().text(device.company.phone);
            }
        } else {
            // Hide company details section if no company
            $('.card-header:contains("Company Details")').closest('.col-md-12').hide();
        }

        // Update Location Details section
        if (device.location) {
            console.log('Updating location details:', device.location);
            // Update location name
            $('.card-header:contains("Location Details")').next('.card-body').find('h6.fw-semibold').text(device.location.name);

            // Update location email
            $('.card-header:contains("Location Details")').next('.card-body').find('.ti-mail').parent().text(device.location.email);

            // Update location address
            $('.card-header:contains("Location Details")').next('.card-body').find('.ti-map-pin').parent().text(device.location.address);

            // Update location city, state, country
            $('.card-header:contains("Location Details")').next('.card-body').find('.ti-building').parent().text(device.location.city + ', ' + device.location.state + ', ' + device.location.country);
        } else {
            // Hide location details section if no location
            $('.card-header:contains("Location Details")').closest('.col-md-12').hide();
        }

        // Update Area Details section
        if (device.area) {
            console.log('Updating area details:', device.area);
            // Update area name
            $('.card-header:contains("Area Details")').next('.card-body').find('h6.fw-semibold').text(device.area.name);

            // Update area code
            if (device.area.code) {
                $('.card-header:contains("Area Details")').next('.card-body').find('.ti-map-pin').parent().text(device.area.code);
            } else {
                $('.card-header:contains("Area Details")').next('.card-body').find('.ti-map-pin').parent().text('N/A');
            }

            // Update area description
            if (device.area.description) {
                $('.card-header:contains("Area Details")').next('.card-body').find('.ti-note').parent().text(device.area.description);
            }
        } else {
            // Hide area details section if no area
            $('.card-header:contains("Area Details")').closest('.col-md-12').hide();
        }
    }

    // Function to add new address in edit form
    function addEditAddress() {
        const container = document.getElementById('edit-addresses-container');
        const addressCount = container.children.length;

        const addressHtml = `
            <div class="address-item border rounded p-3 mb-2">
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-select" name="addresses[${addressCount}][type]" required>
                            <option value="">Select Type</option>
                            <option value="Head Office">Head Office</option>
                            <option value="Branch">Branch</option>
                            <option value="Office">Office</option>
                            <option value="Warehouse">Warehouse</option>
                            <option value="Factory">Factory</option>
                            <option value="Store">Store</option>
                            <option value="Billing">Billing</option>
                            <option value="Shipping">Shipping</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <input type="text" class="form-control" name="addresses[${addressCount}][address]" placeholder="Address">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="addresses[${addressCount}][city]" placeholder="City">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="addresses[${addressCount}][state]" placeholder="State">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="addresses[${addressCount}][country]" placeholder="Country">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="addresses[${addressCount}][zip_code]" placeholder="Zip Code">
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="this.parentElement.remove()">
                    <i class="ti ti-trash me-1"></i>Remove
                </button>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', addressHtml);
    }

    // Function to add new contact in edit form
    function addEditContact() {
        const container = document.getElementById('edit-contacts-container');
        const contactCount = container.children.length;

        const contactHtml = `
            <div class="contact-item border rounded p-3 mb-2">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="contacts[${contactCount}][name]" placeholder="Contact Name">
                    </div>
                    <div class="col-md-4">
                        <input type="email" class="form-control" name="contacts[${contactCount}][email]" placeholder="Email">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="contacts[${contactCount}][phone]" placeholder="Phone">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="contacts[${contactCount}][designation]" placeholder="Designation">
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="contacts[${contactCount}][is_primary]" value="1">
                            <label class="form-check-label">Primary Contact</label>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="this.parentElement.remove()">
                    <i class="ti ti-trash me-1"></i>Remove
                </button>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', contactHtml);
    }

    // Function to add new note in edit form
    function addEditNote() {
        const container = document.getElementById('edit-notes-container');
        const noteCount = container.children.length;

        const noteHtml = `
            <div class="note-item border rounded p-3 mb-2">
                <div class="row">
                    <div class="col-md-9">
                        <textarea class="form-control" name="notes[${noteCount}][note]" rows="3" placeholder="Note content"></textarea>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="notes[${noteCount}][status]">
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
            </div>
        `;

        container.insertAdjacentHTML('beforeend', noteHtml);
    }

    // Make functions globally available
    window.addEditAddress = addEditAddress;
    window.addEditContact = addEditContact;
    window.addEditNote = addEditNote;

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle collapse header button
    $('#collapse-header').on('click', function () {
        const icon = $(this).find('i');
        if (icon.hasClass('ti-transition-top')) {
            icon.removeClass('ti-transition-top').addClass('ti-transition-bottom');
        } else {
            icon.removeClass('ti-transition-bottom').addClass('ti-transition-top');
        }
    });

    // Handle refresh button
    $('.btn-outline-info[aria-label="Refresh"]').on('click', function () {
        location.reload();
    });

    // Handle export dropdown items
    $('.dropdown-item').on('click', function (e) {
        e.preventDefault();
        const exportType = $(this).text().toLowerCase();
        if (exportType.includes('pdf')) {
            // Handle PDF export
            console.log('PDF export functionality to be implemented');
        } else if (exportType.includes('excel')) {
            // Handle Excel export
            console.log('Excel export functionality to be implemented');
        }
    });

    // Device Layout Management Functions
    // Load device layouts when layout management offcanvas is shown
    $('#offcanvas_layout_management').on('show.bs.offcanvas', function () {
        loadDeviceLayouts();
        // Ensure inline alert container exists and is cleared
        if ($('#layout-form-alert').length === 0) {
            $('#layout-form').prepend('<div id="layout-form-alert" class="mb-3" style="display:none;"></div>');
        } else {
            $('#layout-form-alert').hide().empty();
        }
    });

    // Handle layout form submission
    $('#layout-form').on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const layoutId = $('#layout-id').val();
        const isEdit = layoutId !== '';

        const url = isEdit ? `/device-layout/${layoutId}` : '/device-layout';

        // Add _method field for PUT requests
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
                // Prepare inline alert container inside the form
                if ($('#layout-form-alert').length === 0) {
                    $('#layout-form').prepend('<div id="layout-form-alert" class="mb-3" style="display:none;"></div>');
                }

                if (response.success) {
                    // Inline success alert inside the form
                    var $wrapper = $('#layout-form-alert');
                    var $alert = $wrapper.find('.alert');
                    if ($alert.length === 0) {
                        $wrapper.html('<div class="alert alert-success alert-dismissible fade show" role="alert"></div>');
                        $alert = $wrapper.find('.alert');
                    }
                    $alert.removeClass('alert-danger').addClass('alert-success');
                    $alert.html(response.message + ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                    $wrapper.show();

                    $('#layout-form')[0].reset();
                    $('#layout-id').val('');
                    $('#layout-submit-btn').text('Add Layout');
                    $('#layout-cancel-btn').hide();
                    $('#layout-form-title').text('Add New Layout');
                    loadDeviceLayouts();
                    if (window.loadDeviceScreens) { window.loadDeviceScreens(); setTimeout(window.loadDeviceScreens, 800); }
                    // If preview offcanvas is open, refresh preview as well
                    if ($('#offcanvas_screen_preview').hasClass('show') && typeof window.screenPreviewData !== 'undefined') {
                        // Clear cached data and trigger reload via existing button handler
                        window.screenPreviewData = undefined;
                        $('#refresh-preview-btn').trigger('click');
                    }

                    // Close the offcanvas shortly after showing the message
                    setTimeout(function () {
                        $('#offcanvas_layout_management').offcanvas('hide');
                    }, 1200);
                } else {
                    // Inline error alert inside the form
                    var $wrapper2 = $('#layout-form-alert');
                    var $alert2 = $wrapper2.find('.alert');
                    if ($alert2.length === 0) {
                        $wrapper2.html('<div class="alert alert-danger alert-dismissible fade show" role="alert"></div>');
                        $alert2 = $wrapper2.find('.alert');
                    }
                    $alert2.removeClass('alert-success').addClass('alert-danger');
                    $alert2.html((response.message || 'Something went wrong') + ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                    $wrapper2.show();
                }
            },
            error: function (xhr) {
                // Inline error alert inside the form
                if ($('#layout-form-alert').length === 0) {
                    $('#layout-form').prepend('<div id="layout-form-alert" class="mb-3" style="display:none;"></div>');
                }
                const response = xhr.responseJSON;
                var $wrapper3 = $('#layout-form-alert');
                var $alert3 = $wrapper3.find('.alert');
                if ($alert3.length === 0) {
                    $wrapper3.html('<div class="alert alert-danger alert-dismissible fade show" role="alert"></div>');
                    $alert3 = $wrapper3.find('.alert');
                }
                $alert3.removeClass('alert-success').addClass('alert-danger');
                $alert3.html(((response && response.message) ? response.message : 'An error occurred') + ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                $wrapper3.show();
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
                        if (window.loadDeviceScreens) { window.loadDeviceScreens(); setTimeout(window.loadDeviceScreens, 800); }
                        // Also refresh device screens on the show page after layout deletion
                        if (window.loadDeviceScreens) { window.loadDeviceScreens(); }
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
        try {
            const deviceId = $('#layout-device-id').val();
            if (!deviceId) {
                console.warn('Device ID not found for loading layouts');
                return;
            }

            $.ajax({
                url: `/device/${deviceId}/layouts`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (!response || !response.success) {
                        console.warn('Failed to load layouts');
                        return;
                    }

                    const layouts = response.layouts || [];
                    const counts = response.counts || { total: layouts.length, active: 0, inactive: 0, blocked: 0 };

                    // Update counts in the Device Layouts card header
                    const header = $(".card-header:contains('Device Layouts')");
                    const badges = header.find('.d-flex .badge');
                    // Total
                    badges.filter(function () { return $(this).text().trim().startsWith('Total:'); })
                        .text(`Total: ${counts.total}`);
                    // Active
                    badges.filter(function () { return $(this).text().trim().startsWith('Active:'); })
                        .text(`Active: ${counts.active}`);
                    // Inactive
                    badges.filter(function () { return $(this).text().trim().startsWith('Inactive:'); })
                        .text(`Inactive: ${counts.inactive}`);
                    // Blocked
                    badges.filter(function () { return $(this).text().trim().startsWith('Blocked:'); })
                        .text(`Blocked: ${counts.blocked}`);

                    // Target the card body of the Device Layouts section
                    const cardBody = header.next('.card-body');

                    if (layouts.length === 0) {
                        const emptyHtml = `
                            <div class="text-center py-4">
                                <i class="ti ti-layout-grid text-muted" style="font-size: 3rem;"></i>
                                <h6 class="text-muted mt-2">No layouts found</h6>
                                <p class="text-muted">This device doesn't have any layouts configured yet.</p>
                                <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_layout_management">
                                    <i class="ti ti-plus me-1"></i>Add First Layout
                                </button>
                            </div>`;
                        cardBody.html(emptyHtml);
                        return;
                    }

                    // Build table rows
                    const statusMap = {
                        0: { class: 'badge-soft-secondary', icon: 'ti-trash', text: 'Delete' },
                        1: { class: 'badge-soft-success', icon: 'ti-check', text: 'Active' },
                        2: { class: 'badge-soft-warning', icon: 'ti-player-pause', text: 'Inactive' },
                        3: { class: 'badge-soft-danger', icon: 'ti-lock', text: 'Block' }
                    };

                    const layoutTypeName = function (t) {
                        if (t === 0) return 'Full Screen';
                        if (t === 1) return 'Split Screen';
                        if (t === 2) return 'Three Grid Screen';
                        if (t === 3) return 'Four Grid Screen';
                        return 'Unknown';
                    };

                    let rowsHtml = '';
                    layouts.forEach(function (l) {
                        const st = statusMap[l.status] || statusMap[0];
                        const createdAt = l.created_at ? new Date(l.created_at).toLocaleString() : '';
                        rowsHtml += `
                            <tr>
                                <td>${l.layout_name || ''}</td>
                                <td><span class="badge badge-soft-info">${layoutTypeName(l.layout_type)}</span></td>
                                <td><span class="badge ${st.class}"><i class="ti ${st.icon} me-1"></i>${st.text}</span></td>
                                <td>${createdAt}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary edit-layout-btn" data-layout-id="${l.id}" data-layout-name="${l.layout_name}" data-layout-type="${l.layout_type}" data-layout-status="${l.status}">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-layout-btn" data-layout-id="${l.id}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                    });

                    const tableHtml = `
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Layout Name</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>${rowsHtml}</tbody>
                            </table>
                        </div>`;

                    cardBody.html(tableHtml);
                },
                error: function () {
                    console.error('Error fetching device layouts');
                }
            });
        } catch (e) {
            console.error('Failed to load device layouts:', e);
        }
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
});

// Helper function for screen alerts
function showScreenAlert(type, message) {
    $('#screen-form-alert').show().html(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `);
}

// Device Screen Management Functions
$(document).ready(function () {
    // Load device screens when screen management offcanvas is shown
    $('#offcanvas_screen_management').on('show.bs.offcanvas', function () {
        loadDeviceScreens();
        if ($('#screen-form-alert').length) {
            $('#screen-form-alert').hide().empty();
        }
        // Reset layout info
        $('#layout-info').hide();
    });

    // Handle layout selection change
    $('#screen-layout-id').on('change', function () {
        const layoutId = $(this).val();
        const selectedOption = $(this).find('option:selected');
        const layoutType = selectedOption.data('layout-type');
        const layoutTypeName = selectedOption.data('layout-type-name');

        if (layoutId && layoutType !== undefined) {
            // Get layout information and current screen count
            $.ajax({
                url: `/device/{{ $device->id }}/screens`,
                type: 'GET',
                data: { layout_id: layoutId },
                success: function (response) {
                    if (response.success && response.layout_info) {
                        const layoutInfo = response.layout_info;
                        const remainingSlots = layoutInfo.remaining_slots;
                        const maxScreens = layoutInfo.max_screens;
                        const currentScreens = layoutInfo.current_screens;

                        $('#layout-type-info').text(layoutTypeName);
                        $('#layout-limit-info').text(`Max: ${maxScreens} screens, Current: ${currentScreens}, Remaining: ${remainingSlots}`);
                        $('#layout-info').show();

                        // Disable/enable submit button based on remaining slots
                        const submitBtn = $('#screen-submit-btn');
                        if (remainingSlots <= 0 && !$('#screen-id').val()) {
                            submitBtn.prop('disabled', true).text('Layout Full');
                        } else {
                            submitBtn.prop('disabled', false).text($('#screen-id').val() ? 'Update Screen' : 'Add Screen');
                        }
                    }
                },
                error: function () {
                    $('#layout-info').hide();
                }
            });
        } else {
            $('#layout-info').hide();
            $('#screen-submit-btn').prop('disabled', false);
        }
    });

    // Handle screen form submission
    $('#screen-form').on('submit', function (e) {
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
                    $('#layout-info').hide();
                    loadDeviceScreens();

                    // Update layout info if provided
                    if (response.layout_info) {
                        const layoutInfo = response.layout_info;
                        const remainingSlots = layoutInfo.remaining_slots;
                        const maxScreens = layoutInfo.max_screens;
                        const currentScreens = layoutInfo.current_screens;

                        $('#layout-limit-info').text(`Max: ${maxScreens} screens, Current: ${currentScreens}, Remaining: ${remainingSlots}`);

                        // Update submit button state
                        const submitBtn = $('#screen-submit-btn');
                        if (remainingSlots <= 0) {
                            submitBtn.prop('disabled', true).text('Layout Full');
                        } else {
                            submitBtn.prop('disabled', false).text('Add Screen');
                        }
                    }

                    setTimeout(function () {
                        $('#offcanvas_screen_management').offcanvas('hide');
                        $alertWrap.hide().empty();
                    }, 1200);
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

    // Edit screen
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

    // Delete screen
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
                        loadDeviceScreens();
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
    $('#screen-cancel-btn').on('click', function () {
        $('#screen-form')[0].reset();
        $('#screen-id').val('');
        $('#screen-submit-btn').text('Add Screen');
        $('#screen-cancel-btn').hide();
        $('#screen-form-title').text('Add New Screen');
    });

    function loadDeviceScreens() {
        try {
            const deviceId = $('#screen-device-id').val();
            if (!deviceId) return;

            $.ajax({
                url: `/device/${deviceId}/screens?_ts=${Date.now()}`,
                type: 'GET',
                cache: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (!response || !response.success) return;

                    const screens = response.screens || [];

                    // Update counts in the Device Screens card header
                    const header = $(".card-header:contains('Device Screens')");
                    header.find('.badge:contains("Total:")').text(`Total: ${response.counts?.total ?? screens.length}`);

                    // Target the card body of the Device Screens section
                    const cardBody = header.next('.card-body');

                    if (screens.length === 0) {
                        cardBody.html(`
                            <div class="text-center py-4">
                                <i class="ti ti-layout-grid text-muted" style="font-size: 3rem;"></i>
                                <h6 class="text-muted mt-2">No screens found</h6>
                                <p class="text-muted">This device doesn't have any screens configured yet.</p>
                                <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_screen_management">
                                    <i class="ti ti-plus me-1"></i>Add First Screen
                                </button>
                            </div>
                        `);
                        return;
                    }

                    let rowsHtml = '';
                    screens.forEach(function (s) {
                        const createdAt = s.created_at ? new Date(s.created_at).toLocaleString() : '';
                        rowsHtml += `
                            <tr>
                                <td>${s.screen_no ?? ''}</td>
                                <td>${s.screen_height ?? ''}</td>
                                <td>${s.screen_width ?? ''}</td>
                                <td>${(s.layout && s.layout.layout_name) ? s.layout.layout_name : ''}</td>
                                <td>${createdAt}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary edit-screen-btn" data-screen-id="${s.id}" data-screen-no="${s.screen_no}" data-screen-height="${s.screen_height}" data-screen-width="${s.screen_width}" data-layout-id="${s.layout_id}">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-screen-btn" data-screen-id="${s.id}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                    });

                    const tableHtml = `
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Screen No</th>
                                        <th>Height</th>
                                        <th>Width</th>
                                        <th>Layout</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>${rowsHtml}</tbody>
                            </table>
                        </div>`;

                    cardBody.html(tableHtml);
                },
                error: function () {
                    console.error('Error fetching device screens');
                }
            });
        } catch (e) {
            console.error('Failed to load device screens:', e);
        }
    }
    // Expose for other modules in this file
    window.loadDeviceScreens = loadDeviceScreens;
});
