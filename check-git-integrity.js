'use strict';

const getRepoInfo = require('git-repo-info');
const program = require('commander');
 
const kuler = require('kuler');
const infoMobicoopBundle = getRepoInfo('interfaces/mobicoop/src/MobicoopBundle');
const infoRoot = getRepoInfo();

program
  .version('0.1.0')
  .parse(process.argv);

console.log(program.args);

if(infoRoot.branch !== infoMobicoopBundle.branch){
  console.error(kuler(`Bundle & main repo are not on the same branch; repo is on ${infoRoot.branch} | branch is on ${infoMobicoopBundle.branch}`,'red'));
  process.exit(1);
}
console.log(kuler('Root & bundle are ont the same branch', 'green'));
console.log(info.branch);

process.exit(1);