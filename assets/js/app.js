// assets/js/app.js
import Vue from 'vue';
import "@babel/polyfill";

import RecentLive from './components/intro/RecentLive'

/**
 * Create a fresh Vue Application instance
 */
new Vue({
    el: '#app',
    components: {RecentLive}
});