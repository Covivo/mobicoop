describe("Reset", function () {
	it.skip("Should reset", function () {
		cy.exec('cd .. && make go-platform')
	})

	it.skip("Should migrate", function () {
		cy.exec('cd .. && make db-migrate')
	})
}) 