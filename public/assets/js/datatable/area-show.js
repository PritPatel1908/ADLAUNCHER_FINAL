$(document).ready(function () {
    // Initialize Select2 with proper configuration
    $('.select2-multiple').each(function () {
        console.log('Initializing Select2 for:', this);
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
        var submitBtn = $('#edit-area-form').find('button[type="submit"]');
        submitBtn.html('Update Area').prop('disabled', false);

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

    // Handle form submission for editing area
    $('#edit-area-form').on('submit', function (e) {
        e.preventDefault();
        console.log('Area edit form submitted');

        // Get form data and action URL
        const formData = $(this).serialize();
        const actionUrl = $(this).attr('action');
        console.log('Form action URL:', actionUrl);
        console.log('Form data:', formData);

        // Disable submit button to prevent double submission
        var submitBtn = $(this).find('button[type="submit"]');
        var originalBtnText = 'Update Area'; // Use fixed text instead of current HTML
        submitBtn.html('Updating...').prop('disabled', true);

        $.ajax({
            url: actionUrl,
            type: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                console.log('Area update success response:', response);

                if (response.success) {
                    // Show success message
                    $('#edit-form-alert').removeClass('alert-danger').addClass('alert-success');
                    $('#edit-form-alert').html('Area updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                    $('#edit-form-alert').show();

                    // Update the page content with the new area data
                    updateAreaDetails(response.area);

                    // Close the offcanvas after a delay (matching Index page behavior)
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
                console.error('Response status:', xhr.status);
                console.error('Response text:', xhr.responseText);

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
            },
            complete: function () {
                // Re-enable submit button
                submitBtn.html(originalBtnText).prop('disabled', false);
            }
        });
    });

    // Function to update area details on the page without reloading
    function updateAreaDetails(area) {
        console.log('Updating area details:', area);

        try {
            // Update area name in header - use more specific selector
            const nameHeader = $('.avatar-xxl').next('div').find('h5.mb-1');
            if (nameHeader.length > 0) {
                nameHeader.text(area.name);
                console.log('Updated area name in header');
            } else {
                console.log('Could not find area name header');
            }

            // Update description in header
            const descHeader = $('.avatar-xxl').next('div').find('p.mb-2');
            if (descHeader.length > 0) {
                descHeader.text(area.description || 'No description available');
                console.log('Updated area description in header');
            } else {
                console.log('Could not find area description header');
            }

            // Update status badge in area header (main status badge)
            let statusClass = 'badge-soft-danger';
            let statusIcon = 'ti-lock';
            let statusText = 'Inactive';

            if (area.status == 0) {
                statusClass = 'badge-soft-secondary';
                statusIcon = 'ti-trash';
                statusText = 'Delete';
            } else if (area.status == 1) {
                statusClass = 'badge-soft-success';
                statusIcon = 'ti-check';
                statusText = 'Active';
            } else if (area.status == 2) {
                statusClass = 'badge-soft-warning';
                statusIcon = 'ti-lock';
                statusText = 'Inactive';
            } else if (area.status == 3) {
                statusClass = 'badge-soft-danger';
                statusIcon = 'ti-ban';
                statusText = 'Block';
            }

            // Update the main status badge in area header
            const mainStatusBadge = $('.avatar-xxl').next('div').find('.badge').first();
            if (mainStatusBadge.length > 0) {
                mainStatusBadge
                    .removeClass('badge-soft-success badge-soft-danger badge-soft-warning badge-soft-secondary')
                    .addClass(statusClass)
                    .html(`<i class="ti ${statusIcon} me-1"></i>${statusText}`);
                console.log('Updated main status badge');
            } else {
                console.log('Could not find main status badge');
            }

            // Update code in area header if it exists
            if (area.code) {
                const codeElement = $('.d-inline-flex').filter(function () {
                    return $(this).text().includes(area.code);
                });
                if (codeElement.length > 0) {
                    codeElement.html(`<i class="ti ti-code text-warning me-1"></i> ${area.code}`);
                    console.log('Updated area code');
                } else {
                    console.log('Could not find area code element');
                }
            }

            // Update area overview section - use more specific selectors
            const overviewCard = $('.card-title').filter(function () {
                return $(this).text().trim() === 'Area Overview';
            }).closest('.card');

            if (overviewCard.length > 0) {
                // Update Area Name
                const nameElement = overviewCard.find('.mb-4').filter(function () {
                    return $(this).find('h6').text().trim() === 'Area Name';
                }).find('p');
                if (nameElement.length > 0) {
                    nameElement.text(area.name);
                    console.log('Updated area name in overview');
                }

                // Update Description
                const descElement = overviewCard.find('.mb-4').filter(function () {
                    return $(this).find('h6').text().trim() === 'Description';
                }).find('p');
                if (descElement.length > 0) {
                    descElement.text(area.description || 'No description available');
                    console.log('Updated area description in overview');
                }

                // Update Code
                const codeElement = overviewCard.find('.mb-4').filter(function () {
                    return $(this).find('h6').text().trim() === 'Code';
                }).find('p');
                if (codeElement.length > 0) {
                    codeElement.text(area.code || 'N/A');
                    console.log('Updated area code in overview');
                }

                // Update Status
                const statusElement = overviewCard.find('.mb-4').filter(function () {
                    return $(this).find('h6').text().trim() === 'Status';
                }).find('p span');
                if (statusElement.length > 0) {
                    statusElement
                        .removeClass('badge-soft-success badge-soft-danger badge-soft-warning badge-soft-secondary')
                        .addClass(statusClass)
                        .html(`<i class="ti ${statusIcon} me-1"></i>${statusText}`);
                    console.log('Updated area status in overview');
                }
            } else {
                console.log('Could not find Area Overview card');
            }

            // Update area statistics section
            const statsCard = $('.card-title').filter(function () {
                return $(this).text().trim() === 'Area Statistics';
            }).closest('.card');

            if (statsCard.length > 0) {
                // Update Locations count
                const locationsElement = statsCard.find('.mb-4').filter(function () {
                    return $(this).find('h6').text().trim() === 'Locations';
                }).find('p');
                if (locationsElement.length > 0) {
                    const locationsCount = area.locations ? area.locations.length : 0;
                    locationsElement.text(locationsCount);
                    console.log('Updated locations count in statistics:', locationsCount);
                }

                // Update Companies count
                const companiesElement = statsCard.find('.mb-4').filter(function () {
                    return $(this).find('h6').text().trim() === 'Companies';
                }).find('p');
                if (companiesElement.length > 0) {
                    const companiesCount = area.companies ? area.companies.length : 0;
                    companiesElement.text(companiesCount);
                    console.log('Updated companies count in statistics:', companiesCount);
                }

                // Update Created date
                const createdElement = statsCard.find('.mb-4').filter(function () {
                    return $(this).find('h6').text().trim() === 'Created';
                }).find('p');
                if (createdElement.length > 0) {
                    const createdDate = new Date(area.created_at);
                    createdElement.text(createdDate.toLocaleDateString('en-US', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }));
                    console.log('Updated created date in statistics');
                }

                // Update Last Updated date
                const updatedElement = statsCard.find('.mb-4').filter(function () {
                    return $(this).find('h6').text().trim() === 'Last Updated';
                }).find('p');
                if (updatedElement.length > 0) {
                    const updatedDate = new Date(area.updated_at);
                    updatedElement.text(updatedDate.toLocaleDateString('en-US', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }));
                    console.log('Updated last updated date in statistics');
                }

                // Update the detailed timestamps at the bottom
                const detailedCreated = statsCard.find('.mt-3 h6').filter(function () {
                    return $(this).text().trim() === 'Created';
                }).next('p');
                if (detailedCreated.length > 0) {
                    const createdDate = new Date(area.created_at);
                    detailedCreated.text(createdDate.toLocaleDateString('en-US', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }) + ', ' + createdDate.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    }));
                }

                if (area.updated_at) {
                    const detailedUpdated = statsCard.find('.mt-3 h6').filter(function () {
                        return $(this).text().trim() === 'Last Updated';
                    }).next('p');
                    if (detailedUpdated.length > 0) {
                        const updatedDate = new Date(area.updated_at);
                        detailedUpdated.text(updatedDate.toLocaleDateString('en-US', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }) + ', ' + updatedDate.toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        }));
                    }
                }
            }

            // Update the page title if it contains the area name
            const pageTitle = $('h4.mb-1');
            if (pageTitle.length > 0 && pageTitle.text().includes('Area Details')) {
                // Keep the page title as "Area Details" but update breadcrumb if needed
                console.log('Page title updated');
            }

            // Update Locations section
            updateLocationsSection(area.locations);

            // Update Companies section
            updateCompaniesSection(area.companies);

            console.log('Area details update completed successfully');
        } catch (error) {
            console.error('Error updating area details:', error);
        }
    }

    // Function to update the Locations section
    function updateLocationsSection(locations) {
        console.log('Updating locations section:', locations);

        const locationsCard = $('.card-title').filter(function () {
            return $(this).text().trim().includes('Locations (');
        }).closest('.card');

        if (locationsCard.length > 0) {
            const locationsBody = locationsCard.find('.card-body');

            if (locations && locations.length > 0) {
                // Update the header count
                const headerTitle = locationsCard.find('.card-title');
                headerTitle.text(`Locations (${locations.length})`);

                // Clear existing location items
                locationsBody.find('.row').empty();

                // Add new location items
                let locationsHtml = '<div class="row">';
                locations.forEach(function (location) {
                    locationsHtml += `
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="fw-semibold">${location.name || 'N/A'}</h6>
                                <p class="mb-1"><i class="ti ti-mail text-info me-2"></i>${location.email || 'N/A'}</p>
                                <p class="mb-1"><i class="ti ti-map-pin text-warning me-2"></i>${location.address || 'N/A'}</p>
                                <p class="mb-0"><i class="ti ti-building text-success me-2"></i>${location.city || 'N/A'}, ${location.state || 'N/A'}, ${location.country || 'N/A'}</p>
                            </div>
                        </div>
                    `;
                });
                locationsHtml += '</div>';

                locationsBody.html(locationsHtml);
                console.log('Updated locations section with', locations.length, 'locations');
            } else {
                // Hide the locations section if no locations
                locationsCard.hide();
                console.log('Hidden locations section - no locations');
            }
        } else {
            console.log('Could not find locations card');
        }
    }

    // Function to update the Companies section
    function updateCompaniesSection(companies) {
        console.log('Updating companies section:', companies);

        const companiesCard = $('.card-title').filter(function () {
            return $(this).text().trim().includes('Companies (');
        }).closest('.card');

        if (companiesCard.length > 0) {
            const companiesBody = companiesCard.find('.card-body');

            if (companies && companies.length > 0) {
                // Update the header count
                const headerTitle = companiesCard.find('.card-title');
                headerTitle.text(`Companies (${companies.length})`);

                // Clear existing company items
                companiesBody.find('.row').empty();

                // Add new company items
                let companiesHtml = '<div class="row">';
                companies.forEach(function (company) {
                    companiesHtml += `
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="fw-semibold">${company.name || 'N/A'}</h6>
                                <p class="mb-1"><i class="ti ti-building text-info me-2"></i>${company.industry || 'N/A'}</p>
                                <p class="mb-1"><i class="ti ti-mail text-warning me-2"></i>${company.email || 'N/A'}</p>
                                <p class="mb-0"><i class="ti ti-phone text-success me-2"></i>${company.phone || 'N/A'}</p>
                            </div>
                        </div>
                    `;
                });
                companiesHtml += '</div>';

                companiesBody.html(companiesHtml);
                console.log('Updated companies section with', companies.length, 'companies');
            } else {
                // Hide the companies section if no companies
                companiesCard.hide();
                console.log('Hidden companies section - no companies');
            }
        } else {
            console.log('Could not find companies card');
        }
    });

// Function to add new location in edit form
function addEditLocation() {
    const container = document.getElementById('edit-locations-container');
    const locationCount = container.children.length;

    const locationHtml = `
        <div class="location-item border rounded p-3 mb-2">
            <div class="row">
                <div class="col-md-8">
                    <select class="form-select" name="location_ids[]" required>
                        <option value="">Select Location</option>
                        <!-- Location options will be populated dynamically -->
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.parentElement.remove()">
                        <i class="ti ti-trash me-1"></i>Remove
                    </button>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', locationHtml);
}

// Function to add new company in edit form
function addEditCompany() {
    const container = document.getElementById('edit-companies-container');
    const companyCount = container.children.length;

    const companyHtml = `
        <div class="company-item border rounded p-3 mb-2">
            <div class="row">
                <div class="col-md-8">
                    <select class="form-select" name="company_ids[]" required>
                        <option value="">Select Company</option>
                        <!-- Company options will be populated dynamically -->
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.parentElement.remove()">
                        <i class="ti ti-trash me-1"></i>Remove
                    </button>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', companyHtml);
}
