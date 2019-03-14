# Mobicoop-api

![Logo Mobicoop](logo.png)

<p align="center">
  <a href="https://www.gnu.org/licenses/agpl-3.0" ><img alt="License: AGPL v3" src="https://img.shields.io/badge/License-AGPL%20v3-blue.svg"/></a>
</p>

### About mobicoo-api

Simple API based on [api-plateform](https://api-platform.com), which is a symfony like project to build RESTAPI


### Requirements

- PHP: =>7.1
- Composer =>1.7
- Node.js => 10
- xdebug (needed for code-coverage)

- for Windows check the [windows requirement](#windows-requirements) part

- MariaDB Database with access user connection with an [already existing bu empty](https://dev.mysql.com/doc/refman/8.0/en/creating-database.html) database for api

### Install

`npm install` will do the necessary jobs

*DUPLICATE THE [config.json.dist](config.json.dist) INTO A `config.json` FILE IF YOU WANT TO ADD ANY EXTERNAL PROVIDER*

#### Install Databases needs

⚠️ Don't forget to setup your SQL before !

- Create a new database schema needed for api based on the name you gave in .env file
- Configure environment variable DATABASE_URL in .env
- Migrate all tables using : `php bin/console doctrine:migrations:migrate -n`
- You're finally ready to simply run it !

If you want to check that you are up-to-date in your SQL schema : `npm run updateDb`

#### Security

The api is secured using JWT (Json Web Token), so you need to generate ssl keys in config/jwt : 
- private.pem
- public.pem

To generate ssl keys, use these commands in a terminal, at the root of the api : 
```
$ openssl genrsa -out config/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```
You will be asked for a passphrase, you can use the one in the [.env](.env) file or change this passphrase to use your own (see *stuff for devs* below for your local .env).

### Test
 for the moment there is no any tests yet..

### Start

To start the application simply enter :

`npm start`

& just go to [http://localhost:8080/doc](http://localhost:8080/doc) to see the swagger documentation

The api itself stands at [http://localhost:8080](http://localhost:8080)
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

### Documentation
The swagger documentation can be found at [http://localhost:8080/doc](http://localhost:8080/doc)
To send queries using a token you first need to get a token (see above), and copy it.
Then use this token to authorize queries :
- click on Authorize button
- on 'Value' write : bearer \<your token\>
- click on Authorize

#### Stuff for devs

If you are in developpement mod, after `composer install` you could see a new `.env`. This file is the default configuration file and *is versioned* (this is a new behavior in Symfony 4.2). *DO NOT* modify this file for your own needs, create instead a [.env.local](.env.file), which *won't be versioned*.

- APP_ENV=dev *used to indicate you are in developpement mod*
- DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name *used to connect to mysql DB*

*IF YOU NEED TO ADD OTHER ENV VARIABLES ADD IT TO [.env.local](.env.local), not just .env*

When you push on this repo, pipeline are automatically trigerred, if you do not want that, please add the message `skip` into your commit; for eg: `git commit -m"update readme, skip"`


### Database

You will find a documentation about the database [here](https://mobicoop.gitlab.io/mobicoop/database/)


##### Update Schema Database

- `npm run updateDb`, will start migration with new schema if need, if i'ts the first install, it will create the schema in the empty database.


### Conventions

Some conventions are used by api-plateform such as [schema.org](https://schema.org) & [JSON-LD](https://json-ld.org)


### Licence
Mobicoop software is owned by Mobicoop cooperative. Mobicoop cooperative is opened to any individual, company or public authority who wish to become a shareholder.
In order to increase the impact of our platform to any sort of clients whatever type of contractual relationship theyu require, Mobicoop software is dual-licensed:
 - [AGPL-3](https://www.gnu.org/licenses/agpl-3.0)
 - proprietary software
