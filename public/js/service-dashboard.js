document.addEventListener('DOMContentLoaded', function() {
    loadServices();
    initializeRequestButtons();
});

function initializeRequestButtons() {
    // View Request Button
    document.querySelectorAll('.btn-view').forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-id');
            window.location.href = '/request/' + requestId;
        });
    });

    // Edit Request Button
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-id');
            const requestType = this.getAttribute('data-type');
            window.location.href = '/' + (requestType === 'student' ? 'student-request' : 'faculty-service') + '/edit/' + requestId;
        });
    });

    // Delete Request Button
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-id');
            if(confirm('Are you sure you want to delete this request?')) {
                fetch('/request/delete/' + requestId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (response.ok) {
                        location.reload();
                    } else {
                        throw new Error('Failed to delete request');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting request');
                });
            }
        });
    });
}


document.addEventListener('DOMContentLoaded', function() {
    loadServices();
});
function loadServices() {
    fetch('/services/list')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Adjust to match the backend response structure
            const services = data.services || data || [];
            displayServices(services);
        })
        .catch(error => {
            console.error('Error loading services:', error);
            displayServices([]);
        });
}

function displayServices(services) {
    const serviceList = document.querySelector('.service-list');
    if (!serviceList) return;

    serviceList.innerHTML = services.map(service => `
        <div class="category-card">
            ${service.image 
                ? `<img src="${service.image}" alt="${service.name}" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">` 
                : `<i class="fas ${getServiceIcon(service.name)}"></i>`
            }
            <h3>${service.name}</h3>
            <p>${service.description || 'No description available'}</p>
        </div>
    `).join('');
}
function loadServices() {
    fetch('/services/list')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Adjust to match the backend response structure
            const services = data.services || data || [];
            displayServices(services);
        })
        .catch(error => {
            console.error('Error loading services:', error);
            displayServices([]);
        });
}

function displayServices(services) {
    const serviceList = document.querySelector('.service-list');
    if (!serviceList) return;

    serviceList.innerHTML = services.map(service => `
        <div class="category-card">
            ${service.image 
                ? `<img src="${service.image}" alt="${service.name}" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">` 
                : `<i class="fas ${getServiceIcon(service.name)}"></i>`
            }
            <h3>${service.name}</h3>
            <p>${service.description || 'No description available'}</p>
        </div>
    `).join('');
}

function getServiceIcon(serviceName) {
    const name = serviceName.toLowerCase();
    if (name.includes('computer') || name.includes('pc')) return 'fa-desktop';
    if (name.includes('printer')) return 'fa-print';
    if (name.includes('network')) return 'fa-network-wired';
    if (name.includes('account')) return 'fa-user-lock';
    return 'fa-cogs'; // default icon
}

document.addEventListener('DOMContentLoaded', function() {
    loadServices();
});

