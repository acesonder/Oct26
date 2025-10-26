/**
 * OUTSINC Platform - Messenger Module
 * Real-time messaging functionality
 */

class Messenger {
    constructor() {
        this.currentConversationId = null;
        this.updateInterval = null;
        this.lastMessageId = 0;
    }
    
    init() {
        this.loadConversations();
        this.startAutoUpdate();
    }
    
    loadConversations() {
        fetch('/api/conversations.php')
            .then(response => response.json())
            .then(data => {
                this.renderConversations(data.conversations || []);
            })
            .catch(error => {
                console.error('Error loading conversations:', error);
            });
    }
    
    renderConversations(conversations) {
        const container = document.getElementById('messenger-conversations');
        if (!container) return;
        
        if (conversations.length === 0) {
            container.innerHTML = '<p class="text-center text-muted">No conversations yet</p>';
            return;
        }
        
        container.innerHTML = conversations.map(conv => `
            <div class="conversation-item ${conv.unread_count > 0 ? 'unread' : ''}" 
                 onclick="messenger.openConversation(${conv.id})">
                <div class="conv-avatar">${this.escapeHtml(conv.initials)}</div>
                <div class="conv-info">
                    <div class="conv-header">
                        <strong>${this.escapeHtml(conv.name)}</strong>
                        <small>${this.formatTime(conv.last_message_time)}</small>
                    </div>
                    <p class="conv-preview">${this.escapeHtml(conv.last_message || 'No messages yet')}</p>
                </div>
                ${conv.unread_count > 0 ? `<span class="unread-count">${conv.unread_count}</span>` : ''}
            </div>
        `).join('');
    }
    
    openConversation(id) {
        this.currentConversationId = id;
        window.location.href = `/modules/messenger/conversation.php?id=${id}`;
    }
    
    sendMessage(recipientId, message, attachment = null) {
        const formData = new FormData();
        formData.append('recipient_id', recipientId);
        formData.append('message', message);
        
        if (attachment) {
            formData.append('attachment', attachment);
        }
        
        return fetch('/api/send-message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                playSound('success');
                return data;
            } else {
                throw new Error(data.error || 'Failed to send message');
            }
        });
    }
    
    loadMessages(conversationId, limit = 50) {
        return fetch(`/api/messages.php?conversation_id=${conversationId}&limit=${limit}`)
            .then(response => response.json())
            .then(data => {
                if (data.messages) {
                    this.renderMessages(data.messages);
                    this.lastMessageId = data.messages.length > 0 
                        ? Math.max(...data.messages.map(m => m.id)) 
                        : 0;
                }
                return data;
            });
    }
    
    renderMessages(messages) {
        const container = document.getElementById('messages-container');
        if (!container) return;
        
        container.innerHTML = messages.map(msg => {
            const isSent = msg.is_sent_by_me;
            return `
                <div class="message ${isSent ? 'sent' : 'received'}">
                    ${!isSent ? `<div class="message-avatar">${this.escapeHtml(msg.sender_initials)}</div>` : ''}
                    <div class="message-content">
                        ${!isSent ? `<div class="message-sender">${this.escapeHtml(msg.sender_name)}</div>` : ''}
                        <div class="message-text">${this.escapeHtml(msg.message_text)}</div>
                        ${msg.attachment_url ? `
                            <div class="message-attachment">
                                <a href="${this.escapeHtml(msg.attachment_url)}" target="_blank">
                                    📎 Attachment
                                </a>
                            </div>
                        ` : ''}
                        <div class="message-time">${this.formatTime(msg.created_at)}</div>
                    </div>
                </div>
            `;
        }).join('');
        
        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }
    
    checkNewMessages() {
        if (!this.currentConversationId) return;
        
        fetch(`/api/new-messages.php?conversation_id=${this.currentConversationId}&after=${this.lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    this.appendNewMessages(data.messages);
                    playSound('notification');
                }
            })
            .catch(error => {
                console.error('Error checking new messages:', error);
            });
    }
    
    appendNewMessages(messages) {
        const container = document.getElementById('messages-container');
        if (!container) return;
        
        messages.forEach(msg => {
            const isSent = msg.is_sent_by_me;
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
            messageDiv.innerHTML = `
                ${!isSent ? `<div class="message-avatar">${this.escapeHtml(msg.sender_initials)}</div>` : ''}
                <div class="message-content">
                    ${!isSent ? `<div class="message-sender">${this.escapeHtml(msg.sender_name)}</div>` : ''}
                    <div class="message-text">${this.escapeHtml(msg.message_text)}</div>
                    ${msg.attachment_url ? `
                        <div class="message-attachment">
                            <a href="${this.escapeHtml(msg.attachment_url)}" target="_blank">
                                📎 Attachment
                            </a>
                        </div>
                    ` : ''}
                    <div class="message-time">${this.formatTime(msg.created_at)}</div>
                </div>
            `;
            container.appendChild(messageDiv);
            
            this.lastMessageId = Math.max(this.lastMessageId, msg.id);
        });
        
        container.scrollTop = container.scrollHeight;
    }
    
    markAsRead(conversationId) {
        fetch('/api/mark-read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ conversation_id: conversationId })
        })
        .catch(error => {
            console.error('Error marking as read:', error);
        });
    }
    
    startAutoUpdate() {
        // Check for new messages every 5 seconds
        this.updateInterval = setInterval(() => {
            this.checkNewMessages();
        }, 5000);
    }
    
    stopAutoUpdate() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }
    
    escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }
    
    formatTime(timestamp) {
        if (!timestamp) return '';
        
        const date = new Date(timestamp);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        
        if (diffMins < 1) return 'just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        
        const diffHours = Math.floor(diffMins / 60);
        if (diffHours < 24) return `${diffHours}h ago`;
        
        const diffDays = Math.floor(diffHours / 24);
        if (diffDays < 7) return `${diffDays}d ago`;
        
        return date.toLocaleDateString();
    }
}

// Initialize messenger
const messenger = new Messenger();

// Auto-initialize if on messenger page
if (document.getElementById('messenger-conversations')) {
    messenger.init();
}

// Export for global use
window.messenger = messenger;
