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

describe('Visitor & User ', () => {

    const baseUrl = Cypress.env("baseUrl");
    const email = "johndoe@fakemail.com"
    let password = "OldPassord!*$"
    const newPassword = "NewPassord$**"


    it('A visitor comes back to home when he clicks to logo on the website', () => {
        cy.visit(baseUrl)
        cy.get('.logo')
            .click()
        cy.url().should('include', baseUrl)
    })

    it('A visitor signs up', () => {

        cy.contains('Inscription').click()
        cy.url().should('include', baseUrl + 'utilisateur/inscription')
        cy.wait(1500)
        cy.percySnapshot('Home')

        /* Email */
        cy.get('input[id=user_form_email]')
            .should('have.attr', 'placeholder', 'Saisissez votre adresse email')
            .type(email)

        /* Lastname */
        cy.get('input[id=user_form_givenName]')
            .should('have.attr', 'placeholder', 'Saisissez votre prénom')
            .type('John')

        /* Name */
        cy.get('input[id=user_form_familyName]')
            .should('have.attr', 'placeholder', 'Saisissez votre nom')
            .type('Doe')

        /* Gender */
        cy.get('select[id=user_form_gender]')
            .select('1')
            .should('have.value', '1')

        /* Birthyear */
        cy.get('select[id=user_form_birthYear]')
            .select('2000')
            .should('have.value', '2000')

        /* Phone */
        cy.get('input[id=user_form_telephone]')
            .should('have.attr', 'placeholder', 'Saisissez votre numéro de téléphone')
            .type('0610111213')

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

    it('A user logged updates his password', () => {

        /* Account */
        cy.contains('Mon profil')
            .click()
        cy.loginWith(email, password)
        cy.url().should('include', baseUrl + 'utilisateur/profil')

        /* Update */
        cy.contains('Mot de passe')
            .click()
        cy.url().should('include', baseUrl + 'utilisateur/mot-de-passe/modifier')

        /* Password */
        cy.get('#user_form_password_first')
            .should('have.attr', 'placeholder', 'Saisissez votre mot de passe')
            .type(newPassword)

        /* Password */
        cy.get('#user_form_password_second')
            .should('have.attr', 'placeholder', 'Confirmez votre mot de passe')
            .type(newPassword)

        /* Submit */
        cy.get('#user_form_submit')
            .click()
    })

    it('A user logged updates his account', () => {
        let password = newPassword
            /* Account */
        cy.contains('Mon profil').click()
        cy.loginWith(email, password)
        cy.url().should('include', baseUrl + 'utilisateur/profil')

        /* Update */
        cy.contains('Mettre à jour').click()
        cy.url().should('include', baseUrl + 'utilisateur/profil/modifier')

        /* Gender */
        cy.get('select[id=user_form_gender]')
            .select('2')
            .should('have.value', '2')

        // Change phone number
        cy.get('input[id=user_form_telephone]').clear()
            .should('have.attr', 'placeholder', 'Saisissez votre numéro de téléphone')
            .type('0610111214')

        cy.get('button[id=user_form_submit]').click()
        cy.url().should('include', baseUrl) // should be redirected to home   
    })
})