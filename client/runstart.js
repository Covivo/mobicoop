'use strict';

const { spawn, execFile } = require('child_process');
const os = require('os');
const program = require('commander');
const path = require('path');
const pathStart = path.resolve(__dirname, 'bin/console');
const pathEncore = path.resolve(__dirname, './node_modules/.bin/encore');
// Get programm options args
program
  .parse(process.argv);

// port == False => Production or bad params
// port == undefined => Dev default port 8079
// port == XXXX [NUMBER] => a port to use for webpack

let port = program.args[0] && !!parseInt(program.args[0]) && parseInt(program.args[0]);
let production = port === false && String(program.args[0]) === 'production';

/*We try to check if we are on unix || windows & apply the right path to execute */
let command = os.platform() === 'win32' ? 'cmd.exe' : 'php';
let encoreCommand = os.platform() === 'win32' ? 'cmd.exe' : pathEncore;
// Start test only, or with coverage if asked
let host = port ? `0.0.0.0:${port}` : '0.0.0.0:8081';
let encorePort = 8079;


if (!production && port) {
  encorePort = parseInt(program.args[0]) + 100; // We are in dev mod + a setted port
}

if(production){
  console.log('prod')
  process.env.NODE_ENV = "production";
}

let options = [pathStart, 'server:run', host];
let optionsEncore = production ? ['production'] : ['dev-server', '--port', encorePort];
console.log('We are in dev mod the port is', port, production, encorePort, command, options)

if (os.platform() === 'win32') {
  options = ['/c', 'php', ...options]
  optionsEncore = ['/c', pathEncore, ...optionsEncore]
}

let startCmd = spawn(command, options, { stdio: 'inherit' });

startCmd.on('close', (code) => {
  if (code != 0) {
    process.exit(code);
  }
});


process.on('exit', (code) => {
  console.log('killing subprocess ..');
  startCmd.kill();
  if (!production) {
    encoreStart.kill();
  }
});

// We do not use webpack encore dev-server for production
// if (production) return;
let encoreStart = spawn(encoreCommand, optionsEncore, { stdio: 'inherit' });
encoreStart.on('close', (code) => {
  if (code != 0) {
    process.exit(code);
  }
});