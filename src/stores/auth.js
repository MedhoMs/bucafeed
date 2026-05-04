import { ref } from 'vue'

const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

// Estado global reactivo
export const user = ref(JSON.parse(localStorage.getItem('user')) || null)
export const token = ref(localStorage.getItem('token') || null)

export const login = (userData, userToken) => {
    user.value = userData
    token.value = userToken
    localStorage.setItem('user', JSON.stringify(userData))
    localStorage.setItem('token', userToken)
}

export const logout = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
}

/**
 * Refresca los datos del usuario desde la API al arrancar la app.
 * - Si el token expiró o fue revocado (401) → logout automático.
 * - Si el rol cambió (ej: se le quitó el admin) → actualiza localStorage.
 */
export const refreshUser = async () => {
    if (!token.value) return

    try {
        const res = await fetch(`${API_BASE}/me`, {
            headers: {
                'Authorization': `Bearer ${token.value}`,
                'Accept': 'application/json',
            }
        })

        if (res.status === 401) {
            // Token revocado o expirado → forzar logout
            logout()
            return
        }

        if (res.ok) {
            const freshUser = await res.json()
            user.value = freshUser
            localStorage.setItem('user', JSON.stringify(freshUser))
        }
    } catch {
        // Sin conexión: dejamos los datos en caché sin cerrar sesión
    }
}