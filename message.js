// message.js - Messaging System

class MessageSystem {
    constructor(receiverID, csrfToken) {
        this.chatMessages = document.getElementById('chatMessages');
        this.messageForm = document.getElementById('messageForm');
        this.messageInput = document.getElementById('messageInput');
        this.typingIndicator = document.getElementById('typingIndicator');
        this.receiverID = receiverID;
        this.csrfToken = csrfToken;
        this.lastMessageID = 0;
        this.renderedMessageIDs = new Set();
        this.pollingInterval = null;
        this.lastSendTime = 0;
        this.sendCooldown = 1000; // 1 second between messages

        this.init();
    }

    init() {
        this.scrollToBottom();
        this.setupEventListeners();
        this.initializeRenderedMessages();
        this.startPolling();
    }

    initializeRenderedMessages() {
        // Track already-rendered messages to prevent duplicates
        const messages = this.chatMessages.querySelectorAll('[data-message-id]');
        messages.forEach(msg => {
            const id = parseInt(msg.getAttribute('data-message-id'), 10);
            this.renderedMessageIDs.add(id);
            this.lastMessageID = Math.max(this.lastMessageID, id);
        });
    }

    setupEventListeners() {
        this.messageForm.addEventListener('submit', (e) => this.handleSubmit(e));

        this.messageInput.addEventListener('input', () => this.autoResize());

        this.messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.handleSubmit(e);
            }
        });
    }

    handleSubmit(e) {
        e.preventDefault();

        const messageText = this.messageInput.value.trim();
        
        // Validation
        if (!messageText) {
            alert('Message cannot be empty.');
            return;
        }

        if (messageText.length > 5000) {
            alert('Message is too long (max 5000 characters).');
            return;
        }

        // Rate limiting
        const now = Date.now();
        if (now - this.lastSendTime < this.sendCooldown) {
            alert('Please wait before sending another message.');
            return;
        }
        this.lastSendTime = now;

        // Disable button during send
        const sendBtn = this.messageForm.querySelector('.send-btn');
        sendBtn.disabled = true;

        // Send to server
        fetch('sendMessage.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `receiverID=${encodeURIComponent(this.receiverID)}&messageText=${encodeURIComponent(messageText)}&csrf_token=${encodeURIComponent(this.csrfToken)}`
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                this.appendMessage(data.messageID, messageText, true, data.time);
                this.messageInput.value = '';
                this.autoResize();
                this.scrollToBottom();
            } else {
                alert(data.error || 'Message could not be sent.');
            }
        })
        .catch(err => {
            console.error('Error sending message:', err);
            alert('An error occurred while sending your message.');
        })
        .finally(() => {
            sendBtn.disabled = false;
        });
    }

    appendMessage(messageID, messageText, isMe = false, time = null) {
        // Prevent duplicate messages
        if (this.renderedMessageIDs.has(messageID)) {
            return;
        }

        const messageEl = document.createElement('article');
        messageEl.classList.add('message', isMe ? 'my-message' : 'their-message');
        messageEl.setAttribute('role', 'article');
        messageEl.setAttribute('data-message-id', messageID);
        messageEl.setAttribute('aria-labelledby', `message-${messageID}`);

        const paragraphId = `message-${messageID}`;

        messageEl.innerHTML = `
            <p id="${paragraphId}">${this.escapeHtml(messageText)}</p>
            <section class="message-meta">
                <time class="message-time">${time || ''}</time>
                ${isMe ? '<span class="message-status sent" aria-label="Message sent">✓</span>' : ''}
            </section>
        `;

        this.chatMessages.appendChild(messageEl);
        this.renderedMessageIDs.add(messageID);
        this.lastMessageID = Math.max(this.lastMessageID, messageID);
    }

    autoResize() {
        this.messageInput.style.height = 'auto';
        const scrollHeight = this.messageInput.scrollHeight;
        this.messageInput.style.height = Math.min(scrollHeight, 120) + 'px';
    }

    scrollToBottom() {
        setTimeout(() => {
            this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
        }, 0);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    fetchMessages() {
        const url = `fetchMessages.php?receiverID=${encodeURIComponent(this.receiverID)}&lastID=${encodeURIComponent(this.lastMessageID)}`;
        
        fetch(url)
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                if (data.messages && Array.isArray(data.messages)) {
                    let hasNewMessages = false;
                    data.messages.forEach(msg => {
                        if (!this.renderedMessageIDs.has(msg.messageID)) {
                            this.appendMessage(msg.messageID, msg.messageText, msg.isMe, msg.sentAt);
                            hasNewMessages = true;
                        }
                    });
                    if (hasNewMessages) {
                        this.scrollToBottom();
                    }
                }
            })
            .catch(err => console.error('Error fetching messages:', err));
    }

    startPolling() {
        // Poll for new messages every 2 seconds
        this.pollingInterval = setInterval(() => this.fetchMessages(), 2000);
    }

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }

    destroy() {
        this.stopPolling();
        this.messageForm.removeEventListener('submit', this.handleSubmit);
    }
}

// Initialize after DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const formEl = document.getElementById('messageForm');
    if (!formEl) {
        console.error('Message form not found');
        return;
    }

    const receiverID = formEl.getAttribute('data-receiver-id');
    const csrfToken = formEl.getAttribute('data-csrf-token');

    if (!receiverID || !csrfToken) {
        console.error('Missing required data attributes on message form');
        return;
    }

    window.messageSystem = new MessageSystem(parseInt(receiverID, 10), csrfToken);
});

// Clean up on page unload
window.addEventListener('beforeunload', () => {
    if (window.messageSystem) {
        window.messageSystem.destroy();
    }
});