$(document).ready(function() {
    // Select All Checkbox
    $('#select-all').change(function() {
        $('.user-select').prop('checked', $(this).prop('checked'));
    });

    // Role Filter
    $('#role').change(function() {
        var selectedRole = $(this).val();
        $.ajax({
            url: '{{ route("admin.user-management") }}',
            type: 'GET',
            data: {
                role: selectedRole
            },
            success: function(response) {
                updateTable(response.users);
            }
        });
    });

    // Update Table Function
    function updateTable(users) {
        var tbody = $('#users-table-body');
        tbody.empty();
        
        users.forEach(function(user) {
            var row = `
                <tr>
                    <td><input type="checkbox" class="user-select" value="${user.id}"></td>
                    <td>${user.id}</td>
                    <td>
                        <strong>Name: </strong>${user.name}<br>
                        <strong>Email: </strong>${user.email || user.username}
                    </td>
                    <td>${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</td>
                    <td>
                        <span class="status-badge ${user.status || 'active'}">
                            ${user.status || 'Active'}
                        </span>
                    </td>
                    <td>
                        <strong>Date: </strong>${new Date(user.created_at).toLocaleDateString()}<br>
                        <strong>Time: </strong>${new Date(user.created_at).toLocaleTimeString()}
                    </td>
                    <td>
                        <button class="btn-edit" title="Edit" data-id="${user.id}">Edit</button>
                        <button class="btn-status" title="Toggle Status" data-id="${user.id}">Status</button>
                        <button class="btn-reset" title="Reset Password" data-id="${user.id}">Reset</button>
                        <button class="btn-delete" title="Delete" data-id="${user.id}">Delete</button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
});

$(document).ready(function() {
    // Edit User
    $('.btn-edit').click(function() {
        const userId = $(this).data('id');
        
        // Fetch user data
        $.ajax({
            url: `/admin/users/${userId}`,
            method: 'GET',
            success: function(response) {
                $('#edit-user-id').val(userId);
                $('#edit-name').val(response.name);
                $('#edit-email').val(response.email || response.username);
                $('#edit-role').val(response.role);
                $('#editUserModal').modal('show');
            },
            error: function(xhr) {
                alert('Error fetching user data');
            }
        });
    });

    // Save User Changes
    $('#saveUserChanges').click(function() {
        const userId = $('#edit-user-id').val();
        const userData = {
            name: $('#edit-name').val(),
            email: $('#edit-email').val(),
            role: $('#edit-role').val()
        };

        $.ajax({
            url: `/admin/users/${userId}`,
            method: 'PUT',
            data: userData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#editUserModal').modal('hide');
                // Refresh the page to show updated data
                location.reload();
            },
            error: function(xhr) {
                alert('Error updating user');
            }
        });
    });

    // Toggle User Status
    $('.btn-status').click(function() {
        const userId = $(this).data('id');
        const button = $(this);
        const statusBadge = button.closest('tr').find('.status-badge');

        $.ajax({
            url: `/admin/users/${userId}/toggle-status`,
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Update the status badge
                statusBadge.removeClass('active inactive').addClass(response.status);
                statusBadge.text(response.status.charAt(0).toUpperCase() + response.status.slice(1));
            },
            error: function(xhr) {
                alert('Error updating status');
            }
        });
    });

    // Reset Password
    $('.btn-reset').click(function() {
        if (confirm('Are you sure you want to reset this user\'s password?')) {
            const userId = $(this).data('id');
            
            $.ajax({
                url: `/admin/users/${userId}/reset-password`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    alert(`Password has been reset successfully!\nNew password: ${response.default_password}\nPlease inform the user of their new password.`);
                },
                error: function(xhr) {
                    alert('Error resetting password');
                }
            });
        }
    });

    // Delete User
    $('.btn-delete').click(function() {
        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            const userId = $(this).data('id');
            const row = $(this).closest('tr');
            
            $.ajax({
                url: `/admin/users/${userId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Remove the row from the table
                    row.remove();
                    alert('User deleted successfully');
                },
                error: function(xhr) {
                    alert('Error deleting user: ' + xhr.responseJSON.error);
                }
            });
        }
    });

    // Bulk Delete Users
    $('#bulk-delete').click(function() {
        const selectedUsers = $('.user-select:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedUsers.length === 0) {
            alert('Please select users to delete');
            return;
        }

        if (confirm(`Are you sure you want to delete ${selectedUsers.length} users? This action cannot be undone.`)) {
            const promises = selectedUsers.map(userId => {
                return $.ajax({
                    url: `/admin/users/${userId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            });

            Promise.all(promises)
                .then(() => {
                    // Remove all deleted rows
                    $('.user-select:checked').closest('tr').remove();
                    alert('Selected users deleted successfully');
                })
                .catch(error => {
                    alert('Error deleting some users. Please try again.');
                });
        }
    });
});