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
            },
            templateResult: function (data) {
                if (data.loading) return data.text;
                return data.text;
            },
            templateSelection: function (data) {
                return data.text;
            }
        });
    });

    // Reset form when offcanvas is opened
    $('#offcanvas_edit').on('show.bs.offcanvas', function () {
        // Reset submit button text and state
        var submitBtn = $('#edit-company-form').find('button[type="submit"]');
        submitBtn.html('Update Company').prop('disabled', false);

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

    // Handle form submission for editing company
    $('#edit-company-form').on('submit', function (e) {
        e.preventDefault();

        // Get form data and action URL
        const formData = $(this).serialize();
        const actionUrl = $(this).attr('action');

        // Disable submit button to prevent double submission
        var submitBtn = $(this).find('button[type="submit"]');
        var originalBtnText = 'Update Company'; // Use fixed text instead of current HTML
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
                    // Show success message using inner alert element for proper styling
                    var $editAlertWrapper = $('#edit-form-alert');
                    var $editAlert = $editAlertWrapper.find('.alert');
                    if ($editAlert.length === 0) {
                        $editAlertWrapper.html('<div class="alert alert-success alert-dismissible fade show" role="alert"></div>');
                        $editAlert = $editAlertWrapper.find('.alert');
                    }
                    $editAlert.removeClass('alert-danger').addClass('alert-success');
                    $editAlert.html('Company updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                    $editAlertWrapper.show();

                    // Update the page content with the new company data
                    updateCompanyDetails(response.company);

                    // Close the offcanvas after a delay (matching Index page behavior)
                    setTimeout(function () {
                        $('#offcanvas_edit').offcanvas('hide');
                    }, 2000);
                } else {
                    var $editAlertWrapper1 = $('#edit-form-alert');
                    var $editAlert1 = $editAlertWrapper1.find('.alert');
                    if ($editAlert1.length === 0) {
                        $editAlertWrapper1.html('<div class="alert alert-danger alert-dismissible fade show" role="alert"></div>');
                        $editAlert1 = $editAlertWrapper1.find('.alert');
                    }
                    $editAlert1.removeClass('alert-success').addClass('alert-danger');
                    $editAlert1.html(`Failed to update company: ${response.message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                    $editAlertWrapper1.show();
                }
            },
            error: function (xhr) {
                console.error('Error updating company:', xhr);

                let errorMessage = 'Failed to update company. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = '<ul>';
                    for (const field in xhr.responseJSON.errors) {
                        errorMessage += `<li>${xhr.responseJSON.errors[field][0]}</li>`;
                    }
                    errorMessage += '</ul>';
                }

                var $editAlertWrapper2 = $('#edit-form-alert');
                var $editAlert2 = $editAlertWrapper2.find('.alert');
                if ($editAlert2.length === 0) {
                    $editAlertWrapper2.html('<div class="alert alert-danger alert-dismissible fade show" role="alert"></div>');
                    $editAlert2 = $editAlertWrapper2.find('.alert');
                }
                $editAlert2.removeClass('alert-success').addClass('alert-danger');
                $editAlert2.html(`${errorMessage} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`);
                $editAlertWrapper2.show();
            },
            complete: function () {
                // Re-enable submit button
                submitBtn.html(originalBtnText).prop('disabled', false);
            }
        });
    });

    // Function to update company details on the page without reloading
    function updateCompanyDetails(company) {
        // Update company name
        $('h5.mb-1:contains("' + company.name + '")').text(company.name);

        // Update industry
        $('p.mb-2:contains("' + company.industry + '")').text(company.industry);

        // Update status badge in company header (main status badge)
        let statusClass = 'badge-soft-danger';
        let statusIcon = 'ti-lock';
        let statusText = 'Inactive';

        if (company.status == 0) {
            statusClass = 'badge-soft-danger';
            statusIcon = 'ti-trash';
            statusText = 'Delete';
        } else if (company.status == 1) {
            statusClass = 'badge-soft-success';
            statusIcon = 'ti-check';
            statusText = 'Active';
        } else if (company.status == 2) {
            statusClass = 'badge-soft-warning';
            statusIcon = 'ti-lock';
            statusText = 'Inactive';
        } else if (company.status == 3) {
            statusClass = 'badge-soft-danger';
            statusIcon = 'ti-ban';
            statusText = 'Block';
        }

        // Update the main status badge in company header
        $('.avatar-xxl + div .badge').first()
            .removeClass('badge-soft-success badge-soft-danger badge-soft-warning')
            .addClass(statusClass)
            .html(`<i class="ti ${statusIcon} me-1"></i>${statusText}`);

        // Update website, email, phone in company header
        if (company.website) {
            $('.d-inline-flex:contains("' + company.website + '")').html(`<i class="ti ti-world text-warning me-1"></i> ${company.website}`);
        }
        if (company.email) {
            $('.d-inline-flex:contains("' + company.email + '")').html(`<i class="ti ti-mail text-info me-1"></i> ${company.email}`);
        }
        if (company.phone) {
            $('.d-inline-flex:contains("' + company.phone + '")').html(`<i class="ti ti-phone text-success me-1"></i> ${company.phone}`);
        }

        // Update company overview section
        $('.col-md-6 .mb-4:contains("Company Name") p').text(company.name);
        $('.col-md-6 .mb-4:contains("Email Address") p a').text(company.email).attr('href', 'mailto:' + company.email);
        $('.col-md-6 .mb-4:contains("Industry") p').text(company.industry);
        $('.col-md-6 .mb-4:contains("Website") p').text(company.website);
        $('.col-md-6 .mb-4:contains("Phone") p').text(company.phone);

        // Update status in overview section
        $('.col-md-6 .mb-4:contains("Status") p span')
            .removeClass('badge-soft-success badge-soft-danger badge-soft-warning')
            .addClass(statusClass)
            .html(`<i class="ti ${statusIcon} me-1"></i>${statusText}`);

        // Note: For locations, addresses, contacts, and notes, a page reload would be needed
        // to properly update these complex sections. However, the main company details
        // will be updated without reload.
    }
});

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
