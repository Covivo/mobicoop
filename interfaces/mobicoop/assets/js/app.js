'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import '../css/app.scss';
import 'babel-polyfill';
import Vue from 'vue';
import Buefy from 'buefy';
 
import Webcalculator from './components/Webcalculator';
import Parrot from './components/Parrot';

Vue.use(Buefy);
let app = new Vue({
  el: '#app',
  components: {Webcalculator,Parrot}
});

// this is a sample async function to show modern way to code in js ..
async function f() {
  let p = new Promise((resolve, reject) => {
    setTimeout(() => resolve("done!"), 1000);
  });

  let result = await p; // wait till the promise resolves (*)
  let a =42;
  console.log(result,a);
}

f();