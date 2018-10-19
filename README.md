Mobicoop
=======

![Logo mobicoop](logo.jpg)

<p align="center">
  <a href="https://www.gnu.org/licenses/agpl-3.0" ><img alt="License: AGPL v3" src="https://img.shields.io/badge/License-AGPL%20v3-blue.svg"/></a>
  <a href="https://gitlab.com/mobicoop/mobicoop/-/jobs"><img alt="Build Status" src="https://gitlab.com/mobicoop/mobicoop/badges/master/build.svg"></a>
  <a href="https://gitlab.com/mobicoop/mobicoop/commits/master"><img alt="coverage report" src="https://gitlab.com/mobicoop/mobicoop/badges/master/coverage.svg" /></a>
  <a href="https://ci.appveyor.com/project/MatthD/mobicoop/branch/master"><img src="https://ci.appveyor.com/api/projects/status/lxrhumbiss1s084h/branch/dev?svg=true"></a>
</p>

### About mobicoop

Carpools apps available on a territory and allows connection between carpoolers making the same trip daily or punctually.

For more informations, check their readme:

- [Api](/api)
- [Admin](/interfaces/admin)
- [Mobicoop](/interfaces/mobicoop)

### Requirements 💻

- PHP: =>7.1
- Composer =>1.7
- Node.js => 10
- xdebug (needed for code-coverage)
- Google Chrome (for functionnals tests)

- for Windows check the [windows requirement](#windows-requirements) part

### Install 🤖

- Clone the repo

`git clone https://gitlab.com/mobicoop/mobicoop`

*DUPLICATE THE [config.json.dist](config.json.dist) INTO A `config.json` FILE*

`cd mobicoop`

`npm install` will install api vendor, mobicoop vendor+node_modules+build assets, admin node_modules, download tools binaries

- Sometimes you will ne on unix systems: `chmod 775 bin/*`

- Edit [.env api](api/.env.dist) [.env mobicoop](interfaces/mobicoop/.env.dist) files (*do not edit the dist file*)



### Tests 🎰

`npm test` will test the three apps

-We use [Kahlan](https://kahlan.github.io/docs/) to create units/functionnals tests, you can launch them easily with:
-For functionnals tests you can do it via 3 ways, with [kernels](https://api.symfony.com/4.1/Symfony/Component/HttpKernel/Kernel.html) (limited--), with [client](https://api.symfony.com/4.1/Symfony/Component/HttpKernel/Client.html) (limited), with [panther](https://github.com/symfony/panther) for a real browser testing (click,form ..)


### Start 🚀

To start the application simply enter :

`npm start`

& just go to [http://localhost:8080](http://localhost:8080) for API 
& just go to [http://localhost:8081](http://localhost:8081)  for mobicoop app
& just go to [http://localhost:8082](http://localhost:8082) for admin 


### Developpement

To contribute to the mobicoop application, please do the following:

1. Create a branch by feature or fork the repo if you are not in dev team
2. [Start](#start) the 3 apps  (necesary to watch js/css/sass):
  `npm run compileAndWatch`
3. Add some Unit Tests and/or functionnals test and check if build passed
4. Create a pull request & set reviewver before merge

** In developpement mode we use .env file, but not in production following [symfony spec](https://symfony.com/doc/current/deployment.html#common-post-deployment-tasks)


### Documentation

A developer doc is available [here](https://mobicoop.gitlab.io/mobicoop/build/doc) (it is generated automatically after each production release), in order to generate the doc just do:

`npm run generateDoc`

### Contribute Guideline 📖📚

Please check:

- [JS GuideLine](https://github.com/airbnb/javascript#whitespace) 
- [Symfony GuideLine](https://symfony.com/doc/current/contributing/code/standards.html)

and [contributor covenant](https://www.contributor-covenant.org)

*To check & fix your code*, just do:

`npm run testFixAndCoverage`


### Licence
Mobicoop software is owned by Mobicoop cooperative. Mobicoop cooperative is opened to any individual, company or public authority who wish to become a shareholder.
In order to increase the impact of our platform to any sort of clients whatever type of contractual relationship theyu require, Mobicoop software is dual-licensed:
 - [AGPL-3](https://www.gnu.org/licenses/agpl-3.0)
 - proprietary software



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
- Install [xdebug](https://burhandodhy.me/2017/08/29/how-to-install-xdebug-on-windows/) & link it to you php.ini 
- ... then you can follow the [install section](#install)
