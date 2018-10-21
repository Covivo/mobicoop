'use strict';

const { spawn,execFile } = require('child_process');
const os = require('os');
const program = require('commander');
const path = require('path');
const pathStart = path.resolve(__dirname,'bin/console');
const pathEncore = path.resolve(__dirname,'./node_modules/.bin/encore');
// Get programm options args
program
  .parse(process.argv);

/*We try to check if we are on unix || windows & apply the right path to execute */
let command= os.platform() === 'win32' ? 'cmd.exe' :  'php';
// Start test only, or with coverage if asked
let host = program.args[0] ? `127.0.0.1:${program.args[0]}` : '127.0.0.1:8081';
let options = [pathStart,'server:run', host];


if (os.platform() === 'win32'){
  options = ['/c', ...options]
}

let startCmd= spawn(command, options, {stdio: 'inherit'});
let encorePort = program.args[0] ? parseInt(program.args[0]) + 100 : 8079 ;
let encoreStart = spawn(pathEncore, ['dev-server','--port', encorePort], {stdio: 'inherit'});

startCmd.on('close', (code) => {
  if(code != 0){
    process.exit(code);
  }
});

encoreStart.on('close', (code) => {
  if(code != 0){
    process.exit(code);
  }
});

process.on('exit', (code) => {
  console.log('killing subprocess ..');
  startCmd.kill();
  encoreStart.kill();
});