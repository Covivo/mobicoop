'use strict';

/*
* This file is used to create a canvas for front app & link bundle
*/

const fs = require('fs-extra');
const kuler = require('kuler');
const program = require('commander');
const path = require('path');
const to = require('await-to-js').default;
const replace = require('replace-in-file');


program
  .version('0.1.0')
  .option('-n, --project  <string>', 'Name of the instance client')
  .parse(process.argv);

if (!program.project) {
  console.error(kuler('You did not specify a name project to copy canvas to .. ', 'orange'));
  process.exit(0);
}

const pathToClient = path.resolve(__dirname, '../../client');
const pathToRoot = path.resolve(__dirname, '../../');
const pathToMobicoopBundle = path.resolve(pathToClient, 'src/MobicoopBundle');
const destinationProject = path.resolve(__dirname, `../../../${program.project}`);
const pathToClientAssets = path.resolve(pathToMobicoopBundle, 'Resources/assets');
const destinationAssets = path.resolve(destinationProject, 'assets');


/** -------------------------------------------------
                Start the creation
*-----------------------------------------------------*/
createCanvas().then(_ => {
  crawlDir(destinationAssets);
  replaceDataInCanvas().then(_ => console.log('ok')).catch(err => console.error(err))
})



/**
 * Main function to create new client
 */
async function createCanvas() {

  /**
   * Create the dir folder base on name sent
   */
  let err, exists, success, folders;
  [err, exists] = await to(fs.mkdirp(destinationProject));
  if (err) {
    console.error(kuler(`Path specified is not writable or already exists! ${destinationProject} \n`, 'red'));
    console.error(err);
    process.exit(0);
  }

  /**
   * Copy all client files but filters to destination
   */
  const filter = {
    elementToExclude: ['MobicoopBundle', 'node_modules', 'vendor', 'var', 'cypress.json', 'phpunit.xml.dist', 'assets', 'package-lock', 'kahlan-config.php', 'tests', 'package.json'],
    extToExclude: ['.lock'],
    filter: function (currentPath) {
      if (this.elementToExclude.includes(path.basename(currentPath)) || this.extToExclude.includes(path.extname(currentPath))) { return false; }
      return true;
    }
  };

  console.log(kuler(`Copying files to ${destinationProject}\n`, 'green'));
  [err, success] = await to(fs.copy(pathToClient, destinationProject, filter));

  if (err) {
    console.error(kuler('Cannot copy to specified path!\n', 'red'));
    console.error(err);
    process.exit(0);
  }

  /**
   * Create Assets structure
   */
  const filterAssets = {
    filter: function (currentPath) {
      let assetsToKeep = ['_variables.scss']
      let basename = path.basename(currentPath);
      let stats = fs.lstatSync(currentPath);
      if (!stats.isDirectory() && !assetsToKeep.includes(basename)) { return false; }
      return true;
    }
  };

  fs.mkdirp(destinationAssets);

  console.log(kuler(`Creating assets structure into ${destinationAssets} \n`, 'green'));
  [err, success] = await to(fs.copy(pathToClientAssets, destinationAssets, filterAssets));
  if (err) {
    console.error(kuler('Cannot copy to specified path!\n', 'red'));
    console.error(err);
    process.exit(0);
  }

  /**
   * Copy specific client assets (client-canvas-folder)
   */
  console.log(kuler(`Copying specific assets for ${destinationAssets} 🚀 \n`, 'pink'));
  let appjs = path.resolve(__dirname, 'client-canvas/app.js');
  let gitignore = path.resolve(__dirname, 'client-canvas/docker-compose-builder-darwin.yml');
  let dcbl = path.resolve(__dirname, 'client-canvas/docker-compose-builder-linux.yml');
  let dcbd = path.resolve(__dirname, 'client-canvas/docker-compose-builder-linux.yml');
  let dcl = path.resolve(__dirname, 'client-canvas/docker-compose-linux.yml');
  let dcd = path.resolve(__dirname, 'client-canvas/docker-compose-darwin.yml');
  let gitlabci = path.resolve(__dirname, 'client-canvas/.gitlab-ci');
  let packagejson = path.resolve(__dirname, 'client-canvas/package.json');
  let makefile = path.resolve(__dirname, 'client-canvas/makefile');
  let entryBuilder = path.resolve(__dirname, 'client-canvas/entrypoint-builder.sh');
  let entry = path.resolve(__dirname, 'client-canvas/entrypoint.sh');
  [err, success] = await to(fs.copy(appjs, `${destinationAssets}/js/app.js`));
  [err, success] = await to(fs.copy(dcbd, `${destinationProject}/docker-compose-builder-darwin.yml`));
  [err, success] = await to(fs.copy(dcbl, `${destinationProject}/docker-compose-builder-linux.yml`));
  [err, success] = await to(fs.copy(dcd, `${destinationProject}/docker-compose-darwin.yml`));
  [err, success] = await to(fs.copy(dcl, `${destinationProject}/docker-compose-linux.yml`));
  [err, success] = await to(fs.copy(packagejson, `${destinationProject}/package.json`));
  [err, success] = await to(fs.copy(gitlabci, `${destinationProject}/.gitlab-ci`));
  [err, success] = await to(fs.copy(makefile, `${destinationProject}/makefile`));
  [err, success] = await to(fs.copy(entryBuilder, `${destinationProject}/entrypoint-builder.sh`));
  [err, success] = await to(fs.copy(entry, `${destinationProject}/entrypoint.sh`));

}

/**
 * Replace some text to help the creation of the new client platform
 */
async function replaceDataInCanvas() {
  const options = {
    files: `${destinationProject}/**/*`,
    from: /\$\$NAME\$\$/gi,
    to: `${program.project}_platform`,
  };
  await replace(options);
}


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
  });
}