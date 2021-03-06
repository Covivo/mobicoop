'use scrit';

import '@percy/cypress';

// User

Cypress.Commands.add('signIn', (email, password) => {
	cy.visit(Cypress.env('baseUrl') + "utilisateur/connexion")
	cy.get('#email').type(email)
	cy.get('#password').type(password)
	cy.get('#formLogin > .v-btn').click()
});

Cypress.Commands.add('signOut', () => {
	cy.get('[data-v-33788174=""][type="button"] > .v-btn__content').trigger('mouseenter');
	cy.xpath('//*[@id="list-item-99"]/a').click()
});

Cypress.Commands.add('changeProfil', () => {
	cy.visit(Cypress.env('baseUrl') + "utilisateur/profil/modifier/mon-profil")
	cy.get('#input-69').attachFile('profil.jpg');
	cy.get('.v-form > .button > .v-btn__content').click()
	cy.get('.v-snack--active > .v-snack__wrapper > .v-snack__content').contains('Votre profil a bien été mis à jour!')
});

Cypress.Commands.add('signUp', (email, password, lastname, name, phone, gender) => {
	cy.visit(Cypress.env('baseUrl') + "utilisateur/inscription")
	cy.get('#email').type(email)
	cy.get('#telephone').type(phone)
	cy.get('#password').type(password)
	cy.get('#buttonNext1 > .v-btn__content').click()

	cy.get('#givenName').type(name)
	cy.get('#familyName').type(lastname)
	cy.get('#step2 > .v-select > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections').click()
	cy.contains(gender).click();

	cy.get('#birthday').click({ force: true })
	cy.get('.v-date-picker-years > :nth-child(6)').click()
	cy.get('tbody > :nth-child(1) > :nth-child(1) > .v-btn > .v-btn__content').click()
	cy.get(':nth-child(1) > :nth-child(5) > .v-btn > .v-btn__content').click()
	cy.get('#buttonNext2 > .v-btn__content').click()

	cy.get('.v-input--selection-controls__ripple').click()
	cy.get('#step3 > .row > :nth-child(2) > .v-btn__content').click()
	cy.get('.v-alert__content > :nth-child(1)').contains('L\'inscription est presque finie.')
});

Cypress.Commands.add('delete', () => {
	cy.visit(Cypress.env('baseUrl') + "utilisateur/profil/modifier/mon-profil")
	cy.get('.text-center > .button > .v-btn__content').click()
	cy.get('.v-card__actions > a.v-btn > .v-btn__content').click()
	cy.get('.v-snack__content > div').contains('Votre compte à été supprimé avec succès.')
});

// Carpool

Cypress.Commands.add('createOccasionalCarpool', (start, destination) => {
	cy.visit(Cypress.env('baseUrl') + "covoiturage/publierannonce")

	cy.get('.v-input--radio-group__input > :nth-child(1) > .v-input--selection-controls__input > .v-input--selection-controls__ripple').click()
	cy.get('#from > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(start)
	cy.get('#list-item-252-0 > .v-list > #content > .v-list-item__content > .v-list-item__title').click()
	cy.get('#to > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(destination)
	cy.get('#list-item-298-0 > .v-list > #content > .v-list-item__content > .v-list-item__title').click()

	cy.get('.col-md-5 > .v-input > .v-input__control').click()
	cy.get(':nth-child(5) > :nth-child(5) > .v-btn > .v-btn__content').click()
	cy.get('[mt-5=""] > .v-btn > .v-btn__content').click()

	cy.get('.justify-center > .col-4 > .v-input > .v-input__control').click()
	cy.xpath('//*[@id="app"]/div[5]/div/div[2]/div/div/div/span[22]/span').click()
	cy.xpath('//*[@id="app"]/div[5]/div/div[2]/div/div/div/span[10]/span').contains('45').click()
	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()

	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()
	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()
	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()
	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()
	cy.get('[mt-5=""] > div > .v-btn > .v-btn__content').click()
	cy.url().should('include', '/utilisateur/profil/modifier/mes-annonces')
});

Cypress.Commands.add('joinOccasionalCarpool', (start, destination) => {
	cy.visit(Cypress.env('baseUrl') + "")
	cy.get('#from > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(start)
	cy.get('#list-item-94-0').click()
	cy.get('#to > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(destination)
	cy.get('#list-item-134-0').click()
	cy.get('.row > :nth-child(3) > .v-btn').click()
	cy.get('.v-btn__content > span').click()
	cy.get('.v-card__actions > .v-btn > .v-btn__content').click()
	cy.url().should('include', '/utilisateur/messages')
});

Cypress.Commands.add('createRegularCarpool', (start, destination) => {
	cy.visit(Cypress.env('baseUrl') + "covoiturage/publierannonce")

	cy.get('.v-input--radio-group__input > :nth-child(1) > .v-input--selection-controls__input > .v-input--selection-controls__ripple').click()
	cy.get('#from > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(start)
	cy.get('#list-item-252-0 > .v-list > #content > .v-list-item__content > .v-list-item__title').click()
	cy.get('#to > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(destination)
	cy.get('#list-item-298-0 > .v-list > #content > .v-list-item__content > .v-list-item__title').click()
	cy.get('.ma-2 > .v-input > .v-input__control > .v-input__slot > .v-input--selection-controls__input > .v-input--selection-controls__ripple').click()
	cy.get('[mt-5=""] > .v-btn > .v-btn__content').click()

	cy.get(':nth-child(1) > :nth-child(1) > .v-input__control > .v-input__slot > .v-input--selection-controls__input > .v-input--selection-controls__ripple').click({ force: true })
	cy.get('#input-367').click({ force: true })
	cy.xpath('//*[@id="app"]/div[4]/div/div[2]/div/div/div/span[22]/span').click()
	cy.xpath('//*[@id="app"]/div[4]/div/div[2]/div/div/div/span[10]/span').contains('45').click()
	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()

	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()
	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()
	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()
	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()
	cy.get('[mt-5=""] > div > .v-btn > .v-btn__content').click()
	cy.url().should('include', '/utilisateur/profil/modifier/mes-annonces')
});

Cypress.Commands.add('joinRegularCarpool', (start, destination) => {
	cy.get('#from > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(start)
	cy.get('#list-item-94-0').click()
	cy.get('#to > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(destination)
	cy.get('#list-item-134-0').click()
	cy.get('.v-input--selection-controls__ripple').click()
	cy.get('.row > :nth-child(3) > .v-btn > .v-btn__content').click()
	cy.get('.v-btn__content > span').click()
	cy.get('.v-card__actions > .v-btn > .v-btn__content').click()

	cy.get('.col-2 > .v-input > .v-input__control > .v-input__slot > .v-input--selection-controls__input > .v-input--selection-controls__ripple').click()
	cy.xpath('//*[@id="app"]/div[3]/div/div/div[1]/div/div[2]/div/div[1]/div[2]/div/div/div[1]/div/div[2]/span[2]').click()
	cy.get('.v-card__actions > .v-btn--contained > .v-btn__content').click()
	cy.url().should('include', '/utilisateur/messages')
});

// Event

Cypress.Commands.add('createEvent', () => {
	cy.visit(Cypress.env('baseUrl') + 'evenements');
	cy.get('.secondary > .v-btn__content').click();
	cy.url().should('contains', Cypress.env('baseUrl') + 'evenement/creer');
	cy.get('#input-31').type('Title event');
	cy.get('#input-34').type('Short description');
	cy.get('#input-37').type('Long description');
	cy.get('#address').type("Nancy");
	cy.get('#list-item-88-0 .v-list-item__title').click();
	cy.get(':nth-child(5) > :nth-child(1) > .v-input > .v-input__control').click();
	cy.get(':nth-child(5) > :nth-child(5) > .v-btn > .v-btn__content').click()
	cy.get(':nth-child(5) > :nth-child(2) > .v-input > .v-input__control').click()
	cy.get('.menuable__content__active > .v-picker > .v-picker__body > :nth-child(1) > .v-date-picker-table > table > tbody > :nth-child(5) > :nth-child(5) > .v-btn > .v-btn__content').click()
	cy.get('#input-73').attachFile('event.jpg');
	cy.get('.col > .text-center > .v-btn > .v-btn__content').click()
	cy.get(':nth-child(2) > .v-btn__content').click()
	cy.url().should('include', '/evenements')
});

Cypress.Commands.add('createCarpoolEvent', () => {
	cy.visit(Cypress.env('baseUrl') + 'evenements');
	cy.get('.col-6:nth-child(1)').click();
	cy.get('.my-2 .v-btn__content').click();
	cy.url().should('contains', Cypress.env('baseUrl') + 'evenement/1');
	cy.get('.secondary').click();
	cy.url().should('contains', Cypress.env('baseUrl') + 'covoiturage/recherche/poster');
	cy.get('.v-radio:nth-child(1) > .v-label').click();
	cy.get('#from > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type('Metz');
	cy.get('#list-item-258-0 .v-list-item__subtitle').click();
	cy.get('.col-md-5 > .v-input > .v-input__control').click();
	cy.get('tr:nth-child(5) > td:nth-child(5) .v-btn__content').click();
	cy.get('.v-btn--contained:nth-child(1)').click();
	cy.get('.justify-center > .col-4 > .v-input > .v-input__control').click();
	cy.xpath('//*[@id="app"]/div[4]/div/div[2]/div/div/div/span[14]').click();
	cy.xpath('//*[@id="app"]/div[4]/div/div[2]/div/div/div/span[10]/span').contains('45').click()
	cy.get('.v-btn:nth-child(2)').click();
	cy.get('.v-btn:nth-child(2)').click();
	cy.get('.v-btn:nth-child(2) > .v-btn__content').click();
	cy.get('.v-btn:nth-child(2)').click();
	cy.get('.v-btn:nth-child(2) > .v-btn__content').click();
	cy.get('.v-btn--contained:nth-child(1)').click();
	cy.url().should('contains', '/utilisateur/profil/modifier/mes-annonces');
});

Cypress.Commands.add('joinCarpoolEvent', (start, destination) => {
	cy.visit('http://localhost:8081')
	cy.get('#from > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(start)
	cy.get('#list-item-94-0 > .v-list > #content > .v-list-item__content > .v-list-item__title').click()
	cy.get('#to > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(destination)
	cy.get('#list-item-134-3 > .v-list > #content > .v-list-item__content > .v-list-item__title').click()
	cy.get('.row > :nth-child(3) > .v-btn > .v-btn__content').click()
	cy.get('.v-btn__content > span').click()
	cy.get('.v-card__actions > .v-btn > .v-btn__content').click()
	cy.url().should('contains', '/utilisateur/messages');
});

Cypress.Commands.add('signalEvent', (email) => {
	cy.visit(Cypress.env('baseUrl') + 'evenements')
	cy.get(':nth-child(2) > .v-btn > .v-btn__content').click()
	cy.get('#input-94').type(email)
	cy.get('#input-97').type('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed nunc sem, fermentum ac convallis eget, venenatis nec neque. Fusce sagittis quam maximus, elementum purus at, posuere leo.')
	cy.get('.error--text > .v-btn__content').click()
	cy.get('.v-snack__content').contains('Merci d\'avoir signaler cet événement')
});

// Community

Cypress.Commands.add('createCommunity', () => {
	cy.visit(Cypress.env('baseUrl') + 'communautes')
	cy.get('.secondary > .v-btn__content').click();
	cy.url().should('contains', Cypress.env('baseUrl') + 'communaute/creer');
	cy.get('#input-31').click();
	cy.get('#input-31').type('Ma communauté');
	cy.get('#input-34').type('Description courte de ma communauté');
	cy.get('#input-37').type('Description détaillée de la communauté');
	cy.get('#address').type('Paris');
	cy.get('#list-item-68-0 #content').click();
	cy.get('#input-54').attachFile('community.jpg');
	cy.get('.col > .v-btn > .v-btn__content').click()
	cy.get('.pa-6.v-card > :nth-child(1) > .ma-3 > .v-card > .row > .text-center > .my-2 > .secondary > .v-btn__content').click()
});

Cypress.Commands.add('createCarpoolCommunity', (start, destination) => {
	cy.visit(Cypress.env('baseUrl') + 'communautes')
	cy.get('.pa-6.v-card > :nth-child(1) > .ma-3 > .v-card > .row > .text-center > .my-2 > .secondary > .v-btn__content').click()
	cy.get('.text-center > div > .secondary > .v-btn__content').click()
	cy.get('#from > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(start)
	cy.get('#list-item-259-0 > .v-list > #content > .v-list-item__content').click()
	cy.get('#to > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(destination)
	cy.get('#list-item-305-0 > .v-list > #content > .v-list-item__content').click()
	cy.get('#date').click({ force: true })
	cy.get(':nth-child(5) > :nth-child(5) > .v-btn > .v-btn__content').click()
	cy.get('[mt-5=""] > .v-btn > .v-btn__content').click()

	cy.get('.justify-center > .col-4 > .v-input > .v-input__control').click()
	cy.xpath('//*[@id="app"]/div[5]/div/div[2]/div/div/div/span[22]/span').click()
	cy.xpath('//*[@id="app"]/div[5]/div/div[2]/div/div/div/span[10]/span').contains('45').click()
	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()

	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()
	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()
	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()
	cy.get('[mt-5=""] > .v-btn--contained > .v-btn__content').click()
	cy.get('[mt-5=""] > div > .v-btn').click()
});

Cypress.Commands.add('joinCarpoolCommunity', (start, destination) => {
	cy.visit(Cypress.env('baseUrl') + 'communautes')
	cy.get('.mt-5 > .v-btn__content').click()
	cy.get('#from > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(start)
	cy.get('#list-item-103-0 > .v-list > #content > .v-list-item__content > .v-list-item__title').click()
	cy.get('#to > .v-input > .v-input__control > .v-input__slot > .v-select__slot > .v-select__selections > #address').type(destination)
	cy.get('#list-item-143-0 > .v-list > #content > .v-list-item__content > .v-list-item__title').click()
	cy.get('.row > :nth-child(3) > .v-btn > .v-btn__content').click()

	cy.get('.v-btn__content > span').click()
	cy.get('.v-card__actions > .v-btn > .v-btn__content').click()
	cy.url().should('contains', '/utilisateur/messages');
});

// Message

Cypress.Commands.add('acceptCarpool', () => {
	cy.visit(Cypress.env('baseUrl') + 'utilisateur/messages')
	cy.get(':nth-child(2) > .v-main__wrap > .container > .mx-0 > .row.ma-0 > :nth-child(2) > :nth-child(2) > .col-8').click()
	cy.get('.mr-12 > .v-btn__content').click()
	cy.get('.v-main__wrap > :nth-child(1) > .white--text').contains('Le covoiturage a été accepté')
});

Cypress.Commands.add('refuseCarpool', () => {
	cy.visit(Cypress.env('baseUrl') + 'utilisateur/messages')
	cy.get(':nth-child(2) > .v-main__wrap > .container > .mx-0 > .row.ma-0 > :nth-child(2) > :nth-child(2) > .col-8').click()
	cy.get('.ml-12 > .v-btn__content').click()
	cy.get('.v-main__wrap > :nth-child(1) > .white--text').contains('Le covoiturage a été refusé')
});

Cypress.Commands.add('sendMessage', (msg) => {
	cy.visit(Cypress.env('baseUrl') + 'utilisateur/messages')
	cy.get(':nth-child(1) > .v-main__wrap > .container > .mx-0 > .row.ma-0 > :nth-child(2) > :nth-child(2) > .col-8').click()
	cy.get('#input-100').type(msg)
	cy.get('#validSendMessage > .v-btn__content').click()
	cy.get('.elevation-2 > .v-card__text').contains(msg)
});