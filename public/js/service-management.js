document.addEventListener('DOMContentLoaded', function() {
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
            if (data && Array.isArray(data.services)) {
                displayServices(data.services);
                updateDashboardServices(data.services);
            } else {
                console.error('Invalid data format received:', data);
                displayServices([]);
            }
        })
        .catch(error => {
            console.error('Error loading services:', error);
            displayServices([]);
        });
}

function displayServices(services) {
    const serviceList = document.querySelector('.service-list');
    if (!serviceList) return;

    if (!Array.isArray(services)) {
        console.error('Services is not an array:', services);
        services = [];
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
    document.getElementById('editServiceId').value = id;
    document.getElementById('editServiceName').value = name;
    document.getElementById('editServiceDescription').value = description;
    $('#editServiceModal').modal('show');
}

function saveEditedService() {
    const id = document.getElementById('editServiceId').value;
    const formData = new FormData();
    
    formData.append('_method', 'PUT');
    formData.append('name', document.getElementById('editServiceName').value);
    formData.append('description', document.getElementById('editServiceDescription').value);
    
    const imageFile = document.getElementById('editServiceImage').files[0];
    if (imageFile) {
        formData.append('image', imageFile);
    }
    
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
        loadServices();
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.error || 'Error updating service. Please try again.');
    });
}

function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this service?')) {
        deleteService(id);
    }
}

function deleteService(id) {
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
        loadServices();
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.error || 'Error deleting service. Please try again.');
    });
}

function saveNewService() {
    const form = document.getElementById('addServiceForm');
    const formData = new FormData(form);
    
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
        loadServices();
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.error || 'Error adding service. Please try again.');
    });
}
