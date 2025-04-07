/**
 * Public JavaScript for the WC Intelligent Chatbot plugin.
 *
 * @since      1.0.0
 */

(function($) {
    'use strict';

    // Chatbot class
    class WCIntelligentChatbot {
        constructor() {
            this.chatbot = $('#wcic-chatbot');
            this.chatbotButton = $('#wcic-chatbot-button');
            this.chatbotMessages = $('.wcic-chatbot-messages');
            this.chatbotInput = $('.wcic-chatbot-input');
            this.chatbotSend = $('.wcic-chatbot-send');
            this.chatbotMinimize = $('.wcic-chatbot-minimize');
            this.chatbotClose = $('.wcic-chatbot-close');
            this.sessionId = this.getCookie('wcic_session_id') || this.generateSessionId();
            this.isOpen = false;
            
            this.init();
        }
        
        init() {
            // Set cookie with session ID
            this.setCookie('wcic_session_id', this.sessionId, 30);
            
            // Event listeners
            this.chatbotButton.on('click', this.toggleChatbot.bind(this));
            this.chatbotMinimize.on('click', this.minimizeChatbot.bind(this));
            this.chatbotClose.on('click', this.closeChatbot.bind(this));
            this.chatbotSend.on('click', this.sendMessage.bind(this));
            this.chatbotInput.on('keypress', (e) => {
                if (e.which === 13) {
                    this.sendMessage();
                }
            });
            
            // Scroll to bottom of messages
            this.scrollToBottom();
        }
        
        toggleChatbot() {
            if (this.isOpen) {
                this.minimizeChatbot();
            } else {
                this.openChatbot();
            }
        }
        
        openChatbot() {
            this.chatbot.show();
            this.chatbotButton.hide();
            this.isOpen = true;
            this.scrollToBottom();
            this.chatbotInput.focus();
        }
        
        minimizeChatbot() {
            this.chatbot.hide();
            this.chatbotButton.show();
            this.isOpen = false;
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
                            this.setCookie('wcic_session_id', this.sessionId, 30);
                        }
                    } else {
                        // Add error message
                        this.addMessage(wcic_params.i18n.error, false);
                    }
                },
                error: () => {
                    // Hide typing indicator
                    this.hideTypingIndicator();
                    
                    // Add error message
                    this.addMessage(wcic_params.i18n.error, false);
                }
            });
        }
        
        addMessage(message, isUser, recommendations = null) {
            let html = `
                <div class="wcic-message wcic-message-${isUser ? 'user' : 'bot'}">
                    <div class="wcic-message-content">${message}</div>
            `;
            
            // Add recommendations if available
            if (!isUser && recommendations && recommendations.length > 0) {
                html += '<div class="wcic-recommendations">';
                
                recommendations.forEach((rec) => {
                    html += `
                        <a href="${rec.url}" class="wcic-recommendation-item" target="_blank">
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
                        </a>
                    `;
                });
                
                html += '</div>';
            }
            
            html += '</div>';
            
            this.chatbotMessages.append(html);
            this.scrollToBottom();
        }
        
        showTypingIndicator() {
            const html = `
                <div class="wcic-message wcic-message-bot wcic-typing">
                    <div class="wcic-typing-indicator" style="background-color: ${$('.wcic-message-bot .wcic-message-content').css('background-color')}">
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
            $('.wcic-typing').remove();
        }
        
        scrollToBottom() {
            this.chatbotMessages.scrollTop(this.chatbotMessages[0].scrollHeight);
        }
        
        generateSessionId() {
            return 'wcic_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
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
        const chatbot = new WCIntelligentChatbot();
    });

})(jQuery);