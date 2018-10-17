'use strict';

const program = require('commander');
const fs = require('fs');
const path = require('path');
const kuler = require('kuler');
const cp = require('child_process');
const shell = require('shelljs');
const bundlePath = path.resolve(__dirname, 'interfaces/mobicoop/src/MobicoopBundle');
const gitBundlePath = path.resolve(__dirname, '.git/modules/interfaces/mobicoop/src/MobicoopBundle');


// Get the commit_msg path ($2)
program
  .version('0.1.0')
  .parse(process.argv);

console.log(bundlePath)
cp.execSync(`cd ${bundlePath} git status`,{stdio: 'inherit' });
// shell.exec(`which git`)

setTimeout(function(){
  process.exit(1);
},3000)


// if(!program.args[0]){
//   console.error(kuler('there is no commit message dude 🤪','red'));
// }

// // The commit message is stocked into the file path sent via hook
// let commitMsgPath = path.resolve(__dirname,program.args[0]);
// try{
//   let commit_msg = fs.readFileSync(commitMsgPath).toString();
//   process.exit(1);
// }catch(error){
//   console.error(kuler('Cannot read commit_msg file','red'));
//   process.exit(1);
// }

// setTimeout(function (argument) {
//   process.exit(1)
// },3000)