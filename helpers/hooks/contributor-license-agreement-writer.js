const fs = require('fs');
const path = require('path');

agreementSample = function(country, date, name, surname, gitEmail, gitUserName){

    return `*************************************************************************************

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

exports.addContributor = function(fileName, country, date, name, surname, gitEmail, gitUserName){

    fs.writeFile('ContributorLicenseAgreement/' + fileName, agreementSample(country,date,name,surname,gitEmail,gitUserName), (err) => {
        if (err) {
            throw err;
            console.log("err")
            process.exit(1);
        }
        console.log('The file has been saved!');
    });
}

