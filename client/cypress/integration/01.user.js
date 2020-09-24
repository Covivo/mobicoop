describe("User", function() {
	it("Should register on the site", function() {
		let email = "first@user.com"
		let password = "Asefth123"
		let lastname = "First"
		let name = "User"
		let phone = "0612345678"
		let gender = "Monsieur"

		cy.signUp(email, password, lastname, name, phone, gender)
	})

	it("Should connect and disconnect on the site", function() {
		let email = "first@user.com"
		let password = "Asefth123"

		cy.signIn(email, password)
		cy.signOut()
	})

	it("Should change profil picture", function () {
		let email = "first@user.com"
		let password = "Asefth123"

		cy.signIn(email, password)
		cy.changeProfil()
	})

	it("Should delete account", function () {
		let email = "first@user.com"
		let password = "Asefth123"

		cy.signIn(email, password)
		cy.delete()
	})
}) 