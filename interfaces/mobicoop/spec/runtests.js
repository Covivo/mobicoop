'use strict';

const { spawn,execFile } = require('child_process');
const os = require('os');
const program = require('commander');
const path = require('path');

// Get programm options args
program
  .option('-c, --coverage', 'Add peppers')
  .parse(process.argv);

/*We try to check if we are on unix || windows & apply the right path to execute */
let kahlanPath= os.platform() === 'win32' ? 'vendor\\bin\\kahlan.bat' : 'vendor/bin/kahlan';

// Start test only, or with coverage if asked
let options = ['--reporter=verbose'];
if(program.coverage){
  options = [...options, '--cc=true','--coverage=4'];
}

// We execute the file needed following the OS environnement
execFile(kahlanPath,options, (error, stdout, stderr) => {
  if (error) {
    console.error(error);
  }
  console.log(stdout);
})