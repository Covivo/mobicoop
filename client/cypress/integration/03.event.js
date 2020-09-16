describe("Event", function () {
	it("Should create an event", function () {
		let email = "driver@user.com"
		let password = "Asefth123"

		cy.signIn(email, password)
		cy.createEvent()
	})

	it("Should create carpool event", function () {
		let email = "driver@user.com"
		let password = "Asefth123"

		cy.signIn(email, password)
		cy.createCarpoolEvent()
	})

	it("Should join carpool event", function () {
		let email = "passenger@user.com"
		let password = "Asefth123"
		let start = "Metz"
		let destination = "title"

		cy.signIn(email, password)
		cy.joinCarpoolEvent(start, destination)
	})

	it("Should signal event", function () {
		let email = "passenger@user.com"
		let password = "Asefth123"

		cy.signIn(email, password)
		cy.signalEvent(email)
	})
}) 