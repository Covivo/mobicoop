
describe('My ads', () => {

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

    cy.get('button[id=user_login_form_login]').click()


    /* Profil */
    cy.contains('Mon profil').click()
      .click
    cy.url().should('include', baseUrl + 'utilisateur/profil')

    /* Update */
    cy.contains('Mes annonces').click()
    cy.url().should('include', baseUrl + 'utilisateur/annonces')
  })

  it('Results ', () => {
    cy.get(':nth-child(1) > .columns > :nth-child(1) > :nth-child(4) > .button')    
    .click()
    cy.url().should('include', baseUrl + 'covoiturage/annonce/1/resultats')
  })

})
