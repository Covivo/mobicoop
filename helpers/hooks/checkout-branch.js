'use strict';

const fs = require('fs');
const path = require('path');
const kuler = require('kuler');
const getRepoInfo = require('git-repo-info');
const bundleSrc = path.resolve(__dirname, '../../interfaces/mobicoop/src');
const bundlePath = path.resolve(__dirname, '../../interfaces/mobicoop/src/MobicoopBundle');
const bundleGit = require('simple-git')(bundlePath);

let branchRoot = getRepoInfo().branch;
let branchBundle = getRepoInfo(bundlePath).branch;

// branch have differents name, so it's a big error.
if(branchRoot === branchBundle){
  console.log(kuler(`Bundle & main are on the same branch; ${branchRoot} `,'cyan'));
  return;
}

// try to change branch bundle to the same name of the root branch
bundleGit
.silent(true)
.checkout(branchRoot,function(err,res){
  // If bundle has not branch yet we must create it
  if(!err){
    console.log(kuler(`Monorepo & bundle are now on the ${branchRoot} branch 💪`,'green'));
    return;
  }
  console.error(kuler(`Branch ${branchRoot} not existing in bundle 🤯; creating it ...`,'yellow'));
  // Create local branch in bundle
  bundleGit
  .silent(true)
  .checkoutLocalBranch(branchRoot,function(err,res){
    if(err){
      console.error(kuler('Cannot create local branch ☹️, this is a really anoying problem dude','red'));
    }
    console.log(kuler(`...Created !Monorepo & bundle are now on the ${branchRoot} branch 💪`,'green'));
  });
})
