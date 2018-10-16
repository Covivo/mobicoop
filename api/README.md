# Mobicoop-api

![Logo Mobicoop](logo.png)



<p align="center">
  <a href="https://www.gnu.org/licenses/agpl-3.0" ><img alt="License: AGPL v3" src="https://img.shields.io/badge/License-AGPL%20v3-blue.svg"/></a>
  <a href="https://gitlab.com/mobicoop/mobicoop-api/-/jobs"><img alt="Build Status" src="https://gitlab.com/mobicoop/mobicoop-api/badges/master/build.svg"></a>
  <a href="https://gitlab.com/mobicoop/mobicoop-api/commits/master"><img alt="coverage report" src="https://gitlab.com/mobicoop/mobicoop-api/badges/master/coverage.svg" /></a>
  <a href="https://ci.appveyor.com/project/MatthD/mobicoop/branch/master"><img src="https://ci.appveyor.com/api/projects/status/lxrhumbiss1s084h/branch/dev?svg=true"></a>
</p>

### About mobicoo-api

Simple API based on [api-plateform](https://api-platform.com), which is a symfony like project to build RESTAPI


### Requirements

- PHP: =>7.1
- Composer =>1.7
- Node.js => 10
- xdebug (needed for code-coverage)

- for Windows check the [windows requirement](#windows-requirements) part

- MariaDB Databses with access user connection with an [already existing bu empty](https://dev.mysql.com/doc/refman/8.0/en/creating-database.html) database for api

### Install

- Clone the repo

`git clone https://gitlab.com/mobicoop/mobicoop-api`

`cd mobicoop-api`

- Install symfony dependencies: `composer install`

- Download dev requirements `npm run postinstall`

- On unix systems: `chmod 775 bin/*`

- Edit [.env](.env) file (check [Stuff for devs Section](#stuff-for-devs))

- [Update Schema Database](#update-schema-database) if it's the first time you start the app


### Test
 this section if clearly empty ... 🙁

### Start

To start the application simply enter :

`npm start`

& just go [http://localhost:8080](http://localhost:8080) 


##### Stuff for devs

If you are in developpement mod, after `composer install` you could see a new `.env` file which is an instance of [.env.dist](.env.dist), *this file is not versionned*, be sure to update this file to indicate the *right ENV variables*

- APP_ENV=dev *used to indicate you are in developpement mod*
- DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name *used to connect to mysql DB*

*IF YOU NEED TO ADD OTHER ENV VARIABLES ADD IT TO [.env.dist](.env.dist), not just .env*

When you push on this repo, pipeline are automatically trigerred, if you do not want that, please add the message `skip` into your commit; for eg: `git commit -m"update readme, skip"`

##### Update Schema Database

- `npm run updateDb`, will start migration with new schema if need, if i'ts the first install, it will create the schema in the empty database.


### Conventions

Some conventions are used by api-plateform such as [schema.org](https://schema.org) & [JSON-LD](https://json-ld.org)


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
- Install [xdebug](https://burhandodhy.me/2017/08/29/how-to-install-xdebug-on-windows/) & link it to you php.ini 
- Install windows dev tools: `npm install --global --production windows-build-tools`
- ... then you can follow the [install section](#install)
