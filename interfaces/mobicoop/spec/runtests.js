'use strict';

const { spawn } = require('child_process');
const os = require('os');
const path = require('path');
const kuler = require('kuler');

/*We try to check if we are on unix || windows & apply the right path to execute */
let kahlanPath= os.platform() === 'win32' ? 'vendor/bin/kahlan.bat' : 'vendor/bin/kahlan';

if(os.platform() !== 'win32'){
  let kahlan = spawn(kahlanPath,['--cc=true','--coverage=4','--reporter=verbose'],{ stdio: 'inherit'});
  kahlan.stdout.on('data', (data) => {
    console.log(data.toString());
  });

  kahlan.stderr.on('data', (data) => {
    console.log(kuler(data.toString(),'orange'));
  });

  kahlan.on('exit', (code) => {
    console.log(kuler(`Child exited with code ${code}`,'red'));
  });
}
else{
  let kahlan = spawn('cmd.exe', ['/c', kahlanPath, '--cc=true','--coverage=4','--reporter=verbose'], { stdio: 'inherit'});
}
