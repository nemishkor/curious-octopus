import Vue from "vue"
import App from "./app/App.vue"
import router from './app/router'
import store from './app/store'
import './app.css';

new Vue({
    el: '#app',
    router,
    store,
    render: h => h(App),
    beforeCreate() {
        this.$store.commit('initialiseStore');
    }
});
