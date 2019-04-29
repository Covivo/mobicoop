


describe('Share an ad -> Passenger -> round trip -> ponctual', () => {

  const baseUrl = Cypress.env('CYPRESS_BASEURL');
  
      it('Visits mobicoop', () => {
          cy.visit(baseUrl)
          cy.contains('Connexion').click()
          cy.url().should('include',baseUrl + 'utilisateur/connexion')
      })
  
      it('Connexion mobicoop + Share an ad', () => {
      /* Email */
      cy.get('input[id=user_login_form_username]')
      .should('have.attr','placeholder','Saisissez votre adresse email')
      .type('totosmith@email.com')
  
      /* Password */
      cy.get('input[id=user_login_form_password]')
      .should('have.attr','placeholder','Saisissez votre mot de passe')
      .type('motdepasse')
  
      cy.get('button[id=user_login_form_login]')
      .click()  
     
      /* Share an ad */
      cy.contains('Partager une annonce')
      .click()
      cy.url().should('include',baseUrl + 'covoiturage/annonce/poster')
  
      /* Passenger */
      cy.get('#Vousêtes0 > .field > :nth-child(2) > .b-radio').contains('Passager')
      .click()
  
      /* Next */
      cy.get('.wizard-btn')
      .click()
  
      /* Round trip */
      cy.get('#Trajet2 > .fieldsContainer > :nth-child(2) > .b-radio').contains('Aller-retour')
      .click()
      cy.get('.control > #origin')
      .should('have.attr','placeholder','Depuis')
      .type('Lyon')
      cy.get ('[data-v-12259723]')
      .contains('Lyon')
      .click()
  
      /* To */
      cy.get('#destination')
      .should('have.attr','placeholder','Vers')
      .type('Strasbourg')
      cy.get ('[data-v-12259723]')
      .contains('Strasbourg')
      .click()
  
      /* Next */
      cy.get('.wizard-footer-right > span > .wizard-btn')
      .click()
  
      /* Ponctual */
      cy.get('#Fréquence4 > .fieldsContainer > :nth-child(1) > .b-radio')
      .click()
  
      /* **************ONE WAY************** */
      /* One way - Date */
      cy.get (':nth-child(1) > .datepicker > .dropdown > .dropdown-trigger > .control > .input')
      .should('have.attr','placeholder','Date de départ...')
      .click()
      cy.get('.datepicker-body > :nth-child(5) > :nth-child(2)')
      .contains('30')
      .click()
  
      /* One way - Time */
      cy.get (':nth-child(3) > .columns > .timepicker > .dropdown > .dropdown-trigger > .control > .input')
      .should('have.attr','placeholder','Heure de départ...')
      .click()
      cy.get(':nth-child(3) > .columns > .timepicker > .dropdown > .dropdown-menu > .dropdown-content > .dropdown-item > .timepicker-footer > .is-mobicoopgreen')    
      .click()
  
      // in order to close the window timepicker
      cy.get(':nth-child(1) > .datepicker > .dropdown > .dropdown-trigger > .control > .input')
      .click({force: true})  
  
      /* Margin */
      cy.get('select').then(function($select){
          $select.val('5')
        })
      cy.get('select').should('have.value','5')
      /* ************************************* */
  
      /* **************RETURN************** */
      /* Return - Date */
      cy.get (':nth-child(2) > .datepicker > .dropdown > .dropdown-trigger > .control > .input')
      .should('have.attr','placeholder','Date de retour...')
      .click()
      cy.get(':nth-child(2) > .datepicker > .dropdown > .dropdown-menu > .dropdown-content > .dropdown-item > .datepicker-table > .datepicker-body > :nth-child(5) > :nth-child(3)')
      .contains('30')
      .click()
  
      /* Return - Time */
      cy.get (':nth-child(2) > .columns > .timepicker > .dropdown > .dropdown-trigger > .control > .input')
      .should('have.attr','placeholder','heure de retour...')
      .click()
      
      cy.get(':nth-child(2) > .columns > .timepicker > .dropdown > .dropdown-menu > .dropdown-content > .dropdown-item > .timepicker-footer > .is-mobicoopgreen')    
      .click()
  
      // in order to close the window timepicker
      cy.get(':nth-child(1) > .datepicker > .dropdown > .dropdown-trigger > .control > .input')
      .click({force: true})  
  
      /* Margin */
      cy.get('select').then(function($select){
          $select.val('5')
        })
      cy.get('select').should('have.value', '5')
      /* ************************************* */
  
    /* I share my ad */
    cy.get('.wizard-footer-right > span > .wizard-btn')
    .click()
  
    
      })
    })
  