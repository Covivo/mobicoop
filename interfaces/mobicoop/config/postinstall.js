#!/usr/bin/env node
'use strict';

const request = require('request-promise-native');
const fs = require('fs');

// Create the .phar file to check symfony lint
let phpCsFixer = fs.createWriteStream('vendor/bin/php-cs-fixer.phar');
request('https://cs.sensiolabs.org/download/php-cs-fixer-v2.phar')
.then(function (body) {
  phpCsFixer.write(body);
}).catch(function (err) {
  console.error(`an error happend while downlaod php-cs-fixer ${err}`)
})
