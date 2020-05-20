# Mobicoop-admin

![Logo mobicoop-bo](logo.png)

BackOffice in front of coviride API

<p align="center">
  <a href="https://www.gnu.org/licenses/agpl-3.0" ><img alt="License: AGPL v3" src="https://img.shields.io/badge/License-AGPL%20v3-blue.svg"/></a>
</p>


### Test

`npm test` will lauch tests

### Start

To start the application simply enter :

`npm start`

& just go [http://localhost:8082](http://localhost:8082)


### Licence
Mobicoop software is owned by Mobicoop cooperative. Mobicoop cooperative is opened to any individual, company or public authority who wish to become a shareholder.
In order to increase the impact of our platform to any sort of clients whatever type of contractual relationship theyu require, Mobicoop software is dual-licensed:
 - [AGPL-3](https://www.gnu.org/licenses/agpl-3.0)
 - proprietary software

### Scope
In order to link the dashboard and display the statistics, you need to :
_ Create a new kibana instance, linked to the database you want
_ Set the good infos in the .env files in the Admin folder
    `REACT_APP_KIBANA_URL` = https://scope.test.mobicoop.io
      Test or without test, dependind on the instance
    `REACT_APP_SCOPE_INSTANCE_NAME`   
      The name of the instance in the kibana config file
    `REACT_APP_KIBANA_DASHBOARD`
      The user dashboard id in Kibana

For the .env files, this is how react read .env : files on the left have more priority than files on the right:

npm start: .env.development.local, .env.development, .env.local, .env
npm run build: .env.production.local, .env.production, .env.local, .env
npm test: .env.test.local, .env.test, .env (note .env.local is missing)

Every time you do a modifications in the .env files, you have te rebuild the admin.
Go in the admin folder and execute : `npm run build`
