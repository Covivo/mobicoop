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

 describe('Search an ad - user logged', () => {

  const baseUrl = Cypress.env("baseUrl");

  it('Home', () => {
    cy.visit(baseUrl)
    cy.contains('Connexion').click()
    cy.url().should('include', baseUrl + 'utilisateur/connexion')
  })

  it('Login', () => {
    /* Email */
    cy.get('input[id=user_login_form_username]')
      .should('have.attr', 'placeholder', 'Saisissez votre adresse email')
      .type('totosmith@email.com')

    /* Password */
    cy.get('input[id=user_login_form_password]')
      .should('have.attr', 'placeholder', 'Saisissez votre mot de passe')
      .type('motdepasse')

    cy.get('button[id=user_login_form_login]').click()
  })

  it('Search an ad with no result', () => {

    /* Departure */
    cy.get('.control > #origin')
      .should('have.attr', 'placeholder', 'Depuis')
      .type('Creuse')
    cy.get('[data-v-12259723]')
      .contains('Creuse')
      .click()

    /* To */
    cy.get('.control > #destination')
      .should('have.attr', 'placeholder', 'Vers')
      .type('Cré-sur-Loir')
    cy.get('[data-v-12259723]')
      .contains('Cré-sur-Loir')
      .click()

    /* Datepicker */
    cy.get('.datepicker')
      .click()
    cy.get('.datepicker-body > :nth-child(5) > :nth-child(2)')
      .contains('30')
      .click()

    /* Timepicker */
    cy.get('.timepicker > .dropdown > .dropdown-trigger > .control > .input')
      .click()

    cy.get('.is-mobicoopgreen')
      .click()

    // in order to close the window timepicker
    cy.contains('Mobicoop!')
      .click({ force: true })

    /* Search */
    cy.get('#rechercher > .button')
      .click()
  })
})