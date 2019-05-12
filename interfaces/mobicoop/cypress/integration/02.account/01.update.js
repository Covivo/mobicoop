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

describe('Update account', () => {

  const baseUrl = Cypress.env("baseUrl");

  it('Home', () => {
    /* Home */
    cy.visit(baseUrl)
    cy.contains('Connexion').click()
    cy.url().should('include', baseUrl + 'utilisateur/connexion')

    /* Connexion */
    // Email
    cy.get('input[id=user_login_form_username]')
      .should('have.attr', 'placeholder', 'Saisissez votre adresse email')
      .type('totosmith@email.com')

    // Password
    cy.get('input[id=user_login_form_password]')
      .should('have.attr', 'placeholder', 'Saisissez votre mot de passe')
      .type('motdepasse')

    cy.get('button[id=user_login_form_login]').click()


    /* Profil */
    cy.contains('Mon profil').click()
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
