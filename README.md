# CafThé Dashboard

Back-office de la boutique CafThé pour les vendeurs et l'administrateur : indicateurs, produits,
commandes en ligne, clients, ventes en magasin, gestion des vendeurs.
Il utilise la même base de données que l'[API CafThé](https://github.com/Sylvain-code815/Cafthe-API).

**Technologies :** PHP 8 (sans framework), architecture MVC, PDO + MySQL / MariaDB, sessions PHP.

## Installation en local

1. Avoir la base `cafthe` créée par l'API (`database/cafthe.sql` du dépôt de l'API).
2. Copier `config/config.example.php` en `config/config.php` et renseigner les identifiants de la base.
3. Lancer le serveur : `php -S localhost:8000 -t public`
4. Ouvrir `http://localhost:8000`

## Comptes de test

Mot de passe commun : `CafThe#2026test`

| Rôle | Identifiant | Accès |
|---|---|---|
| Administrateur | admin@cafthe.com | Tout, y compris la gestion des vendeurs |
| Vendeur | paul.durand@cafthe.com | Tout sauf la gestion des vendeurs |

## Structure MVC

```
public/index.php     Point d'entrée unique : autoload, session, table de routage, contrôle des accès
public/css/          Feuille de style
config/              config.example.php (modèle commité) ; config.php (identifiants, jamais commité)
src/Core/            Database (PDO), Model et Controller de base, Auth (rôles, expiration de session)
src/Controllers/     Reçoivent la requête, valident, appellent le modèle, choisissent la vue
src/Models/          Requêtes SQL préparées (Product, Order, Client, Employee, Stats)
src/helpers.php      Fonctions utilitaires (échappement HTML, redirections…)
views/               Gabarits HTML par section + layout.php commun
```

Seul `public/` est exposé au web : `config/` et `src/` restent hors de portée du navigateur.

## Sécurité

- Mots de passe hachés (bcrypt), requêtes préparées, sorties échappées
- Jeton CSRF sur tous les formulaires
- Cookie de session HttpOnly + SameSite Strict, Secure en HTTPS
- Déconnexion après 30 minutes d'inactivité, accès par rôle (vendeur / admin)
- En-tête `X-Robots-Tag: noindex` : le dashboard n'est pas indexé

## Déploiement (Plesk)

Site en ligne : https://dashboard-cafthe.slaurent.dev-campus.fr

1. Créer un sous-domaine avec PHP 8.x, racine du document : `…/public`
2. Déployer le code par Git (branche `master`), puis « Récupérer maintenant » après chaque push
3. Créer `config/config.php` sur le serveur avec les identifiants de la base de prod
   (`host` = `localhost`, `port` = `3306`, base partagée avec l'API)
4. Activer HTTPS (Let's Encrypt) et la redirection HTTP vers HTTPS
5. Vérifier : la page de connexion s'affiche et `/config/config.php` renvoie une erreur 404

## Planification

| Étape | Tâche | Réalisée le | Temps prévu | Temps passé |
|---|---|---|---|---|
| 1 | Structure MVC, configuration, connexion à la base | 13/11/2026 | | |
| 2 | Authentification (connexion, rôles, expiration de session) | 13/11/2026 | | |
| 3 | Tableau de bord et KPI | 14/11/2026 | | |
| 4 | Gestion des produits | 14/11/2026 | | |
| 5 | Commandes en ligne et statuts | 14/11/2026 | | |
| 6 | Clients | 14/11/2026 | | |
| 7 | Vente en magasin | 14/11/2026 | | |
| 8 | Gestion des vendeurs (admin) | 14/11/2026 | | |
| 9 | Profil et changement de mot de passe | 14/11/2026 | | |
| 10 | GitHub, déploiement, documentation, planification | 14/11/2026 | | |
