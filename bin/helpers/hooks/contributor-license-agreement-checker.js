const fs = require('fs');
const path = require('path');
const kuler = require('kuler');
const gitUserName = require('git-user-name');
const repoName = require('git-repo-name');
const readline = require('readline');
const reader = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});
const claFolderPath = path.resolve(__dirname, '../../../docs/ContributorLicenseAgreement');
const copyright = fs.readFileSync(path.resolve(__dirname, '../copyright.txt'))

//CHECK IF AM ON MOBICOOP's REPO
if (repoName.sync() == "mobicoop") {
    if (fs.existsSync(claFolderPath + "/" + gitUserName() + "_Agreement.txt")) {
        console.log(kuler(`Contributor License Agreement accepted 😊 `, 'green'))
        process.exit(0)
    }
    console.log(kuler(` It seems that you have not accepted our Contributor License Agreement yet.\r \n If you want to contribute to Mobicoop, you first need to accept this statement :\n`, "red"))
    console.log(kuler(copyright, 'fdd000'));
    console.log(kuler(`Please run 'npm run contribute' in order to sign our Contributor License Agreement`, 'red'));
    process.exit(1)
}