$(document).ready(function() {
    // Select All Checkbox
    $('#select-all').change(function() {
        $('.user-select').prop('checked', $(this).prop('checked'));
    });

    // Role Filter
    $('#role').change(function() {
        var selectedRole = $(this).val();
        $.ajax({
            url: '/user-management',
            type: 'GET',
            data: {
                role: selectedRole
            },
            success: function(response) {
                if (response && response.users) {
                    updateTable(response.users);
                } else {
                    console.error('Invalid response format:', response);
                }
            },
            error: function(xhr) {
                console.error('Error filtering users:', xhr);
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
                    <td>${user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : ''}</td>
                    <td>
                        <span class="status-badge ${user.status || 'active'}">
                            ${user.status ? user.status.charAt(0).toUpperCase() + user.status.slice(1) : 'Active'}
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

    // Set up CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
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
                success: function(response) {
                    row.remove();
                    alert(response.message || 'User deleted successfully');
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON && xhr.responseJSON.error 
                        ? xhr.responseJSON.error 
                        : 'Failed to delete user. Please try again.';
                    alert('Error: ' + errorMsg);
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
            $.ajax({
                url: '/admin/users/bulk-delete',
                type: 'POST',
                data: { users: selectedUsers },
                success: function(response) {
                    $('.user-select:checked').closest('tr').remove();
                    alert(response.message || 'Selected users deleted successfully');
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON && xhr.responseJSON.error 
                        ? xhr.responseJSON.error 
                        : 'Failed to delete users. Please try again.';
                    alert('Error: ' + errorMsg);
                }
            });
        }
    });
});


// Show verification modal
$(document).on('click', '.btn-verify', function() {
    const userId = $(this).data('id');
    
    // Fetch student details
    $.ajax({
        url: `/admin/student/${userId}/details`,
        method: 'GET',
        success: function(student) {
            $('#student-name').text(student.name);
            $('#student-email').text(student.email);
            $('#student-id').text(student.student_id);
            $('#student-course').text(student.course || 'Not provided');
            $('#student-year').text(student.year_level || 'Not provided');
            $('#student-verification-status').text(student.verification_status || 'Pending Verification');
            
            // Store user ID for verification
            $('#verifyStudentModal').data('userId', userId);
            
            // Show the modal
            $('#verifyStudentModal').modal('show');
        },
        error: function(xhr) {
            alert('Error fetching student details');
            console.error(xhr);
        }
    });
});
// Handle verification decision
$('#verification-decision').change(function() {
    if ($(this).val() === 'reject') {
        $('#rejection-notes').show();
    } else {
        $('#rejection-notes').hide();
    }
});

// Submit verification
$('#submit-verification').click(function() {
    const userId = $('.btn-verify').data('id');
    const decision = $('#verification-decision').val();
    const notes = $('#admin-notes').val();

    if (decision === 'reject' && !notes.trim()) {
        alert('Please provide rejection notes');
        return;
    }

    $.ajax({
        url: `/admin/student/${userId}/verify`,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            decision: decision,
            notes: notes
        },
        success: function(response) {
            if (response.success) {
                $('#verifyStudentModal').modal('hide');
                // Refresh table or update status
                location.reload();
            }
        },
        error: function(xhr) {
            if (xhr.status === 401) {
                alert('You are not authorized to perform this action. Please log in as an admin.');
            } else {
                alert('Error processing verification. Please try again.');
            }
            console.error('Error:', xhr);
        }
    });
});