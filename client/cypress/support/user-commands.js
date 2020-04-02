'use scrit';

import '@percy/cypress';
// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
//
const baseUrl = Cypress.env("baseUrl");

//Login
Cypress.Commands.add('loginWith', (email, password) => {

  cy.get('[href="/utilisateur/connexion"] > .v-btn__content')
    .contains('Connexion').click();
  cy.url().should('include', baseUrl + 'utilisateur/connexion');

  /* Email */
  cy.get('#email').click()
    .type(email);

  /* Password */
  cy.get('#password').click()
    .type(password);

    cy.get('#formLogin > .v-btn > .v-btn__content').click();
});

//Logout
Cypress.Commands.add('logout', (email, password) => {
  cy.get('.buttons > [href="/user/logout"]')
    .click();
});

//Home
Cypress.Commands.add('home', () => {
  cy.get('.logo')
    .click();
  cy.url().should('include', baseUrl);
  cy.wait(1500);
});

//SignUp
Cypress.Commands.add('signUp', (email, password, lastname, name, phone) => {

  cy.get('[href="/utilisateur/inscription"] > .v-btn__content').click();
  cy.url().should('include', baseUrl + 'utilisateur/inscription');
  cy.wait(2500)

  /* Email */
  cy.get('#email')
    .type(email);

  /* PhoneNumber*/
  cy.get('#telephone')
    .type(phone);

  /* Password*/
  cy.get('#password')   
    .type(password);

    
  /* Next */
  cy.get('#buttonNext1')
    .click();

  /* GivenName */
  cy.get('#givenName')    
    .type(lastname);

  /* FamilyName */
  cy.get('#familyName')
    .type(name);

  /* Gender */
  cy.get('#step2 > .v-select > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections').click();
  cy.contains('Monsieur').click();

   /* Birthyear */
   cy.get('#birthday').click({force:true});
   cy.get('.v-date-picker-years > :nth-child(7)') .click();
   cy.contains('juin').click();
   cy.get(':nth-child(4) > :nth-child(4) > .v-btn > .v-btn__content').click();

  /* Next */
  cy.get('#step2 > .row > [type="submit"] > .v-btn__content')
    .click();

  // /* HomeTown */
  // cy.get('#address')
  //   .type('Nancy');
  // cy.get('#content')
  //     .contains('Nancy').click();
  // cy.wait(2500);


  /* Validation condition (confirmation) */
  cy.get('.v-input--selection-controls__ripple')
      .click();

  /* Subscribe */
  cy.get('.mr-4 > .v-btn__content')
    .click()
  cy.url().should('include', baseUrl); // should be redirected to home
  
  /* Account validation */
  cy.get('.v-alert__wrapper') ;

});

/**delete**/
Cypress.Commands.add('delete', () => {

// close snackbar
cy.get('.v-snack__content > .v-btn > .v-btn__content > .v-icon').click();

cy.get('[data-v-33788174=""][type="button"] > .v-btn__content').trigger('mouseenter') 
cy.get(':nth-child(3) > a > .v-list-item__title').should('contain', 'Profil')
  .click();

cy.url().should('include', baseUrl + 'utilisateur/profil');
cy.get('.text-center > .button > .v-btn__content')
  .click();
cy.url().should('include', baseUrl + 'utilisateur/profil/modifier/mon-profil');
cy.get('.v-card__actions > a.v-btn > .v-btn__content')
  .click();
cy.get('.v-snack__content')
  .contains ('Votre compte à été supprimé avec succès.')

// cy.url().should('include', baseUrl);
});

//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This is will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })
