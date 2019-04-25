
describe('Profil', () => {
    it('user_login -> Connexion mobicoop -> localhost:8081', function() {
        /* Home */
        cy.visit('/')
        cy.contains('Connexion').click()
        cy.url().should('include', '/utilisateur/connexion')

        /* Connexion */
        // Email
        cy.get('input[id=user_login_form_username]')
        .should('have.attr','placeholder','Saisissez votre adresse email')
        .type('totosmith@email.com')

        // Password
        cy.get('input[id=user_login_form_password]')
        .should('have.attr','placeholder','Saisissez votre mot de passe')
        .type('motdepasse')

        cy.contains('Se connecter').click()
  
        /* Profil */
        cy.contains('Mon profil').click()
        cy.url().should('include', '/utilisateur/profil')

        /* Delete */
        cy.contains('Supprimer mon compte').click()
        cy.url().should('include', '/utilisateur/profil/supprimer')

        cy.get('button[id=user_delete_form_submit]').click()   // à mettre modifier, valider....
        cy.url().should('include', '/')           

    })

})
