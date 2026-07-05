# Mon Petit Tour

Application Symfony de fantasy cycling entre amis autour du Tour de France.

Chaque utilisateur compose une seule equipe fantasy de 8 coureurs avec un budget maximum de 1 500 000 EUR. Cette equipe est commune a toutes ses ligues. Apres chaque etape, les temps des coureurs sont importes, additionnes, puis utilises pour calculer les classements.

## Fonctionnement metier

- Un utilisateur cree un compte avec un username et un mot de passe.
- Une fois connecte, il compose une equipe de 8 coureurs.
- Il peut creer une ligue, partager son code, ou rejoindre une ligue existante.
- Les classements de ligue comparent les equipes des participants de cette ligue.
- Le classement "petit toureur" compare toutes les equipes fantasy de l'application, meme hors ligues communes.
- Le classement officiel des coureurs additionne les temps reels importes sur toutes les etapes parcourues.
- Les coureurs abandonnes sont affiches comme tels et restent derriere les coureurs encore en course dans le classement officiel.

## Donnees

L'application utilise PostgreSQL.

Les donnees principales sont :

- `rider` : coureurs, equipe officielle, nationalite, valeur, specialite, statut en course.
- `stage` : etapes du Tour, numero officiel, depart, arrivee, distance, denivele, image de profil.
- `stage_rider_result` : temps et ecart d'un coureur sur une etape.
- `application_user` : utilisateurs de l'application.
- `fantasy_team` : equipe fantasy unique d'un utilisateur.
- `fantasy_league` : ligues et participants.

Les imports actuels ne passent pas par une API payante. Ils s'appuient sur les pages publiques de letour.fr.

## Architecture

Le projet suit une organisation DDD/CQRS pragmatique :

```txt
src/
├── Domain/
├── Application/
├── Infrastructure/
└── UI/
```

- `Domain` contient les concepts metier et invariants.
- `Application` contient les handlers, DTOs, commandes, queries et ports.
- `Infrastructure` contient Doctrine, fixtures, scrapers et implementations techniques.
- `UI` contient les controllers, formulaires Symfony et commandes console.

Les controllers restent fins : ils deleguent aux handlers applicatifs.

## Demarrage local

Prerequis :

- Docker
- Docker Compose

Demarrer l'environnement de dev :

```bash
make up
```

Installer les dependances si besoin :

```bash
make composer-install
```

L'application est disponible sur :

```txt
http://localhost:8080
```

La base PostgreSQL de dev est exposee sur :

```txt
Host: localhost
Port: 54329
Database: monpetittour
User: monpetittour
Password: monpetittour
```

## Commandes utiles

```bash
make up
make down
make build
make logs
make sh
make console
make test
make stan
```

Executer une commande Symfony :

```bash
make console
```

ou directement :

```bash
docker compose -f compose.dev.yaml exec app php bin/console
```

## Imports Tour de France 2026

Importer ou mettre a jour les etapes :

```bash
docker compose -f compose.dev.yaml exec app php bin/console app:tour-de-france-2026:import-stages
```

Importer la startlist publiee :

```bash
docker compose -f compose.dev.yaml exec app php bin/console app:tour-de-france-2026:import-published-startlist
```

Importer les resultats d'une etape :

```bash
docker compose -f compose.dev.yaml exec app php bin/console app:tour-de-france-2026:import-stage-results 2
```

L'import de resultats :

- verifie d'abord la page des abandons ;
- marque les coureurs concernes avec `is_still_racing = false` ;
- importe les temps et ecarts de l'etape ;
- remplace les resultats existants de cette etape pour rester idempotent.

## Tests et qualite

Avant chaque push :

```bash
make test
make stan
```

La CI GitHub execute aussi PHPUnit et PHPStan avant le deploiement.

## Production

Le deploiement production passe par GitHub Actions sur `master`.

Le serveur execute :

```bash
cd /srv/apps/monpetittour
git pull --rebase origin master
docker compose up -d --build
docker image prune -f
```

Apres un deploiement qui modifie la base, executer les migrations sur le serveur :

```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

Puis lancer les imports necessaires en prod avec `--env=prod`.

## Assets

Les images publiques sont dans `public/`.

Exemples :

```txt
public/UI/logo.png
public/UI/background.png
```

Dans Twig, elles sont referencees avec :

```twig
{{ asset('UI/logo.png') }}
```
