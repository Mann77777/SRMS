// User Notification polling and interaction (for students and faculty)
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
    
    // Create notification element - Student/Faculty specific version
    function createNotificationElement(notification, isUnread) {
        const notificationEl = document.createElement('div');
        notificationEl.className = `notification-item${isUnread ? ' unread' : ''}`;
        notificationEl.dataset.id = notification.id;
        
        const data = notification.data;
        const time = new Date(notification.created_at).toLocaleString();
        
        // Create notification content specifically for student/faculty view
        notificationEl.innerHTML = `
            <div class="notification-content">
                ${data.message}
            </div>
            <div class="notification-time">
                ${time}
            </div>
        `;
        
        // Click handler - different from admin - redirects to student/faculty my requests
        notificationEl.addEventListener('click', function() {
            if (isUnread) {
                markAsRead(notification.id);
            }
            
            // For students and faculty, always redirect to myrequests
            window.location.href = '/myrequests';
        });
        
        return notificationEl;
    }
    
    // Mark notification as read
    function markAsRead(notificationId) {
        fetch('/user/notifications/mark-as-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                notification_id: notificationId
            })
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
            console.error('Marking notification as read failed:', error);
        });
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
                
                // Show success message
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