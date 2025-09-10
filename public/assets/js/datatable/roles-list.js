$(document).ready(function () {
    let rolesTable;

    if($('#roles_list').length > 0) {
		rolesTable = $('#roles_list').DataTable({
			"bFilter": false,
			"bInfo": false,
			"ordering": true,
			"autoWidth": true,
			"processing": true,
			"serverSide": false,
			"ajax": {
				"url": "/roles/data",
				"type": "GET",
				"dataSrc": "data"
			},
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
			initComplete: (settings, json)=>{
				$('.dataTables_paginate').appendTo('.datatable-paginate');
				$('.dataTables_length').appendTo('.datatable-length');
			},
			"columns": [
				{ "render": function ( data, type, row ){
					return '<div class="form-check form-check-md"><input class="form-check-input" type="checkbox" data-id="' + row.id + '"></div>';
				}},
				{ "data": "role_name" },
				{ "render": function ( data, type, row ){
					const grantedCount = row.permissions_count || 0;
					const totalCount = row.total_permissions || 0;
					const badgeClass = grantedCount > 0 ? 'bg-success' : 'bg-secondary';
					return '<span class="badge ' + badgeClass + '">' + grantedCount + '/' + totalCount + '</span>';
				}},
				{ "data": "created_at" },
				{ "render": function ( data, type, row ){
					return '<div class="dropdown table-action"><a href="#" class="action-icon btn btn-xs shadow btn-icon btn-outline-light" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></a><div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item edit-role" href="javascript:void(0);" data-id="' + row.id + '" data-name="' + row.role_name + '" data-bs-toggle="modal" data-bs-target="#edit_role"><i class="ti ti-edit text-blue"></i> Edit</a><a class="dropdown-item" href="/permissions?role_id=' + row.id + '"><i class="ti ti-shield"></i> Permission</a><a class="dropdown-item delete-role" href="javascript:void(0);" data-id="' + row.id + '" data-name="' + row.role_name + '"><i class="ti ti-trash text-red"></i> Delete</a></div></div>';
				}}
			]
		});
	}

    // Add Role Form Submit
    $('#addRoleForm').on('submit', function(e) {
        e.preventDefault();

        const roleName = $('#add_role_name').val().trim();

        if (!roleName) {
            showNotification('Please enter a role name', 'error');
            return;
        }

        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Creating...');

        // Send AJAX request
        $.ajax({
            url: '/roles',
            type: 'POST',
            data: {
                role_name: roleName,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Reload table data
                    rolesTable.ajax.reload();

                    // Reset form and close modal
                    $('#addRoleForm')[0].reset();
                    $('#add_role').modal('hide');

                    // Show success message
                    showNotification('Role created successfully!', 'success');
                } else {
                    showNotification(response.message || 'Error creating role', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                if (response && response.errors && response.errors.role_name) {
                    showNotification(response.errors.role_name[0], 'error');
                } else {
                    showNotification(response?.message || 'Error creating role', 'error');
                }
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Edit Role - Populate modal with data
    $(document).on('click', '.edit-role', function() {
        const roleId = $(this).data('id');
        const roleName = $(this).data('name');

        $('#edit_role_name').val(roleName);
        $('#editRoleForm').data('role-id', roleId);
    });

    // Edit Role Form Submit
    $('#editRoleForm').on('submit', function(e) {
        e.preventDefault();

        const roleId = $(this).data('role-id');
        const roleName = $('#edit_role_name').val().trim();

        if (!roleName) {
            showNotification('Please enter a role name', 'error');
            return;
        }

        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Saving...');

        // Send AJAX request
        $.ajax({
            url: '/roles/' + roleId,
            type: 'PUT',
            data: {
                role_name: roleName,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Reload table data
                    rolesTable.ajax.reload();

                    // Close modal
                    $('#edit_role').modal('hide');

                    // Show success message
                    showNotification('Role updated successfully!', 'success');
                } else {
                    showNotification(response.message || 'Error updating role', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                if (response && response.errors && response.errors.role_name) {
                    showNotification(response.errors.role_name[0], 'error');
                } else {
                    showNotification(response?.message || 'Error updating role', 'error');
                }
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Delete Role
    $(document).on('click', '.delete-role', function() {
        const roleId = $(this).data('id');
        const roleName = $(this).data('name');

        if (confirm('Are you sure you want to delete the role "' + roleName + '"? This action cannot be undone.')) {
            // Send AJAX request
            $.ajax({
                url: '/roles/' + roleId,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Reload table data
                        rolesTable.ajax.reload();

                        // Show success message
                        showNotification('Role deleted successfully!', 'success');
                    } else {
                        showNotification(response.message || 'Error deleting role', 'error');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showNotification(response?.message || 'Error deleting role', 'error');
                }
            });
        }
    });

    // Select All Checkbox
    $('#select-all').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('input[type="checkbox"][data-id]').prop('checked', isChecked);
    });

    // Individual checkbox change
    $(document).on('change', 'input[type="checkbox"][data-id]', function() {
        const totalCheckboxes = $('input[type="checkbox"][data-id]').length;
        const checkedCheckboxes = $('input[type="checkbox"][data-id]:checked').length;

        $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    // Search functionality
    $('.form-control[placeholder="Search"]').on('keyup', function() {
        rolesTable.search($(this).val()).draw();
    });

    // Refresh button
    $('a[aria-label="Refresh"]').on('click', function() {
        rolesTable.ajax.reload();
    });

    // Notification function
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        $('.alert-notification').remove();

        // Determine alert class based on type
        let alertClass = 'alert-info';
        if (type === 'success') {
            alertClass = 'alert-success';
        } else if (type === 'error') {
            alertClass = 'alert-danger';
        } else if (type === 'warning') {
            alertClass = 'alert-warning';
        }

        const notification = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed alert-notification" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('body').append(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            $('.alert-notification').fadeOut();
        }, 5000);
    }

});
