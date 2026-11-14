# CafThé Dashboard

Back-office de la boutique CafThé pour les vendeurs et l'administrateur : indicateurs, produits,
commandes en ligne, clients, ventes en magasin, gestion des vendeurs.
Il utilise la même base de données que l'[API CafThé](https://github.com/Sylvain-code815/Cafthe-API).

**Technologies :** PHP 8 (sans framework), architecture MVC, PDO + MySQL / MariaDB, sessions PHP.

## Installation en local

1. Avoir la base `cafthe` créée par l'API (`database/cafthe.sql` du dépôt de l'API, voir [Base de données](#base-de-données)).
2. Copier `config/config.example.php` en `config/config.php` et renseigner les identifiants de la base.
   En local, mettre `'app_url' => 'http://localhost:8000'` et `'transport' => 'log'` dans `mail` :
   les emails (mot de passe oublié) sont alors écrits dans `storage/mails.log` au lieu d'être envoyés.
3. Lancer le serveur : `php -S localhost:8000 -t public`
4. Ouvrir `http://localhost:8000`

Le guide d'utilisation pour les vendeurs est dans [docs/MANUEL_VENDEUR.md](docs/MANUEL_VENDEUR.md).

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
src/Core/            Database (PDO), Model et Controller de base, Auth (rôles, expiration de session),
                     Config (lecture de config.php), Mailer (envoi des emails)
src/Controllers/     Reçoivent la requête, valident, appellent le modèle, choisissent la vue
src/Models/          Requêtes SQL préparées (Product, Order, Client, Employee, PasswordReset, Stats)
src/autoload.php     Chargement automatique des classes (pas besoin de Composer en production)
src/helpers.php      Fonctions utilitaires (échappement HTML, redirections…)
views/               Gabarits HTML par section + layout.php commun
tests/               Tests PHPUnit (unitaires et intégration)
docs/                Manuel d'utilisation vendeur
```

Seul `public/` est exposé au web : `config/`, `src/` et `tests/` restent hors de portée du navigateur.

## Base de données

Le dashboard utilise la **même base que l'API** (`cafthe` en local, la base de prod sur Plesk) :
une vente en magasin enregistrée ici apparaît dans l'espace client du site, et inversement.
Le schéma est géré dans le dépôt de l'API, dossier `database/`.

Tables utilisées par le dashboard :

| Table | Usage |
|---|---|
| `employe` | Comptes vendeurs et administrateur (connexion, gestion des vendeurs) |
| `produit` | Catalogue, stock, prix HT/TTC, TVA, référence SKU, désactivation |
| `client` | Fiches clients (créées sur le site ou en boutique, sans mot de passe) |
| `commande`, `ligne_commande` | Commandes du site et ventes en magasin |
| `adresse`, `livraison` | Adresse et transporteur affichés dans le détail d'une commande |
| `password_reset` | Jetons de réinitialisation du mot de passe |

- **Installation neuve** : `database/cafthe.sql` de l'API crée la base complète avec un jeu de test.
- **Base déjà en service** : ne pas relancer `cafthe.sql` (il supprime la base). Sauvegarder la base,
  puis appliquer les migrations de l'API dans l'ordre :
  - `database/migrations/001_exam_features.sql` : client sans mot de passe, désactivation des produits,
    ventes en caisse, date d'inscription des clients ;
  - `database/migrations/002_sku_password_reset.sql` : référence SKU des produits et table `password_reset`.
- **Référence SKU** (`produit.sku`) : obligatoire et unique, format `CAF-0001`, `THE-0003`, `ACC-0005`
  (préfixe de catégorie + 4 chiffres). La migration 002 la remplit pour les produits existants.
- **Mot de passe oublié** (`password_reset`) : un jeton par demande, valable 1 heure, à usage unique.
  Seule l'empreinte SHA-256 du jeton est stockée ; une nouvelle demande invalide les liens précédents.
  La table sert aussi à l'API pour les clients (`account_type` = `client` ou `employee`).

## Sécurité

- Mots de passe hachés (bcrypt), requêtes préparées, sorties échappées
- Jeton CSRF sur tous les formulaires
- Cookie de session HttpOnly + SameSite Strict, Secure en HTTPS
- Déconnexion après 30 minutes d'inactivité, accès par rôle (vendeur / admin)
- En-tête `X-Robots-Tag: noindex` : le dashboard n'est pas indexé
- Mot de passe oublié : lien à usage unique valable 1 heure, jeton stocké haché, même réponse que
  l'email existe ou non, nombre de demandes limité (par session et par compte)

## Tests

Tests automatisés avec PHPUnit (CDC 8.1). Composer ne sert qu'aux tests : l'application garde son propre
autoload et `vendor/` n'est pas déployé.

1. Installer les dépendances de développement : `composer install`
2. Créer la base de test `cafthe_test` à partir du schéma de l'API :
   `php tests/create_test_db.php`
   (le script lit `database/cafthe.sql` dans le dossier voisin `../Cafthe-API`, ou le chemin passé
   en argument, et remplace le nom de base `cafthe` par `cafthe_test`)
3. Lancer les tests : `vendor/bin/phpunit` (ou `composer test`)

Réglages de la base de test : `config/config.test.example.php` (root sans mot de passe, port 3307).
Pour les changer, copier ce fichier en `config/config.test.php` (non commité) ou utiliser les variables
d'environnement `TEST_DB_HOST`, `TEST_DB_PORT`, `TEST_DB_NAME`, `TEST_DB_USER`, `TEST_DB_PASS`.
Par sécurité, le nom de la base de test doit finir par `_test`. Chaque test d'intégration tourne dans
une transaction annulée à la fin : la base de test reste dans son état initial.

Ce qui est couvert :

- **Tests unitaires** (`tests/Unit`, sans base) : prix remisé, vente au poids ou à l'unité, TVA par
  catégorie et calcul du TTC, format de la référence SKU, règle de mot de passe, génération et
  empreinte des jetons, expiration et usage unique, fonctions utilitaires (échappement, prix, URL),
  envoi d'email en mode `log`.
- **Tests d'intégration** (`tests/Integration`, base `cafthe_test`) : statuts de commande
  (en attente → en préparation → expédiée → livrée, puis plus rien), vente en magasin (stock diminué,
  total calculé, refus sans rien enregistrer si le stock manque ou si le produit est désactivé),
  produit (TVA et TTC calculés, SKU en double refusé, SKU proposé, recherche par SKU, désactivation qui
  garde la ligne), jetons de mot de passe (valide, expiré, déjà utilisé, remplacé par un nouveau,
  limite par compte), KPI du tableau de bord.

## Déploiement (Plesk)

Site en ligne : https://dashboard-cafthe.slaurent.dev-campus.fr

1. Créer un sous-domaine avec PHP 8.x, racine du document : `…/public`
2. Déployer le code par Git (branche `master`), puis « Récupérer maintenant » après chaque push
3. Créer `config/config.php` sur le serveur avec les identifiants de la base de prod
   (`host` = `localhost`, `port` = `3306`, base partagée avec l'API), en ajoutant
   `app_url` (`https://dashboard-cafthe.slaurent.dev-campus.fr`) et `mail`
   (`from` = `no-reply@slaurent.dev-campus.fr`, `transport` = `mail`) comme dans `config.example.php`
4. Activer HTTPS (Let's Encrypt) et la redirection HTTP vers HTTPS
5. Vérifier : la page de connexion s'affiche et `/config/config.php` renvoie une erreur 404

**Mise à jour avec la référence SKU et le mot de passe oublié** (base déjà en service) :

1. Sauvegarder la base de prod, puis appliquer `database/migrations/002_sku_password_reset.sql`
   (dépôt de l'API) **avant** de récupérer le nouveau code : sinon les pages produits cherchent une
   colonne `sku` qui n'existe pas encore.
2. Ajouter `app_url` et `mail` dans le `config/config.php` du serveur (voir l'étape 3 ci-dessus).
   Sans ces clés le dashboard fonctionne quand même : le lien est construit avec le domaine de la
   requête en HTTPS et l'email part avec `mail()`, mais il vaut mieux les renseigner.
3. « Récupérer maintenant » dans Plesk, puis tester « Mot de passe oublié ? » avec un compte vendeur.

## Planification

J'avais prévu une petite journée de travail (6 h) ; le dashboard m'a pris un après-midi (4 h 20).
Le temps passé est mesuré d'après les heures des sessions de travail et des commits.

| Étape | Tâche | Réalisée le | Temps prévu | Temps passé |
|---|---|---|---|---|
| 1 | Structure MVC, configuration, connexion à la base | 13/11/2026 | 1 h | 1 h |
| 2 | Authentification (connexion, rôles, expiration de session) | 14/11/2026 | 30 min | 20 min |
| 3 | Tableau de bord et KPI | 14/11/2026 | 30 min | 10 min |
| 4 | Gestion des produits | 14/11/2026 | 40 min | 20 min |
| 5 | Commandes en ligne et statuts | 14/11/2026 | 30 min | 5 min |
| 6 | Clients | 14/11/2026 | 20 min | 10 min |
| 7 | Vente en magasin | 14/11/2026 | 40 min | 20 min |
| 8 | Gestion des vendeurs (admin) | 14/11/2026 | 30 min | 15 min |
| 9 | Profil et changement de mot de passe | 14/11/2026 | 15 min | 5 min |
| 10 | GitHub, déploiement, documentation, planification | 14/11/2026 | 1 h 05 | 1 h 35 |
| | **Total** | | **6 h** (une petite journée) | **4 h 20** (un après-midi) |
