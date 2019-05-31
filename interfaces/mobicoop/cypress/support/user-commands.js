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

    cy.wait(1500)
    cy.percySnapshot('Login')

    /* Password */
    cy.get('input[id=user_login_form_password]')
        .should('have.attr', 'placeholder', 'mot de passe')
        .type(password)

    cy.get('button[id=user_login_form_login]').click()
    cy.percySnapshot('Logged')
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
cy.percySnapshot('home')
});

//SignUp
Cypress.Commands.add('signUp', (email, password, lastname, name, gender, birthyear, phone) => {

    cy.contains('Inscription').click()
    cy.url().should('include', baseUrl + 'utilisateur/inscription')
    cy.wait(1500)
    cy.percySnapshot('signUp')

    /* Email */
    cy.get('input[id=user_form_email]')
        .should('have.attr', 'placeholder', 'Saisissez votre adresse email')
        .type(email)

    /* Lastname */
    cy.get('input[id=user_form_givenName]')
        .should('have.attr', 'placeholder', 'Saisissez votre prénom')
        .type(lastname)

    /* Name */
    cy.get('input[id=user_form_familyName]')
        .should('have.attr', 'placeholder', 'Saisissez votre nom')
        .type(name)

    /* Gender */
    cy.get('select[id=user_form_gender]')
        .select(gender)
        .should('have.value', gender)

    /* Birthyear */
    cy.get('select[id=user_form_birthYear]')
        .select(birthyear)
        .should('have.value', birthyear)

    /* Phone */
    cy.get('input[id=user_form_telephone]')
        .should('have.attr', 'placeholder', 'Saisissez votre numéro de téléphone')
        .type(phone)

    /* Password */
    cy.get('input[id=user_form_password_first]')
        .should('have.attr', 'placeholder', 'Saisissez votre mot de passe')
        .type(password)

    /* Password (confirmation) */
    cy.get('input[id=user_form_password_second]')
        .should('have.attr', 'placeholder', 'Confirmez votre mot de passe')
        .type(password)

    /* Validation condition (confirmation) */
    cy.get('input[id=user_form_conditions]').check()


    cy.contains('Je m\'inscris').click()
    cy.url().should('include', baseUrl) // should be redirected to home    
})

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