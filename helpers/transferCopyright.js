const fs = require('fs');
const path = require('path');
const kuler = require('kuler');
const readline = require('readline');
const reader = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});



// Mobicoop's copyright that has to be changed
const oldCopyright = `/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/`;

/**
 *
 * @param filePath - path of a file
 * @param oldCopyright - text going to be replaced
 * @param replacement - text that will replace the old one
 */
function replaceText(filePath,oldCopyright,replacement){
    fs.readFile(filePath, 'utf8', function (err,data) {
        if (err) {
            return console.log(err);
        }
        var result = data.replace(oldCopyright, replacement);
        //If you need to reverse the process uncomment the line below ⬇
        //var result = data.replace(replacement, oldCopyright);

        fs.writeFile(filePath, result, 'utf8', function (err) {
            if (err) return console.log(err);
        });
    });
}

/**
 * @param dir - directory of the bundle in which we get all files
 * @returns an array containing every path file of the directory given
 */
const getAllFiles = (dir,replacement) =>
    fs.readdirSync(dir).reduce((files, file) => {
        const name = path.join(dir, file);
        //If it is a php file we replace the old copyright in it by the new one
        if (name.includes('.php')) {
            replaceText(name, oldCopyright, replacement)
        }
        const isDirectory = fs.statSync(name).isDirectory();
        return isDirectory ? [...files, ...getAllFiles(name,replacement)] : [...files, name];
    }, []);

function transferCopyright() {
    let dirname = path.resolve(__dirname);
    //We ask for bundle directory path & the new copyright that will replace Mobicoop's one
    reader.question(kuler(`Where is <Bundle Path> from ${dirname}: \n`, 'yellow'), (bundlePath) => {
        reader.question(kuler(`Select the file that contains the new copyright from ${dirname} :`, 'yellow')
            +kuler('\r\n(Do not forget to add /* */ (multiline comment) to your text !) \n','red'), (replacementPath) => {

            let bundlePathResolved = path.resolve(__dirname, bundlePath);
            let replacementPathResolved = path.resolve(__dirname, replacementPath);

            //Don't forget to add /* */ (multiline comment) to your text
            const replacement = fs.readFileSync(replacementPathResolved,'utf8');
            getAllFiles(bundlePathResolved,replacement);

            //If any error occurred process has been successfully executed
            console.log(kuler(`files' copyright have been successfully replaced`, 'green'));
            console.log(kuler(`You can now press CTRL+C to exit this script 😀 !`,'green'))
        });
    });
}

transferCopyright();