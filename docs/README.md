![Logo mobicoop](https://www.mobicoop.fr/images/Mobicoop/general/logo-mobicoop.png)

# Mobicoop Platform

<p>
  <a href="https://codeclimate.com/github/Covivo/mobicoop/maintainability"><img src="https://api.codeclimate.com/v1/badges/a9393c639d5627da3883/maintainability" /></a>  <a href="https://www.gnu.org/licenses/agpl-3.0" ><img alt="License: AGPL v3" src="https://img.shields.io/badge/License-AGPL%20v3-blue.svg"/></a>
  <a href="https://gitlab.com/mobicoop/mobicoop/pipelines"><img alt="Build Status" src="https://gitlab.com/mobicoop/mobicoop/badges/dev/build.svg"></a>
  <a href="https://gitlab.com/mobicoop/mobicoop/commits/dev"><img alt="coverage report" src="https://gitlab.com/mobicoop/mobicoop/badges/dev/coverage.svg" /></a>
  <a href="https://ci.appveyor.com/project/MatthD/mobicoop/branch/dev"><img src="https://ci.appveyor.com/api/projects/status/lxrhumbiss1s084h/branch/dev?svg=true"></a>
</p>


# About
Open source api-based carpooling platform

# Install 🤖

For every method you will need to clone the repo

- Clone the repo
    - with ssh : `git clone git@gitlab.com:mobicoop/mobicoop-platform.git`
    - with https : `git clone https://gitlab.com/mobicoop/mobicoop-platform.git`

## 🐳 Docker 🐳

If you want to use docker we  provide you a simple way to install/start everything *No need to do the rest of the readme* !

### 🐳 Requirements 💻

- Docker
- Docker-compose
- make (base on unix, install cygwin or other bash on windows)

*Facing problems using docker with our project ? We may have answer at or [FAQ](#ℹ️Docker)*

⚡️ Do not forget to duplicate the .env  into a .env.local & edit the SIG_GEOCODER & SIG_GEOROUTER  

If you want to work on mobicoop we can provide you a some ip for those elements (they are used for autococomplete & road calc)



### 🐳 Install

- `make install` 


### 🐳 Start

- `make start` 

### 🐳 Post Install

After starting the app you can use `make fixtures` this will import de database, create private/public keys .. *DO IT 1 MN AFTER THE START, not before*


### 🐳 Access apps


After docker install, start & fixtures you will have access to the app @ :


- Api just go to [http://localhost:8080](http://localhost:8080) for API 

- Client just go to [http://localhost:8081](http://localhost:8081) for Main interface

- Admin just go to [http://localhost:8082](http://localhost:8082) for Main interface


### 🐳 Commands

There is a list of available command you can use with make to easily use the app inside docker


## 🐒 Without Docker 🐒


### Requirements 💻


#### Main interface

- PHP: =>7.1
- Composer =>1.7
- Node.js => 10
- xdebug (needed for code-coverage)
- Google Chrome (for functionnals tests)
- Openssl (for api certificats)
- If you have missing requirements during the installation check this docker file
install & enable in your .ini all its php extensions : [Docker file](https://github.com/vyuldashev/docker-ci-php-node/blob/master/Dockerfile)

### API

- MariaDB Database with a dedicated user on an [already existing bu empty](https://dev.mysql.com/doc/refman/8.0/en/creating-database.html) database
- A Geographic Information System (GIS), needed for direction calculation; we use the [Grapphopper routine engine](https://github.com/graphhopper/graphhopper) on a separate server; for now it's the only router supported but it could be replaced by any routing engine with little developments
- A Geocoder for reverse geocoding, we use [BazingaGeocoderBundle](https://github.com/geocoder-php/BazingaGeocoderBundle) that can use any geocoder (locationiq, google maps, bing...)

### Admin (Back office)

*for the moment there is no admin yet 🧐*



### Optional

Still this is purely optional it will drastically increase the speed to download deps

`composer global require hirak/prestissimo`

### Install deps

`npm install --no-save && npm run install-all` will perfom:
 - Api php vendor
 - Mobicoop vendor + node_modules + build css&js assets (webpack + babel) 
 - Download tools binaries (php-cs-fixer & phpdocumentor)

*Optional `npm install -g request-dispatcher` if you need Mass Matching.*

### Config

- *Duplicate, rename with .env.local & edit some env.local:*  
    - [.env api](api/.env)   
    - [.env mobicoop](client/.env) 

- *Duplicate, rename without .dist & edit the rdex json config files:*
  *Files .json are needed but you can let them with examples if you do not use RDEX*
    - [rdex operator api](api/config/rdex/operator.json.dist)
    - [rdex clients api](api/config/rdex/clients.json.dist)
    - [rdex providers api](api/config/rdex/providers.dist)


### Databases

⚠️ Don't forget to setup your SQL before !

- Create a new database schema needed for api based on the name you gave in .env file
- Configure environment variable DATABASE_URL in .env to connect your mysql/mariadb database
- Migrate all tables using : `cd api && npm run update-db`
- You're finally ready to simply run it !

### Security

The api is secured using JWT (Json Web Token), so you need to generate ssl keys in config/jwt : 
- private.pem
- public.pem

*You will be asked for a passphrase, you can use the one in the [.env](.env) file or change this passphrase to use your own*

To generate ssl keys, use these commands in a terminal: 

```bash
$ cd api
$ openssl genrsa -out config/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```


### install problems    

- *Sometimes if tools binaries do not work you will need on unix systems: `chmod 775 bin/*`*

- *Do not edit the dist file / .env with your config info*

 For all deps needed please check this [Docker file](https://github.com/vyuldashev/docker-ci-php-node/blob/master/Dockerfile)*


#### Start 🚀

To start the application simply run :

##### All

`npm start`

& just go to [http://localhost:8080](http://localhost:8080) for API 
& just go to [http://localhost:8081](http://localhost:8081) for mobicoop platform app

#### Api

& just go to [http://localhost:8080](http://localhost:8080) for API 

#### Client

& just go to [http://localhost:8081](http://localhost:8081) for Main interface

#### Admin

& just go to [http://localhost:8082](http://localhost:8082) for Main interface



### Tests 🎰

`npm test` will test the three apps

- We use [Kahlan](https://kahlan.github.io/docs/) to create unit/functional tests, you can launch them easily with:
- For functional tests you can do it 3 ways, with [kernels](https://api.symfony.com/4.1/Symfony/Component/HttpKernel/Kernel.html) (limited--), with [client](https://api.symfony.com/4.1/Symfony/Component/HttpKernel/Client.html) (limited), with [panther](https://github.com/symfony/panther) for a real browser testing (click,form ..)

## Functional tests

- *Duplicate, rename cypress.json.dist
    - [.cypress.json ](client)
- Start mobicoop in prod mode (important) `npm run start-production`
- On another terminal start functional/design tests `npm run test-functional-ci`   


# Documentation

A developer doc is available [here](https://mobicoop.gitlab.io/mobicoop/build/doc) (it is generated automatically after each production release), in order to generate the doc just do:

`npm run generateDoc`

# FAQ


### ℹ️ Why your app is doing something strange before any commit ？

    There are some git hooks implemented in this app:

    Run php-cs-fixer to clean your code following standard rules before any commit


### ℹ️ How to create my own front application from yours ？

    Because Mobicoop Platform is a monorepo, you can ask yourself how to create & dev on your own front-end application.
    Mobicoop comes with a simple script to run, it will create a canvas skeleton based on mobicoop front-end & link the main bundle to it.
    Just go to the root of Mobicoop repo and do the flollowing:

1. `npm run create-front-canvas $$nameOfProject$$` will create the folder next to the current
2. Edit the webpack file into the $$nameOfProject$$ folder & comment/uncomment the needeed
3. Read the doc inside the created folder 
4. Every Specific file copied is in `bin/helper/client-canvas` do not forget to update thoses files when needed! (for eg add dependencies inside package.json)

    ☢️ *Do not forget to commit into monorepo  ( & create branch if needed) when you edit bundle files* ☣️ 

    ☢️ *This does just duplicate the front app, you can overwrite template, controller ..., the API is still the same, bundle too* ☣️ 


### ℹ️ How to link the bundle to an already existing app ？

`cd ./path/to/mobicoop-mono-repo`

`npm run link-bundle ../path/to/my/already-existing-app`


### ℹ️ How can I contribute to Mobicoop Platofmr developpement ？

To contribute to Mobicoop Platform, please do the following:

1. Complete and sign the Contributor License Agreement ([see examples here](https://gitlab.com/mobicoop/mobicoop-platform/tree/master/docs/ContributorLicenseAgreement)) : must be part of your first commit
2. Create a branch by feature or fork the repo
3. [Start](#start) the 3 apps  (mandatory to watch js/css/sass):
4. Add some Unit Tests and/or functional tests and check if build passes
5. Create a pull request & set reviewer before merge

  We have some guidelines 📖📚
  - [JS GuideLine](https://github.com/airbnb/javascript#whitespace) 
  - [Symfony GuideLine](https://symfony.com/doc/current/contributing/code/standards.html)
  - api-plateform use [schema.org](https://schema.org) & [JSON-LD](https://json-ld.org)

  and [contributor covenant](https://www.contributor-covenant.org)

When you push on this repo, pipeline are automatically trigerred, if you do not want that, please add the message `skip` into your commit; for eg: `git commit -m"update readme, skip"`


### ℹ️ I do not understand which .env to edit  ？

If you are in developpement mod, after `composer install` you could see a new `.env`. This file is the default configuration file and *is versioned* (this is a new behavior in Symfony 4.2). *DO NOT* modify this file for your own needs, create instead a [.env.local](.env.file), which *won't be versioned*.

- APP_ENV=dev *used to indicate you are in developpement mod*
- DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name *used to connect to mysql DB*

*IF YOU NEED TO ADD OTHER ENV VARIABLES ADD IT TO [.env.local](.env.local), not just .env*



### ℹ️ How to send data to the api ？

The api is secured using JWT (Json Web Token), so you need to get a token before you can send queries to the api.
To do so you have to send your credentials to [http://localhost:8080/auth](http://localhost:8080/auth)
You can do it using an app like Postman with the following settings : 
- method : POST
- Headers : Content-Type: application/json
- Body: 
  ```
  {
    "username":"your username",
	  "password":"your password"
  }
  ```

The swagger documentation can be found after install to see all route @ [http://localhost:8080/doc](http://localhost:8080/doc)

### ℹ️ What kind of technos do you use ？

![Technos used](tech.png)


### ℹ️ VueJS help


The main library for javascript used is [vue-js](https://fr.vuejs.org/index.html), everything about them are in [components](assets/js/components)

We use webpack-encore with babel, if you need to use some new fun stuf in js you can [check stage](http://kangax.github.io/compat-table/esnext/)(babel 6 part) install & require the [right plugin](https://babeljs.io/docs/en/6.26.3/plugins) in [webpack config](webpack.config.js)

💄 We use [esLint rules](https://eslint.org/docs/rules/) to check code linting,if some rules are too restricted you can disable them or make the warning only in [.eslintrc.json](.eslintrc.json)

*For some mistakes as bad indent, spaces .. a fix is automaticaly applied !*


### ℹ️ Bulma/Buefy help

We use buefy which is a vue adaptation of bulma as a front-end scss framework, you can:

- Check the [buefy doc](https://buefy.github.io/documentation/layout) to find elements you want to use, such as modal, layou ..
- Change any default [bulma variables](https://bulma.io/documentation/customize/variables/) in [variable.scss](assets/css/_variables.scss) such as [mobile variables](https://bulma.io/documentation/overview/responsiveness/#variables)
- Use bulma variables inside vue-js components as in the [parrot sample](assets/js/components/Parrot.vue)

###  ℹ️ PHP tech doc

You can find our php technical doc for api & interface [here](https://mobicoop.gitlab.io/mobicoop/php/)


### ℹ️ Is Windows supported ？

Currently not but you are welcome to increase this guide to help windows community, for the moment we know those needs:

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

### ℹ️How to contribute to Mobicoop 

*Don't forget to install all dependencies before (check README.md)*

- Go into project's root folder
- Run your console
- execute `npm run contribute`
- Follow all steps and fill in asked information.


### ℹ️Docker

#### Commands:

- install `make install`, install all dependencies & stuffs
- Start `make start`, start the api, client, admin
- stop `make stop` , will stop all the containers
- restart `make restart` , will restats all the containers
- Update `make update`, stop & remove containers, pull the branch, install deps & restart containers
- logs `make logs` , will show you the last 100 lines logs for all containers
- status `make status` , will get info about the current container status
- remove `make remove` , will remove the sopped containers
- clean `make clean` , will remove container, volumes,  drops downloaded deps (vendor , node_modules)
- go-platform `make go-platform`, will send you inside the running platform container
- go-db `make go-db`, will send you inside the running mariadb container

#### Docker commun errors

- If your docker require sudo after install just [add it to your group + restart your computer](https://docs.docker.com/install/linux/linux-postinstall/)

#### Docker-composer commun errors

- If you do not have docker-compose command just [install it](https://docs.docker.com/compose/install/#install-compose) 


#### Make on windows

- Windows does not have a prebuild make , install one of this [two possibilities](http://stat545.com/automation02_windows.html)

### ℹ️ What are the hardware requirements needed to run an instance of Mobicoop Platform ?

The instance of Mobicoop-platform itself doesn't need a tremendous server configuration : our test instance runs on a (very) small virtual machine with 2 vCores @3Ghz, 7Go of RAM and 50Go SSD !

Of course you will need more hardware requirements if you think your instance will be very used; in this case 250Go SSD, 8 cores and 16Go RAM would be a good configuration, especially if you need to make mass matching.

On the other hand, the platform needs information coming from a geographic information system (GIS), which can be VERY needy ! For example, the GIS we use for our platform is a dedicated server with 8 Xeon CPU @2.40Ghz, 64go RAM and 2To HD.


### ℹ️ What screen sizes are covered by Mobicoop Platform, and how do you manage mobile devices ?

To offer a similar user experience to mobile users between the mobile app and the web app, a dedicated project exists : **[Mobicoop Mobile](https://gitlab.com/mobicoop/mobicoop-mobile)** .
Mobile devices are thus managed by a redirection to a web app instance of Mobicoop Mobile hosted with its own subdomain.

The redirection depends on the screen size as follows :

| Device | Vuetify code | Viewport range  | Assigned project  |
|-----------------|------------|:---------------:|--------------|
| Extra small | xs  | < 600px      | Mobicoop Mobile    |
| Small | sm  | 600px > < 960px      | Mobicoop Mobile    |
| Medium | md  | 960px > < 1264px*      | **Mobicoop Platform**    |
| Large | lg  | 1264px > < 1904px*      | **Mobicoop Platform**    |
| Extra large | xl  | > 1904px*     | **Mobicoop Platform**    |

_NB: * -16px on Desktop (see [Vuetify Grid system](https://vuetifyjs.com/en/components/grids) for more information)_




# 🎉Welcome to Mobicoop's contributing program !🎉