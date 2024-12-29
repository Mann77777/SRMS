// Handle Add User Form Submission
$('#addUserForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#addUserModal').modal('hide');
                $('#addUserForm')[0].reset();
                // Refresh the table without page reload
                $.ajax({
                    url: '/user-management',
                    type: 'GET',
                    success: function(response) {
                        if (response && response.users) {
                            updateTable(response.users);
                        }
                    }
                });
            } else {
                alert(response.error || 'Error adding user');
            }
        },
        error: function(xhr) {
            console.error('Error adding user:', xhr);
            alert('Error adding user: ' + (xhr.responseJSON?.error || 'Unknown error'));
        }
    });
});

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
                role: selectedRole === 'all' ? null : selectedRole
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
    $(document).ready(function() {
        // Status Filter
        $('#status').change(function() {
            var selectedStatus = $(this).val();
            $.ajax({
                url: '/user-management',
                type: 'GET',
                data: {
                    status: selectedStatus
                },
                success: function(response) {
                    if (response && response.users) {
                        updateTable(response.users);
                    } else {
                        console.error('Invalid response format:', response);
                    }
                },
                error: function(xhr) {
                    console.error('Error filtering users by status:', xhr);
                }
            });
        });
    
        // Combined Role and Status Filter
        function applyFilters() {
            var selectedRole = $('#role').val();
            var selectedStatus = $('#status').val();
            
            $.ajax({
                url: '/user-management',
                type: 'GET',
                data: {
                    role: selectedRole,
                    status: selectedStatus
                },
                success: function(response) {
                    if (response && response.users) {
                        updateTable(response.users);
                    } else {
                        console.error('Invalid response format:', response);
                    }
                },
                error: function(xhr) {
                    console.error('Error applying filters:', xhr);
                }
            });
        }
    
        // Add event listeners for combined filtering
        $('#role, #status').change(applyFilters);
    });

    // Update Table Function
    function updateTable(users) {
        var tbody = $('#users-table-body');
        tbody.empty();
        
        users.forEach(function(user) {
            // Determine verification status
            let verificationStatus = '<span class="status-badge">N/A</span>';
            if (user.role === 'Student') {
                if (!user.email_verified_at) {
                    verificationStatus = '<span class="status-badge pending">Email Unverified</span>';
                } else if (!user.student_id) {
                    verificationStatus = '<span class="status-badge pending">Details Required</span>';
                } else if (!user.admin_verified) {
                    verificationStatus = `
                        <span class="status-badge pending">Pending Verification</span>
                        <button class="btn-verify" title="Verify Student" data-id="${user.id}">Verify</button>
                    `;
                } else {
                    verificationStatus = '<span class="status-badge verified">Verified</span>';
                }
            } else if (user.role === 'Faculty') {
                if (!user.email_verified_at) {
                    verificationStatus = '<span class="status-badge pending">Email Unverified</span>';
                } else if (!user.admin_verified) {
                    verificationStatus = `
                        <span class="status-badge pending">Pending Verification</span>
                        <button class="btn-verify" title="Verify Faculty" data-id="${user.id}">Verify</button>
                    `;
                } else {
                    verificationStatus = '<span class="status-badge verified">Verified</span>';
                }
            }

            var row = `
                <tr>
                    <td><input type="checkbox" class="user-select" value="${user.id}"></td>
                    <td>${user.id}</td>
                    <td>
                        <strong>Name: </strong>${user.name}<br>
                        <strong>Username: </strong>${user.username}<br>
                        <strong>Email: </strong>${user.email || user.username}<br>
                        <strong>Student ID: </strong>${user.student_id || 'N/A'}
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
                        ${verificationStatus}
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

// Search Functionality
$('#search-input').on('input', function() {
    var searchTerm = $(this).val().toLowerCase().trim();
    var selectedRole = $('#role').val() === 'all' ? null : $('#role').val();
    var selectedStatus = $('#status').val() === 'all' ? null : $('#status').val();
    
    $.ajax({
        url: '/user-management',
        type: 'GET',
        data: {
            search: searchTerm,
            role: selectedRole,
            status: selectedStatus
        },
        success: function(response) {
            if (response && response.users) {
                updateTable(response.users);
            } else {
                console.error('Invalid response format:', response);
            }
        },
        error: function(xhr) {
            console.error('Error searching users:', xhr);
        }
    });
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
            $('#student-college').text(student.college || 'Not provided');
            $('#student-course').text(student.course || 'Not provided');
            $('#student-year').text(student.year_level || 'Not provided');
            $('#student-verification-status').text(student.verification_status || 'Pending Verification');
            
            // Store user ID for verification
            $('#verifyStudentModal').data('user-id', userId);
            
            // Show the modal
            $('#verifyStudentModal').modal('show');
        },
        error: function(xhr) {
            alert('Error fetching student details');
            console.error(xhr);
        }
    });
});

// Show/hide notes field based on decision
$('#verification-decision').on('change', function() {
    if ($(this).val() === 'reject') {
        $('#rejection-notes').show();
    } else {
        $('#rejection-notes').hide();
        $('#admin-notes').val(''); // Clear notes when switching back to approve
    }
});

// Handle verification submission
$('#submit-verification').click(function() {
    const userId = $('#verifyStudentModal').data('user-id');
    const decision = $('#verification-decision').val();
    const notes = $('#admin-notes').val();

    // Validate rejection notes
    if (decision === 'reject' && !notes.trim()) {
        alert('Please provide rejection notes');
        return;
    }

    $.ajax({
        url: `/admin/student/${userId}/verify`,  
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            decision: decision,
            notes: notes
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#verifyStudentModal').modal('hide');
                location.reload();
            } else {
                alert(response.error || 'Error processing verification');
            }
        },
        error: function(xhr) {
            console.error('Verification error:', xhr);
            alert('Error processing verification: ' + (xhr.responseJSON?.error || 'Unknown error'));
        }
    });
});