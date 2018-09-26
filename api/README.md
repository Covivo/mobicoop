# Mobicoop-api

![Logo Mobicoop](logo.jpg)



<p align="center">
  <a href="https://www.gnu.org/licenses/agpl-3.0" ><img alt="License: AGPL v3" src="https://img.shields.io/badge/License-AGPL%20v3-blue.svg"/></a>
  <a href="https://gitlab.com/mobicoop/mobicoo-api/-/jobs"><img alt="Build Status" src="https://gitlab.com/mobicoop/mobicoo-api/badges/master/build.svg"></a>
  <a href="https://gitlab.com/covivo/mobicoop/commits/master"><img alt="coverage report" src="https://gitlab.com/covivo/mobicoop/badges/master/coverage.svg" /></a>
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

### Install

- Clone the repo

`git clone https://gitlab.com/mobicoop-company/mobicoop-api`

`cd mobicoop-api`

- Install symfony dependencies & npm dependencies
`composer install`


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
