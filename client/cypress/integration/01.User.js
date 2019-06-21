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

  //TODO Check if user is not present in the database, delete it if exists

  beforeEach(() => {
    cy.visit(baseUrl)
  })

  it('Should not be able to connect, because user should not exists', () => {
    cy.contains('Connexion').click()
    cy.get('input[id=user_login_form_username]')
      .type(email)
    cy.get('input[id=user_login_form_password]')
      .type(password)
    cy.get('button[id=user_login_form_login]').click()
    cy.get('.error').contains('La connexion a échoué')

  })

  it('A vistor searches an proposal with no result', () => {

    /* Departure */
    cy.get('.control > #origin')
      .should('have.attr', 'placeholder', 'Lieu de départ')
      .type('Metz')
    cy.wait(1500)
    cy.get('[data-v-12259723]')
      .contains('Metz')
      .click()

    /* To */
    cy.get('.control > #destination')
      .should('have.attr', 'placeholder', 'Lieu d\'arrivée')
      .type('Strasbourg')
    cy.wait(1500)
    cy.get('[data-v-12259723]')
      .contains('Strasbourg')
      .click()

    /* Search */
    cy.get('#rechercher')
      .click()

    cy.get('#app > :nth-child(3)').contains('Pas de conducteur trouvé')
    cy.get('.column > .tag').contains('Pas de voyage trouvé.')
    cy.get('#app > :nth-child(4)').contains('Pas de passager trouvé.')

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

  it('An user adds a proposal', () => {
    // In order to have a proposal in database
    // let randNb = Math.floor(Math.random() * 3000) + 1
    // let email = `toto-${randNb}@fakemail.com`
    let email = "toto@fakemail.com"
    let password = "Passpass!*$"
    let lastname = 'Toto'
    let name = 'Toto'
    let gender = '1'
    let birthyear = '1987'
    let phone = '0612345678'



    cy.signUp(email, password, lastname, name, gender, birthyear, phone)
    cy.addProposal()
  })

  it('A visitor searches a proposal with a result', () => {
    /* Departure */
    cy.get('.control > #origin')
      .should('have.attr', 'placeholder', 'Lieu de départ')
      .type('Metz')
    cy.wait(1500)

    cy.get('[data-v-12259723]')
      .contains('Metz')
      .click()

    /* To */
    cy.get('.control > #destination')
      .should('have.attr', 'placeholder', 'Lieu d\'arrivée')
      .type('Marseille')
    cy.wait(1500)
    cy.get('[data-v-12259723]')
      .contains('Marseille')
      .click()

    /* Search */
    cy.get('#rechercher')
      .click()

    // In order to have a result
    cy.get('#dateDepart')
      .click()
    cy.get(':nth-child(1) > .datepicker > .dropdown > .dropdown-menu > .dropdown-content > .dropdown-item > :nth-child(1) > .pagination > .pagination-list > .field > :nth-child(1) > .select > select').select('Juin')
    cy.get(':nth-child(1) > .datepicker > .dropdown > .dropdown-menu > .dropdown-content > .dropdown-item > :nth-child(1) > .pagination > .pagination-list > .field > :nth-child(2) > .select > select').select('2022')
    cy.get(':nth-child(1) > .datepicker > .dropdown > .dropdown-menu > .dropdown-content > .dropdown-item > .datepicker-table > .datepicker-body > :nth-child(5) > :nth-child(4)').contains('30')
      .click()

    cy.get('.control > #origin')
      .type('Metz')
    cy.wait(600)
    cy.get(':nth-child(1) > .label > section > .field > .autocomplete > .dropdown-menu > .dropdown-content > :nth-child(1) > .media')
      .click()
    cy.get('.control > #destination')
      .type('Marseille')
    cy.wait(600)
    cy.get(':nth-child(2) > .label > section > .field > .autocomplete > .dropdown-menu > .dropdown-content > :nth-child(1) > .media')
      .click()
    cy.get('#rechercher')
      .click()
  })

  it('An User goes to his account and deletes it ', () => {
    let email = "toto@fakemail.com"
    let password = "Passpass!*$"

    cy.loginWith(email, password)
    cy.delete()
  })
})
