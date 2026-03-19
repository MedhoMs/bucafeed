import { ref } from 'vue'

// Estado global reactivo sin Pinia
export const user = ref(JSON.parse(localStorage.getItem('user')) || null)

export const login = (userData) => {
    user.value = userData
    localStorage.setItem('user', JSON.stringify(userData))
}

export const logout = () => {
    user.value = null
    localStorage.removeItem('user')
}