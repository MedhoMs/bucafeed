import { ref } from 'vue'

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