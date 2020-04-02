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

Cypress.Commands.add('addProposal', () => {

  /* Share a proposal PONCTUAL - DRIVER - ONE WAY homepage*/

  cy.get('.v-toolbar__content > .v-btn--contained > .v-btn__content').click()

  /* From */
 cy.get('#from > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address')
    .type('Nancy');
  cy.get('[aria-labelledby="list-item-281-0"] > .v-list > #content')
    .contains('Nancy').click();
  cy.wait(2500); 

    

  /* To */
  cy.get('#to > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address')
    .type('Metz');
  cy.get('[aria-labelledby="list-item-328-0"] > .v-list > #content')
    .contains('Metz').click();
  cy.wait(2500);
  
  /* Date */
  cy.get('#date').click({force:true});

  /* Month Year*/
  cy.get(':nth-child(3) > :nth-child(1) > .v-btn > .v-btn__content').click();

  /* Redirection */
  cy.url().should('include', baseUrl + 'covoiturage/annonce/poster');

  /* Next */
  cy.get('[mt-5=""] > .v-btn > .v-btn__content').click();
  
  /* Departure time */
  cy.get('#outwartTime').click({force:true});
  cy.get('.v-time-picker-clock__item--active > span').trigger('mouseenter') 
    .first().click();
  cy.get('.v-time-picker-clock__item--active > span')
    .last().click();

  /* Next */
  cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click();

  /* Next */
  cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click();

  /* Next */
  cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click();

  /* Next */
  cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click();

  /* Next */
  cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click();

  /* Publish */
  cy.get('[mt-5=""] > :nth-child(3) > .v-btn > .v-btn__content').click();

  /* Redirection */
  cy.url().should('include', baseUrl + 'utilisateur/profil/modifier/mes-annonces');
});
