<?php
/**
 * OUTSINC Platform - Messenger Index
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$pageTitle = 'Messenger';

include __DIR__ . '/../../includes/header.php';
?>

<div class="messenger-app">
    <div class="messenger-sidebar">
        <div class="messenger-sidebar-header">
            <h2>💬 Messages</h2>
            <button class="btn btn-sm btn-primary" onclick="showNewConversation()">+ New</button>
        </div>
        
        <div class="messenger-search">
            <input 
                type="text" 
                class="form-control" 
                id="conversation-search" 
                placeholder="Search conversations..."
                onkeyup="filterConversations()"
            >
        </div>
        
        <div class="conversations-list" id="conversations-list">
            <div class="loading">Loading conversations...</div>
        </div>
    </div>
    
    <div class="messenger-main">
        <div class="no-conversation-selected">
            <div class="empty-state">
                <span class="empty-icon">💬</span>
                <h3>Select a conversation</h3>
                <p>Choose a conversation from the sidebar or start a new one</p>
            </div>
        </div>
        
        <div class="conversation-view" id="conversation-view" style="display: none;">
            <div class="conversation-header">
                <div class="conversation-info">
                    <div class="conversation-avatar" id="conversation-avatar">A</div>
                    <div class="conversation-details">
                        <h3 id="conversation-name">Contact Name</h3>
                        <span class="conversation-status" id="conversation-status">Online</span>
                    </div>
                </div>
                <div class="conversation-actions">
                    <button class="btn-icon" title="Call">📞</button>
                    <button class="btn-icon" title="Video">📹</button>
                    <button class="btn-icon" title="Info">ℹ️</button>
                </div>
            </div>
            
            <div class="messages-container" id="messages-container">
                <!-- Messages will be loaded here -->
            </div>
            
            <div class="message-input-container">
                <form id="message-form" onsubmit="sendMessage(event)">
                    <input type="hidden" id="recipient-id" value="">
                    <button type="button" class="btn-icon" onclick="attachFile()">📎</button>
                    <input 
                        type="text" 
                        class="message-input" 
                        id="message-input" 
                        placeholder="Type a message..."
                        autocomplete="off"
                    >
                    <button type="button" class="btn-icon" onclick="insertEmoji()">😊</button>
                    <button type="submit" class="btn btn-primary">Send</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.messenger-app {
    display: flex;
    height: calc(100vh - 150px);
    background-color: var(--surface-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow);
}

.messenger-sidebar {
    width: 350px;
    border-right: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    background-color: var(--bg-color);
}

.messenger-sidebar-header {
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: var(--surface-color);
}

.messenger-sidebar-header h2 {
    margin: 0;
    font-size: var(--font-size-h3);
}

.messenger-search {
    padding: var(--spacing-md);
    border-bottom: 1px solid var(--border-color);
}

.conversations-list {
    flex: 1;
    overflow-y: auto;
}

.conversation-item {
    display: flex;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    transition: background-color var(--transition-fast);
}

.conversation-item:hover {
    background-color: var(--surface-color);
}

.conversation-item.active {
    background-color: var(--primary-color);
    color: white;
}

.conversation-item.unread {
    background-color: rgba(74, 144, 226, 0.1);
    font-weight: 600;
}

.conv-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
}

.conv-info {
    flex: 1;
    min-width: 0;
}

.conv-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-xs);
}

.conv-header strong {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.conv-header small {
    font-size: 12px;
    color: var(--text-secondary);
}

.conv-preview {
    font-size: var(--font-size-small);
    color: var(--text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin: 0;
}

.unread-count {
    background-color: var(--danger-color);
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
}

.messenger-main {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.no-conversation-selected {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-state {
    text-align: center;
    color: var(--text-secondary);
}

.empty-icon {
    font-size: 64px;
    display: block;
    margin-bottom: var(--spacing-md);
}

.conversation-view {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.conversation-header {
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: var(--surface-color);
}

.conversation-info {
    display: flex;
    gap: var(--spacing-md);
    align-items: center;
}

.conversation-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 20px;
}

.conversation-details h3 {
    margin: 0;
    font-size: var(--font-size-h4);
}

.conversation-status {
    font-size: var(--font-size-small);
    color: var(--success-color);
}

.conversation-actions {
    display: flex;
    gap: var(--spacing-sm);
}

.btn-icon {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    padding: var(--spacing-sm);
    border-radius: var(--radius-md);
    transition: background-color var(--transition-fast);
}

.btn-icon:hover {
    background-color: var(--bg-color);
}

.messages-container {
    flex: 1;
    overflow-y: auto;
    padding: var(--spacing-lg);
    background-color: var(--bg-color);
}

.message {
    display: flex;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
    max-width: 70%;
}

.message.sent {
    margin-left: auto;
    flex-direction: row-reverse;
}

.message.received {
    margin-right: auto;
}

.message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.message-content {
    display: flex;
    flex-direction: column;
}

.message.sent .message-content {
    align-items: flex-end;
}

.message-sender {
    font-size: var(--font-size-small);
    font-weight: 600;
    margin-bottom: var(--spacing-xs);
    color: var(--text-secondary);
}

.message-text {
    background-color: var(--surface-color);
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--radius-md);
    word-wrap: break-word;
}

.message.sent .message-text {
    background-color: var(--primary-color);
    color: white;
}

.message-time {
    font-size: 11px;
    color: var(--text-secondary);
    margin-top: var(--spacing-xs);
}

.message-attachment {
    margin-top: var(--spacing-xs);
}

.message-attachment a {
    color: var(--primary-color);
    text-decoration: none;
}

.message-input-container {
    padding: var(--spacing-lg);
    border-top: 1px solid var(--border-color);
    background-color: var(--surface-color);
}

#message-form {
    display: flex;
    gap: var(--spacing-sm);
    align-items: center;
}

.message-input {
    flex: 1;
    padding: var(--spacing-sm) var(--spacing-md);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-full);
    font-size: var(--font-size-base);
}

.message-input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.loading {
    padding: var(--spacing-lg);
    text-align: center;
    color: var(--text-secondary);
}

@media (max-width: 768px) {
    .messenger-sidebar {
        width: 100%;
        position: absolute;
        z-index: 10;
        height: 100%;
    }
    
    .messenger-sidebar.hidden {
        display: none;
    }
}
</style>

<script>
let currentConversationId = null;

document.addEventListener('DOMContentLoaded', function() {
    loadConversationsList();
});

function loadConversationsList() {
    fetch('/api/conversations.php')
        .then(response => response.json())
        .then(data => {
            renderConversationsList(data.conversations || []);
        })
        .catch(error => {
            console.error('Error loading conversations:', error);
            document.getElementById('conversations-list').innerHTML = 
                '<p class="loading">Failed to load conversations</p>';
        });
}

function renderConversationsList(conversations) {
    const container = document.getElementById('conversations-list');
    
    if (conversations.length === 0) {
        container.innerHTML = '<p class="loading">No conversations yet</p>';
        return;
    }
    
    container.innerHTML = conversations.map(conv => `
        <div class="conversation-item ${conv.unread_count > 0 ? 'unread' : ''}" 
             onclick="openConversation(${conv.id}, '${conv.name}', '${conv.initials}')">
            <div class="conv-avatar">${conv.initials}</div>
            <div class="conv-info">
                <div class="conv-header">
                    <strong>${escapeHtml(conv.name)}</strong>
                    <small>${formatTime(conv.last_message_time)}</small>
                </div>
                <p class="conv-preview">${escapeHtml(conv.last_message || 'No messages yet')}</p>
            </div>
            ${conv.unread_count > 0 ? `<span class="unread-count">${conv.unread_count}</span>` : ''}
        </div>
    `).join('');
}

function openConversation(userId, name, initials) {
    currentConversationId = userId;
    
    // Update UI
    document.querySelector('.no-conversation-selected').style.display = 'none';
    document.getElementById('conversation-view').style.display = 'flex';
    document.getElementById('conversation-name').textContent = name;
    document.getElementById('conversation-avatar').textContent = initials;
    document.getElementById('recipient-id').value = userId;
    
    // Mark conversation as active
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
    
    // Load messages
    loadMessages(userId);
}

function loadMessages(userId) {
    // This would call an API to load messages
    document.getElementById('messages-container').innerHTML = 
        '<div class="loading">Loading messages...</div>';
    
    // Placeholder for demonstration
    setTimeout(() => {
        document.getElementById('messages-container').innerHTML = `
            <div class="message received">
                <div class="message-avatar">JD</div>
                <div class="message-content">
                    <div class="message-sender">John Doe</div>
                    <div class="message-text">Hey, how are you doing today?</div>
                    <div class="message-time">2 hours ago</div>
                </div>
            </div>
            <div class="message sent">
                <div class="message-content">
                    <div class="message-text">I'm doing well, thanks for asking!</div>
                    <div class="message-time">1 hour ago</div>
                </div>
            </div>
        `;
    }, 500);
}

function sendMessage(event) {
    event.preventDefault();
    
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    const recipientId = document.getElementById('recipient-id').value;
    
    if (!message || !recipientId) return;
    
    // Send via API
    fetch('/api/send-message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            recipient_id: recipientId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            // Append message to container
            appendSentMessage(message);
            playSound('success');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        showAlert('Failed to send message', 'error');
    });
}

function appendSentMessage(text) {
    const container = document.getElementById('messages-container');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message sent';
    messageDiv.innerHTML = `
        <div class="message-content">
            <div class="message-text">${escapeHtml(text)}</div>
            <div class="message-time">just now</div>
        </div>
    `;
    container.appendChild(messageDiv);
    container.scrollTop = container.scrollHeight;
}

function filterConversations() {
    const search = document.getElementById('conversation-search').value.toLowerCase();
    const conversations = document.querySelectorAll('.conversation-item');
    
    conversations.forEach(conv => {
        const name = conv.querySelector('strong').textContent.toLowerCase();
        if (name.includes(search)) {
            conv.style.display = 'flex';
        } else {
            conv.style.display = 'none';
        }
    });
}

function showNewConversation() {
    alert('New conversation feature - would show user selector');
}

function attachFile() {
    alert('File attachment feature');
}

function insertEmoji() {
    const input = document.getElementById('message-input');
    input.value += '😊';
    input.focus();
}

function formatTime(timestamp) {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours}h ago`;
    
    return date.toLocaleDateString();
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
