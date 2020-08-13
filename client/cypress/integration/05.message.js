describe("Message", function () {
	it("Should accept a carpool", function () {
		let email = "driver@user.com"
		let password = "Asefth123"

		cy.signIn(email, password)
		cy.acceptCarpool()
	})

	it("Should refuse a carpool", function () {
		let email = "driver@user.com"
		let password = "Asefth123"

		cy.signIn(email, password)
		cy.refuseCarpool()
		
	})

	it("Should send a message", function () {
		let email = "driver@user.com"
		let password = "Asefth123"
		let msg = "Lorem ipsum dolor sit amet, consectetur adipiscing elit."

		cy.signIn(email, password)
		cy.sendMessage(msg)
	})

}) 