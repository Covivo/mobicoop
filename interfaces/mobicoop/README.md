Coviride
=======

![Logo Coviride](logo.jpg)


<p align="center">
  <a href="https://www.gnu.org/licenses/agpl-3.0" ><img alt="License: AGPL v3" src="https://img.shields.io/badge/License-AGPL%20v3-blue.svg"/></a>
  <a href="https://gitlab.com/covivo/CoviRide/-/jobs"><img alt="Build Status" src="https://gitlab.com/covivo/CoviRide/badges/master/build.svg"></a>
  <a href="https://gitlab.com/covivo/CoviRide/commits/master"><img alt="coverage report" src="https://gitlab.com/covivo/CoviRide/badges/master/coverage.svg" /></a>
  <a href="https://ci.appveyor.com/project/MatthD/coviride/branch/master"><img src="https://ci.appveyor.com/api/projects/status/lxrhumbiss1s084h/branch/dev?svg=true"></a>
</p>

### ABout CoviRide

Interface which displays carpools available on a territory and allows connection between carpoolers making the same trip daily or punctually.


### Requirements

- PHP: =>7.1
- Composer =>1.7
- Node.js => 10

- for Windows check the [windows requirement](#windows-requirements) part

### Install

- Clone the repo

`git clone https://gitlab.com/covivo/CoviRide`

`cd CoviRide`

- Install symfony dependencies & npm dependencies
`composer install && npm install`

-Build assets files 
`npm run compile`

-Download tools for dev 
`npm run postinstall`





### Tests

We use [Kahlan](https://kahlan.github.io/docs/) to create unit/functionnals tests, you cna launch them easily with:

`vendor\bin\kahlan --cc=true --reporter=verbose` for cmd/powershell

Or just:

`npm test` On unix systems


### Start

To start the application simply enter :

`npm start`

& just go [http://localhost:8000](http://localhost:8000) 


### Developpement

To contribute to the Coviride application, please do the following:

1. Create a branch by feature or fork the repo if you are not in dev team
2. Start the dev tools (necesary to watch js/css/sass):
	`npm run compileAndWatch`
3. Add some Unit Tests and/or functionnals test and check if build passed
4. Create a pull request & set reviewver before merge

#### Javascript/Vue-js

The main library for javascript used is [vue-js](https://fr.vuejs.org/index.html), everything about them are in [components](assets/js/components)

We use webpack-encore with babel, if you need to use some new fun stuf in js you can [check stage](http://kangax.github.io/compat-table/esnext/)(babel 6 part) install & require the [right plugin](https://babeljs.io/docs/en/6.26.3/plugins) in [webpack config](webpack.config.js)

💄 We use [esLint rules](https://eslint.org/docs/rules/) to check code linting,if some rules are too restricted you can disable them or make the warning only in [.eslintrc.json](.eslintrc.json)

*For some mistakes as bad indent, spaces .. a fix is automaticaly applied !*

#### Bulma/buefy

We use buefy which is a vue adaptation of bulma as a front-end scss framework, you can:

- Check the [buefy doc](https://buefy.github.io/documentation/layout) to find elements you want to use, such as modal, layou ..
- Change any default [bulma variables](https://bulma.io/documentation/customize/variables/) in [variable.scss](assets/css/_variables.scss) such as [mobile variables](https://bulma.io/documentation/overview/responsiveness/#variables)
- Use bulma variable inside vue-js components as in the [parrot sample](assets/js/components/Parrot.vue)


#### Mapbox

We are using [Mapbox-glue](https://www.npmjs.com/package/mapbox-gl-vue#setup) for route information & calcs, please check [mapbox-gl api](https://www.mapbox.com/mapbox-gl-js/api/)


### Documentation

A developer doc is available [here](https://covivo.gitlab.io/CoviRide/build/doc) (it is generated automatically afetr each release), in order to generate the doc just do:
`npm run generateDoc`


### Database

You will find a documentation about the database [here](https://covivo.gitlab.io/CoviRide/database/)





### Contribute Guideline

Please check:

- [JS GuideLine](https://github.com/airbnb/javascript#whitespace) 
- [Symfony GuideLine](https://symfony.com/doc/current/contributing/code/standards.html)

and [contributor covenant](https://www.contributor-covenant.org)


### Licence
[AGPL-3](https://www.gnu.org/licenses/agpl-3.0)



##### Windows Requirements

Please use powershell with our project, in requirement you will need:

*BE SURE TO BE IN AN ADMIN CONSOLE MOD !*

- Install windows package manager[chocolatey](https://chocolatey.org/install)
- Restart powershell
- Install php: `choco install php`
- Install composer: `choco install composer`
- Install nodejs: `choco install nodejs.install`
- Install python3: `choco install python`
- Install python2: `choco install python2`
- Install wget: `choco install wget`
- Install windows dev tools: `npm install --global --production windows-build-tools`
- ... then you can follow the [install section](#install)
