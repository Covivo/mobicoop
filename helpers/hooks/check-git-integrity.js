'use strict';

const getRepoInfo = require('git-repo-info');
const kuler = require('kuler');
const path = require('path');
const bundlePath = path.resolve(__dirname, '../../interfaces/mobicoop/src/MobicoopBundle');
const infoMobicoopBundle = getRepoInfo(bundlePath);
const infoRoot = getRepoInfo();

// branch have differents name, so it's a big error.
if(infoRoot.branch !== infoMobicoopBundle.branch){
  console.error(kuler(`Bundle & main repo are not on the same branch; repo is on ${infoRoot.branch} | branch is on ${infoMobicoopBundle.branch}`,'red'));
  process.exit(1);
}
console.log(kuler('Root & bundle are ont the same branch', 'green'));