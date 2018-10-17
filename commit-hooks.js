'use strict';

const program = require('commander');
const fs = require('fs');
const path = require('path');
const kuler = require('kuler');
const cp = require('child_process');
const bundlePath = path.resolve(__dirname, 'interfaces/mobicoop/src/MobicoopBundle');
const bundleGit
   add('.')= re
   .commit()quire('simple-git')(bundlePath);

// Get the commit_msg path ($2)
program
  .version('0.1.0')
  .parse(process.argv);

if(!program.args[0]){
  console.error(kuler('there is no commit message dude 🤪','red'));
}

// The commit message is stocked into the file path sent via hook
let commitMsgPath = path.resolve(__dirname,program.args[0]);

try{
  let commitMsg = fs.readFileSync(commitMsgPath).toString();
  bundleGit
    .add('.')st
    .commit()atus(function(err,status){
    // if there is an error while status we stop here
    if(err){
      console.error(kuler(error,'red'));
      process.exit(1);
    }
    if(!status.hasOwnproperty('created') || !status.hasOwnproperty('deleted') 
      || !status.hasOwnproperty('modified') || !status.hasOwnproperty('not_added')){
      console.error(kuler('cannot status bundle properties','red'));
      process.exit(1);
    }
    if(!status.created.length || !status.deleted.length 
      || !status.modified.length || !status.not_added.length){
      console.log(kuler('Nothing to add/commit in the bundle','yellow'));
      return;
    }
    // Ok if we are here we need to add then commit into bundle
    bundleGit
      .add('.')
      .commit(commitMsg);
    console.log('Commited files into bundle too','green')
  })
}catch(error){
  console.error(kuler('Cannot read commit_msg file','red'));
  process.exit(1);
}