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
