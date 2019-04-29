
describe('Update Profil', () => {

    const baseUrl = Cypress.env('CYPRESS_BASEURL');

    it('user_login -> Connexion to mobicoop ', () => {
        /* Home */
        cy.visit(baseUrl)
        cy.contains('Connexion').click()
        cy.url().should('include', baseUrl + 'utilisateur/connexion')

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
        cy.url().should('include', baseUrl + 'utilisateur/profil')

        /* Update */
        cy.contains('Mettre à jour').click()
        cy.url().should('include', baseUrl + 'utilisateur/profil/modifier')

        /* Gender */
        cy.get('select[id=user_form_gender]')
        .select('2')
        .should('have.value', '2')

        // Change phone number
        cy.get('input[id=user_form_telephone]').clear()
        .should('have.attr','placeholder','Saisissez votre numéro de téléphone')
        .type('0610111214')

        cy.get('button[id=user_form_submit]').click()
        cy.url().should('include', baseUrl) // should be redirected to home    

    })
})
