// Add this to your existing JavaScript file or create a new one
// File: public/js/dashboard-requests.js

document.addEventListener('DOMContentLoaded', function() {
    // If the user is Faculty & Staff, fetch faculty requests
    const userRole = document.body.classList.contains('user-authenticated') ? 
        (document.body.dataset.userRole || '') : '';
    
    if (userRole === 'Faculty & Staff') {
        fetchFacultyRequests();
    }
});

function fetchFacultyRequests() {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch('/faculty-staff-recent-requests', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // Update the table with the fetched data
        updateRecentRequestsTable(data);
    })
    .catch(error => {
        console.error('Error fetching faculty requests:', error);
    });
}

function updateRecentRequestsTable(requests) {
    const tableBody = document.querySelector('.request-table tbody');
    if (!tableBody) return;
    
    // Don't clear existing rows if there are no new requests
    if (requests.length === 0) return;
    
    // Format the date
    function formatDate(dateString) {
        const date = new Date(dateString);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const month = months[date.getMonth()];
        const day = date.getDate();
        const year = date.getFullYear();
        let hours = date.getHours();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // Convert 0 to 12
        const minutes = String(date.getMinutes()).padStart(2, '0');
        
        return `${month} ${day}, ${year} ${hours}:${minutes} ${ampm}`;
    }
    
    // Get badge class based on status
    function getBadgeClass(status) {
        switch(status) {
            case 'Pending': return 'badge-warning';
            case 'In Progress': return 'badge-info';
            case 'Completed': return 'badge-success';
            case 'Rejected': return 'badge-danger';
            default: return 'badge-secondary';
        }
    }
    
    // Create rows for new requests
    const existingRows = Array.from(tableBody.querySelectorAll('tr'));
    const existingIds = existingRows.map(row => {
        const firstCell = row.querySelector('td:first-child');
        return firstCell ? firstCell.textContent.trim() : '';
    });
    
    // Add new requests that aren't already in the table
    requests.forEach(request => {
        // Skip if this request is already in the table
        if (existingIds.includes(String(request.id))) return;
        
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>${request.id}</td>
            <td>${request.service_type}</td>
            <td>${formatDate(request.created_at)}</td>
            <td>${formatDate(request.updated_at)}</td>
            <td><span class="badge ${getBadgeClass(request.status)}">${request.status}</span></td>
        `;
        
        // Insert at the top of the table
        if (tableBody.firstChild) {
            tableBody.insertBefore(row, tableBody.firstChild);
        } else {
            tableBody.appendChild(row);
        }
    });
    
    // If we have more than 3 rows now, remove excess
    const allRows = tableBody.querySelectorAll('tr');
    if (allRows.length > 3) {
        for (let i = 3; i < allRows.length; i++) {
            tableBody.removeChild(allRows[i]);
        }
    }
}