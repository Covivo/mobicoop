'use strict';

const program = require('commander');
const fs = require('fs');
const path = require('path');
const kuler = require('kuler');
const cp = require('child_procress')
const bundlePath = path.resolve(__dirname, 'interfaces/mobicoop/src/MobicoopBundle');


// Get the commit_msg path ($2)
program
  .version('0.1.0')
  .parse(process.argv);

cp.execSync('git status',{cwd: bundlePath});

if(!program.args[0]){
  console.error(kuler('there is no commit message dude 🤪','red'));
}

// The commit message is stocked into the file path sent via hook
let commitMsgPath = path.resolve(__dirname,program.args[0]);
try{
  let commit_msg = fs.readFileSync(commitMsgPath).toString();

}catch(error){
  console.error(kuler('Cannot read commit_msg file','red'));
  process.exit(1);
}

setTimeout(function (argument) {
  process.exit(1)
},3000)