'use strict';

/*
* This file is used to create a canvas for front app & link bundle
*/

const fs = require('fs-extra');
const kuler = require('kuler');
const program = require('commander');
const path = require('path');
const to = require('await-to-js').default;

/*//path to bundle
const bundle = path.resolve(__dirname, '../interfaces/mobicoop/src/MobicoopBundle');
//filter so that we don't need to copy Bundle
  const filter = {
    filter:path => {
      if (path === bundle){return false;}
      else{return true;}
    }
  }*/

program
    .version('0.1.0')
    .option('-d, --destination  <dir>', 'Path to copy Bundle to')
    .parse(process.argv);


if (!program.destination)  {
  process.stderr.write(kuler('You did not specify a path to copy canvas to .. ','orange'));
  process.exit(0);
}


// This function check copy to path sent & link to bunlde
async function linkBundle () {
  // Check if specified path is a dir & exist
  let err, exists, success;
  let destination = path.resolve(program.destination);
  [err,exists] = await to(fs.ensureDir(destination));
  if(err){
    process.stderr.write(kuler('Path specified does not exists or is not a directory! \n','red'))
    console.error(err);
    process.exit(0);
  }
  // Copy mobicoop files to sent path

  let pathToMobicoop = path.resolve(__dirname, '../interfaces/mobicoop');
  let pathToMobicoopBundle = path.resolve(pathToMobicoop, 'src/MobicoopBundle');
  let pathToCopiedBundle = path.resolve(destination, 'src/MobicoopBundle');

  // We link bundle to new created folder
  [err,success] = await to(fs.symlink(pathToMobicoopBundle,pathToCopiedBundle,'dir'))
  if(err){
    process.stderr.write(kuler('Cannot create symlink bundle\n','red'))
    console.error(err);
    process.exit(0);
  }
  process.stdout.write(kuler('Bundle are now symlinked 💪 ...\n','green'));

  // We add bundle to .gitignore
  process.stdout.write(kuler('☢️ Do not forget to commit into monorepo when you edit bundle files ☣️ \n','cyan'));
}

// Run the main job
linkBundle();
