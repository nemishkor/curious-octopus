/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
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
