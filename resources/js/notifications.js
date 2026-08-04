import Alpine from 'alpinejs';

// Register Notification Bell Alpine Data
Alpine.data('notificationBell', () => ({
    unreadCount: 0,
    showPopup: false,
    isLoading: false,
    notifications: [],
    unreadInterval: null,

    init() {
        console.log('[Notification Bell] Alpine component initialized');
        console.log('[Notification Bell] Routes:', window.notificationRoutes);
        this.startUnreadInterval();
        this.loadUnreadCount();
    },

    loadUnreadCount() {
        if (!window.notificationRoutes || !window.notificationRoutes.unreadCount) {
            console.error('Notification route not configured');
            return;
        }
        this.fetchNotifications(window.notificationRoutes.unreadCount)
            .then(data => {
                console.log('[Bell] Unread count:', data);
                this.unreadCount = data.count || 0;
            })
            .catch(e => {
                console.error('[Bell] Unread count error:', e);
                this.unreadCount = 0;
            });
    },

    loadNotifications() {
        console.log('[Bell] loadNotifications called, showPopup=', this.showPopup);
        if (!this.showPopup || this.isLoading) {
            console.log('[Bell] Skipping: showPopup or isLoading false');
            return;
        }
        if (!window.notificationRoutes || !window.notificationRoutes.recent) {
            console.log('[Bell] Skipping: no routes');
            return;
        }
        this.isLoading = true;
        this.fetchNotifications(window.notificationRoutes.recent)
            .then(data => {
                console.log('[Bell] Got data:', data);
                this.notifications = Array.isArray(data) ? data : [];
            })
            .catch(e => {
                console.error('[Bell] Fetch error:', e);
                this.notifications = [];
            })
            .finally(() => { this.isLoading = false; });
    },

    fetchNotifications(url) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        return fetch(url, {
            headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            credentials: 'same-origin'
        }).then(r => {
            if (r.status === 401 || r.status === 403) throw new Error('Not authorized');
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        });
    },

    startUnreadInterval() {
        if (this.unreadInterval) clearInterval(this.unreadInterval);
        this.unreadInterval = setInterval(() => this.loadUnreadCount(), 15000);
    },

    togglePopup() {
        this.showPopup = !this.showPopup;
        if (this.showPopup) {
            this.loadNotifications();
        }
    },

    closePopup() {
        this.showPopup = false;
        this.isLoading = false;
    },

    formatDate(dateString) {
        if (!dateString) return '';
        try { return new Date(dateString).toLocaleString('id-ID'); }
        catch (e) { return dateString; }
    }
}));

/**
 * Web Notification API untuk Sistem Antrian
 */
class TicketNotificationManager {
    constructor() {
        this.lastSeen = null;
        this.pollingInterval = null;
        this.isAllowed = false;
        this.toastContainer = null;
    }

    init() {
        this.requestPermission();
        this.createToastContainer();
        this.startPolling();
    }

    requestPermission() {
        if (!('Notification' in window)) return;
        if (Notification.permission === 'granted') {
            this.isAllowed = true;
            return;
        }
        Notification.requestPermission().then(permission => {
            this.isAllowed = permission === 'granted';
        });
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; max-height: 300px; overflow-y: auto;';
        document.body.appendChild(container);
        this.toastContainer = container;
    }

    showToast(title, message, options = {}) {
        if (this.isAllowed && ('Notification' in window)) {
            new Notification(title, { body: message, icon: '/favicon.ico', tag: Date.now(), requireInteraction: true });
        }
        this.renderToast(title, message, options.color || '#3B82F6');
    }

    renderToast(title, message, color = '#3B82F6') {
        if (!this.toastContainer) return;
        const toast = document.createElement('div');
        const time = new Date().toLocaleTimeString('id-ID');
        toast.innerHTML = '<div style="background: white; border-left: 4px solid ' + color + '; box-shadow: 0 10px 25px rgba(0,0,0,0.1); padding: 16px; min-width: 300px; border-radius: 8px; margin-bottom: 10px;"><div style="display: flex; justify-content: space-between; align-items: center;"><span style="font-weight: bold; color: #1F2937;">' + title + '</span><button onclick="this.parentElement.parentElement.remove();" style="background:none;cursor:pointer;color:#6B7280;">x</button></div><p style="color:#6B7280;font-size:13px;margin:0;">' + message + '</p><span style="color:#9CA3AF;font-size:11px;">' + time + '</span></div>';
        toast.style.opacity = '0'; toast.style.transform = 'translateX(100%)'; toast.style.transition = 'all 0.3s ease';
        this.toastContainer.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '1'; toast.style.transform = 'translateX(0)'; }, 10);
        setTimeout(() => {
            toast.style.opacity = '0'; toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    startPolling() {
        this.lastSeen = new Date().toISOString();
        this.pollingInterval = setInterval(async () => await this.fetchNewTickets(), 10000);
    }

    stopPolling() { if (this.pollingInterval) clearInterval(this.pollingInterval); }

    async fetchNewTickets() {
        try {
            const url = '/api/tickets/new?last_seen=' + encodeURIComponent(this.lastSeen);
            const resp = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' } });
            if (!resp.ok) return;
            const data = await resp.json();
            if (data.tickets.length > 0) {
                this.lastSeen = data.tickets[0].created_at;
                data.tickets.forEach(t => this.showNewTicketNotification(t));
            }
        } catch (e) { console.error(e); }
    }

    showNewTicketNotification(ticket) {
        const colors = { Spp: '#3B82F6', Tunai: '#8B5CF6', Tabungan: '#10B981' };
        this.showToast('Antrian Baru Dipanggil!', 'Tiket ' + ticket.ticket_number + ' (' + ticket.type + ') sedang dilayani', { color: colors[ticket.type] || '#3B82F6' });
    }

    cleanup() {
        this.stopPolling();
        if (this.toastContainer && this.toastContainer.parentNode) this.toastContainer.parentNode.removeChild(this.toastContainer);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const paths = ['/tickets', '/dashboard'];
    if (paths.some(p => window.location.pathname.startsWith(p))) {
        const manager = new TicketNotificationManager();
        manager.init();
        window.ticketNotificationManager = manager;
    }
});
