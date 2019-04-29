  
  
describe('Search Carpool', () => {

    const baseUrl = Cypress.env('CYPRESS_BASEURL');

    it('user_login -> Connexion to mobicoop', () => {
        /* Home */
        cy.visit(baseUrl + 'utilisateur/connexion')
        cy.get('input[id=origin]')
        .should('have.attr','placeholder','Depuis')
        .type('Nancy')
        cy.get('[data-v-12259723="Nancy"]')
        


    cy.get('[data-cy=rsHomeAddress]').find('[data-cy=tbAddressLookup]')
    .type('901 Dunbar Drive Dunwoody', {force: true})
    .type(' {downarrow}{enter}', {delay: 300, force: true});


    })
})

  
  
  
 