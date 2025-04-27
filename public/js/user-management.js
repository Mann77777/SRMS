// Handle Add User Form Submission
$('#addUserForm').on('submit', function(e) {
    e.preventDefault();
    
    // Validate role and student-specific fields if needed
    var role = $('#add-role').val();
    
    // Additional validation for student role
    if (role === 'Student') {
        var college = $('#add-college').val();
        var course = $('#add-course').val();
        var studentId = $('input[name="student_id"]').val();
        
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


// Courses map similar to details-form.blade.php
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

// Dynamic role-based field display
$(document).ready(function() {
    // Role and Student Details Handling
    $('#add-role').on('change', function() {
        const studentDetails = $('#student-details');
        const studentIdInput = $('input[name="student_id"]');
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

    // Validate form before submission
    $('#addUserForm').on('submit', function(e) {
        const role = $('#add-role').val();
        
        // If not a student, remove student-specific field validations
        if (role !== 'Student') {
            $('input[name="student_id"]').removeAttr('required');
            $('#add-college').removeAttr('required');
            $('#add-course').removeAttr('required');
        }
    });

    // Trigger change event on page load to handle initial state
    $('#add-role').trigger('change');

    // Dynamic Course Selection for Student
    $('#add-college').on('change', function() {
        const courseSelect = $('#add-course');
        const selectedCollege = $(this).val();
        
        // Clear previous courses
        courseSelect.html('<option value="">Select Course</option>');
        
        if (selectedCollege) {
            // Enable course dropdown
            courseSelect.prop('disabled', false);
            
            // Populate courses based on college
            const courses = {
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
                    { value: 'BSES', label: 'Bachelor of Science in Hospitality Management' }
                ]
            };

            const collegeCourses = courses[selectedCollege] || [];
            collegeCourses.forEach(course => {
                courseSelect.append(`<option value="${course.value}">${course.label}</option>`);
            });
        } else {
            // Disable course dropdown if no college selected
            courseSelect.prop('disabled', true);
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
            } else if (user.role === 'Faculty & Staff') {
                if (!user.email_verified_at) {
                    verificationStatus = '<span class="status-badge pending">Email Unverified</span>';
                } else if (!user.admin_verified) {
                    verificationStatus = `
                        <span class="status-badge pending">Pending Verification</span>
                        <button class="btn-verify-faculty" title="Verify Faculty/Staff" data-id="${user.id}">Verify</button>
                    `;
                } else {
                    verificationStatus = '<span class="status-badge verified">Verified</span>';
                }
            }

             // Create student-specific field if needed
            let studentIdField = '';
            if (user.role === 'Student') {
                studentIdField = `<strong>Student ID: </strong>${user.student_id || 'Not Assigned'}<br>`;
            }


            var row = `
                <tr>
                    <td>${user.id}</td>
                    <td>
                        <strong>Name: </strong>${user.name}<br>
                        <strong>Username: </strong>${user.username}<br>
                        <strong>Email: </strong>${user.email || user.username}<br>
                        ${studentIdField}
                    </td>
                    <td>${user.role ? (user.role.charAt(0).toUpperCase() + user.role.slice(1)) : ''}</td>
                    <td>
                        <span class="status-badge ${user.status || 'active'}">
                            ${user.status ? (user.status.charAt(0).toUpperCase() + user.status.slice(1)) : 'Active'}
                        </span>
                    </td>
                    <td>
                        ${new Date(user.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}<br>
                        ${new Date(user.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })}
                    </td>
                    <td>
                        ${verificationStatus}
                    </td>
                    <td class="b">
                        <button class="btn-edit" title="Edit" data-id="${user.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5z"/>
                            </svg> Edit
                        </button>

                        <button class="btn-status" title="Toggle Status" data-id="${user.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 8c0 1 1 1 1 1h5.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.544-3.393C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4Zm9.886-3.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382l.045-.148ZM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z"/>
                            </svg> Status
                        </button>

                        <button class="btn-reset" title="Reset Password" data-id="${user.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                                <path d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                            </svg> Reset
                        </button>

                        <button class="btn-delete" title="Delete" data-id="${user.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                            </svg> Delete
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
});

// Search Functionality
$('#user-search').on('input', function() {
    var searchTerm = $(this).val().toLowerCase().trim();
    var selectedRole = $('#role').val() === 'all' ? null : $('#role').val();
    
    $.ajax({
        url: '/user-management',
        type: 'GET',
        data: {
            search: searchTerm,
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
                    
                    const courses = {
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
                    
                    const collegeCourses = courses[response.college] || [];
                    collegeCourses.forEach(course => {
                        courseSelect.append(`<option value="${course.value}">${course.label}</option>`);
                    });
                    
                    courseSelect.prop('disabled', false);
                    $('#edit-course').val(response.course);
                    $('#edit-student-id').val(response.student_id);
                }
                
                $('#editUserModal').modal('show');
            },
            error: function(xhr) { u
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
            method: 'POST',
            data: $('#editUserForm').serialize(),
            success: function(response) {
                if (response.success) {
                    // Redirect to user-management page
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
            courseSelect.prop('disabled', true);
            studentIdInput.prop('required', true);
        } else {
            studentDetails.hide();
            collegeSelect.val('');
            courseSelect.val('').prop('disabled', true);
            studentIdInput.val('').prop('required', false);
        }
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
    if ($(this).val() === 'reject') {
        $(this).closest('.verification-action').find('[id^=rejection-notes]').show();
    } else {
        $(this).closest('.verification-action').find('[id^=rejection-notes]').hide();
        $(this).closest('.verification-action').find('textarea[id^=admin-notes]').val(''); // Clear notes
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

// Faculty/Staff Verification Submission
$('#submit-facultystaff-verification').click(function() {
    const userId = $('#verifyFacultyStaffModal').data('user-id');
    const decision = $('#verification-decision-faculty').val();
    const notes = $('#admin-notes').val();

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
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            decision: decision,
            notes: notes
        },
        success: function(response) {
            console.log('Verification Success:', response);
            if (response.success) {
                alert(response.message);
                $('#verifyFacultyStaffModal').modal('hide');
                location.reload();
            } else {
                alert(response.error || 'Error processing verification');
            }
        },
        error: function(xhr) {
            console.error('Verification Error:', xhr);
            console.error('Response Text:', xhr.responseText);
            console.error('Status:', xhr.status);
            console.error('Response JSON:', xhr.responseJSON);
            
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

$(document).on('click', '.btn-verify-faculty', function() {
    var $row = $(this).closest('tr');
    var userId = $(this).data('id');
    
    // Set user ID on modal
    $('#verifyFacultyStaffModal').data('user-id', userId);

    // Populate modal details
    $('#facultystaff-name').text($row.find('td:nth-child(2)').text());
    $('#facultystaff-email').text($row.find('td:nth-child(3)').text());
    $('#facultystaff-username').text($row.find('td:nth-child(4)').text());
    $('#facultystaff-verification-status').text($row.find('td:nth-child(7)').text());

    // Reset modal state
    $('#verification-decision-faculty').val('approve');
    $('#rejection-notes').hide();
    $('#admin-notes').val('');

    $('#verifyFacultyStaffModal').modal('show');
});