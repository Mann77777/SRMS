document.addEventListener('DOMContentLoaded', function() {
    loadServices();
});

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

    serviceList.innerHTML = services.map(service => `
        <div class="category-card">
            ${service.image 
                ? `<img src="${service.image}" alt="${service.name}" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">` 
                : `<i class="fas ${getServiceIcon(service.name)}"></i>`
            }
            <h3>${service.name}</h3>
            <p>${service.description}</p>
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

function requestService(serviceId) {
    // TODO: Implement service request functionality
    alert('Service request functionality will be implemented soon!');
}
