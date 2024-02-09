# HealthStatus

## 1. Retrouvez sur une page l'état de santé de votre Thelia

- Proposer de partager des infos anonymes avec OpenStudio
    - version thelia
    - version modules
    - liste des modules et état d'activité
    - check les overrides
    - compatibles tout mod
    - ????

## 2. Spécifications

L'objectif de ce module est d'offrir aux commerçants une vision synthétique de l'état de leur Thelia,
à la manière de WordPress (cf. https://fr.wordpress.org/support/article/site-health-screen).

Le module utilise les données suivantes pour établir des diagnostics :

- Le nom du module
- Sa version courante
- Si le module est activé ou non
- S'il a été installé par composer ou manuellement (présence dans le `composer.json`)
- S'il est surchargé (overriden) (examiner la section PSR-4 pour contrôler qu'un module est surchargé)
- Si des portions de Thelia sont overrident (même principe)
- Si un README figure dans le répertoire de base d'un override

## Diagnostics réalisé&s par le module



### Données envoyées à thelia.net

Si le client a accepté de le faire, thelia va remonter des information sur thelia.net et/ou sur une instance Matomo
On peut proposer (à valider) des stats anonymes ou non-anonymes. Les clients identifies pourront recevoir
des notifications de mise à jour de thelia, des modules et templates.

Les informations transmises sont les suivantes :

- Données analytiques gérées par Matomo
- Numéro de version de Thelia
- Liste des modules activés avec leur type
- Pour les clients non-anonymisés on remonte en plus les données citées au §2
