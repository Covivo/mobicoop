describe('Sign up', () => {

  const baseUrl = Cypress.env("baseUrl");

  it('Home', () => {
    cy.visit(baseUrl)
    cy.contains('Inscription').click()
    cy.url().should('include', baseUrl + 'utilisateur/inscription')

    /* Email */
    cy.get('input[id=user_form_email]')
      .should('have.attr', 'placeholder', 'Saisissez votre adresse email')
      .type('totosmith@email.com')

    /* Lastname */
    cy.get('input[id=user_form_givenName]')
      .should('have.attr', 'placeholder', 'Saisissez votre prénom')
      .type('Robert')

    /* Name */
    cy.get('input[id=user_form_familyName]')
      .should('have.attr', 'placeholder', 'Saisissez votre nom')
      .type('Smith')

    /* Gender */
    cy.get('select[id=user_form_gender]')
      .select('1')
      .should('have.value', '1')

    /* Birthyear */
    cy.get('select[id=user_form_birthYear]')
      .select('2000')
      .should('have.value', '2000')

    /* Phone */
    cy.get('input[id=user_form_telephone]')
      .should('have.attr', 'placeholder', 'Saisissez votre numéro de téléphone')
      .type('0610111213')

    /* Password */
    cy.get('input[id=user_form_password_first]')
      .should('have.attr', 'placeholder', 'Saisissez votre mot de passe')
      .type('motdepasse')

    /* Password (confirmation) */
    cy.get('input[id=user_form_password_second]')
      .should('have.attr', 'placeholder', 'Confirmez votre mot de passe')
      .type('motdepasse')

    /* Validation condition (confirmation) */
    cy.get('input[id=user_form_conditions]').check()


    cy.contains('Je m\'inscris').click()
    cy.url().should('include', baseUrl) // should be redirected to home    
  })
})







