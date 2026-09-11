# Escale — API (back)

API REST **Symfony** de l'application Escale (catalogue de voyages), pour le
**Dossier Professionnel DWWM**. Fournit le catalogue (public en lecture) et la
gestion sécurisée (inscription / connexion **JWT**, écritures protégées).

L'environnement tourne dans **Docker** : **nginx + PHP-FPM (Symfony) + MySQL +
phpMyAdmin**, plus **MongoDB** (base NoSQL) et son interface **mongo-express**.

## Services

| Service        | Rôle                                  | Accès                              |
|----------------|---------------------------------------|------------------------------------|
| `nginx`        | Serveur web                           | http://localhost:8000              |
| `php`          | PHP-FPM (Symfony)                     | (interne, `php:9000`)              |
| `mysql`        | Base de données MySQL 8               | `localhost:3307` (base `voyages`)  |
| `phpmyadmin`   | Administration MySQL                  | http://localhost:8081              |
| `mongo`        | Base de données **NoSQL** MongoDB 7   | `localhost:27017` (base `escale`)  |
| `mongo-express`| Administration MongoDB                | http://localhost:8082              |

Identifiants MySQL : `voyages` / `voyages` (root : `root` / `root`).

### Deux bases pour deux usages

- **MySQL (relationnel)** : source de vérité — destinations, catégories,
  membres, favoris, demandes de contact.
- **MongoDB (NoSQL, orienté documents)** : journal des **demandes** par
  destination (un document à chaque demande « Je suis intéressé », rendez-vous…).
  Cet historique volumineux et sans schéma fixe alimente le classement des
  destinations les plus demandées, sans alourdir la base relationnelle.

## Démarrer

```bash
docker compose up -d --build
```

Puis, la première fois, installer les dépendances (dont la librairie MongoDB) et
préparer la base MySQL (migrations + jeu de données) :

```bash
docker compose exec php composer install
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php php bin/console doctrine:fixtures:load --no-interaction
```

MongoDB ne nécessite ni migration ni fixtures : la collection `interests`
se crée d'elle-même à la première demande liée à une destination.

L'API répond alors sur **http://localhost:8000/api/destinations**. Chaque
demande liée à une destination (`POST /api/requests`) est journalisée dans
MongoDB, et le classement des destinations les plus demandées (toutes les
destinations, y compris à zéro) est renvoyé par
**http://localhost:8000/api/stats/popular** (réservé à l'administrateur).

## Compte de démonstration

- **admin@escale.fr** / **escale2026** (ou inscription libre via le front)

## Arrêter

```bash
docker compose down
```

## Commandes utiles

```bash
# Installer les dépendances PHP (si besoin)
docker compose exec php composer install

# Ouvrir un shell dans le conteneur PHP
docker compose exec php bash

# Voir les logs
docker compose logs -f
```

> Les clés JWT (`config/jwt/*.pem`) et les secrets (`.env.local`) restent hors
> du dépôt Git. En cas de souci d'écriture sur `var/` : `docker compose exec php chmod -R 777 var`.
