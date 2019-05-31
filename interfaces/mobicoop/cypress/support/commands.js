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
//Login
Cypress.Commands.add('loginWith', (email, password) => {


    /* Email */
    cy.get('input[id=user_login_form_username]')
        .should('have.attr', 'placeholder', 'adresse email')
        .type(email)

    cy.wait(1500)
    cy.percySnapshot('Login')

    /* Password */
    cy.get('input[id=user_login_form_password]')
        .should('have.attr', 'placeholder', 'mot de passe')
        .type(password)

    cy.get('button[id=user_login_form_login]').click()
    cy.percySnapshot('Loged')
});
//Logout
Cypress.Commands.add('logout', (email, password) => {
    cy.get('.buttons > [href="/user/logout"]')
        .click()
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