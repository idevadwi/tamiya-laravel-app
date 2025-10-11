import './bootstrap';
import { createApp } from 'vue';

// Import your Vue components here
import Dashboard from './components/Dashboard.vue';

const app = createApp({});

// Register components
app.component('dashboard', Dashboard);

// Mount the app
app.mount('#app');
