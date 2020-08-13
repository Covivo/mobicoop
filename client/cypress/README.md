# Cypress
Cypress est un outil open source de test d’application web, qui a pour but de rendre la mise en place des tests end-to-end moins laborieuse.

### Créer une nouvelle instance
Pour créer une nouvelle instance de Mobicoop, il faut modifier certains fichiers docker
```
admin/package.json : ligne 59 -> remplacer "8082" par "8086"
api/package.json : ligne 8-9 -> remplacer "8080" par "8084"
client/cypress.json :
client/package.json : ligne 10-11 -> remplacer "8081" par "8085"
docker-compose-builder-darwin.yml :
docker-compose-builder-linux.yml :
docker-compose-darwin.yml : ligne 26, 39, 54, 57, 58 -> remplacer "mobicoop_platform" par "mobicoop_platform_test" et ligne 31 -> remplacer "mobicoop_db" par "mobicoop_db_test"
docker-compose-linux.yml :
```

### Avant de lancer les tests
Avnt de lancer les tests, il est préférable d'avoir une base vide.
Pour vider votre de base, aller a la racine du dépot Mobicoop, puis tapez la comamande : 
```sh
$ go-platform
$ php bin/console doctrine:schema:drop --env=dev --force --full-database    
$ exit
```
Puis pour remigrer la DB
```sh
$ go-platform
```

### Lancement
```sh
$ cd mobicoop-platform
$ cd client
$ npm run cypress
```
Pour executer des tests, cliquez sur un de fichier sur la fenetre qui vient d'étre ouvert
  - 00.reset.js : Supprimer les données dans la base puis migre la DB
  - 01.user.js : Permet de tester l'inscription, la connexion et la suppression d'un compte
  - 02.carpool.js : Créer une annonce occasionel et regulier, puis verifie l'existence des deux annonces
  - 03.event.js : Test la création d'un evenement. Il va ensuite créer une annonce et verifier son existence.
  - 04.event.js : Créer une communauté et une annonce, puis verifie leurs existance.
  - 05.message.js : Test l'envoie d'un message et la possibilité d'accepeter ou refuser une demande de covoiturage. 
