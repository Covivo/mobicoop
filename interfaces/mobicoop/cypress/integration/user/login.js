
describe('Login', () => {

  const baseUrl = Cypress.env("baseUrl");

  it('Visits mobicoop', () => {
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
})