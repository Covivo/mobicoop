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

describe('User account', () => {

  const baseUrl = Cypress.env("baseUrl");

  beforeEach(() => {
    cy.visit(baseUrl)
  });

  // afterEach(()=> {
  //     cy.logout()
  // })

  it('An user logged updates his password', () => {
    let email = `johndoe@fakemail.com`
    let password = "OldPassord!*$"
    let newPassword = "NewPassword$**"

    cy.loginWith(email, password)

    cy.wait(1500)
    cy.percySnapshot('login')

    /* Account */
    cy.contains('Mon profil')
      .click()
    cy.url().should('include', baseUrl + 'utilisateur/profil')

    /* Password */
    cy.contains('Mot de passe')
      .click()
    cy.url().should('include', baseUrl + 'utilisateur/mot-de-passe/modifier')

    /* Change password */
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

    cy.logout()
    cy.wait(1500)
    cy.percySnapshot('logout')
  });

  it('An user logged updates his account', () => {

    let email = `johndoe@fakemail.com`
    let newPassword = "NewPassword$**"
    let password = newPassword

    cy.loginWith(email, password)

    /* Account */
    cy.contains('Mon profil').click()
    cy.url().should('include', baseUrl + 'utilisateur/profil')
    cy.wait(1500)
    cy.percySnapshot('Account')

    /* Update */
    cy.get('.column > input').click()
    cy.url().should('include', baseUrl + 'utilisateur/profil/modifier')

    /* Gender */
    cy.get(':nth-child(3) > .field > .control > .select > select')
      .select('2')
      .should('have.value', '2')

    // Change phone number
    cy.get('.contact > .phone > .field > .control > .input').clear()
      .should('have.attr', 'placeholder', 'Numéro de téléphone')
      .type('0610111214')

      cy.get('.save > .column > .button').click()
    cy.url().should('include', baseUrl) // should be redirected to home

    cy.logout()
  });

  it('An user goes to his account and click to Mes annonces', () => {
    let email = "johndoe@fakemail.com"
    let password = "NewPassword$**"

    cy.loginWith(email, password)


    /* Profil */
    cy.contains('Mon profil').click()
    cy.url().should('include', baseUrl + 'utilisateur/profil')

    /* My proposals */
    cy.contains('Mes annonces').click()
    cy.url().should('include', baseUrl + 'utilisateur/annonces')
  });

  it('An user goes to his account and deletes it', () => {
    let email = "johndoe@fakemail.com"
    let password = "NewPassword$**"

    cy.loginWith(email, password)
     cy.delete()
  });
});
