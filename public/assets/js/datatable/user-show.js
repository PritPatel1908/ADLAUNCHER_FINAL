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
        var submitBtn = $('#edit-user-form').find('button[type="submit"]');
        submitBtn.html('Update User').prop('disabled', false);

        // Hide any previous alerts
        $('#edit-form-alert').hide();

        // Load current user data into form fields
        loadUserDataIntoForm();
    });

    // Re-initialize Select2 after dynamic content is added
    $(document).on('shown.bs.offcanvas', function () {
        $('.select2-multiple').each(function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    theme: 'default',
                    width: '100%',
                    placeholder: 'Choose...',
                    allowClear: true,
                    closeOnSelect: false,
                    tags: false,
                    tokenSeparators: [',', ' ']
                });
            }
        });
    });

    // Handle form submission for editing user
    $('#edit-user-form').on('submit', function (e) {
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
                if (response.success) {
                    // Show success message using inner alert element (keeps background styles)
                    var $wrapper = $('#edit-form-alert');
                    var $alert = $wrapper.find('.alert');
                    if ($alert.length === 0) {
                        $wrapper.html('<div class="alert alert-success alert-dismissible fade show" role="alert"></div>');
                        $alert = $wrapper.find('.alert');
                    }
                    $alert.removeClass('alert-danger').addClass('alert-success');
                    $alert.html('User updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                    $wrapper.show();

                    // Update the page content with the new user data
                    updateUserDetails(response.user);

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
                    $alert2.html(`Failed to update user: ${response.message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                    $wrapper2.show();
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

    // Function to load current user data into form fields
    function loadUserDataIntoForm() {
        // Get user data from the page (from the initial load)
        const userData = window.currentUserData;
        if (userData) {
            console.log('Loading user data into form:', userData);
            console.log('User status value:', userData.status, 'Type:', typeof userData.status);

            // Update all form fields
            $('#edit-first_name').val(userData.first_name || '');
            $('#edit-middle_name').val(userData.middle_name || '');
            $('#edit-last_name').val(userData.last_name || '');
            $('#edit-email').val(userData.email || '');
            $('#edit-mobile').val(userData.mobile || '');
            $('#edit-phone').val(userData.phone || '');
            $('#edit-employee_id').val(userData.employee_id || '');
            $('#edit-gender').val(userData.gender || '');

            // Format and set dates
            const formattedBirthDate = formatDateForInput(userData.date_of_birth);
            $('#edit-date_of_birth').val(formattedBirthDate);
            console.log('Loaded birth date:', formattedBirthDate);

            const formattedJoiningDate = formatDateForInput(userData.date_of_joining);
            $('#edit-date_of_joining').val(formattedJoiningDate);
            console.log('Loaded joining date:', formattedJoiningDate);

            // Set status
            let statusValue = 1; // Default to active
            if (userData.status == 0) statusValue = 0; // delete
            else if (userData.status == 1) statusValue = 1; // active
            else if (userData.status == 2) statusValue = 2; // deactivate
            else if (userData.status == 3) statusValue = 3; // block

            console.log('Setting status dropdown to value:', statusValue, 'for user status:', userData.status);
            $('#edit-status').val(statusValue);

            // Verify the value was set
            setTimeout(function () {
                const currentValue = $('#edit-status').val();
                console.log('Status dropdown current value after setting:', currentValue);
            }, 100);

            // Update relationships
            if (userData.companies && userData.companies.length > 0) {
                const companyIds = userData.companies.map(company => company.id);
                $('#edit-company_ids').val(companyIds).trigger('change');
            } else {
                $('#edit-company_ids').val([]).trigger('change');
            }

            if (userData.locations && userData.locations.length > 0) {
                const locationIds = userData.locations.map(location => location.id);
                $('#edit-location_ids').val(locationIds).trigger('change');
            } else {
                $('#edit-location_ids').val([]).trigger('change');
            }

            if (userData.areas && userData.areas.length > 0) {
                const areaIds = userData.areas.map(area => area.id);
                $('#edit-area_ids').val(areaIds).trigger('change');
            } else {
                $('#edit-area_ids').val([]).trigger('change');
            }

            if (userData.roles && userData.roles.length > 0) {
                const roleIds = userData.roles.map(role => role.id);
                $('#edit-role_ids').val(roleIds).trigger('change');
            } else {
                $('#edit-role_ids').val([]).trigger('change');
            }
        }
    }

    // Function to format dates for HTML date input (YYYY-MM-DD)
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

    // Function to update user details on the page without reloading
    function updateUserDetails(user) {
        console.log('Updating user details with:', user);

        // Construct full name if not available
        const fullName = user.full_name || (user.first_name + (user.middle_name ? ' ' + user.middle_name : '') + ' ' + user.last_name).trim();

        // Update user name in header - more specific selector
        $('.card-body h5.mb-1').text(fullName);
        console.log('Updated header name to:', fullName);

        // Update username - more specific selector
        $('.card-body p.mb-2').text(user.username);
        console.log('Updated username to:', user.username);

        // Update avatar text with first letter of first name
        $('.avatar-text').text(user.first_name ? user.first_name.charAt(0).toUpperCase() : 'U');

        // Update status badge
        var statusClass = 'badge-soft-secondary';
        var statusIcon = 'ti-circle';
        var statusText = 'Unknown';
        if (user.status == 0) {
            statusClass = 'badge-soft-secondary';
            statusIcon = 'ti-trash';
            statusText = 'Delete';
        } else if (user.status == 1) {
            statusClass = 'badge-soft-success';
            statusIcon = 'ti-check';
            statusText = 'Active';
        } else if (user.status == 2) {
            statusClass = 'badge-soft-warning';
            statusIcon = 'ti-player-pause';
            statusText = 'Deactivate';
        } else if (user.status == 3) {
            statusClass = 'badge-soft-danger';
            statusIcon = 'ti-lock';
            statusText = 'Block';
        }
        // Update header status badge by id
        $('#header-status-badge')
            .removeClass('badge-soft-success badge-soft-danger badge-soft-warning badge-soft-secondary')
            .addClass(statusClass)
            .html(`<i class="ti ${statusIcon} me-1"></i>${statusText}`);

        // Update email, mobile, employee_id in header
        $('.d-inline-flex').each(function () {
            const $this = $(this);
            if ($this.find('.ti-mail').length > 0) {
                $this.html(`<i class="ti ti-mail text-info me-1"></i> ${user.email || 'N/A'}`);
            } else if ($this.find('.ti-phone').length > 0) {
                $this.html(`<i class="ti ti-phone text-success me-1"></i> ${user.mobile || 'N/A'}`);
            } else if ($this.find('.ti-id').length > 0) {
                $this.html(`<i class="ti ti-id text-warning me-1"></i> ${user.employee_id || 'N/A'}`);
            }
        });

        // Update user overview section - more specific selectors
        $('.card:has(.card-title:contains("User Overview")) .col-md-6 .mb-4').each(function () {
            const $this = $(this);
            const label = $this.find('h6').text();
            console.log('Processing overview field:', label);

            if (label.includes('Full Name')) {
                $this.find('p').text(fullName);
                console.log('Updated Full Name to:', fullName);
            } else if (label.includes('Email Address')) {
                $this.find('p a').text(user.email).attr('href', 'mailto:' + user.email);
                console.log('Updated Email to:', user.email);
            } else if (label.includes('Username')) {
                $this.find('p').text(user.username);
                console.log('Updated Username to:', user.username);
            } else if (label.includes('Employee ID')) {
                $this.find('p').text(user.employee_id || 'N/A');
                console.log('Updated Employee ID to:', user.employee_id);
            } else if (label.includes('Mobile')) {
                $this.find('p').text(user.mobile || 'N/A');
                console.log('Updated Mobile to:', user.mobile);
            } else if (label.includes('Phone')) {
                $this.find('p').text(user.phone || 'N/A');
                console.log('Updated Phone to:', user.phone);
            } else if (label.includes('Gender')) {
                let genderText = 'N/A';
                if (user.gender == 1) genderText = 'Male';
                else if (user.gender == 2) genderText = 'Female';
                else if (user.gender == 3) genderText = 'Other';
                $this.find('p').text(genderText);
                console.log('Updated Gender to:', genderText);
            } else if (label.includes('Date of Birth')) {
                const birthDate = user.date_of_birth ? new Date(user.date_of_birth).toLocaleDateString() : 'N/A';
                $this.find('p').text(birthDate);
                console.log('Updated Date of Birth to:', birthDate);
            } else if (label.includes('Date of Joining')) {
                const joiningDate = user.date_of_joining ? new Date(user.date_of_joining).toLocaleDateString() : 'N/A';
                $this.find('p').text(joiningDate);
                console.log('Updated Date of Joining to:', joiningDate);
            }
        });

        // Update status in overview
        $('#overview-status-badge')
            .removeClass('badge-soft-success badge-soft-danger badge-soft-warning badge-soft-secondary')
            .addClass(statusClass)
            .text(statusText);

        // Update status in edit form
        let editStatusValue = 1; // Default to active
        if (user.status == 0) editStatusValue = 0; // delete
        else if (user.status == 1) editStatusValue = 1; // active
        else if (user.status == 2) editStatusValue = 2; // deactivate
        else if (user.status == 3) editStatusValue = 3; // block
        $('#edit-status').val(editStatusValue);
        console.log('Updated edit form status:', editStatusValue, 'for status:', user.status);

        // Update statistics section - more specific selectors
        $('.card:has(.card-title:contains("User Statistics")) .col-md-6 .mb-4').each(function () {
            const $this = $(this);
            const label = $this.find('h6').text();
            console.log('Processing statistics field:', label);

            if (label.includes('Companies')) {
                $this.find('p').text(user.companies ? user.companies.length : 0);
                console.log('Updated Companies count to:', user.companies ? user.companies.length : 0);
            } else if (label.includes('Locations')) {
                $this.find('p').text(user.locations ? user.locations.length : 0);
                console.log('Updated Locations count to:', user.locations ? user.locations.length : 0);
            } else if (label.includes('Areas')) {
                $this.find('p').text(user.areas ? user.areas.length : 0);
                console.log('Updated Areas count to:', user.areas ? user.areas.length : 0);
            } else if (label.includes('Roles')) {
                $this.find('p').text(user.roles ? user.roles.length : 0);
                console.log('Updated Roles count to:', user.roles ? user.roles.length : 0);
            } else if (label.includes('Last Login')) {
                const lastLogin = user.last_login_at ? new Date(user.last_login_at).toLocaleDateString() : 'Never';
                $this.find('p').text(lastLogin);
                console.log('Updated Last Login to:', lastLogin);
            }
        });

        // Update created and updated dates
        $('.mt-3').each(function () {
            const $this = $(this);
            const createdLabel = $this.find('h6:contains("Created")');
            const updatedLabel = $this.find('h6:contains("Last Updated")');

            if (createdLabel.length > 0) {
                const createdDate = user.created_at ? new Date(user.created_at).toLocaleString() : 'N/A';
                createdLabel.next('p').text(createdDate);
            }

            if (updatedLabel.length > 0) {
                const updatedDate = user.updated_at ? new Date(user.updated_at).toLocaleString() : 'N/A';
                updatedLabel.next('p').text(updatedDate);
            }
        });

        // Update relationship sections
        updateRelationshipSection('Companies', user.companies, 'building', 'industry');
        updateRelationshipSection('Locations', user.locations, 'map-pin', 'address');
        updateRelationshipSection('Areas', user.areas, 'map-pin', 'description');
        updateRelationshipSection('Roles', user.roles, 'shield-check', 'role_name');

        // Show/hide relationship sections based on data
        toggleRelationshipSection('Companies', user.companies);
        toggleRelationshipSection('Locations', user.locations);
        toggleRelationshipSection('Areas', user.areas);
        toggleRelationshipSection('Roles', user.roles);

        // Update all edit form fields
        $('#edit-first_name').val(user.first_name || '');
        $('#edit-middle_name').val(user.middle_name || '');
        $('#edit-last_name').val(user.last_name || '');
        $('#edit-email').val(user.email || '');
        $('#edit-mobile').val(user.mobile || '');
        $('#edit-phone').val(user.phone || '');
        $('#edit-employee_id').val(user.employee_id || '');
        $('#edit-gender').val(user.gender || '');

        // Update date fields in edit form
        const formattedBirthDate = formatDateForInput(user.date_of_birth);
        $('#edit-date_of_birth').val(formattedBirthDate);
        console.log('Updated birth date:', formattedBirthDate);

        const formattedJoiningDate = formatDateForInput(user.date_of_joining);
        $('#edit-date_of_joining').val(formattedJoiningDate);
        console.log('Updated joining date:', formattedJoiningDate);

        // Update relationship dropdowns in edit form
        if (user.companies && user.companies.length > 0) {
            const companyIds = user.companies.map(company => company.id);
            $('#edit-company_ids').val(companyIds).trigger('change');
        } else {
            $('#edit-company_ids').val([]).trigger('change');
        }

        if (user.locations && user.locations.length > 0) {
            const locationIds = user.locations.map(location => location.id);
            $('#edit-location_ids').val(locationIds).trigger('change');
        } else {
            $('#edit-location_ids').val([]).trigger('change');
        }

        if (user.areas && user.areas.length > 0) {
            const areaIds = user.areas.map(area => area.id);
            $('#edit-area_ids').val(areaIds).trigger('change');
        } else {
            $('#edit-area_ids').val([]).trigger('change');
        }

        if (user.roles && user.roles.length > 0) {
            const roleIds = user.roles.map(role => role.id);
            $('#edit-role_ids').val(roleIds).trigger('change');
        } else {
            $('#edit-role_ids').val([]).trigger('change');
        }

        // Update global user data
        window.currentUserData = user;

        // Direct updates for specific highlighted fields
        // Update header name directly
        $('.card-body h5.mb-1').text(fullName);
        console.log('Direct update - Header name:', fullName);

        // Update overview full name directly
        $('.card:has(.card-title:contains("User Overview")) h6:contains("Full Name")').next('p').text(fullName);
        console.log('Direct update - Overview Full Name:', fullName);

        // Update statistics counts directly
        $('.card:has(.card-title:contains("User Statistics")) h6:contains("Companies")').next('p').text(user.companies ? user.companies.length : 0);
        $('.card:has(.card-title:contains("User Statistics")) h6:contains("Locations")').next('p').text(user.locations ? user.locations.length : 0);
        $('.card:has(.card-title:contains("User Statistics")) h6:contains("Areas")').next('p').text(user.areas ? user.areas.length : 0);
        $('.card:has(.card-title:contains("User Statistics")) h6:contains("Roles")').next('p').text(user.roles ? user.roles.length : 0);

        // Fallback method - try multiple selectors for each field
        // Header name fallback
        if ($('.card-body h5.mb-1').text() !== fullName) {
            $('h5.mb-1').text(fullName);
            $('.card h5').text(fullName);
            console.log('Fallback - Updated header name');
        }

        // Overview full name fallback
        $('h6:contains("Full Name")').next('p').text(fullName);
        $('.card-body h6:contains("Full Name")').next('p').text(fullName);

        // Statistics fallback
        $('h6:contains("Companies")').next('p').text(user.companies ? user.companies.length : 0);
        $('h6:contains("Locations")').next('p').text(user.locations ? user.locations.length : 0);
        $('h6:contains("Areas")').next('p').text(user.areas ? user.areas.length : 0);
        $('h6:contains("Roles")').next('p').text(user.roles ? user.roles.length : 0);

        console.log('Fallback updates completed');
        console.log('Direct updates completed for highlighted fields');
        console.log('All fields updated successfully for user:', fullName);
    }

    // Function to update relationship sections (companies, locations, areas, roles)
    function updateRelationshipSection(sectionName, items, iconClass, detailField) {
        const sectionSelector = `.card-header:contains("${sectionName}")`;
        const $section = $(sectionSelector).closest('.card');

        if ($section.length > 0) {
            const $cardBody = $section.find('.card-body');
            const $row = $cardBody.find('.row');

            if (items && items.length > 0) {
                // Update count in header
                $section.find('.card-title').text(`${sectionName} (${items.length})`);

                // Clear existing content
                $row.empty();

                // Add new items
                items.forEach(function (item) {
                    let detailText = '';
                    if (detailField === 'industry') {
                        detailText = `<p class="mb-1"><i class="ti ti-${iconClass} text-info me-2"></i>${item[detailField]}</p>`;
                        if (item.email) {
                            detailText += `<p class="mb-1"><i class="ti ti-mail text-warning me-2"></i>${item.email}</p>`;
                        }
                        if (item.phone) {
                            detailText += `<p class="mb-0"><i class="ti ti-phone text-success me-2"></i>${item.phone}</p>`;
                        }
                    } else if (detailField === 'address') {
                        detailText = `<p class="mb-1"><i class="ti ti-mail text-info me-2"></i>${item.email}</p>`;
                        detailText += `<p class="mb-1"><i class="ti ti-map-pin text-warning me-2"></i>${item[detailField]}</p>`;
                        detailText += `<p class="mb-0"><i class="ti ti-building text-success me-2"></i>${item.city}, ${item.state}, ${item.country}</p>`;
                    } else if (detailField === 'description') {
                        if (item.code) {
                            detailText = `<p class="mb-1"><i class="ti ti-code text-info me-2"></i>${item.code}</p>`;
                        }
                        if (item[detailField]) {
                            detailText += `<p class="mb-0"><i class="ti ti-note text-warning me-2"></i>${item[detailField]}</p>`;
                        }
                    } else if (detailField === 'role_name') {
                        detailText = `<p class="mb-1 text-muted"><i class="ti ti-calendar text-info me-2"></i>Created: ${new Date(item.created_at).toLocaleDateString()}</p>`;
                        if (item.updated_at) {
                            detailText += `<p class="mb-0 text-muted"><i class="ti ti-clock text-warning me-2"></i>Updated: ${new Date(item.updated_at).toLocaleDateString()}</p>`;
                        }
                    }

                    const itemHtml = `
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="fw-semibold">
                                    <i class="ti ti-${iconClass} text-primary me-2"></i>${item[detailField] || item.name}
                                </h6>
                                ${detailText}
                            </div>
                        </div>
                    `;
                    $row.append(itemHtml);
                });

                // Show the section
                $section.show();
            } else {
                // Hide the section if no items
                $section.hide();
            }
        }
    }

    // Function to show/hide relationship sections based on data
    function toggleRelationshipSection(sectionName, items) {
        const sectionSelector = `.card-header:contains("${sectionName}")`;
        const $section = $(sectionSelector).closest('.card').parent();

        if ($section.length === 0) {
            console.log(`Section not found for toggle: ${sectionName}`);
            return;
        }

        if (!items || items.length === 0) {
            $section.hide();
        } else {
            $section.show();
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
});
