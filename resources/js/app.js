import { createApp } from 'vue';
import { createPinia } from 'pinia';

import App from './App.vue';
import router from './router';
import reveal from './directives/reveal';

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.directive('reveal', reveal);

app.mount('#app');

const splash = document.getElementById('app-splash');
if (splash) {
  splash.style.opacity = '0';
  setTimeout(() => splash.remove(), 250);
}
