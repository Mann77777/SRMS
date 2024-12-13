// Initialize BotMan Widget with custom configuration
var botmanWidget = {
    frameEndpoint: '/botman/chat',
    chatServer: '/botman',
    title: 'SRMS Assistant',
    introMessage: 'Hello! 👋 I\'m your SRMS Assistant. How can I help you today?',
    placeholderText: 'Send a message...',
    mainColor: '#C4203C',
    bubbleBackground: '#C4203C',
    aboutText: 'SRMS Chatbot Assistant',
    bubbleAvatarUrl: '/images/chat.png',
    desktopHeight: 400,
    desktopWidth: 350,
    mobileHeight: '80vh',
    headerTextColor: '#ffffff',
    backgroundColor: '#ffffff',  // Add plain white background
    
    // Custom widget display
    displayMessageTime: true,
    timestampFormat: 'HH:mm',
    
    // Animation settings
    widgetAnimation: true,
    messageAnimationDelay: 200,
    
    // Mobile settings
    mobileBreakpoint: 500,
    
    // Conversation settings
    userId: 'user_' + Math.random().toString(36).substr(2, 9),
    
    // Custom widget behavior
    alwaysUseFloatingButton: true,
    buttonIconUrl: '/images/chat-icon.png',
    
    // Widget positioning
    position: 'right',
    marginRight: 20,
    marginBottom: 20,
    
    // Additional customization
    showCloseButton: true,
    closeButtonIconUrl: '/images/close-icon.png',
};

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