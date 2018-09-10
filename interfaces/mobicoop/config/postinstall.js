#!/usr/bin/env node
'use strict';

const request = require('request-promise-native');
const fs = require('fs');

const fileOptions = {
  url: 'https://cs.sensiolabs.org/download/php-cs-fixer-v2.phar',
  encoding: null
};

// Create the .phar file to check symfony lint
let phpCsFixer = fs.createWriteStream('vendor/bin/php-cs-fixer.phar');
request(fileOptions)
.then(function (body) {
  phpCsFixer.write(body);
  phpCsFixer.end();
}).catch(function (err) {
  console.error(`an error happend while downlaod php-cs-fixer ${err}`)
})
