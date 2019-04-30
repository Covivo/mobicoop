
describe('Delete account', () => {

  const baseUrl = Cypress.env("baseUrl");

  it('user_login -> Connexion to mobicoop ', () => {
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

    cy.contains('Se connecter').click()

    /* Profil */
    cy.contains('Mon profil').click()
    cy.url().should('include', baseUrl + 'utilisateur/profil')

    /* Delete */
    cy.contains('Supprimer mon compte').click()
    cy.url().should('include', baseUrl + 'utilisateur/profil/supprimer')

    cy.get('button[id=user_delete_form_submit]').click()
    cy.url().should('include', baseUrl) // should be redirected to home    

  })

})
