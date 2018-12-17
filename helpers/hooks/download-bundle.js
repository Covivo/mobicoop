'use strict';

const program = require('commander');
const fs = require('fs');
const path = require('path');
const kuler = require('kuler');
const getRepoInfo = require('git-repo-info');
const bundleSrc = path.resolve(__dirname, '../../interfaces/mobicoop/src');
const bundlePath = path.resolve(__dirname, '../../interfaces/mobicoop/src/MobicoopBundle');
const git = require('simple-git')(bundleSrc);

let branchRoot = getRepoInfo().branch;

// Has already been clone .. just go out
if (fs.existsSync(bundlePath)){
  console.log(kuler('Bundle has already been download .. nothing to do 😴','yellow'));
  process.exit(0);
}

git
    .silent(true)
    .clone('http://gitlab.com/mobicoop/MobicoopBundle', function (err,res) {
      if(err){
        console.error(kuler(err,'red'));
        console.error(kuler('Cannot clone Bundle repo','red'));
        process.exit(1);
      }
      console.log(kuler('Bundle has been cloned 👌','green'));
      let bundleGit = require('simple-git')(bundlePath);
      bundleGit.checkout(branchRoot,function(err,res){
        if(err){
          console.error(kuler(`Cannot change bundle branch there is no ${branchRoot} branch in bundle 🤯; please change the branch of the monorepo first 🤞`,'red'));
          process.exit(1);
        }
        console.log(kuler(`Monorepo & bundle are now on the ${branchRoot} branch 💪`,'green'));
      })
    });
