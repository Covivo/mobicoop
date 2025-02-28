_Remplacer le texte en italique par votre propre description_

### Pourquoi cette merge request ?

_A quoi répond cette merge ? Décrit le problème ou la user story à résoudre._

### Quel est le code implémenté, quelle est la solution choisie

_Décrire le code implémenté, quelle est la solution choisie, quelles étaient les solutions envisagées._

### Problèmes et impacts sur le code d'autres projets

_Fournir les liens vers les tickets et les merge requests (depuis Gitlab et Redmine)._

_Ajouter les liens vers d'autres projets impactés._

### Other Information

_Inclure toutes autres informations ou considérations pour les reviewers._

## Checklists

### Merge Request

-   [ ] Branche cible identifiée.
-   [ ] Le code est à jour avec la branche cible (par exemple, fait un `git pull origin dev` localement dans votre branche si elle vient de `dev` avant de demander une MR. Vous réduirez les risques de conflits).
-   [ ] Description remplie.
-   [ ] Impacts sur les autres projets identifiés.
-   [ ] Tests lancés localement.
-   [ ] Besoin d'ajouter une variable au .env (éventuellement également dans twig.yaml) dans chaque client vérifié.
-   [ ] Besoin d'adapter les composants surchargés (MHeader, MFooter...) dans chaque client vérifié.
-   [ ] Ajouter des tâches à faire dans le fichier [met-mep](https://docs.google.com/document/d/1U3edMGP6MrqX5fApMnx2hfTvMG-Xd6v9PpDVFR37UVE/edit) vérifié.
-   [ ] Toutes les migrations ont été testées et lancées sur l'environnement local.

### Code Review

-   [ ] La branche cible de la MR et le champ `Version Cible` dans redmine sont les mêmes.
-   [ ] Le code est facilement lisible et compréhensible.
-   [ ] Les commit sont tous reliés à la MR et bien écrits (Atomic commit).
-   [ ] Pas d'affichage de logs ou de débug inutiles.
