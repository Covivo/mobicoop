describe("Carpool", function () {
	it("Should create a occasional carpool", function () {
		let email = "driver@user.com"
		let password = "Asefth123"
		let lastname = "User"
		let name = "Driver"
		let phone = "0601020304"
		let gender = "Monsieur"
		let start = "Nancy"
		let destination = "Metz"

		cy.signUp(email, password, lastname, name, phone, gender);
		cy.signIn(email, password)
		cy.createOccasionalCarpool(start, destination)
		cy.get('.d-inline-flex > .v-list-item > .v-list-item__content > .v-list-item__title > .primary--text').contains('Nancy')
	})

	it("Should join a occasional carpool", function() {
		let email = "passenger@user.com"
		let password = "Asefth123"
		let lastname = "user"
		let name = "Passenger"
		let phone = "0609876543"
		let gender = "Madame"
		let start = "Nancy"
		let destination = "Metz"

		cy.signUp(email, password, lastname, name, phone, gender)
		cy.signIn(email, password)
		cy.joinOccasionalCarpool(start, destination)
	})

	it("Should create a regular carpool", function () {
		let email = "driver@user.com"
		let password = "Asefth123"
		let start = "Nancy"
		let destination = "Metz"

		cy.signIn(email, password)
		cy.createRegularCarpool(start, destination)
	})

	it("Should join a regular carpool", function () {
		let email = "passenger@user.com"
		let password = "Asefth123"
		let start = "Nancy"
		let destination = "Metz"

		cy.signIn(email, password)
		cy.joinRegularCarpool(start, destination)
	})
}) 