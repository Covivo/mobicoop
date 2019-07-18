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


  cy.contains('Connexion').click()
  cy.url().should('include', baseUrl + 'utilisateur/connexion')

  /* Email */
  cy.get('input[id=user_login_form_username]')
    .should('have.attr', 'placeholder', 'adresse email')
    .type(email)

  /* Password */
  cy.get('input[id=user_login_form_password]')
    .should('have.attr', 'placeholder', 'mot de passe')
    .type(password)

  cy.get('button[id=user_login_form_login]').click()
});

//Logout
Cypress.Commands.add('logout', (email, password) => {
  cy.get('.buttons > [href="/user/logout"]')
    .click()
});

//Home
Cypress.Commands.add('home', () => {
  cy.get('.logo')
    .click()
  cy.url().should('include', baseUrl)
  cy.wait(1500)
});

//SignUp
Cypress.Commands.add('signUp', (email, password, lastname, name, gender, birthyear, phone) => {

  cy.contains('Inscription').click()
  cy.url().should('include', baseUrl + 'utilisateur/inscription')
  cy.wait(2500)

  /* Email */
  cy.get('.email > input')
    .should('have.attr', 'placeholder', 'Email')
    .type(email)

  /* PhoneNumber*/
  cy.get('.telephone>input')
    .should('have.attr', 'placeholder', 'Numéro de téléphone')
    .type(phone)

  /* Password*/
  cy.get('.password > input')
    .should('have.attr', 'placeholder', 'Mot de passe')
    .type(password)

  /* Next */
  cy.get('.wizard-btn').contains('Suivant')
    .click()

  /* GivenName */
  cy.get('.givenName > input')
    .should('have.attr', 'placeholder', 'Prénom')
    .type(lastname)

  /* FamilyName */
  cy.get('.familyName > input')
    .should('have.attr', 'placeholder', 'Nom')
    .type(name)


  /* Next */
  cy.get('.wizard-btn').contains('Suivant')
    .click()

  /* Gender */
  cy.get('.gender select')
    .select(gender)
    .should('have.value', gender)



  /* Next */
  cy.get('.wizard-btn').contains('Suivant')
    .click()

  /* Birthyear */
  cy.get('.birthYear select')
    .select(birthyear)
    .should('have.value', birthyear)


  /* Next */
  cy.get('.wizard-btn').contains('Suivant')
    .click()

  /* Validation condition (confirmation) */
  cy.get('.b-checkbox > .check')
    .click()

  /* HomeTown */
  cy.get('.control > #homeAddress')
    .type('metz')
  cy.wait(2000)
  cy.get('.media')
    .click()

  /* Subscribe */
  cy.get('.wizard-footer-right > span > .wizard-btn')
    .click()
  cy.url().should('include', baseUrl) // should be redirected to home   
});

//delete
Cypress.Commands.add('delete', () => {
  cy.contains('Mon profil').click()
  cy.url().should('include', baseUrl + 'utilisateur/profil')
  cy.get(':nth-child(4) > a').contains('delete').click()
  cy.url().should('include', baseUrl + 'utilisateur/profil/supprimer')
  cy.get('#user_delete_form_submit')
    .click()
  cy.url().should('include', baseUrl)
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
