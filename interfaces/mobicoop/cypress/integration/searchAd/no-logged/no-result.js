describe('Search an ad - user no logged', () => {

  const baseUrl = Cypress.env("baseUrl");

  it('user_login -> Connexion to mobicoop ', () => {
    /* Home */
    cy.visit(baseUrl)
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