/**
 * Public JavaScript for the Ezeze Intelligent Chatbot plugin.
 *
 * @since      1.0.1
 */

(function($) {
    'use strict';

    // Chatbot class
    class EzezeIntelligentChatbot {
        constructor() {
            this.chatbot = $('#wcic-chatbot');
            this.chatbotButton = $('#wcic-chatbot-button');
            this.chatbotMessages = $('.wcic-chatbot-messages');
            this.chatbotInput = $('.wcic-chatbot-input');
            this.chatbotSend = $('.wcic-chatbot-send');
            this.chatbotMinimize = $('.wcic-chatbot-minimize');
            this.chatbotClose = $('.wcic-chatbot-close');
            this.chatbotBody = $('.wcic-chatbot-body');
            this.sessionId = this.getCookie('ezeze_session_id') || this.generateSessionId();
            this.isOpen = false;
            this.conversationStarted = false;
            this.messageCount = 0;
            
            this.init();
        }
        
        init() {
            // Add SVG icon to the button
            this.chatbotButton.html(`
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
            `);
            
            // Add send icon to the button
            this.chatbotSend.html(`
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            `);
            
            // Set cookie with session ID
            this.setCookie('ezeze_session_id', this.sessionId, 30);
            
            // Event listeners
            this.chatbotButton.on('click', this.toggleChatbot.bind(this));
            this.chatbotMinimize.on('click', this.minimizeChatbot.bind(this));
            this.chatbotClose.on('click', this.closeChatbot.bind(this));
            this.chatbotSend.on('click', this.sendMessage.bind(this));
            this.chatbotInput.on('keypress', (e) => {
                if (e.which === 13) {
                    this.sendMessage();
                    e.preventDefault();
                }
            });
            
            // Focus input when clicking anywhere in the chatbot body
            this.chatbotBody.on('click', () => {
                this.chatbotInput.focus();
            });
            
            // Load saved messages from localStorage
            this.loadSavedMessages();
            
            // Scroll to bottom of messages
            this.scrollToBottom();
        }
        
        toggleChatbot() {
            if (this.isOpen) {
                this.minimizeChatbot();
            } else {
                this.openChatbot();
                
                // Show welcome message if conversation hasn't started
                if (!this.conversationStarted && this.messageCount === 0) {
                    setTimeout(() => {
                        const welcomeMessage = wcic_params.welcome_message || 'Hello! I\'m your personal shopping assistant. How can I help you today?';
                        this.addMessage(welcomeMessage, false);
                        this.conversationStarted = true;
                        this.saveMessages();
                    }, 500);
                }
            }
        }
        
        openChatbot() {
            this.chatbot.fadeIn(300);
            this.chatbotButton.fadeOut(300);
            this.isOpen = true;
            
            // Add entrance animation
            this.chatbot.css({
                'transform': 'translateY(20px)',
                'opacity': '0'
            });
            
            setTimeout(() => {
                this.chatbot.css({
                    'transform': 'translateY(0)',
                    'opacity': '1',
                    'transition': 'transform 0.3s ease, opacity 0.3s ease'
                });
            }, 10);
            
            this.scrollToBottom();
            this.chatbotInput.focus();
        }
        
        minimizeChatbot() {
            this.chatbot.css({
                'transform': 'translateY(20px)',
                'opacity': '0',
                'transition': 'transform 0.3s ease, opacity 0.3s ease'
            });
            
            setTimeout(() => {
                this.chatbot.fadeOut(100);
                this.chatbotButton.fadeIn(300);
                this.isOpen = false;
            }, 300);
        }
        
        closeChatbot() {
            this.minimizeChatbot();
        }
        
        sendMessage() {
            const message = this.chatbotInput.val().trim();
            
            if (!message) {
                return;
            }
            
            // Add user message to chat
            this.addMessage(message, true);
            
            // Clear input
            this.chatbotInput.val('');
            
            // Show typing indicator
            this.showTypingIndicator();
            
            // Send message to server
            $.ajax({
                url: wcic_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcic_send_message',
                    nonce: wcic_params.nonce,
                    message: message,
                    session_id: this.sessionId
                },
                success: (response) => {
                    // Hide typing indicator
                    this.hideTypingIndicator();
                    
                    if (response.success) {
                        // Add bot response to chat
                        this.addMessage(response.data.message, false, response.data.recommendations);
                        
                        // Update session ID if needed
                        if (response.data.session_id) {
                            this.sessionId = response.data.session_id;
                            this.setCookie('ezeze_session_id', this.sessionId, 30);
                        }
                    } else {
                        // Add error message
                        this.addMessage(wcic_params.i18n.error || 'Sorry, I encountered an error. Please try again later.', false);
                    }
                    
                    // Save messages to localStorage
                    this.saveMessages();
                },
                error: () => {
                    // Hide typing indicator
                    this.hideTypingIndicator();
                    
                    // Add error message
                    this.addMessage(wcic_params.i18n.error || 'Sorry, I encountered an error. Please try again later.', false);
                    
                    // Save messages to localStorage
                    this.saveMessages();
                }
            });
        }
        
        addMessage(message, isUser, recommendations = null) {
            this.messageCount++;
            const delay = this.messageCount * 0.05;
            
            let html = `
                <div class="wcic-message wcic-message-${isUser ? 'user' : 'bot'}" style="animation-delay: ${delay}s">
                    <div class="wcic-message-content">${message}</div>
            `;
            
            // Add recommendations if available
            if (!isUser && recommendations && recommendations.length > 0) {
                html += '<div class="wcic-recommendations">';
                
                recommendations.forEach((rec, index) => {
                    html += `
                        <a href="${rec.url}" class="wcic-recommendation-item" target="_blank" style="animation-delay: ${0.1 + (index * 0.1)}s; opacity: 0; transform: translateY(10px); animation: message-appear 0.3s ease-out forwards;">
                    `;
                    
                    if (rec.image) {
                        html += `<img src="${rec.image}" class="wcic-recommendation-image" alt="${rec.title}" />`;
                    }
                    
                    html += `
                        <div class="wcic-recommendation-details">
                            <div class="wcic-recommendation-title">${rec.title}</div>
                    `;
                    
                    if (rec.price) {
                        html += `<div class="wcic-recommendation-price">${rec.price}</div>`;
                    }
                    
                    html += `
                        </div>
                        <button class="wcic-recommendation-button">View</button>
                        </a>
                    `;
                });
                
                html += '</div>';
            }
            
            html += '</div>';
            
            this.chatbotMessages.append(html);
            this.scrollToBottom();
            this.conversationStarted = true;
        }
        
        showTypingIndicator() {
            const html = `
                <div class="wcic-message wcic-message-bot wcic-typing" style="opacity: 0; transform: translateY(10px); animation: message-appear 0.3s ease-out forwards;">
                    <div class="wcic-typing-indicator">
                        <span class="wcic-typing-dot"></span>
                        <span class="wcic-typing-dot"></span>
                        <span class="wcic-typing-dot"></span>
                    </div>
                </div>
            `;
            
            this.chatbotMessages.append(html);
            this.scrollToBottom();
        }
        
        hideTypingIndicator() {
            const $typing = $('.wcic-typing');
            $typing.css({
                'opacity': '1',
                'transform': 'translateY(0)'
            }).animate({
                'opacity': '0',
                'transform': 'translateY(10px)'
            }, 200, function() {
                $typing.remove();
            });
        }
        
        scrollToBottom() {
            this.chatbotBody.stop().animate({
                scrollTop: this.chatbotBody[0].scrollHeight
            }, 300);
        }
        
        saveMessages() {
            const messages = [];
            
            this.chatbotMessages.find('.wcic-message').each(function() {
                const $message = $(this);
                const isUser = $message.hasClass('wcic-message-user');
                const content = $message.find('.wcic-message-content').html();
                
                if (content) {
                    messages.push({
                        isUser: isUser,
                        content: content
                    });
                }
            });
            
            localStorage.setItem('ezeze_chatbot_messages', JSON.stringify(messages));
            localStorage.setItem('ezeze_chatbot_session', this.sessionId);
        }
        
        loadSavedMessages() {
            const savedMessages = localStorage.getItem('ezeze_chatbot_messages');
            const savedSession = localStorage.getItem('ezeze_chatbot_session');
            
            if (savedMessages && savedSession === this.sessionId) {
                try {
                    const messages = JSON.parse(savedMessages);
                    
                    if (messages.length > 0) {
                        this.conversationStarted = true;
                        
                        messages.forEach(message => {
                            this.addMessage(message.content, message.isUser);
                        });
                    }
                } catch (e) {
                    console.error('Error loading saved messages:', e);
                }
            }
        }
        
        generateSessionId() {
            return 'ezeze_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        }
        
        setCookie(name, value, days) {
            let expires = '';
            
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = '; expires=' + date.toUTCString();
            }
            
            document.cookie = name + '=' + value + expires + '; path=/';
        }
        
        getCookie(name) {
            const nameEQ = name + '=';
            const ca = document.cookie.split(';');
            
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            
            return null;
        }
    }

    $(document).ready(function() {
        // Initialize chatbot
        const chatbot = new EzezeIntelligentChatbot();
    });

})(jQuery);