/**
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
**************************/

describe('Visitor / home', () => {

  const baseUrl = Cypress.env("baseUrl");
  //TODO Check if user is not present in the database, delete it if exists

  beforeEach(() => {
    cy.visit(baseUrl);
  });

  it('A visitor signs up and validates his account', () => {
    // In order to have a proposal in database
    // let randNb = Math.floor(Math.random() * 3000) + 1
    // let email = `mobicoop-${randNb}@fakemail.com`
    let email = 'first_user_mobicoop@yopmail.com'
    let password = 'Mobicoop54'
    let lastname = 'John'
    let name = 'Doe'
    let phone = '0612345678'

    cy.signUp(email, password, lastname, name, phone);

    cy.get('#token').click()
      .type('b0d5528165ec74fb4f9afd8772a2549fe24c57892cd9642cf991bbdf43ef6529');
    cy.get('#formLoginValidation > .v-btn > .v-btn__content').click();
    cy.get('.v-alert__content > :nth-child(1)')     
      .contains('Bravo John !');

    cy.wait(3000); // error no redirection to home

  });

  // it('A user logs in and add a proposal', () => {
  //   let email = "first_user_mobicoop@yopmail.com";
  //   let password = "Mobicoop54";

  //   cy.loginWith(email, password);
  //   cy.wait(600);

  //   // cy.percySnapshot('logged_home');

  //   cy.addProposal();

  //   cy.wait(600);
  // });

  // // CREATE AN OTHER USER
  // it('A second  user is created', () => {
  //   // In order to have a proposal in database
  //   let email = 'second_user_mobicoop@yopmail.com'
  //   let password = 'Mobicoop54'
  //   let lastname = 'John'
  //   let name = 'Smith'
  //   let phone = '0611111111'

  //   cy.signUp(email, password, lastname, name, phone);

  //   cy.get('#token').click()
  //     .type('b0d5528165ec74fb4f9afd7654a6543fe24c57892cd9642cf991bbdf43ef9878');
  //   cy.get('#formLoginValidation > .v-btn > .v-btn__content').click();
  //   cy.get('.v-alert__content > :nth-child(1)')     
  //     .contains('Bravo John !');

  //   cy.wait(3000); // error no redirection to home  });
  // });

  //  it('A user log in, add a proposal and find a result', () => {
  //   let email = "second_user_mobicoop@yopmail.com";
  //   let password = "Mobicoop54";
  //   cy.loginWith(email, password);
  //   cy.wait(600);

  //   cy.addProposal();
  //   cy.wait(600);

  //   cy.get('.col-6 > .v-btn > .v-btn__content')
  //   .click();
  //   cy.contains('p','1 annonce en covoiturage trouvée' )

  //   cy.get('.v-btn__content > span').click();

  //   cy.contains('.v-dialog > :nth-child(1) > .v-sheet--tile > .v-toolbar__content','Détail du trajet')
  //   cy.wait(12000);

  //   cy.contains('Covoiturer comme passager').click();
  //   cy.wait(6000);


  //   cy.url().should('include', baseUrl + 'utilisateur/messages');

  // });

  // it('A user checks his messages', () => {
  //   let email = "first_user_mobicoop@yopmail.com";
  //   let password = "Mobicoop54";
  //   cy.loginWith(email, password);
    
  //   cy.get('.v-toolbar__items > a.v-btn > .v-btn__content')
  //     .click();

  //   cy.url().should('include', baseUrl + 'utilisateur/messages');
  //   cy.wait(6000);
  // });

  it('A User goes to his account and deletes it ', () => {
    let email = "first_user_mobicoop@yopmail.com"
    let password = "Mobicoop54"

    cy.loginWith(email, password);
    cy.delete();
  });

  // it('An User goes to his account and deletes it ', () => {
  //   let email = "second_user_mobicoop@yopmail.com"
  //   let password = "Mobicoop54"

  //   cy.loginWith(email, password);

  //   cy.delete();
  // });
})


  /** Functionnal testing - TODO**/

  // it('A visitor searches a proposal with a result', () => {
  //   /* Departure */
  //   cy.get('.control > #origin')
  //     .should('have.attr', 'placeholder', 'Lieu de départ')
  //     .type('Metz')
  //   cy.wait(2500)

  //   cy.get('[data-v-12259723]')
  //     .contains('Metz')
  //     .click()

  //   /* To */
  //   cy.get('.control > #destination')
  //     .should('have.attr', 'placeholder', 'Lieu d\'arrivée')
  //     .type('Marseille')
  //   cy.wait(1500)
  //   cy.get('[data-v-12259723]')
  //     .contains('Marseille')
  //     .click()


  //   /* Search */
  //   cy.get('#rechercher')
  //     .click()

  //   // In order to have a result
  //   cy.get('#dateDepart')
  //     .click()
  //   cy.get(':nth-child(1) > .datepicker > .dropdown > .dropdown-menu > .dropdown-content > .dropdown-item > :nth-child(1) > .pagination > .pagination-list > .field > :nth-child(1) > .select > select').select('Juin')
  //   cy.get(':nth-child(1) > .datepicker > .dropdown > .dropdown-menu > .dropdown-content > .dropdown-item > :nth-child(1) > .pagination > .pagination-list > .field > :nth-child(2) > .select > select').select('2022')
  //   cy.get('.datepicker-body > :nth-child(5) > :nth-child(4)').contains('30')
  //     .click()

  //   cy.percySnapshot('search_result');

  //   cy.get('.control > #origin')
  //     .type('Metz')
  //   cy.wait(8000)
  //   cy.get('.media')
  //     .contains('Metz')
  //     .click()
  //   cy.get('.control > #destination')
  //     .type('Marseille')
  //   cy.wait(6000)
  //   cy.get(':nth-child(2) > .label > section > .field > .autocomplete > .dropdown-menu > .dropdown-content > :nth-child(1) > .media')
  //     .click()
  //   cy.get('#rechercher')
  //     .click()
  // });
  
  // it('Should not be able to connect, because user should not exists', () => {
  //   let email = "nologinmobicoop@yopmail.com";
  //   let password = "Password54!*$";
  //   cy.contains('Connexion').click();
  //   cy.get('#email')
  //     .type(email);
  //   cy.get('#password')
  //     .type(password);
  //   cy.get('#formLogin > .v-btn > .v-btn__content').click();
  //   cy.get('.v-alert__content').contains('Nom d\'utilisateur ou mot de passe incorrect');
  // });

  // it('A third visitor signs up and he validates his account with a fake token', () => {
  //   // In order to have a proposal in database
  //   let email = 'usermobicoop@yopmail.com';
  //   let password = 'Passpass54!*';
  //   let lastname = 'David';
  //   let name = 'Bowie';
  //   let phone = '0612345678';
  //   cy.signUp(email, password, lastname, name, phone);
  //   cy.get('#input-23').click()
  //     .type('c43604b7bbefdf0565901fd0c5ed638c04eb2c458bd4ac3f901ee24b02e64a7d');
  //   cy.get('#formLoginValidation > .v-btn > .v-btn__content').click();
  //   cy.get('.align-center > .col-4 > .v-alert > .v-alert__wrapper > .v-alert__content').contains('Ce code ne correspond à aucun compte enregistré')
  // });

  // it('An user wants to login but the account is not validated ', () => {
  //   let email= "nologinmobicoop@yopmail.com";
  //   let password = "Password54!*$";
  //   cy.contains('Connexion').click();
  //   cy.get('#email')
  //     .type(email);
  //   cy.get('#password')
  //     .type(password);
  //   cy.get('#formLogin > .v-btn > .v-btn__content').click();
  //   cy.get('.v-alert__content')
  //     .contains('Nom d\'utilisateur ou mot de passe incorrect');
  // });

  // it('A visitor signs up', () => {
  //   let email = "covoitmobicoop@yopmail.com";
  //   let password = "Password54!*$";
  //   let lastname = 'John';
  //   let name = 'Doe';
  //   let phone = '0612345678';
  //   cy.signUp(email, password, lastname, name, phone)
  // })

  //  it('A user signs up with an email already used ', () => {
  //   let email = "covoitmobicoop@yopmail.com";
  //   let phone = '0612345678';
  //   cy.get('[href="/utilisateur/inscription"] > .v-btn__content').click();
  //   cy.url().should('include', baseUrl + 'utilisateur/inscription');
  //   cy.wait(2500);
  //   /* Email */
  //   cy.get('#email')
  //     .type(email);
  //   /* PhoneNumber*/
  //   cy.get('#telephone')
  //     .type(phone);
  //   cy.get('.v-alert').contains('Cet email est déjà pris');
  // });

  // it('A vistor searches an proposal with no result', () => {
  //   /* Departure */
  //   cy.get('#input-30').click()
  //     .type('Metz');
  //   cy.wait(2500)
  //   cy.contains('Metz').click();
  //   /* To */
  //   cy.get('#input-44')
  //     .type('Strasbourg')
  //   cy.wait(2500)
  //   cy.contains('Strasbourg').click()
  //   /* Date */
  //   cy.get('#input-57').click({force : true})
  //   cy.get(':nth-child(3) > :nth-child(4) > .v-btn > .v-btn__content').click()
  //   /* Search */
  //   cy.get('.row > :nth-child(3) > .v-btn > .v-btn__content')      
  //   .click();
  //   cy.get(':nth-child(4) > .text-left > :nth-child(1)').contains('Aucune annonce en covoiturage trouvée...')
  //   cy.get('.text-left > .font-weight-bold').contains('Réessayer en modifiant votre recherche ? Un autre itinéraire ?')
  //   cy.get('#carpools > :nth-child(1) > :nth-child(1) > .col').contains('Aucun résultat')
  // });

  // it('A visitor comes back to home when he clicks to logo on the website', () => {
  //   // cy.percySnapshot('visitor_home');
  //   cy.home();
  // });

 


