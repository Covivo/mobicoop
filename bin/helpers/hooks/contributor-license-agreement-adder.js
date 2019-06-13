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
const claWriter = require('./contributor-license-agreement-writer.js');
const claFolderPath = path.resolve(__dirname, '../../../docs/ContributorLicenseAgreement');
const claPath = path.resolve(__dirname, '../../../docs/ContributorLicenseAgreement/ContributorLicenseAgreement.txt');
const copyright = fs.readFileSync(path.resolve(__dirname, '../copyright.txt'));

//CHECK IF AM ON MOBICOOP's REPO
if (repoName.sync() == "mobicoop") {
    if (fs.existsSync(claFolderPath + "/" + gitUserName() + "_Agreement.txt")) {
        console.log(kuler(`Contributor License Agreement already accepted 😊 `, 'green'));
        process.exit(0)
    }
    console.log(kuler(` It seems that you have not accepted our Contributor License Agreement yet.\r \n If you want to contribute to Mobicoop, you first need to accept this statement :\n`, "red"));
    console.log(kuler(copyright, 'fdd000'));
    console.log("\nPlease fill the following informations : ");
    reader.question('<Your Country> : ', (country) => {
        reader.question('<Your Surname> : ', (surname) => {
            reader.question('<Your First name> : ', (name) => {
                reader.question('<Your Git Email> : ', (gitEmail) => {
                    let date = new Date(Date.now()).toLocaleString();
                    console.log(kuler(finalResultToShow(country, date, name, surname, gitEmail, gitUserName())).style('fdd000'));
                    console.log('\nThis will be saved in docs/ContributorLicenseAgreement/' + gitUserName() + '_Agreement.txt');
                    reader.question('Would you like to sign this agreement ? (Y/n)', (answer) => {
                        let validAnswer = ['yes', 'Y', 'y', ''];
                        if (answer.includes(answer)) {
                            let hasBeenWritten = claWriter.addContributor(gitUserName() + '_Agreement.txt', country, date, name, surname, gitEmail, gitUserName());
                            reader.close();
                            if (hasBeenWritten) {
                                console.log(kuler(`The file has been saved! Don't forget to git add your Contribution Agreement file`, 'green'));
                                process.exit(0)
                            }
                            console.log(kuler(`The file has not been saved!`, 'red'));
                            process.exit(1)
                        }
                        console.log(kuler(`\nCancelling...`, 'red'));
                        reader.close();
                        process.exit(0);
                    });
                });
            });
        });
    });
}

function finalResultToShow(country, date, name, surname, gitEmail, gitUserName) {

    return `\nMobicoop Contributor License Agreement v1.0

    *****************************************************************************************************************************
    IMPORTANT - PLEASE READ CAREFULLY:
    This document ("Agreement") constitutes a legal agreement. By signing this Agreement below, You, either an individual or the 
    organization indicated below ("You"), agree to be legally bound. You may want to consult an attorney before signing. You 
    acknowledge that you are entering into this Agreement in consideration of the opportunity to contribute to and participate in 
    Mobicoop's open source software projects, which opportunity is of value to You. By signing this agreement you represent and 
    warrant that you are at least 18 years of age.
    *****************************************************************************************************************************
    
    "Submission" means any work of authorship, software code, documentation, creation, images or sound, provided by You to 
    Mobicoop via Mobicoop's official project submission system, in human or machine readable form, at any time (both prior and 
    subsequent to Your execution of this Agreement).
    
    You hereby grant Mobicoop a perpetual, worldwide, royalty-free, irrevocable, non-exclusive, and transferable license to use, 
    reproduce, prepare derivative works of, publicly display, publicly perform, distribute the Submissions, and to sublicense 
    such rights to others. The rights granted may be exercised in any form or format, and Mobicoop may distribute and sublicense 
    to others on any licensing terms, including without limitation: (a) open source licenses like the Affero GNU General Public 
    License (AGPL); or (b) binary, proprietary, or commercial licenses. If Your Submission is derived from software released by 
    Mobicoop under the AGPL, Mobicoop as licensor thereof waives such requirements of the AGPL as applied to that software to the 
    limited extent necessary to allow you to provide the Submission and the foregoing license to Mobicoop.
    You hereby grant Mobicoop a perpetual, worldwide, royalty-free, irrevocable, non-exclusive, sublicenseable and transferable 
    license under any patent You own or control, now or in the future, to make, have made, use, sell, offer for sale, or import 
    Submissions or any modifications thereof, including without limitation any combinations of the Submissions or modifications 
    thereof with software, technology or services of Mobicoop or its affiliates.
    
    You hereby represent that you are the sole and original author of all Submissions and that, to the best of your knowledge, 
    the Submissions do not infringe upon the rights of any third party. If you are providing the Submission on behalf of an 
    organization of which you are not an employee or if you are providing on behalf of an organization of which you are an 
    employee, the person signing this Agreement represents that he or she is expressly authorized to execute this Agreement on 
    that organization's behalf. Except for the expressed representations set forth above, the Submission and all licenses 
    granted above are made on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, EITHER EXPRESSED OR IMPLIED, 
    INCLUDING, WITHOUT LIMITATION, ANY WARRANTIES OR CONDITIONS OF MERCHANTABILITY, or FITNESS FOR A PARTICULAR PURPOSE. You 
    acknowledge that the decision to include the Submission in any code base is entirely the decision of Mobicoop, and this 
    Agreement does not guarantee that the Submissions will be included in any code base. The parties agree that any facsimile 
    copy of this Agreement will be binding upon the parties to the same effect as originals.
    
    *****************************************************************************************************************************
    
    ${country}, ${date}
    
    I hereby agree to the terms of the Mobicoop Contributor License Agreement.
    I declare that I am authorized and able to make this agreement and sign this declaration.
    
    Signed,
    ${name} ${surname}
    ${gitEmail} | ${gitUserName}
    
    *************************************************************************************`;

}