Mobicoop
=======

![Logo mobicoop](logo.jpg)

<p align="center">
  <a href="https://codeclimate.com/github/Covivo/mobicoop/maintainability"><img src="https://api.codeclimate.com/v1/badges/a9393c639d5627da3883/maintainability" /></a>  <a href="https://www.gnu.org/licenses/agpl-3.0" ><img alt="License: AGPL v3" src="https://img.shields.io/badge/License-AGPL%20v3-blue.svg"/></a>
  <a href="https://gitlab.com/mobicoop/mobicoop/pipelines"><img alt="Build Status" src="https://gitlab.com/mobicoop/mobicoop/badges/dev/build.svg"></a>
  <a href="https://gitlab.com/mobicoop/mobicoop/commits/dev"><img alt="coverage report" src="https://gitlab.com/mobicoop/mobicoop/badges/dev/coverage.svg" /></a>
  <a href="https://ci.appveyor.com/project/MatthD/mobicoop/branch/dev"><img src="https://ci.appveyor.com/api/projects/status/lxrhumbiss1s084h/branch/dev?svg=true"></a>
</p>

# About mobicoop

Carpool apps available on a territory, allows connection between carpoolers making the same trip daily or punctually.

For more informations, check their readme:

- [Api](/api)
- [Admin](/interfaces/admin)
- [Mobicoop](/interfaces/mobicoop)

# Requirements 💻

- PHP: =>7.1
- Composer =>1.7
- Node.js => 10
- xdebug (needed for code-coverage)
- Google Chrome (for functionnals tests)
- Openssl (for api certificats)
- If you have missing requirements during installation check this docker file
install & enable in your .ini all its php extensions : [Docker file](https://github.com/vyuldashev/docker-ci-php-node/blob/master/Dockerfile)


# Install 🤖

- Clone the repo

`git clone https://gitlab.com/mobicoop/mobicoop`

`npm install --no-save` will perfom:
 - Api php vendor
 - Mobicoop vendor + node_modules + build css&js assets (webpack + babel) 
 - Download tools binaries (php-cs-fixer & phpdocumentor)

*Sometimes if tools binaries do not work you will need on unix systems: `chmod 775 bin/*`*

*=> Duplicate, rename without .dist & config files:*
    - [config.json api](api/config.json.dist)

*=> Duplicate, rename with .env.local & edit some env.local:*  
    - [.env api](api/.env)   
    - [.env mobicoop](interfaces/mobicoop/.env) 

*=> Do not edit the dist file with your config info*

*If you have missing modules during installation check this docker file
and install all its php extensions : [Docker file](https://github.com/vyuldashev/docker-ci-php-node/blob/master/Dockerfile)*


### API

There are some needs for api database , check them [/api](/api)

# Start 🚀

To start the application simply run :

`npm start`

& just go to [http://localhost:8080](http://localhost:8080) for API 
& just go to [http://localhost:8081](http://localhost:8081) for mobicoop app


# Tests 🎰

`npm test` will test the three apps

- We use [Kahlan](https://kahlan.github.io/docs/) to create unit/functional tests, you can launch them easily with:
- For functional tests you can do it 3 ways, with [kernels](https://api.symfony.com/4.1/Symfony/Component/HttpKernel/Kernel.html) (limited--), with [client](https://api.symfony.com/4.1/Symfony/Component/HttpKernel/Client.html) (limited), with [panther](https://github.com/symfony/panther) for a real browser testing (click,form ..)



## Documentation

A developer doc is available [here](https://mobicoop.gitlab.io/mobicoop/build/doc) (it is generated automatically after each production release), in order to generate the doc just do:

`npm run generateDoc`

# FAQ


### ℹ️ Why your app is doing something strange before any commit ？

    There are some git hooks implemented in this app:

    Run php-cs-fixer to clean your code following standard rules before any commit


### ℹ️ How to create my own front application from yours ？

    Because Mobicoop is a monorepo, you can ask yourself how to create & dev on your own front-end application.
    Mobicoop comes with a simple script to run, it will create a canvas skeleton based on mobicoop front-end & link the main bundle to it.
    Just go to the root of Mobicoop repo and do the flollowing:

`mkdir -p ../path/to/newFront`

`npm run create-front-canvas ../path/to/newFront`

    ☢️ *Do not forget to commit into monorepo  ( & create branch if needed) when you edit bundle files* ☣️ 

    ☢️ *This does just duplicate the front app, you can overwrite template, controller ..., the API is still the same, bundle too* ☣️ 


### ℹ️ How to link the bundle to an already existing app ？

`cd ./path/to/mobicoop-mono-repo`

`npm run link-bundle ../path/to/my/already-existing-app`


### ℹ️ How can I contribute to the mobicoop developpement ？

    To contribute to the mobicoop application, please do the following:

    1. Create a branch by feature or fork the repo
    2. [Start](#start) the 3 apps  (mandatory to watch js/css/sass):
    3. Add some Unit Tests and/or functional tests and check if build passes
    4. Create a pull request & set reviewer before merge

  We have some guidelines 📖📚
  - [JS GuideLine](https://github.com/airbnb/javascript#whitespace) 
  - [Symfony GuideLine](https://symfony.com/doc/current/contributing/code/standards.html)

  and [contributor covenant](https://www.contributor-covenant.org)


### ℹ️ Could you give me some informations about your licence ？

    Mobicoop software is owned by Mobicoop cooperative. Mobicoop cooperative is opened to any individual, company or public authority who wish to become a shareholder.
    In order to increase the impact of our platform to any sort of clients whatever type of contractual relationship they require, Mobicoop software is dual-licensed:
    - [AGPL-3](https://www.gnu.org/licenses/agpl-3.0)
    - proprietary software

    Since Mobicoop is dual licensed AGPLv3/proprietary, all components used for Mobicoop must be compatible with both licenses. As a consequence, all components integrated into Mobicoop source code **must be released with a _permissive_ open source license**. More information on license compatibility for [software components](https://dwheeler.com/essays/floss-license-slide.html) and [content components (Creative Commons issues)](https://opensource.stackexchange.com/questions/7750/which-creative-commons-licenses-are-permissive-enough-for-proprietary-software/7751).

    Mobicoop CI process includes a License Management which checks the license of all components part of a merge request. The most common _permissive_ licenses have already been added to the approved licenses list of this License Management process.
    In case you have one of the following situation while merging, please get in touch with Mobicoop project licensing issues expert before merging:
    - one of the license pops up as non part of the approved license for the project
    - a component is license under AGPLv3 and is not Mobicoop itself