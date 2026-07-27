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
        console.log('Ticket Notification Manager initialized');
    }

    requestPermission() {
        if (!('Notification' in window)) {
            console.warn('Browser ini tidak mendukung notifikasi web.');
            return;
        }

        if (Notification.permission === 'granted') {
            this.isAllowed = true;
            return;
        }

        Notification.requestPermission().then(permission => {
            this.isAllowed = permission === 'granted';
            if (this.isAllowed) {
                console.log('Izin notifikasi diberikan');
            } else {
                console.log('Izin notifikasi ditolak');
            }
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
            new Notification(title, {
                body: message,
                icon: '/favicon.ico',
                tag: Date.now() + Math.random(),
                requireInteraction: true,
            });
        }
        this.renderToast(title, message, options.color || '#3B82F6');
    }

    renderToast(title, message, color = '#3B82F6') {
        if (!this.toastContainer) return;

        const toast = document.createElement('div');
        const currentTime = new Date().toLocaleTimeString('id-ID');

        toast.innerHTML = `<div style="background: white; border-left: 4px solid ${color}; box-shadow: 0 10px 25px rgba(0,0,0,0.1); padding: 16px; min-width: 300px; max-width: 350px; border-radius: 8px; margin-bottom: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-weight: bold; color: #1F2937;">${title}</span>
                <button onclick="this.parentElement.parentElement.remove();" style="background: none; border: none; cursor: pointer; color: #6B7280; font-size: 16px;">x</button>
            </div>
            <p style="color: #6B7280; font-size: 13px; margin: 0;">${message}</p>
            <span style="color: #9CA3AF; font-size: 11px;">${currentTime}</span>
        </div>`;

        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';

        this.toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        }, 10);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    startPolling() {
        this.lastSeen = new Date().toISOString();

        this.pollingInterval = setInterval(async () => {
            await this.fetchNewTickets();
        }, 10000);
    }

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
    }

    async fetchNewTickets() {
        try {
            const url = '/api/tickets/new?last_seen=' + encodeURIComponent(this.lastSeen);
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!response.ok) return;

            const data = await response.json();

            if (data.tickets.length > 0) {
                this.lastSeen = data.tickets[0].created_at;
                data.tickets.forEach(ticket => {
                    this.showNewTicketNotification(ticket);
                });
            }
        } catch (error) {
            console.error('Error fetching new tickets:', error);
        }
    }

    showNewTicketNotification(ticket) {
        const typeColors = {
            'Spp': '#3B82F6',
            'Tunai': '#8B5CF6',
            'Tabungan': '#10B981',
        };

        const title = 'Antrian Baru Dipanggil!';
        const message = 'Tiket ' + ticket.ticket_number + ' (' + ticket.type + ') sedang dilayani';

        this.showToast(title, message, {
            color: typeColors[ticket.type] || '#3B82F6'
        });
    }

    cleanup() {
        this.stopPolling();
        if (this.toastContainer && this.toastContainer.parentNode) {
            this.toastContainer.parentNode.removeChild(this.toastContainer);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const paths = ['/tickets', '/dashboard'];
    const currentPath = window.location.pathname;

    if (paths.some(path => currentPath.startsWith(path))) {
        const manager = new TicketNotificationManager();
        manager.init();
        window.ticketNotificationManager = manager;
    }
});