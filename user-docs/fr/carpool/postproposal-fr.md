_ [Documentation fonctionnelle](../README.md) / Covoiturage / Publier une annonce_
# Publier une annonce


_**Documentation en cours**_ :construction:

[[_TOC_]]

## 1. Commencer votre annonce :construction:
## 2. Planification :construction:
## 3. Trajet :construction:
## 4. Passagers :construction:
## 5. Participation  :construction:

### Règle d'arrondi des participations financières
La participation financière est calculée en multipliant la distance par le coût kilométrique du trajet. Mais pour plus de facilité d'usage au quotidien, cette valeur est arrondie selon les règles suivantes :

- si c'est une annonce de **trajet régulier**
    - le prix est arrondi alors à la dizaine de centimes (ex: 7,68€ sera arrondi à 7,7€)
- si c'est une annonce de **trajet ponctuel**
    - si le prix calculé est inférieure ou égale à 5€, le prix est arrondi alors au demi-euro près (ex: 4,37€ sera arrondi à 4,5€)
    - sinon, si le prix calculé est supérieur strictemnt à 5€, le prix est arrondi alors à l'euro près (ex: 6,65€ sera arrondi à 7€)



## 6. Message  :construction:
## 7. Récapitulatif  :construction:

## Options techniques

### Règles de lutte contre les arnaqueurs
- Système optionnel activable sur certaines instances.
- Ne sont pas prises en compte les annonces passager.
- Si distance de l'annonce est supérieure à un seuil : 
  - vérification que l'annonce ne dépasse le nombre maximum d'annonces (dont la distance est au-dessus du seuil) pour le même jour
  - SINON vérification que la date de départ de l'annonce en cours de création est bien après l'arrivée de toute annonce (suffisamment longue en distance) publiée pour le même jour
  - SINON vérification que la date de départ de l'annonce en cours de création est bien suffisamment après l'arrivée de toute annonce (suffisamment longue en distance) publiée pour le même jour ou la veille pour le point de départ de l'annonce en cours de création puisse rejoint une fois au point d'arrivée de l'annonce du même jour

En complément, il y a aussi une liste noire de fraudeurs qui peuvent être bloqués à l'inscription.



