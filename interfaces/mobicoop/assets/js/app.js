'use strict';

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');

const $ = require('jquery');
const siteNameStr = require('./src/sitename');

// add h2 to body
$('body').append(`<h2> & hello to ${siteNameStr('CoviRide')} too </h2>`);