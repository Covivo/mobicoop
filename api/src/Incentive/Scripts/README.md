README
==============

***Documentation `/App/Incentive/Scripts`***

## [subscription.sh](./subscription.sh)

Ce script  permet d'éxécuter manuellement des commandes Symfony pour une ou plusieurs souscriptions.

Le script peut être exécuté depuis la commande bash `./subscription.sh`. La commande prend plusieurs paramètres :

| Paramètre       | Valeur                           | Description                                                  |
| --------------- | :------------------------------- | ------------------------------------------------------------ |
| `command`       | `commit` \| `reset` \|  `update` | La commande qui sera utilisée                                |
| `env`           | `dev` \| `test` \| `prod`        | L'environnement de l'instance                                |
| `journeys`      | `[id1,id2]`                      | Les identifiants des `CarpoolProofs` ou ` CarpoolPayments` qui sont utilisés selon le type et la command (commit ou update). La liste doit être ordonnée de la même manière que celle qui est donnée pour le paramètre `subscriptions`. Le `CarpoolProof` d'index 4 doit correspondre à la `Subscription` d'index 4. |
| `pushOnly`      | true                             | Option a utiliser pour les commandes de type **commit** ou **update**. Elle permet de préciser à l'API que le trajet est déjà associé à la souscription et que le processus doit uniquement transmettre les données à **mobConnect** et mettre à jour la souscription. |
| `subscriptions` | `[id1,id2]`                      | Les identifiants des souscriptions concerné. Les valeurs sont séparées par des virgules, sans espace. La liste doit être ordonnée de la même manière que celle qui est donnée pour le paramètre `journeys`. La `Subscription`  d'index 4 doit correspondre à la `CarpoolProof` d'index 4. |
| `type`          | `short` \|  `long`               | Le type des souscriptions à traiter par lot.                 |

Par exemple :

```bash
./subscription.sh --command=reset --type=short --subscriptions=1497,1343,1264 --pushOnly=true
```

produira :

```bash
../../../bin/console app:incentive:subscription-reset --env=dev --type=short --subscriptions=1497 --pushOnly
../../../bin/console app:incentive:subscription-reset --env=dev --type=short --subscriptions=1343 --pushOnly
../../../bin/console app:incentive:subscription-reset --env=dev --type=short --subscriptions=1264 --pushOnly
```

L'omission du paramètre `--env` produit un traitement par défaut sur l'environnement de développement.
