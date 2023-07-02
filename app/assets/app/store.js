'use strict';

import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex)

const store = new Vuex.Store({
    state: {
        user: null,
    },
    getters: {
        token: state => {
            if (state.user === null) {
                return null;
            }
            return state.user.token
        }
    },
    mutations: {
        initialiseStore(state) {
            // Check if the ID exists
            if (localStorage.getItem('store')) {
                // Replace the state object with the stored item
                this.replaceState(
                    Object.assign(state, JSON.parse(localStorage.getItem('store')))
                );
            }
        },
        setUser(state, payload) {
            this.state.user = {
                token: payload.token,
                email: payload.email,
            };
        },
        deleteUser() {
            this.state.user = null;
        },
    },
});

store.subscribe((mutation, state) => {
    // Store the state object as a JSON string
    localStorage.setItem('store', JSON.stringify(state));
});

export default store;
