# Release 1.38.0
* Solidary : Can update Volunteers availabilities via PATCH route.

# Release 1.36.0
* User can now see their own Ad in search results
* User have a Badges board in their profile to follow their progress

# Release 1.35.0
* Fix : Cannot longer contact someone from an archived Ad

# Release 1.33.0
* Alternate horizontal version of Search component
* Add editable editorial content

# Release 1.31.0
* Gamification : Resources and API treatment of gamification actions
* More precision in prices (especially price per km) to avoid rounding issues when computing final prices
* No more build of admin (ReactAdmin) via postdeploy.sh. This part of the projet **will be removed entirely**. From now on, the official backoffice of Mobicoop Platform is https://gitlab.com/mobicoop/mobicoop-admin.

# Release 1.30.0
* Rework of journey's details : Map, new layout...
* Number of members in communities list
* New translation system for entities loaded from the database like articles

# Release 1.29.0
* POST an RDEX+ entity Journey
* Rework of the API documentation

# Release 1.28.0
* First version of an anti-fraud system.
* Driver's origin / destination always shown in result. We added a pickup info instead.

# Release 1.27.0
* After a simple contact from search results, the message thread is no longer kept in the mailbox

# Release 1.26.0
* Display public transport operator in results. Show duration of the journey.
* CO2 economy is computed by the API and visible in user's public profile and user's profile
* Event url in event detail

# Release 1.25.0
* Give the reason of the refusal when an identity document is refused.

## API
* Several parameters of the carpool algorithm are now customable in the API's .env file.

# Release 1.24.0
* New RGPD compliant Cookie component. There is mandatory and optionnal cookie consent to give. Some features won't be available without consent.
* Can import event from APIDAE api.

# Release 1.22.0
* New contact recipients system
* Number of unread messages
* More informations about relaypoints in the popup
* A community moderator can access to the administration of it's community via the front button in community's details

# Release 1.20.0


## CLIENT
* Tip encouragement message : can show a message to encourage users to leave a tips before and after payement
* Can show a fraud warning message (in Mailbox and contact form after a carpool search)
* News subscription : News subscription checkbox in the signup form. Unchecked by default.
* SEO optimisation : meta data in translation files, url optimization for events and communities, many other stuffs.

# Release 1.19.0

* External connection : Send a message to an external user via RDEX protocol

# Release 1.18.0

## API
* New common report system via the Report ressource.
* Review Dashboard.
* New simplified resource for user ads/carpools.

## CLIENT
* Review Dashboard : See the given, left and ready to give reviews.
* Report a User.
* New 'star' icon for experienced users.
* Language selection is now available

# Release 1.17.0

## API

* Review system : entities and API Ressources
* Public Profile and Profile Summary
* Report a User
* Rework the Event Report system to match the User report system

## CLIENT

* New i18n file format for Vue components (monolingual json)
* Display reviews
* Show profile summary and public profile of a user
* Report a user in public profile

# Release 1.16.0

## API
* SSO Data Provider
* Improvement of matching algorithm

## CLIENT
* Support of Grand Lyon Connect SSO
* Results pagination

# Release 1.14.0

## API

* [Payment] : Online payment (with MangoPay provider)
* Possibility to avoid toll for the georouter

## CLIENT

* [Payment] : Better payment component and online payment

# Release 1.12.0

## API
* Multi PT provider mode. You can now define a specific provider for a specific territory (see providers.json)
* Support of Navitia for PT searches
* The entire configuration for PT searches in providers.json, no more in .env (neither API or client .env)

# Release 1.9.0

## API

* [Payment] : Register a bank account to a payment provider service. For now, only MangoPay is supported

# Release 1.8.0

## CLIENT

* Solidary carpool ask are shown in the mailbox and can be accepted

# Release 1.7.0

## API

* [Mobimatch] : Get and Compute the Public Transportation Potential of a Mass
* New Public Transport DataProvider for Conduent
* Fix RDEX : Too much parameters needed
* Return only public relay points instead the user who make the request is entitle to get it (i.e community member)

## CLIENT

* Public Transport solutions can be shown at the same time that carpools researchs.
* New ad public link for external search (like RDEX)
* A disconnected user can now login or register after a search, and get back to the search results
* Different icons depending of the relay point type

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
