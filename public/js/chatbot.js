// Initialize BotMan Widget with custom configuration
var botmanWidget = {
    frameEndpoint: '/botman/chat',
    chatServer: '/botman',
    title: 'SRMS Chatbot',
    introMessage: 'Hello! 👋 I\'m your SRMS Chatbot. How can I help you today?',
    placeholderText: 'Send a message...',
    mainColor: '#C4203C',
    bubbleBackground: '#C4203C',
    aboutText: 'SRMS Chatbot Assistant',
    bubbleAvatarUrl: '/images/chat.png',
    desktopHeight: 400,
    desktopWidth: 350,
    mobileHeight: '80vh',
    localStorage: true,
    headerTextColor: '#ffffff',
    backgroundColor: '#ffffff', // Set plain white background
    displayMessageTime: true,
    timestampFormat: 'HH:mm',
    widgetAnimation: true,
    messageAnimationDelay: 200,
    mobileBreakpoint: 500,
    userId: 'user_' + Math.random().toString(36).substr(2, 9),
    alwaysUseFloatingButton: true,
    buttonIconUrl: '/public/images/chat-icon.png',
    position: 'right',
    marginRight: 20,
    marginBottom: 20,
    showCloseButton: true,
    closeButtonIconUrl: '/images/close-icon.png',
};

// Inject additional styles to ensure plain white background
function injectStyles() {
    const widgetIframe = document.querySelector('#botmanWidgetRoot iframe');
    if (widgetIframe && widgetIframe.contentDocument) {
        try {
            const customStyle = document.createElement('style');
            customStyle.textContent = `
                body, 
                .botman-container, 
                .botman-messages, 
                .botman-widget-container {
                    background-color: #ffffff !important; /* Plain white background */
                }
            `;
            widgetIframe.contentDocument.head.appendChild(customStyle);
        } catch (e) {
            console.error('Failed to inject styles:', e);
            setTimeout(injectStyles, 500);
        }
    } else {
        setTimeout(injectStyles, 500);
    }
}

// Call injectStyles after the widget is initialized
window.addEventListener('load', () => {
    setTimeout(injectStyles, 1000);
});

function injectStyles() {
    const widgetIframe = document.querySelector('#botmanWidgetRoot iframe');
    if (widgetIframe && widgetIframe.contentDocument) {
        try {
            const customStyle = document.createElement('style');
            customStyle.textContent = `
                body, 
                .botman-container, 
                .botman-messages, 
                .botman-widget-container {
                    background-color: #ffffff !important;
                }
            `;
            widgetIframe.contentDocument.head.appendChild(customStyle);

            // Add your CSS file for additional customizations
            const customStyleLink = document.createElement('link');
            customStyleLink.rel = 'stylesheet';
            customStyleLink.type = 'text/css';
            customStyleLink.href = '/css/chatbot.css';
            widgetIframe.contentDocument.head.appendChild(customStyleLink);
        } catch (e) {
            console.error('Failed to inject styles:', e);
            setTimeout(injectStyles, 500);
        }
    } else {
        setTimeout(injectStyles, 500);
    }
}


// Add custom event listeners
document.addEventListener('DOMContentLoaded', function() {
    function injectStyles() {
        const widgetIframe = document.querySelector('#botmanWidgetRoot iframe');
        if (widgetIframe && widgetIframe.contentDocument) {
            try {
                // Add direct style injection
                const customStyle = document.createElement('style');
                customStyle.textContent = `
                    body, 
                    .botman-container, 
                    .botman-messages, 
                    .botman-widget-container {
                        background: white !important;
                        background-image: none !important;
                        background-color: white !important;
                    }
                `;
                widgetIframe.contentDocument.head.appendChild(customStyle);
                
                // Add our custom CSS file
                const customStyleLink = document.createElement('link');
                customStyleLink.rel = 'stylesheet';
                customStyleLink.type = 'text/css';
                customStyleLink.href = '/css/chatbot.css';
                widgetIframe.contentDocument.head.appendChild(customStyleLink);
                
                // Add Font Awesome for icons
                const fontAwesome = document.createElement('link');
                fontAwesome.rel = 'stylesheet';
                fontAwesome.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';
                widgetIframe.contentDocument.head.appendChild(fontAwesome);
            } catch (e) {
                console.log('Failed to inject styles:', e);
                // Try again after a short delay
                setTimeout(injectStyles, 500);
            }
        } else {
            // If iframe not found or content not accessible, try again
            setTimeout(injectStyles, 500);
        }
    }

    // Start trying to inject styles
    setTimeout(injectStyles, 1000);
});

// Custom functions to handle widget state
function openWidget() {
    if (window.botmanChatWidget) {
        window.botmanChatWidget.open();
    }
}

function closeWidget() {
    if (window.botmanChatWidget) {
        window.botmanChatWidget.close();
    }
}

// Handle mobile responsiveness
window.addEventListener('resize', function() {
    const widget = document.querySelector('#botmanWidgetRoot');
    if (widget) {
        if (window.innerWidth <= botmanWidget.mobileBreakpoint) {
            widget.style.width = '100%';
            widget.style.height = botmanWidget.mobileHeight;
        } else {
            widget.style.width = botmanWidget.desktopWidth + 'px';
            widget.style.height = botmanWidget.desktopHeight + 'px';
        }
    }
});


const ChatStorageManager = {
    // Key for storing chat history
    STORAGE_KEY: 'botman_chat_history',
    
    // Save a message to persistent storage
    saveMessage: function(message, sender) {
        try {
            // Retrieve existing chat history
            let chatHistory = this.getChatHistory();
            
            // Create message object
            const chatMessage = {
                id: Date.now(), // Unique identifier
                message: message,
                sender: sender,
                timestamp: new Date().toISOString()
            };
            
            // Add new message
            chatHistory.push(chatMessage);
            
            // Limit to last 100 messages
            chatHistory = chatHistory.slice(-100);
            
            // Save to localStorage
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(chatHistory));
            
            // Attempt to save to server if authenticated
            this.saveToServer(message, sender);
            
            return chatMessage;
        } catch (error) {
            console.error('Error saving chat message:', error);
            return null;
        }
    },
    
    // Retrieve chat history
    getChatHistory: function() {
        try {
            const storedHistory = localStorage.getItem(this.STORAGE_KEY);
            return storedHistory ? JSON.parse(storedHistory) : [];
        } catch (error) {
            console.error('Error retrieving chat history:', error);
            return [];
        }
    },
    
    // Save message to server
    saveToServer: function(message, sender) {
        // Only save if user is authenticated
        const isAuthenticated = localStorage.getItem('isAuthenticated') === 'true';
        
        if (isAuthenticated) {
            fetch('/botman/save-message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    message: message,
                    sender: sender
                })
            }).catch(error => console.error('Failed to save chat message to server:', error));
        }
    },
    
    // Clear chat history
    clearHistory: function() {
        localStorage.removeItem(this.STORAGE_KEY);
    }
};


// Safe method to clear BotMan widget messages
function safeClearBotManMessages() {
    try {
        // Check if widget exists and has clearMessages method
        if (window.botmanChatWidget) {
            if (typeof window.botmanChatWidget.clearMessages === 'function') {
                window.botmanChatWidget.clearMessages();
            } else if (window.botmanChatWidget.container) {
                // Fallback: manually clear messages
                const messagesContainer = window.botmanChatWidget.container.querySelector('.messages');
                if (messagesContainer) {
                    messagesContainer.innerHTML = '';
                }
            } else {
                console.warn('Unable to clear BotMan widget messages');
            }
        } else {
            console.warn('BotMan widget not initialized');
        }
    } catch (error) {
        console.error('Error clearing BotMan messages:', error);
    }
}

// Safe method to add messages to BotMan widget
function safeAddBotManMessage(message, sender) {
    try {
        if (window.botmanChatWidget) {
            if (typeof window.botmanChatWidget.addMessage === 'function') {
                window.botmanChatWidget.addMessage({
                    message: message,
                    type: 'messages',
                    from: sender === 'user' ? 'user' : 'botman'
                });
            } else {
                console.warn('BotMan widget addMessage method not available');
                
                // Fallback: manually add message if container exists
                if (window.botmanChatWidget.container) {
                    const messagesContainer = window.botmanChatWidget.container.querySelector('.messages');
                    if (messagesContainer) {
                        const messageElement = document.createElement('div');
                        messageElement.classList.add('message', sender === 'user' ? 'from-user' : 'from-botman');
                        messageElement.textContent = message;
                        messagesContainer.appendChild(messageElement);
                    }
                }
            }
        } else {
            console.warn('BotMan widget not initialized');
        }
    } catch (error) {
        console.error('Error adding BotMan message:', error);
    }
}

// Save chat message to server
function saveChatMessage(message, sender) {
    fetch('/botman/save-message', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            message: message,
            sender: sender
        })
    }).catch(error => console.error('Failed to save chat message:', error));
}

// Function to save chat messages to storage
function saveChatToStorage(message, sender) {
    // Save to sessionStorage
    const chatHistory = JSON.parse(sessionStorage.getItem('persistentChatHistory') || '[]');
    chatHistory.push({ 
        message, 
        sender, 
        timestamp: new Date().toISOString() 
    });

    // Limit to last 100 messages
    sessionStorage.setItem('persistentChatHistory', JSON.stringify(chatHistory.slice(-100)));

    // Save to server if authenticated
    if (localStorage.getItem('isAuthenticated') === 'true') {
        saveChatMessage(message, sender);
    }
}
// Load chat history function
function loadChatHistory() {
    console.log('Loading Chat History');
    
    // Retrieve chat history from local storage
    const chatHistory = ChatStorageManager.getChatHistory();
    
    // Safely clear messages
    safeClearBotManMessages();
    
    // Display stored chat history
    chatHistory.forEach(chat => {
        displayMessage(chat.message, chat.sender);
    });
    
    // If authenticated, try to fetch server history
    const isAuthenticated = localStorage.getItem('isAuthenticated') === 'true';
    console.log('Authentication Status:', isAuthenticated);
    
    if (isAuthenticated) {
        fetch('/botman/chat-history')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(serverHistory => {
                console.log('Server History Retrieved:', serverHistory);
                
                // Merge and deduplicate
                const mergedHistory = [...chatHistory, ...serverHistory]
                    .filter((chat, index, self) => 
                        index === self.findIndex(t => 
                            t.message === chat.message && 
                            t.sender === chat.sender
                        )
                    )
                    .sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp))
                    .slice(-100);
                
                // Update local storage
                localStorage.setItem(ChatStorageManager.STORAGE_KEY, JSON.stringify(mergedHistory));
                
                // Safely clear and redisplay
                safeClearBotManMessages();
                
                mergedHistory.forEach(chat => {
                    displayMessage(chat.message, chat.sender);
                });
            })
            .catch(error => {
                console.error('Error fetching server chat history:', error);
            });
    }
}

// Consolidated initialization function
function initializeChatHistory() {
    console.log('Initializing Chat History');
    
    // Ensure BotMan widget is initialized
    const initInterval = setInterval(() => {
        if (window.botmanChatWidget) {
            clearInterval(initInterval);
            console.log('BotMan Widget Initialized');
            
            // Load chat history with a slight delay
            setTimeout(loadChatHistory, 2000);
        }
    }, 500);
}

// Initialize on DOM content loaded
document.addEventListener('DOMContentLoaded', initializeChatHistory);