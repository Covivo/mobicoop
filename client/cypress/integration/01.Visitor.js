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
  const email = "johndoe@fakemail.com"
  let password = "OldPassord!*$"

  beforeEach(() => {
    cy.visit(baseUrl)
  })

  it('A visitor comes back to home when he clicks to logo on the website', () => {
    cy.home()
  })

  it('A visitor signs up', () => {
    let lastname = 'John'
    let name = 'Doe'
    let gender = '1'
    let birthyear = '1987'
    let phone = '0612345678'

    cy.signUp(email, password, lastname, name, gender, birthyear, phone)

    cy.logout()
  })

  it('A vistor searches an proposal with no result', () => {

    /* Departure */
    cy.get('.control > #origin')
      .should('have.attr', 'placeholder', 'Lieu de départ')
      .type('Metz')
    cy.get('[data-v-12259723]')
      .contains('Metz')
      .click()

    /* To */
    cy.get('.control > #destination')
      .should('have.attr', 'placeholder', 'Lieu d\'arrivée')
      .type('Strasbourg')
    cy.get('[data-v-12259723]')
      .contains('Strasbourg')
      .click()

    /* Search */
    cy.get('#rechercher')
      .click()

    cy.get('#app > :nth-child(3)').contains('Pas de conducteur trouvé')
    cy.get('#app > :nth-child(4)').contains('Pas de passager trouvé.')
    cy.get('.column > .tag').contains('Pas de voyage trouvé.')
  })

  it('A visitor searches an proposal with no result', () => {

    // In order to have a proposal in database
    let email = "simonmartin@fakemail.com"
    let password = "Passpass!*$"
    let lastname = 'Simon'
    let name = 'Martin'
    let gender = '1'
    let birthyear = '1987'
    let phone = '0612345678'

    cy.loginWith(email, password)
    // cy.signUp(email, password, lastname, name, gender, birthyear, phone)
    cy.addProposal()
    
    cy.home()

    /* Departure */
    cy.get('.control > #origin')
      .should('have.attr', 'placeholder', 'Lieu de départ')
      .type('Metz')
    cy.get('[data-v-12259723]')
      .contains('Metz')
      .click()

    /* To */
    cy.get('.control > #destination')
      .should('have.attr', 'placeholder', 'Lieu d\'arrivée')
      .type('Marseille')
    cy.get('[data-v-12259723]')
      .contains('Marseille')
      .click()

    /* Search */
    cy.get('#rechercher')
      .click()

    cy.get('#app > :nth-child(3)').contains('Pas de conducteur trouvé')
    cy.get('#app > :nth-child(4)').contains('Pas de passager trouvé.')
    cy.get('.column > .tag').contains('Pas de voyage trouvé.')
  })
})