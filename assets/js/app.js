import Vue from 'vue';
import "@babel/polyfill";

import RecentLive from './components/intro/RecentLive';
import CountLive from './components/menu/CountLive';
import LatestStreams from './components/menu/LatestStreams';

/**
 * Intro Page
 */
new Vue({
    el: '#intro',
    components: {RecentLive}
});

/**
 * Menu Sidebar
 */
new Vue({
    el: '#sidebar',
    components: {CountLive, LatestStreams}
});
