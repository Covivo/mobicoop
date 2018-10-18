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


// try to push bundle to remote
bundleGit
.silent(true)
.pull(function (err,res) {
  if(!err){
    console.log(kuler(`Pulled branch ${branchBundle}`,'green'))
    return;
  }
  console.error(kuler(`Cannot pull the branch ${branchBundle} maybe it's not existing on remote`,'red'));
  console.error(err);
})