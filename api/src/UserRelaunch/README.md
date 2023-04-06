# Relance des utilisateurs

La fonctionnalité est configurée par le fichier JSON `api/config/params/reminders.json`.

Il doit être présent sur l'instance. C'est une copie du fichier `reminder.json.dist`, présent dans le même dossier. Sa configuration n'a pas besoin d'être finalisée si la fonctionnalité n'est pas utilisée.

Le contenu du fichier présente les éléments suivants :

```json
[
    {
        "name": "pay_after_carpool_regular",
        "schedules": {
            "onceOnly": true,
            "scheduleDays": ["Mon", "Wed"]
        }
    }
]
```

## Configuration

1. `reminder.json`
   
   Le fichier JSON est constitué d'un tableau d'objet.
   
   Chaque objet représente la configuration d'une relance. On peut y trouver les paramètres suivants :
   
   - **name** * : le nom de l'action qui réalisée. Il doit correspondre à la propriété Action::name sauvegardée en bdd.
   
   - **schedules**  * : c'est ici que sont enregistrés les planifications.
     
     - **onceOnly** : utilisé conjointement avec `scheduleDays` et des jours défini en toute lettre. Cette propriété boléènne permet de définir si la relance est effectuée par exemple tous les lundis ou simplement le lundi suivant la date de l'événement de référence.
       
       - pour une relance unique, on saisira la valeur `true`
       
       - pour une relance multiple, on saisiera la valeur `false` (pardéfaut).
     
     - **scheduleDays** : il s'agit ici de la définition des jours retenus pour les relances. On représentera les jours de la semaines par les 3 premiers caractères de leur nom. Par exemple `mon` pour monday. Il est possible de programmer des relances tous les lundi puis tous les jeudi. La valeur saisie sera `mon, thu`.
   
   Les attributs marqués `(*)` sont obligatoires.

2. Commande CRON
   
   Afin d'automatiser l'exécution dela fonctionnalité, un CRON doit être créé. Il permettra d'exécuter la commande `app:relaunch:users` :
   
   ```bash
   bin/console app:relaunch:users --env=[ENV]
   ```

## Développement

Pour une instance de développement, le controller `api/src/UserRelaunch/Controller/TestController`  est accessible via le endpoint `/relaunches/test`. Il autorise un débogage plus facile.

## Relances disponibles :

| Nom                 | Description                                               |
| ------------------- | --------------------------------------------------------- |
| `pay_after_carpool` | Notification pour paiement après réalisation d'un trajet. |
