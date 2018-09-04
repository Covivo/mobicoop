CoviRide
=======

![Logo Coviride](logo.jpg)


<p align="center">
  <a href="https://www.gnu.org/licenses/agpl-3.0" ><img alt="License: AGPL v3" src="https://img.shields.io/badge/License-AGPL%20v3-blue.svg"/></a>
  <a href="https://gitlab.com/covivo/CoviRide/-/jobs"><img alt="Build Status" src="https://gitlab.com/covivo/CoviRide/badges/master/build.svg"></a>
  <a href="https://gitlab.com/covivo/CoviRide/commits/master"><img alt="coverage report" src="https://gitlab.com/covivo/CoviRide/badges/master/coverage.svg" /></a>
</p>

### ABout CoviRide

Interface which displays carpools available on a territory and allows connection between carpoolers making the same trip daily or punctually.


### Requirements

- PHP: =>7.1
- Composer =>1.7
- Node.js => 10


### Install

- Clone the repo

`git clone https://gitlab.com/covivo/CoviRide`

`cd CoviRide`

- Install symfony dependencies & npm dependencies
`composer install && npm install`


### Tests

We use [Kahlan](https://kahlan.github.io/docs/) to create unit/functionnals tests, you cna launch them easily with:

`vendor/bin/kahlan --cc=true --reporter=verbose`
Or
`npm test`

### Start


To start the application simply enter :

`npm start`

& just go [http://localhost:8000](http://localhost:8000) 


### Developpement

To contribute to the Coviride application, please do the following:

1. Create a branch by feature or fork the repo if you are not in dev team
2. Start the dev tools (necesary to watch js/css/sass):
	`npx encore dev --watch`
3. Add some Unit Tests and/or functionnals test and check if build passed
3. Create a pull request & set reviewver before merge


### Contribute Guideline

Please check [JS GuideLine](https://github.com/airbnb/javascript#whitespace) and [contributor covenant](https://www.contributor-covenant.org)


### Licence
[AGPL-3](https://www.gnu.org/licenses/agpl-3.0)
