// Handle Add User Form Submission (Outside document.ready as it targets a static form)
$('#addUserForm').on('submit', function(e) {
    e.preventDefault();

    // Validate role and student-specific fields if needed
    var role = $('#add-role').val();

    // Additional validation for student role
    if (role === 'Student') {
        var college = $('#add-college').val();
        var course = $('#add-course').val();
        var studentId = $('#addUserForm input[name="student_id"]').val(); // More specific selector

        if (!college) {
            alert('Please select a college');
            return;
        }

        if (!course) {
            alert('Please select a course');
            return;
        }

        if (!studentId) {
            alert('Please enter a student ID');
            return;
        }
    }

    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        headers: { // Ensure CSRF token is sent for POST
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                // Redirect to user-management page
                window.location.href = '/user-management';
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


// Courses map (Global scope is fine)
const coursesMap = {
    'COE': [
        { value: 'BSCE', label: 'Bachelor of Science in Civil Engineering' },
        { value: 'BSEE', label: 'Bachelor of Science in Electrical Engineering' },
        { value: 'BSEsE', label: 'Bachelor of Science in Electronics Engineering' }
    ],
    'CIT': [
        { value: 'BSFT', label: 'Bachelor of Science in Food Technology' },
        { value: 'BSET-CET', label: 'Bachelor of Science in Engineering Technology Major in Computer Engineering Technology' },
        { value: 'BSET-CT', label: 'Bachelor of Science in Engineering Technology Major in Civil Technology' },
        { value: 'BSET-ET', label: 'Bachelor of Science in Engineering Technology Major in Electrical Technology' },
        { value: 'BSET-ECT', label: 'Bachelor of Science in Engineering Technology Major in Electronics Communications Technology' },
        { value: 'BSET', label: 'Bachelor of Science in Engineering Technology Major in Electronics Technology' },
        { value: 'BSET-ICT', label: 'Bachelor of Science in Engineering Technology Major in Instrumentation and Control Technology' },
        { value: 'BSET-MT', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology' },
        { value: 'BSET-MsT', label: 'Bachelor of Science in Engineering Technology Major in Mechatronics Technology' },
        { value: 'BSET-RT', label: 'Bachelor of Science in Engineering Technology Major in Railway Technology' },
        { value: 'BSET-CET-Auto', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology option in Automotive Technology' },
        { value: 'BSET-CET-Foundry', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology option in Foundry Technology' },
        { value: 'BSET-CET-HVAC', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology option in Heating Ventilating & Air-Conditioning/Refrigeration Technology' },
        { value: 'BSET-CET-PowerPlant', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology option in Power Plant Technology' },
        { value: 'BSET-CET-Welding', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology option in Welding Technology' },
        { value: 'BSET-CET-DiesMoulds', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology option in Dies and Moulds Technology' },
        { value: 'BTAF', label: 'Bachelor of Technology in Apparel and Fashion' },
        { value: 'BTNFT', label: 'Bachelor of Technology in Nutrition and Food Technology' },
        { value: 'BTPMT', label: 'Bachelor of Technology in Print Media Technology' }

    ],
    'CIE': [
        { value: 'BTA-ICT', label: 'Bachelor of Technology and Livelihood Education Major in Information and Communication Technology' },
        { value: 'BTA-HE', label: 'Bachelor of Technology and Livelihood Education Major in Home Economics' },
        { value: 'BTA-IA', label: 'Bachelor of Technology and Livelihood Education Major in Industrial Arts' },
        { value: 'BTVTE-Animation', label: 'Bachelor of Technical Vocational Teachers Education Major in Animation' },
        { value: 'BTVTE-BeautyCare', label: 'Bachelor of Technical Vocational Teachers Education Major in Beauty Care and Wellness' },
        { value: 'BTVTE-ComputerProgramming', label: 'Bachelor of Technical Vocational Teachers Education Major in Computer Programming' },
        { value: 'BTVTE-Electrical', label: 'Bachelor of Technical Vocational Teachers Education Major in Electrical' },
        { value: 'BTVTE-Electronics', label: 'Bachelor of Technical Vocational Teachers Education Major in Electronics' },
        { value: 'BTVTE-FoodService', label: 'Bachelor of Technical Vocational Teachers Education Major in Food Service Management' },
        { value: 'BTVTE-FashionGarment', label: 'Bachelor of Technical Vocational Teachers Education Major in Fashion and Garment' },
        { value: 'BTVTE-HVAC', label: 'Bachelor of Technical Vocational Teachers Education Major in Heat Ventilation & Air Conditioning' },
        { value: 'BTTT', label: 'Bachelor of Technical Teacher Education' }
    ],
    'CAFA': [
        { value: 'BSA-Arch', label: 'Bachelor of Science in Architecture' },
        { value: 'BFA', label: 'Bachelor of Fine Arts' },
        { value: 'BGTech-ArchTech', label: 'Bachelor of Graphics Technology Major in Architecture Technology' },
        { value: 'BGTech-IndDesign', label: 'Bachelor of Graphics Technology Major in Industrial Design' },
        { value: 'BGTech-MechanicalDraft', label: 'Bachelor of Graphics Technology Major in Mechanical Drafting Technology' },
    ],
    'COS': [
        { value: 'BSALT', label: 'Bachelor of Applied Science in Laboratory Technology' },
        { value: 'BSCS', label: 'Bachelor of Science in Computer Science' },
        { value: 'BSES', label: 'Bachelor of Science in Environmental Science' },
        { value: 'BSIS', label: 'Bachelor of Science in Information System' },
        { value: 'BSIT', label: 'Bachelor of Science in Information Technology'}
    ],

    'CLA': [
        { value: 'BSES', label: 'Bachelor of Arts in Management Major in Industrial Management' },
        { value: 'BSES', label: 'Bachelor of Science in Entrepreneurship Management' },
        { value: 'BSES', label: 'Bachelor of Science in Hospitality Management' },
    ]
};

// Dynamic role-based field display for Add User Modal
$('#add-role').on('change', function() {
    const studentDetails = $('#student-details');
    const studentIdInput = $('#addUserForm input[name="student_id"]'); // More specific selector
    const collegeSelect = $('#add-college');
    const courseSelect = $('#add-course');

    if ($(this).val() === 'Student') {
        studentDetails.show();
        studentIdInput.prop('required', true);
        collegeSelect.prop('required', true);
        courseSelect.prop('required', true);
    } else {
        studentDetails.hide();
        studentIdInput.prop('required', false);
        collegeSelect.prop('required', false);
        courseSelect.prop('required', false);

        // Clear student-specific inputs
        studentIdInput.val('');
        collegeSelect.val('');
        courseSelect.html('<option value="">Select Course</option>').prop('disabled', true);
    }
});

// Dynamic Course Selection for Add User Modal
$('#add-college').on('change', function() {
    const courseSelect = $('#add-course');
    const selectedCollege = $(this).val();

    // Clear previous courses
    courseSelect.html('<option value="">Select Course</option>');

    if (selectedCollege) {
        // Enable course dropdown
        courseSelect.prop('disabled', false);

        // Populate courses based on college
        const courses = coursesMap[selectedCollege] || []; // Use the map
        courses.forEach(course => {
            courseSelect.append(`<option value="${course.value}">${course.label}</option>`);
        });
    } else {
        // Disable course dropdown if no college selected
        courseSelect.prop('disabled', true);
    }
});

// Trigger change event on page load for Add User Modal
$('#add-role').trigger('change');


// --- Main Document Ready Block ---
$(document).ready(function() {

    // Set up CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Update Table Function (Defined early)
    function updateTable(users) {
        var tbody = $('#users-table-body');
        tbody.empty(); // Clear existing rows

        if (!users || users.length === 0) {
            tbody.append('<tr><td colspan="7" class="text-center">No users found matching your criteria.</td></tr>');
            return;
        }

        users.forEach(function(user) {
            // Determine verification status badge and button
            let verificationStatusHtml = '<span class="status-badge">N/A</span>'; // Default for non-student/faculty
            if (user.role === 'Student') {
                if (!user.email_verified_at) {
                    verificationStatusHtml = '<span class="status-badge pending">Email Unverified</span>';
                } else if (!user.student_id) {
                    verificationStatusHtml = '<span class="status-badge pending">Details Required</span>';
                } else if (!user.admin_verified) {
                    verificationStatusHtml = `
                        <span class="status-badge pending">Pending Verification</span>
                        <button class="btn-verify" title="Verify Student" data-id="${user.id}">Verify</button>
                    `;
                } else {
                    verificationStatusHtml = '<span class="status-badge verified">Verified</span>';
                }
            } else if (user.role === 'Faculty & Staff') {
                if (!user.email_verified_at) {
                    verificationStatusHtml = '<span class="status-badge pending">Email Unverified</span>';
                } else if (!user.admin_verified) {
                    verificationStatusHtml = `
                        <span class="status-badge pending">Pending Verification</span>
                        <button class="btn-verify-faculty" title="Verify Faculty/Staff" data-id="${user.id}">Verify</button>
                    `;
                } else {
                    verificationStatusHtml = '<span class="status-badge verified">Verified</span>';
                }
            }

            // Create student-specific field if needed
            let studentIdField = '';
            if (user.role === 'Student') {
                studentIdField = `<strong>Student ID: </strong>${user.student_id || 'Not Assigned'}<br>`;
            }

            // Format dates
            const createdAtDate = user.created_at ? new Date(user.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
            const createdAtTime = user.created_at ? new Date(user.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : '';

            // Determine status badge class and text
            const statusClass = user.status === 'inactive' ? 'inactive' : 'active';
            const statusText = user.status ? (user.status.charAt(0).toUpperCase() + user.status.slice(1)) : 'Active';

            var row = `
                <tr>
                    <td>${user.id}</td>
                    <td>
                        <strong>Name: </strong>${user.name || 'N/A'}<br>
                        <strong>Username: </strong>${user.username || 'N/A'}<br>
                        <strong>Email: </strong>${user.email || user.username || 'N/A'}<br>
                        ${studentIdField}
                    </td>
                    <td>${user.role ? (user.role.charAt(0).toUpperCase() + user.role.slice(1)) : ''}</td>
                    <td>
                        <span class="status-badge ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td>
                        ${createdAtDate}<br>
                        ${createdAtTime}
                    </td>
                    <td>
                        ${verificationStatusHtml}
                    </td>
                    <td class="b">
                        <button class="btn-edit" title="Edit" data-id="${user.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5z"/></svg> Edit
                        </button>
                        <button class="btn-status" title="Toggle Status" data-id="${user.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 8c0 1 1 1 1 1h5.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.544-3.393C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4Zm9.886-3.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382l.045-.148ZM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z"/></svg> Status
                        </button>
                        <button class="btn-reset" title="Reset Password" data-id="${user.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/><path d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/></svg> Reset
                        </button>
                        <button class="btn-delete" title="Delete" data-id="${user.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg> Delete
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Combined Role, Status, and Search Filter Function
    function applyFiltersAndSearch() {
        var selectedRole = $('#role').val();
        var selectedStatus = $('#status').val(); // Assuming you have a status filter with id="status"
        var searchTerm = $('#user-search').val().toLowerCase().trim();

        $.ajax({
            url: '/user-management', // Your endpoint to fetch users
            type: 'GET',
            data: {
                role: selectedRole === 'all' ? null : selectedRole,
                status: selectedStatus === 'all' ? null : selectedStatus, // Handle status filter
                search: searchTerm
            },
            success: function(response) {
                console.log('Filter/Search Response:', response);
                if (response && response.users) {
                    updateTable(response.users);
                } else {
                    console.error('Invalid response format:', response);
                    // Optionally display a message to the user
                    $('#users-table-body').html('<tr><td colspan="7" class="text-center">Error loading users.</td></tr>');
                }
            },
            error: function(xhr) {
                console.error('Error applying filters/search:', xhr);
                // Display error message in the table
                $('#users-table-body').html('<tr><td colspan="7" class="text-center">Error loading users. Please try again.</td></tr>');
            }
        });
    }

    // Add event listeners for combined filtering (Role, Status, Search)
    $('#role, #status').change(applyFiltersAndSearch); // Trigger on dropdown change

    // Search Functionality - Trigger applyFiltersAndSearch on input (with debounce)
    let searchTimeout;
    $('#user-search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFiltersAndSearch, 300); // Debounce for 300ms
    });

    // --- Other event listeners and functions ---

    // Edit User
    $(document).on('click', '.btn-edit', function() { // Use event delegation
        const userId = $(this).data('id');

        // Fetch user data
        $.ajax({
            url: `/admin/users/${userId}`,
            method: 'GET',
            success: function(response) {
                $('#edit-user-id').val(userId);
                $('#edit-name').val(response.name);
                $('#edit-username').val(response.username);
                $('#edit-email').val(response.email || response.username);
                $('#edit-role').val(response.role);

                // Trigger role change to show/hide student details
                $('#edit-role').trigger('change');

                // If student, populate college and course
                if (response.role === 'Student') {
                    $('#edit-college').val(response.college);

                    // Populate courses for the selected college
                    const courseSelect = $('#edit-course');
                    courseSelect.empty().append('<option value="">Select Course</option>');

                    const courses = coursesMap[response.college] || []; // Use the map
                    courses.forEach(course => {
                        courseSelect.append(`<option value="${course.value}">${course.label}</option>`);
                    });

                    courseSelect.prop('disabled', false);
                    $('#edit-course').val(response.course);
                    $('#edit-student-id').val(response.student_id);
                }

                $('#editUserModal').modal('show');
            },
            error: function(xhr) {
                alert('Error fetching user data');
            }
        });
    });

    // Save User Changes
    $('#saveUserChanges').click(function() {
        // Validate role and student-specific fields if needed
        var role = $('#edit-role').val();

        // Additional validation for student role
        if (role === 'Student') {
            var college = $('#edit-college').val();
            var course = $('#edit-course').val();
            var studentId = $('#edit-student-id').val();

            if (!college) {
                alert('Please select a college');
                return;
            }

            if (!course) {
                alert('Please select a course');
                return;
            }

            if (!studentId) {
                alert('Please enter a student ID');
                return;
            }
        }

        $.ajax({
            url: '/update-user/' + $('#edit-user-id').val(),
            method: 'POST', // Should be PUT or PATCH for update, ensure route supports it
            data: $('#editUserForm').serialize(),
            success: function(response) {
                if (response.success) {
                    // Redirect or reload
                    window.location.href = '/user-management';
                } else {
                    alert(response.error || 'Error updating user');
                }
            },
            error: function(xhr) {
                console.error('Error updating user:', xhr);
                alert('Error updating user: ' + (xhr.responseJSON?.error || 'Unknown error'));
            }
        });
    });

    // Dynamic role-based field display for edit modal
    $('#edit-role').change(function() {
        var selectedRole = $(this).val();
        var studentDetails = $('#edit-student-details');
        var collegeSelect = $('#edit-college');
        var courseSelect = $('#edit-course');
        var studentIdInput = $('#edit-student-id');

        if (selectedRole === 'Student') {
            studentDetails.show();
            // Populate college dropdown if needed (assuming it's static)
            // Populate course dropdown based on selected college
            collegeSelect.trigger('change'); // Trigger change to populate courses if college is pre-selected
            studentIdInput.prop('required', true);
        } else {
            studentDetails.hide();
            collegeSelect.val('');
            courseSelect.html('<option value="">Select Course</option>').prop('disabled', true);
            studentIdInput.val('').prop('required', false);
        }
    });

    // Dynamic Course Selection for Edit Modal
    $('#edit-college').on('change', function() {
        const courseSelect = $('#edit-course');
        const selectedCollege = $(this).val();

        courseSelect.html('<option value="">Select Course</option>'); // Clear previous

        if (selectedCollege) {
            courseSelect.prop('disabled', false);
            const courses = coursesMap[selectedCollege] || [];
            courses.forEach(course => {
                courseSelect.append(`<option value="${course.value}">${course.label}</option>`);
            });
            // Note: Pre-selecting the course happens in the edit button click handler after populating
        } else {
            courseSelect.prop('disabled', true);
        }
    });


    // Toggle User Status
    // Use event delegation for dynamically loaded content
    $(document).on('click', '.btn-status', function() {
        const userId = $(this).data('id');
        const button = $(this);
        const statusBadge = button.closest('tr').find('.status-badge').first(); // Target the first status badge in the row

        $.ajax({
            url: `/admin/users/${userId}/toggle-status`,
            method: 'PUT', // Use PUT for status toggle
            success: function(response) {
                if (response.success) {
                    // Update the status badge text and class
                    statusBadge.removeClass('active inactive').addClass(response.status);
                    statusBadge.text(response.status.charAt(0).toUpperCase() + response.status.slice(1));
                } else {
                     alert(response.error || 'Error updating status');
                }
            },
            error: function(xhr) {
                alert('Error updating status');
            }
        });
    });

    // Reset Password
    // Use event delegation
    $(document).on('click', '.btn-reset', function() {
        if (confirm('Are you sure you want to reset this user\'s password?')) {
            const userId = $(this).data('id');

            $.ajax({
                url: `/admin/users/${userId}/reset-password`,
                method: 'POST',
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
    // Use event delegation
    $(document).on('click', '.btn-delete', function() {
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
    $('#verification-decision, #verification-decision-faculty').on('change', function() {
        const notesContainer = $(this).closest('.modal-body').find('.rejection-notes-container'); // Find the specific container
        if ($(this).val() === 'reject') {
            notesContainer.show();
        } else {
            notesContainer.hide();
            notesContainer.find('textarea').val(''); // Clear notes
        }
    });


    // Handle verification submission
    $('#submit-verification').click(function() {
        const userId = $('#verifyStudentModal').data('user-id');
        const decision = $('#verification-decision').val();
        const notes = $('#admin-notes-student').val(); // Use specific ID

        // Validate rejection notes
        if (decision === 'reject' && !notes.trim()) {
            alert('Please provide rejection notes');
            return;
        }

        $.ajax({
            url: `/admin/student/${userId}/verify`,
            method: 'POST',
            data: {
                decision: decision,
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#verifyStudentModal').modal('hide');
                    location.reload(); // Reload to reflect changes
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

    // Faculty/Staff Verification Submission
    $('#submit-facultystaff-verification').click(function() {
        const userId = $('#verifyFacultyStaffModal').data('user-id');
        const decision = $('#verification-decision-faculty').val();
        const notes = $('#admin-notes-faculty').val(); // Use specific ID

        console.log('Verification Data:', {
            userId: userId,
            decision: decision,
            notes: notes
        });

        // Validate rejection notes
        if (decision === 'reject' && !notes.trim()) {
            alert('Please provide rejection notes');
            return;
        }

        $.ajax({
            url: `/admin/facultystaff/${userId}/verify`,
            method: 'POST',
            data: {
                decision: decision,
                notes: notes
            },
            success: function(response) {
                console.log('Verification Success:', response);
                if (response.success) {
                    alert(response.message);
                    $('#verifyFacultyStaffModal').modal('hide');
                    location.reload(); // Reload to reflect changes
                } else {
                    alert(response.error || 'Error processing verification');
                }
            },
            error: function(xhr) {
                console.error('Verification Error:', xhr);
                let errorMessage = 'Unknown error';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.statusText) {
                    errorMessage = xhr.statusText;
                }
                alert('Error processing verification: ' + errorMessage);
            }
        });
    });

    // Show Faculty/Staff verification modal
    $(document).on('click', '.btn-verify-faculty', function() {
        var $row = $(this).closest('tr');
        var userId = $(this).data('id');

        // Set user ID on modal
        $('#verifyFacultyStaffModal').data('user-id', userId);

        // Populate modal details from table row data
        var userData = $row.find('td:nth-child(2)').html().split('<br>'); // Split user data cell
        var name = userData[0].replace('<strong>Name: </strong>', '').trim();
        var username = userData[1].replace('<strong>Username: </strong>', '').trim();
        var email = userData[2].replace('<strong>Email: </strong>', '').trim();
        var verificationStatusText = $row.find('td:nth-child(6) .status-badge').text().trim();


        $('#facultystaff-name').text(name);
        $('#facultystaff-email').text(email);
        $('#facultystaff-username').text(username);
        $('#facultystaff-verification-status').text(verificationStatusText);

        // Reset modal state
        $('#verification-decision-faculty').val('approve');
        $('#rejection-notes-faculty').hide(); // Hide notes container
        $('#admin-notes-faculty').val(''); // Clear notes textarea

        $('#verifyFacultyStaffModal').modal('show');
    });
// End of the single, main $(document).ready() block
});
