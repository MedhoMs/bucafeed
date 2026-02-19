import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import '../src/sytles/style.css'
import '../src/sytles/main.css'

const app = createApp(App)

app.use(router)
app.mount('#app')

