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
let command= os.platform() === 'win32' ? 'cmd.exe' : 'vendor/bin/kahlan';

// Start test only, or with coverage if asked
let options = ['--spec=src/MobicoopBundle/spec', 'src=src/MobicoopBundle', '--reporter=verbose'];
/*if(program.coverage){
  options = [...options, '--cc=true','--coverage=4'];
}*/
if (os.platform() === 'win32'){
  options = ['/c','vendor\\bin\\kahlan.bat' ,...options]

}
let dirTest= spawn(command, options);

dirTest.stdout.on('data', (data) => {
  process.stdout.write(data);
});

dirTest.stderr.on('data', (data) => {
  process.stderr.write(data);
});

dirTest.on('close', (code) => {
  if(code != 0){
    process.exit(code);
  }
});