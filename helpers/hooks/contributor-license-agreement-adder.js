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
var completed = false
const claWriter = require('./contributor-license-agreement-writer.js')
const claFolderPath = path.resolve(__dirname, '../../ContributorLicenseAgreement');
const claPath = path.resolve(__dirname, '../../ContributorLicenseAgreement/ContributorLicenseAgreement.txt');
const copyright = fs.readFileSync(path.resolve(__dirname, '../copyright.txt'))
    var readlineSync = require('readline-sync');

//CHECK IF AM ON MOBICOOP's REPO
if(repoName.sync() == "mobicoop"){
    if(fs.existsSync(claFolderPath + "/" + gitUserName() + "_Agreement.txt")){
        console.log(kuler(`Contributor License Agreement already accepted 😊 `,'green'))
        process.exit(0)
    }
    console.log(kuler(` It seems that you have not accepted our Contributor License Agreement yet.\r \n If you want to contribute to Mobicoop, you first need to accept this statement :\n`,"red"))
    console.log(kuler(copyright, 'fdd000'));
    console.log("\nPlease fill the following informations : ");
    reader.question('<Your Country> : ', (country) => {
        reader.question('<Your Surname> : ', (surname) => {
            reader.question('<Your Name> : ', (name) => {
                reader.question('<Your Git Email> : ', (gitEmail) => {
                    let date = new Date(Date.now()).toLocaleString();
                    console.log(kuler(finalResultToShow(country, date, name, surname, gitEmail, gitUserName())).style('fdd000'));
                    console.log('\nThis will be saved in /ContributorLicenseAgreement/' + gitUserName() + '_Agreement.txt');
                    reader.question('Would you like to sign this agreement ? (Y/n)', (answer) => {
                        let validAnswer = ['yes','Y','y',''];
                        if (validAnswer.includes(answer)){
                            claWriter.addContributor(gitUserName() + '_Agreement.txt', country, date, name, surname, gitEmail, gitUserName())
                            reader.close();
                            completed = true
                            //continue push
                            process.exit(0);
;
                        }
                        console.log(kuler(`\nCancelling push ...`,'red'))
                        reader.close();
                        process.exit(0);
                    });
                });
            });
        });
    });
}

function finalResultToShow(country, date, name, surname, gitEmail, gitUserName){

    return `\n*************************************************************************************
    ${country} | ${date}

    I hereby agree to the terms of the Mobicoop Contributor License
    Agreement.

    I declare that I am authorized and able to make this agreement and sign
    this declaration.

    Signed,

    ${name} ${surname}
    ${gitEmail} | ${gitUserName}
*************************************************************************************`;

}