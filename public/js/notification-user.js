// notification-user.js - For Students and Faculty

// Notification polling and interaction
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const notificationButton = document.getElementById('notificationButton');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notification-badge');
    const notificationList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    
    // Check if notification elements exist (avoid errors on pages without notifications)
    if (!notificationButton || !notificationDropdown || !notificationBadge || !notificationList) {
        console.log('Notification elements not found on this page');
        return;
    }
    
    // State
    let notificationsVisible = false;
    let pollingInterval = null;
    
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
        fetch('/user/notifications/get', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
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
            updateNotificationBadge(data.count);
        })
        .catch(error => {
            console.error('Fetching notification count failed:', error);
        });
    }
    
    // Fetch full notifications
    function fetchNotifications() {
        notificationList.innerHTML = '<div class="loading-spinner">Loading...</div>';
        
        fetch('/user/notifications/get', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
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
            updateNotificationBadge(data.count);
            renderNotifications(data.unread, data.read);
        })
        .catch(error => {
            console.error('Fetching notifications failed:', error);
            notificationList.innerHTML = '<div class="empty-notifications">Failed to load notifications</div>';
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

    // Add level-specific class for styling (e.g., danger for rejected)
    if (data.level) {
        notificationEl.classList.add(`notification-${data.level}`); // Adds 'notification-danger', 'notification-success', etc.
    }
    
    // Enhanced display for notifications
    let content = data.message;
    let detailsHtml = '';
    let notificationIcon = '';
    
    // Determine notification type and create appropriate display
    if (data.staff_name && data.actions_taken) {
        // This is a completion notification
        notificationIcon = '<i class="fas fa-check-circle text-success mr-2"></i>';
        
        // Create a more detailed view for completion notifications
        detailsHtml = `
            <div class="notification-details completion">
                <div class="detail-item"><strong>Service:</strong> ${data.service_category || 'N/A'}</div>
                <div class="detail-item"><strong>Completed by:</strong> ${data.staff_name || 'N/A'}</div>
                ${data.transaction_type ? `<div class="detail-item"><strong>Transaction Type:</strong> ${data.transaction_type}</div>` : ''}
                ${data.actions_taken ? `<div class="detail-item"><strong>Actions Taken:</strong> ${data.actions_taken}</div>` : ''}
            </div>
        `;
    } else if (data.staff_name && data.service_category) {
        // This is an assignment notification
        notificationIcon = '<i class="fas fa-user-check text-primary mr-2"></i>';
        
        // Create a detailed view for assignment notifications
        detailsHtml = `
            <div class="notification-details assignment">
                <div class="detail-item"><strong>Service:</strong> ${data.service_category || 'N/A'}</div>
                <div class="detail-item"><strong>Assigned to:</strong> ${data.staff_name || 'N/A'}</div>
                ${data.transaction_type ? `<div class="detail-item"><strong>Transaction Type:</strong> ${data.transaction_type}</div>` : ''}
                ${data.notes ? `<div class="detail-item"><strong>Notes:</strong> ${data.notes}</div>` : ''}
            </div>
        `;
    }
    
    notificationEl.innerHTML = `
        <div class="notification-content">
            ${notificationIcon}
            ${content}
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
        
        // Determine redirect URL - always go to myrequests
        let redirectUrl = '/myrequests'; 
        
        // Redirect to the myrequests page
        window.location.href = redirectUrl;
    });
    
    return notificationEl;
}
    
    // Mark all notifications as read
    function markAllAsRead() {
        fetch('/user/notifications/mark-all-as-read', {
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
                fetchNotifications();
                fetchNotificationCount();
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
