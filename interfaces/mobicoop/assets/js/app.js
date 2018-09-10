'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import '../css/app.scss';
import 'babel-polyfill';

import $ from 'jquery';
import siteNameStr from './src/sitename';

// add h2 to body
$('body').append(`<h2> & hello to ${siteNameStr('CoviRide')} too </h2>`);


async function f() {

  let p = new Promise((resolve, reject) => {
    setTimeout(() => resolve("done!"), 1000);
  });

  let result = await p; // wait till the promise resolves (*)
  let a =42;
  console.log(a);
  alert(result); // "done!"
}

f();