'use strict';

/*
* This file is used to create a canvas for front app & link bundle
*/

const fs = require('fs-extra');
const kuler = require('kuler');
const program = require('commander');
const path = require('path');
const to = require('await-to-js').default;

program
  .version('0.1.0')
  .option('-n, --project  <string>', 'Name of the instance client')
  .parse(process.argv);

if (!program.project) {
  process.stderr.write(kuler('You did not specify a name project to copy canvas to .. ', 'orange'));
  process.exit(0);
}

// This function check copy to path sent
async function createCanvas() {

  // Destination next to the platform
  let destinationProject = path.resolve(__dirname, `../../../${program.project}`);

  // Check if specified path is a dir & exists
  let err, exists, success, folders;
  [err, exists] = await to(fs.mkdirp(destinationProject));
  if (err) {
    process.stderr.write(kuler(`Path specified is not writable or already exists! ${destinationProject} \n`, 'red'));
    console.error(err);
    process.exit(0);
  }
  // Copy mobicoop files to sent path

  let pathToClient = path.resolve(__dirname, '../../client');
  // bundle needed for structures assets
  let pathToMobicoopBundle = path.resolve(pathToClient, 'src/MobicoopBundle');
  //filter so that we don't need to copy Bundle
  const filter = {
    dirToExclude: ['MobicoopBundle', 'node_modules', 'vendor', 'var', 'cypress', 'assets'],
    filter: function (currentPath) {
      if (this.dirToExclude.includes(path.basename(currentPath))) { return false; }
      return true;
    }
  };

  // Copy all file but bundle !
  process.stdout.write(kuler(`Copying files to ${destinationProject}\n`, 'green'));
  [err, success] = await to(fs.copy(pathToClient, destinationProject, filter));
  if (err) {
    process.stderr.write(kuler('Cannot copy to specified path!\n', 'red'));
    console.error(err);
    process.exit(0);
  }

  // Create structure assets from bundle
  const filterAssets = {
    filter: function (currentPath) {
      let assetsToKeep = ['_variables.scss']
      let basename = path.basename(currentPath);
      let stats = fs.lstatSync(currentPath);
      if (!stats.isDirectory() && !assetsToKeep.includes(basename)) { return false; }
      return true;
    }
  };
  let pathToClientAssets = path.resolve(pathToMobicoopBundle, 'Resources/assets');
  let destinationAssets = path.resolve(destinationProject, 'assets');

  fs.mkdirp(destinationAssets);

  console.log(pathToClientAssets, destinationAssets)

  process.stdout.write(kuler(`Creating assets structure into ${destinationAssets} \n`, 'green'));
  [err, success] = await to(fs.copy(pathToClientAssets, destinationAssets, filterAssets));
  if (err) {
    process.stderr.write(kuler('Cannot copy to specified path!\n', 'red'));
    console.error(err);
    process.exit(0);
  }

  crawlDir(destinationAssets);

  // Copy app.js because it's a specific version for client
  let appjs = path.resolve(__dirname, 'client-canvas/app.js');
  console.log(appjs, `${destinationAssets}/js`)
  process.stdout.write(kuler(`Copying specific assets for ${destinationAssets} 🚀 \n`, 'pink'));
  [err, success] = await to(fs.copy(appjs, `${destinationAssets}/js/app.js`));


  /**
   * 
   * We need to add those behind to all js file in dest 
   * 'use strict';

import '../../../../src/MobicoopBundle/Resources/assets/js/page/search/simpleResults.js';
import '../../../css/page/search/simpleResults.scss';
   */

  // We add bundle to .gitignore
  // [err, success] = await to(fs.appendFile(path.resolve(destinationProject, '.gitignore'), '\nsrc/MobicoopBundle'));
  // if (!err) process.stdout.write(kuler('Added bundle to .gitignore \n', 'green'));
  // process.stdout.write(kuler('☢️ Do not forget to commit into monorepo when you edit bundle files ☣️ \n', 'cyan'));
}

// Run the main job
createCanvas();


/**
 * recursively crawl directory from dir entrypoint & add a gitkeep inside all folders!
 * @param {string} dir 
 */
function crawlDir(dir) {
  fs.readdirSync(dir).forEach(element => {
    let fullPath = path.join(dir, element);
    if (fs.lstatSync(fullPath).isDirectory()) {
      // add a gitkeep inside all directory strcutures so that they stay in git!
      fs.writeFileSync(`${fullPath}/.gitkeep`, '');
      crawlDir(fullPath);
    }
    // if(path.extname(fullPath) === '.js'){
    //   fs.createFileSync()
    // }
  });
}