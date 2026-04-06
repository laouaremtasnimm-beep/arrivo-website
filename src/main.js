import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './assets/main.css'   // ← make sure this line is here

createApp(App).use(router).mount('#app')