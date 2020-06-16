# Release 1.7.0

## API

* [Mobimatch] : Get and Compute the Public Transportation Potential of a Mass
* New Public Transport DataProvider for Conduent

## CLIENT

* Public Transport solutions can be shown at the same time that carpools researchs.
* New ad public link for external search (like RDEX)
* A disconnected user can now login or register after a search, and get back to the search results

# Release 1.6.0

## API

* New related mobile app versioning system
* A Proposal can now have a specific Subject

## CLIENT

* Display relay points:
    * Display relay points on a map
    * Can use relay point as origin or destination by clicking on it

# Release 1.5.0

## API

* New carpool proof system :
    * Automatic creation of carpool proof for planified carpools
    * Realtime creation of carpool proofs for planified and dynamic carpools using mobile app
    * Link with proof registry via cron job
* New entities for SolidaryUser : SolidaryBeneficiary and SolidaryVolunteer.
* Several utility routes.
* RDEX : Fix bad handling days and times when there is no outward array given

# Release 1.4.0

## API

* New push notifications system
* New migration system for Mobimatch : import Mobimatch persons and their journeys as real users and carpool ads
* Add roles default we set in User entity when Register User (ROLE_USER_REGISTERED_FULL)
* Create new auth item 'community_restrict' for display only communities user created
* Add route for get granted roles an user can create

## ADMIN
* Change the way we prefill roles in user edition, and now can only set one role per territory

# Release 1.3.0

## API

* Direct link between addresses / directions and territories : improve the response speed for territory filters
* Solidary Transport and Carpool Management : Manage solidary volunteers and beneficiaries, searching for solidary solutions, managing files and sollicitations.
* Clean community API

## CLIENT

* Add Driver's License acknowledgement

# Release 1.2.0

## API

* Coordinates prioritization for Geosearcher : a focus point can be define for the whole instance, and for each logged user (using its home address as focus point)
* JSON file to fix wrong geographic data
* Improvement of Geosearcher : Pelias Autocomplete for localities only, then Pelias Search for full search
* New territory filters for communities and events
* Add a login system using tokens (for email and password reset validation)
* Solidary beneficiaries and their solidary files, Structure, Proofs
* Solidary volunteers to help the beneficiaries
* Add Filter extension on User and Territory
* Add function checkUserHaveAuthItem to check if an User have a specified Auth Item
* Add Role 'ROLE_COMMUNITY_MANAGER' to the creators of the community (client side)
* Add button for admin access from the detail page of community (for the creator) + parameters CAN_ACCESS_ADMIN_FROM_COMMUNITY

## CLIENT

* Automatic territory filtering for communities and events
* New authentication for login, reset token and reset password
