import { ref, computed } from 'vue'

const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const token = () => localStorage.getItem('token')

const headers = () => ({
    'Authorization': `Bearer ${token()}`,
    'Accept': 'application/json',
    'Content-Type': 'application/json',
})

export const notifications = ref([])
export const unreadCount = ref(0)
export const loading = ref(false)
export const lastPage = ref(1)
export const currentPage = ref(1)

export const fetchNotifications = async (page = 1, type = null) => {
    if (!token()) return
    loading.value = true
    try {
        let url = `${API_BASE}/notifications?page=${page}`
        if (type) url += `&type=${type}`
        const res = await fetch(url, { headers: headers() })
        if (res.ok) {
            const data = await res.json()
            notifications.value = data.data || []
            lastPage.value = data.last_page || 1
            currentPage.value = data.current_page || 1
        }
    } catch (e) {
        console.error('Error fetching notifications:', e)
    } finally {
        loading.value = false
    }
}

export const fetchUnreadCount = async () => {
    if (!token()) return
    try {
        const res = await fetch(`${API_BASE}/notifications/unread-count`, { headers: headers() })
        if (res.ok) {
            const data = await res.json()
            unreadCount.value = data.count
        }
    } catch (e) {
        console.error('Error fetching unread count:', e)
    }
}

export const markAsRead = async (notification) => {
    try {
        const res = await fetch(`${API_BASE}/notifications/${notification.id}/read`, {
            method: 'POST',
            headers: headers(),
        })
        if (res.ok) {
            notification.read = true
            unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
    } catch (e) {
        console.error('Error marking notification as read:', e)
    }
}

export const markAllAsRead = async () => {
    try {
        const res = await fetch(`${API_BASE}/notifications/read-all`, {
            method: 'POST',
            headers: headers(),
        })
        if (res.ok) {
            notifications.value.forEach(n => n.read = true)
            unreadCount.value = 0
        }
    } catch (e) {
        console.error('Error marking all notifications as read:', e)
    }
}

export const deleteNotification = async (notification) => {
    try {
        const res = await fetch(`${API_BASE}/notifications/${notification.id}`, {
            method: 'DELETE',
            headers: headers(),
        })
        if (res.ok) {
            notifications.value = notifications.value.filter(n => n.id !== notification.id)
            if (!notification.read) {
                unreadCount.value = Math.max(0, unreadCount.value - 1)
            }
        }
    } catch (e) {
        console.error('Error deleting notification:', e)
    }
}
