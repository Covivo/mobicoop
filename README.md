Mobicoop
=======

![Logo mobicoop](logo.jpg)

<p align="center">
  <a href="https://codeclimate.com/github/Covivo/mobicoop/maintainability"><img src="https://api.codeclimate.com/v1/badges/a9393c639d5627da3883/maintainability" /></a>  <a href="https://www.gnu.org/licenses/agpl-3.0" ><img alt="License: AGPL v3" src="https://img.shields.io/badge/License-AGPL%20v3-blue.svg"/></a>
  <a href="https://gitlab.com/mobicoop/mobicoop/pipelines"><img alt="Build Status" src="https://gitlab.com/mobicoop/mobicoop/badges/dev/build.svg"></a>
  <a href="https://gitlab.com/mobicoop/mobicoop/commits/dev"><img alt="coverage report" src="https://gitlab.com/mobicoop/mobicoop/badges/dev/coverage.svg" /></a>
  <a href="https://ci.appveyor.com/project/MatthD/mobicoop/branch/dev"><img src="https://ci.appveyor.com/api/projects/status/lxrhumbiss1s084h/branch/dev?svg=true"></a>
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
- If you have missing modules during installation check this docker file
install & enable in you .ini all its php extensions : [Docker file](https://github.com/vyuldashev/docker-ci-php-node/blob/master/Dockerfile)

- for Windows check the [windows requirement](#windows-requirements) part


### Install 🤖

- Clone the repo

`git clone https://gitlab.com/mobicoop/mobicoop`

*DUPLICATE THE [config.json.dist](config.json.dist) INTO A `config.json` FILE*


#### For all apps

`npm install --no-save` will perfom:
 - Api vendor
 - Mobicoop vendor+node_modules+build assets 
 - Admin node_modules 
 - Download tools binaries (php-cs-fixer & phpdocumentor)

- Sometimes if tools do not work you will ne on unix systems: `chmod 775 bin/*`

- Duplicate, rename without .dist & edit some env & config files:
    - [.env api](api/.env.dist)  
    - [config.json api](api/config.json.dist)
    - [.env mobicoop](interfaces/mobicoop/.env.dist) 

*Do not edit the dist file with your config info*

- If you have missing modules during installation check this docker file
and install all its php extensions : [Docker file](https://github.com/vyuldashev/docker-ci-php-node/blob/master/Dockerfile)

#### API

There are some needs for api database , check them [/api](/api)


### Tests 🎰

`npm test` will test the three apps

-We use [Kahlan](https://kahlan.github.io/docs/) to create units/functionnals tests, you can launch them easily with:
-For functionnals tests you can do it via 3 ways, with [kernels](https://api.symfony.com/4.1/Symfony/Component/HttpKernel/Kernel.html) (limited--), with [client](https://api.symfony.com/4.1/Symfony/Component/HttpKernel/Client.html) (limited), with [panther](https://github.com/symfony/panther) for a real browser testing (click,form ..)


### Start 🚀

To start the application simply run :

`npm start`

& just go to [http://localhost:8080](http://localhost:8080) for API 
& just go to [http://localhost:8081](http://localhost:8081) for mobicoop app
& just go to [http://localhost:8082](http://localhost:8082) for admin 


### Developpement

To contribute to the mobicoop application, please do the following:

1. Create a branch by feature or fork the repo if you are not in dev team
2. [Start](#start) the 3 apps  (necesary to watch js/css/sass):
3. Add some Unit Tests and/or functionnals test and check if build passed
4. Create a pull request & set reviewver before merge

### Documentation

A developer doc is available [here](https://mobicoop.gitlab.io/mobicoop/build/doc) (it is generated automatically after each production release), in order to generate the doc just do:

`npm run generateDoc`

### Contribute Guideline 📖📚

Please check:

- [JS GuideLine](https://github.com/airbnb/javascript#whitespace) 
- [Symfony GuideLine](https://symfony.com/doc/current/contributing/code/standards.html)

and [contributor covenant](https://www.contributor-covenant.org)


### Hooks

There is some git hooks implemented in this app:

- Run test before any push
- Run php-cs-fixer before any commit
- Run npm install after each pull


### How to create my own front application

Because Mobicoop is a monorepo, you can ask yourself you to create & dev on you own front-end application.
Monicoop comes with a simple script to run, it will create a canvas skeletton based on mobicoop front-end & link the main bundle to it.

`mkdir -p ../path/to/newFront`

`npm run create-front-canvas ../path/to/newFront`

☢️ *Do not forget to commit into monorepo  ( & create branch if needed) when you edit bundle files* ☣️ 


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
