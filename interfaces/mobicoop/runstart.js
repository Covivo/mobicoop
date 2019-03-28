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

/*We try to check if we are on unix || windows & apply the right path to execute */
let command = os.platform() === 'win32' ? 'cmd.exe' : 'php';
let encoreCommand = os.platform() === 'win32' ? 'cmd.exe' : pathEncore;
// Start test only, or with coverage if asked
let host = program.args[0] ? `127.0.0.1:${program.args[0]}` : '127.0.0.1:8081';
let encorePort = program.args[0] ? parseInt(program.args[0]) + 100 : 8079;

let options = [pathStart, 'server:run', host];
let optionsEncore = ['dev-server', '--port', encorePort]

if (os.platform() === 'win32') {
  options = ['/c', 'php', ...options]
  optionsEncore = ['/c', pathEncore, ...optionsEncore]
}

let startCmd = spawn(command, options, { stdio: 'inherit' });
let encoreStart = spawn(encoreCommand, optionsEncore, { stdio: 'inherit' });

startCmd.on('close', (code) => {
  if (code != 0) {
    process.exit(code);
  }
});

encoreStart.on('close', (code) => {
  if (code != 0) {
    process.exit(code);
  }
});

process.on('exit', (code) => {
  console.log('killing subprocess ..');
  startCmd.kill();
  encoreStart.kill();
});