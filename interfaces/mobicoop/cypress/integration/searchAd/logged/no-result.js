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