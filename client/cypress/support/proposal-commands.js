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

  /* Share a proposal */
  // cy.contains('Partager une annonce')
  cy.get('.buttons > .is-dark')
    .click()
  cy.wait(600)  
  cy.url().should('include', baseUrl + 'covoiturage/annonce/poster')

  /* Passenger or Driver */
  cy.get(':nth-child(3) > .b-radio').contains('Passager ou Conducteur')
    .click()

  /* Next */
  cy.get('.wizard-btn')
    .click()

  /* One way */
  cy.get('#Trajet2 > .fieldsContainer > :nth-child(1) > .b-radio')
    .contains('Aller')
    .click()
  cy.get('.control > #origin')
    .should('have.attr', 'placeholder', 'Depuis')
    .type('Metz')
    cy.wait(1500)
  cy.get('[data-v-12259723]')
    .contains('Metz')
    .click()

  /* To */
  cy.get('#destination')
    .should('have.attr', 'placeholder', 'Vers')
    .type('Marseille')
  cy.wait(1500)
  cy.get('[data-v-12259723]')
    .contains('Marseille')
    .click()

  /* Next */
  cy.get('.wizard-footer-right > span > .wizard-btn')
    .click()

  /* Ponctual */
  cy.get('#Fréquence4 > .fieldsContainer > :nth-child(1) > .b-radio')
    .click()

  /* One way - Date */
  cy.get(':nth-child(1) > .datepicker > .dropdown > .dropdown-trigger > .control > .input')
    .should('have.attr', 'placeholder', 'Date de départ...')
    .click()
  cy.get(':nth-child(1) > .datepicker > .dropdown > .dropdown-menu > .dropdown-content > .dropdown-item > :nth-child(1) > .pagination > .pagination-list > .field > :nth-child(1) > .select > select').select('Juin')
  cy.get(':nth-child(1) > .datepicker > .dropdown > .dropdown-menu > .dropdown-content > .dropdown-item > :nth-child(1) > .pagination > .pagination-list > .field > :nth-child(2) > .select > select').select('2022')
  cy.get(':nth-child(5) > :nth-child(4)').contains('30')
    .click()

  // in order to close the window datepicker
  cy.get('.title')
    .click({ force: true })

  /* One way - Time */
  cy.get('.timepicker > .dropdown > .dropdown-trigger > .control > .input')
    .should('have.attr', 'placeholder', 'Heure de départ...')
    .click()
    cy.get('.timepicker-footer > .is-primary')    
    .click()

  // in order to close the window timepicker
  cy.get('.title')
    .click({ force: true })

  /* Margin */
  cy.get(':nth-child(3) > .columns > .is-4 > .select > select').select('5')

  /* I share my ad */
  cy.get('.wizard-footer-right > span > .wizard-btn')  
  .click()
});
