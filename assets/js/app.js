global.jQuery = require('jquery');
require('bootstrap-sass');
global.axios = require('axios');

import Vue from 'vue';

import Home from './components/HomeComponent'



new Vue({
    el: '#app',
    components: {Home}
});