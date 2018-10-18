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

console.log(branchBundle);

// try to push bundle to remote
bundleGit
.silent(true)
.push('origin',branchBundle,function (err,res) {
  if(!err){
    console.log(kuler(`Changed push to bundle on branch ${branchBundle}`,'green'))
    return;
  }
  console.error(kuler(`Cannot push the branch ${branchBundle} maybe it's not existing on remote, try with -u .. `,'red'));
  console.error(err);
  // Try to push with set-upstream
  bundleGit
  .silent(true)
  .push(['-u', 'origin', branchBundle],function (err,res) {
    if(!err){
      console.log(kuler(`Changed push to new remote bundle branch ${branchBundle}`,'green'))
      return;
    }
    console.error(kuler(`Cannot create the branch ${branchBundle} on remote .. `,'red'));
    console.error(err);
  })
})