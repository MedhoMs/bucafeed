import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import '@sytles/style.css'
import '@sytles/main.css'

const app = createApp(App)

app.use(router)
app.mount('#app')

