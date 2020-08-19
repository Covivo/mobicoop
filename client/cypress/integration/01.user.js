describe("User", function() {
	
  const baseUrl = Cypress.env("baseUrl");

  beforeEach(() => {
    cy.visit(baseUrl);
	  });

  it("Should register on the se", function() {
    let email = "first@user.com"
    let password = "Asefth123"
    let lastname = "First"
    let name = "User"
    let phone = "0612345678"
    let gender = "Monsieur"
    let token = "c43604b7bbefdf0565901fd0c5ed638c04eb2c458bd4ac3f901ee24b02e64a7d"

    cy.registration(email, password, lastname, name, phone, gender, token)
  })

  it("Should connect and disconnect on the site", function() {
    let email = "first@user.com"
    let password = "Asefth123"

    cy.login(email, password)

    cy.logOut()
  })

  it("Should change profil picture", function () {
    let email = "first@user.com"
    let password = "Asefth123"

	cy.login(email, password)
	
	cy.updateProfile()
})

  it("Should delete account", function () {
    let email = "first@user.com"
    let password = "Asefth123"

    cy.login(email, password)

    cy.delete()
  })
})
