document.addEventListener('DOMContentLoaded', function() {
    // Show initial loading indicator
    Swal.fire({
        title: 'Loading...',
        text: 'Fetching services',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
        timer: 1000, // Auto close after 1 second to avoid flickering for fast responses
        showConfirmButton: false
    });
    
    loadServices();

    // Add event listener for the Add New Service button
    document.querySelector('.btn-primary').addEventListener('click', () => {
        $('#addServiceModal').modal('show');
    });
});

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function loadServices() {
    fetch('/services/list')
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            // Close any open SweetAlert
            Swal.close();
            
            if (data && Array.isArray(data.services)) {
                displayServices(data.services);
                updateDashboardServices(data.services);
            } else {
                console.error('Invalid data format received:', data);
                displayServices([]);
                
                // Show error message
                Swal.fire({
                    title: 'Data Error',
                    text: 'Invalid data format received from server',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error loading services:', error);
            displayServices([]);
            
            // Show error message
            Swal.fire({
                title: 'Loading Error',
                text: 'Failed to load services. Please try again later.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
}

function displayServices(services) {
    const serviceList = document.querySelector('.service-list');
    if (!serviceList) return;

    if (!Array.isArray(services)) {
        console.error('Services is not an array:', services);
        services = [];
    }
    
    if (services.length === 0) {
        serviceList.innerHTML = `
            <div class="col-12 text-center mt-4">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i> No services found. Add a new service to get started.
                </div>
            </div>
        `;
        return;
    }

    serviceList.innerHTML = services.map(service => `
        <div class="col-md-4 mb-4">
            <div class="status-card">
                <div class="service-image">
                    <img src="${service.image || '/images/services/default-service.jpg'}" alt="${service.name}" onerror="this.src='/images/services/default-service.jpg'">
                </div>
                <div class="status-details">
                    <h3>${service.name}</h3>
                    <p>${service.description}</p>
                    <div class="service-actions">
                        <button class="btn btn-warning" onclick="editService(${service.id}, '${service.name.replace(/'/g, "\\'")}', '${service.description.replace(/'/g, "\\'")}')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger" onclick="confirmDelete(${service.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function updateDashboardServices(services) {
    const dashboardServiceCount = document.querySelector('.service-count');
    if (dashboardServiceCount) {
        dashboardServiceCount.textContent = Array.isArray(services) ? services.length : 0;
    }
}

function editService(id, name, description) {
    // Show loading indicator while preparing the edit form
    Swal.fire({
        title: 'Loading...',
        text: 'Preparing edit form',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
        timer: 500, // Short timer since this is a quick operation
        showConfirmButton: false
    });

    document.getElementById('editServiceId').value = id;
    document.getElementById('editServiceName').value = name;
    document.getElementById('editServiceDescription').value = description;
    
    // Close loading indicator and show modal
    setTimeout(() => {
        Swal.close();
        $('#editServiceModal').modal('show');
    }, 300); // Give a slight delay for better UX
}

function saveEditedService() {
    const id = document.getElementById('editServiceId').value;
    const name = document.getElementById('editServiceName').value;
    const description = document.getElementById('editServiceDescription').value;
    
    // Validate form
    if (!name.trim() || !description.trim()) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Service name and description cannot be empty',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    const formData = new FormData();
    
    formData.append('_method', 'PUT');
    formData.append('name', name);
    formData.append('description', description);
    
    const imageFile = document.getElementById('editServiceImage').files[0];
    if (imageFile) {
        formData.append('image', imageFile);
    }
    
    // Show loading indicator
    Swal.fire({
        title: 'Saving...',
        text: 'Updating service information',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`/services/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        $('#editServiceModal').modal('hide');
        
        // Show success message
        Swal.fire({
            title: 'Success!',
            text: 'Service updated successfully',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            loadServices();
        });
    })
    .catch(error => {
        console.error('Error:', error);
        
        Swal.fire({
            title: 'Update Failed',
            text: error.error || 'Error updating service. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Delete Service?',
        text: 'Are you sure you want to delete this service? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteService(id);
        }
    });
}

function deleteService(id) {
    // Show loading indicator
    Swal.fire({
        title: 'Deleting...',
        text: 'Removing service',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`/services/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(() => {
        // Show success message
        Swal.fire({
            title: 'Deleted!',
            text: 'Service has been deleted successfully',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            loadServices();
        });
    })
    .catch(error => {
        console.error('Error:', error);
        
        Swal.fire({
            title: 'Delete Failed',
            text: error.error || 'Error deleting service. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}

function saveNewService() {
    const form = document.getElementById('addServiceForm');
    const formData = new FormData(form);
    
    // Validate form
    const name = formData.get('name');
    const description = formData.get('description');
    const image = formData.get('image');
    
    if (!name || !description) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Service name and description are required',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Show loading indicator
    Swal.fire({
        title: 'Saving...',
        text: 'Creating new service',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('/services', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        $('#addServiceModal').modal('hide');
        form.reset();
        
        // Show success message
        Swal.fire({
            title: 'Success!',
            text: 'New service added successfully',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            loadServices();
        });
    })
    .catch(error => {
        console.error('Error:', error);
        
        Swal.fire({
            title: 'Add Failed',
            text: error.error || 'Error adding service. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}