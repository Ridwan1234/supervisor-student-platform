import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import Toast, { POSITION } from "vue-toastification";
import "vue-toastification/dist/index.css";
import axios from 'axios';
axios.defaults.baseURL = 'http://127.0.0.1:8000';
axios.defaults.withCredentials = true;

export default axios;

const app = createApp(App);
app.use(Toast, {
    position: POSITION.TOP_RIGHT,
    timeout: 5000,
    closeOnClick: true,
    pauseOnHover: true,
    draggable: true,
});

app.use(router);
app.mount("#app");
