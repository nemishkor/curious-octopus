import Vue from 'vue'
import Router from 'vue-router'
import Home from './views/Home'
import Login from "./views/Login";
import Queries from "./views/Queries";
import Query from "./views/Query";
import Databases from "./views/Databases";
import Database from "./views/Database";

Vue.use(Router)

export default new Router({
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'home',
            component: Home
        },
        {
            path: '/login',
            name: 'login',
            component: Login
        },
        {
            path: '/queries/page/:page',
            name: 'queries',
            component: Queries
        },
        {
            path: '/queries/item/:id',
            name: 'query',
            component: Query
        },
        {
            path: '/databases/page/:page',
            name: 'databases',
            component: Databases
        },
        {
            path: '/databases/item/:id',
            name: 'database',
            component: Database
        },
    ]
})
