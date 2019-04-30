
describe('Update password', () => {

  const baseUrl = Cypress.env("baseUrl");

  it('user_login -> Connexion to mobicoop ', () => {
    /* Home */
    cy.visit(baseUrl)
    cy.contains('Connexion')
    .click()
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

    cy.get('button[id=user_login_form_login]')
    .click()


    /* Profil */
    cy.contains('Mon profil')
    .click()
      .click
    cy.url().should('include', baseUrl + 'utilisateur/profil')

    /* Update */
    cy.contains('Mot de passe')
    .click()
    cy.url().should('include', baseUrl + 'utilisateur/mot-de-passe/modifier')

    /* Password */
    cy.get('#user_form_password_first')
      .should('have.attr', 'placeholder', 'Saisissez votre mot de passe')
      .type('motdepasse')

    /* Password */
    cy.get('#user_form_password_second')
      .should('have.attr', 'placeholder', 'Confirmez votre mot de passe')
      .type('motdepasse')

    /* Submit */
    cy.get('#user_form_submit')
    .click()
  })
})
