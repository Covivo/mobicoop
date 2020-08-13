describe("Community", function () {
	it("Should create a community", function () {
		let email = "driver@user.com"
		let password = "Asefth123"

		cy.signIn(email, password)
		cy.createCommunity()
	})

	it("Should create a carpool community", function () {
		let email = "driver@user.com"
		let password = "Asefth123"
		let start = "Paris"
		let destination = "Orléans"

		cy.signIn(email, password)
		cy.createCarpoolCommunity(start, destination)
	})

	it("Should join a carpool community", function () {
		let email = "passenger@user.com"
		let password = "Asefth123"
		let start = "Paris"
		let destination = "Orléans"

		cy.signIn(email, password)
		cy.joinCarpoolCommunity(start, destination)
	})
}) 