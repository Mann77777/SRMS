// Notification polling and interaction
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const notificationButton = document.getElementById('notificationButton');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notification-badge');
    const notificationList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    
    // State
    let notificationsVisible = false;
    let pollingInterval = null;
    
    // Check if elements exist (avoid errors on pages without notification elements)
    if (!notificationButton || !notificationDropdown || !notificationBadge || !notificationList) {
        console.log('Notification elements not found on this page');
        return;
    }
    
    // Toggle notifications dropdown
    notificationButton.addEventListener('click', function(e) {
        e.preventDefault();
        notificationsVisible = !notificationsVisible;
        
        if (notificationsVisible) {
            notificationDropdown.style.display = 'block';
            fetchNotifications();
        } else {
            notificationDropdown.style.display = 'none';
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!notificationButton.contains(e.target) && 
            !notificationDropdown.contains(e.target) && 
            notificationsVisible) {
            notificationDropdown.style.display = 'none';
            notificationsVisible = false;
        }
    });
    
    // Mark all as read
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            markAllAsRead();
        });
    }
    
    // Start polling
    startPolling();
    
    // Polling function
    function startPolling() {
        // Initial fetch
        fetchNotificationCount();
        
        // Set up interval (every 30 seconds)
        pollingInterval = setInterval(fetchNotificationCount, 30000);
    }
    
    // Fetch notification count
    function fetchNotificationCount() {
        fetch('/uitc-staff/notifications/get', { // Changed URL prefix
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                 // Check for 401 Unauthorized specifically
                if (response.status === 401) {
                    console.error('Fetching notification count failed: Unauthorized. Check authentication and route middleware.');
                    // Optionally, redirect to login or show a message
                    // window.location.href = '/login'; // Example redirect
                } else {
                    console.error(`Fetching notification count failed: Network response was not ok (Status: ${response.status})`);
                }
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            updateNotificationBadge(data.count);
        })
        .catch(error => {
             // Error already logged in the first .then block for specific statuses
            if (error.message !== 'Network response was not ok') { // Avoid double logging generic network errors
                 console.error('Error processing notification count:', error);
            }
        });
    }
    
    // Fetch full notifications
    function fetchNotifications() {
        notificationList.innerHTML = '<div class="loading-spinner">Loading...</div>'; // Show loading indicator

        fetch('/uitc-staff/notifications/get', { // Changed URL prefix
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                 if (response.status === 401) {
                    console.error('Fetching notifications failed: Unauthorized.');
                    notificationList.innerHTML = '<div class="empty-notifications">Error: Unauthorized. Please check login status.</div>';
                 } else {
                    console.error(`Fetching notifications failed: Network response was not ok (Status: ${response.status})`);
                    notificationList.innerHTML = `<div class="empty-notifications">Failed to load notifications (Error: ${response.status})</div>`;
                 }
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            updateNotificationBadge(data.count);
            renderNotifications(data.unread, data.read);
        })
        .catch(error => {
             // Error already logged or handled in the first .then block
             if (error.message !== 'Network response was not ok') {
                 console.error('Error processing full notifications:', error);
                 // Keep the error message displayed in notificationList if it was set previously
                 if (!notificationList.querySelector('.empty-notifications')) {
                    notificationList.innerHTML = '<div class="empty-notifications">An unexpected error occurred.</div>';
                 }
             }
        });
    }
    
    // Update notification badge
    function updateNotificationBadge(count) {
        if (count > 0) {
            notificationBadge.textContent = count > 99 ? '99+' : count;
            notificationBadge.style.display = 'block';
        } else {
            notificationBadge.style.display = 'none';
        }
    }
    
    // Render notifications
    function renderNotifications(unread, read) {
        notificationList.innerHTML = '';
        
        if (unread.length === 0 && read.length === 0) {
            notificationList.innerHTML = '<div class="empty-notifications">No notifications</div>';
            return;
        }
        
        // Render unread notifications
        unread.forEach(notification => {
            const notificationEl = createNotificationElement(notification, true);
            notificationList.appendChild(notificationEl);
        });
        
        // Render read notifications
        read.forEach(notification => {
            const notificationEl = createNotificationElement(notification, false);
            notificationList.appendChild(notificationEl);
        });
    }
    
    // Create notification element
    // Create notification element
function createNotificationElement(notification, isUnread) {
    const notificationEl = document.createElement('div');
    notificationEl.className = `notification-item${isUnread ? ' unread' : ''}`;
    notificationEl.dataset.id = notification.id;
    
    const data = notification.data;
    const time = new Date(notification.created_at).toLocaleString();
    
    // Enhanced display for notifications
    let content = data.message;
    let detailsHtml = '';
    let notificationIconHtml = ''; // Changed variable name for clarity
    
    // Default icon
    let iconClass = 'fas fa-bell'; // Default bell icon
    let iconColor = 'inherit'; // Default color

    if (data.icon) {
        iconClass = data.icon;
    }
    if (data.color) {
        iconColor = data.color;
    }

    // Specific handling for unresolvable notifications
    if (data.notification_type === 'unresolvable') {
        iconClass = data.icon || 'fas fa-times-circle'; // Default to X if not provided
        iconColor = data.color || 'red'; // Default to red if not provided
        notificationEl.style.setProperty('--notification-icon-color', iconColor); // For potential CSS targeting

        detailsHtml = `
            <div class="notification-details unresolvable">
                <div class="detail-item"><strong>Service:</strong> ${data.service_name || data.service_category || 'N/A'}</div>
                <div class="detail-item"><strong>Marked by:</strong> ${data.staff_name || 'N/A'}</div>
                <div class="detail-item"><strong>Reason:</strong> ${data.reason || 'N/A'}</div>
                ${data.actions_taken ? `<div class="detail-item"><strong>Actions Taken:</strong> ${data.actions_taken}</div>` : ''}
            </div>
        `;
    } else if (data.staff_name && data.actions_taken) { // Completion
        iconClass = data.icon || 'fas fa-check-circle';
        iconColor = data.color || 'green'; // Or 'text-success' if using Bootstrap classes directly
        notificationEl.style.setProperty('--notification-icon-color', iconColor);

        detailsHtml = `
            <div class="notification-details completion">
                <div class="detail-item"><strong>Service:</strong> ${data.service_name || data.service_category || 'N/A'}</div>
                <div class="detail-item"><strong>Completed by:</strong> ${data.staff_name || 'N/A'}</div>
                ${data.transaction_type ? `<div class="detail-item"><strong>Transaction Type:</strong> ${data.transaction_type}</div>` : ''}
                ${data.actions_taken ? `<div class="detail-item"><strong>Actions Taken:</strong> ${data.actions_taken}</div>` : ''}
            </div>
        `;
    } else if (data.staff_name && (data.service_name || data.service_category)) { // Assignment
        iconClass = data.icon || 'fas fa-user-check';
        iconColor = data.color || 'blue'; // Or 'text-primary'
        notificationEl.style.setProperty('--notification-icon-color', iconColor);

        detailsHtml = `
            <div class="notification-details assignment">
                <div class="detail-item"><strong>Service:</strong> ${data.service_name || data.service_category || 'N/A'}</div>
                <div class="detail-item"><strong>Assigned to:</strong> ${data.staff_name || 'N/A'}</div>
                ${data.transaction_type ? `<div class="detail-item"><strong>Transaction Type:</strong> ${data.transaction_type}</div>` : ''}
                ${data.notes ? `<div class="detail-item"><strong>Notes:</strong> ${data.notes}</div>` : ''}
            </div>
        `;
    }
    // Fallback for other/generic notifications if necessary
    // else {
        // detailsHtml = `<div class="notification-details generic">${data.message}</div>`;
        // content = ''; // Clear content if message is in detailsHtml
    // }

    notificationIconHtml = `<i class="${iconClass}" style="color: ${iconColor}; margin-right: 8px;"></i>`;
    
    notificationEl.innerHTML = `
        <div class="notification-content">
            ${notificationIconHtml}
            <span class="notification-message-text">${content}</span>
            ${detailsHtml}
        </div>
        <div class="notification-time">
            ${time}
        </div>
    `;
    
    // Click handler to mark as read and redirect
    notificationEl.addEventListener('click', function() {
        if (isUnread) {
            markAsRead(notification.id);
        }
        
        // Determine redirect URL based on notification type
        let redirectUrl = '/myrequests';
        
        // If it contains a specific URL, redirect to that URL
        if (data.url) {
            redirectUrl = data.url;
        } else if (data.request_id) { // Fallback to old logic if no specific URL
            const userRole = document.body.dataset.userRole; 
            if (userRole === 'Student') {
                redirectUrl = `/student-requests/${data.request_table_id || data.request_id}`;
            } else if (userRole === 'Faculty & Staff') {
                redirectUrl = `/faculty-requests/${data.request_table_id || data.request_id}`;
            } else if (userRole === 'Admin') {
                // For admin, use request_table_id and request_type if available for a more specific URL
                if (data.request_table_id && data.request_type) {
                     redirectUrl = `/admin/service-requests/${data.request_table_id}?type=${data.request_type}`;
                } else {
                    redirectUrl = '/admin_dashboard/service-request'; // General fallback
                }
            } else if (userRole === 'UITC Staff') {
                 if (data.request_table_id && data.request_type) {
                    redirectUrl = `/uitc-staff/assigned-requests/details/${data.request_table_id}?type=${data.request_type}`; // Example specific URL
                 } else {
                    redirectUrl = '/uitc-staff/assigned-requests';
                 }
            }
        }
        
        window.location.href = redirectUrl;
    });
    
    return notificationEl;
}
    
    // Mark all notifications as read
    function markAllAsRead() {
        fetch('/uitc-staff/notifications/mark-all-as-read', { // Changed URL prefix
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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
            if (data.success) {
                fetchNotifications(); // Re-fetch to update list
                fetchNotificationCount(); // Re-fetch to update badge

                 // Optional - display success message
                if (data.count > 0) {
                    const countMessage = data.count === 1 ? '1 notification' : `${data.count} notifications`;
                    notificationList.innerHTML = `<div class="empty-notifications">${countMessage} marked as read</div>`;
                    setTimeout(fetchNotifications, 1500); // Refresh after 1.5s
                }
            }
        })
        .catch(error => {
            console.error('Marking all notifications as read failed:', error);
        });
    }
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
    });
});
