# Mon Petit Tour

Symfony fantasy cycling application for playing with friends around the Tour de France.

Each user builds one fantasy team of 8 riders with a maximum budget of EUR 1,500,000. This team is shared across all leagues the user joins. After each stage, rider times are imported, aggregated, and used to compute rankings.

## Business Rules

- A user creates an account with a username and password.
- Once authenticated, the user builds one team of 8 riders.
- A user can create a league, share its code, or join an existing league.
- League rankings compare the teams of participants in that league.
- The "Petit Toureur" ranking compares every fantasy team in the application, even if users are not in the same league.
- The official rider general classification sums imported real stage times across all completed stages.
- Abandoned riders are displayed as abandoned and are ranked behind riders who are still racing in the official rider classification.

## Data

The application uses PostgreSQL.

Main data tables:

- `rider`: riders, official team, nationality, market value, specialty, racing status.
- `stage`: Tour stages, official stage number, start, finish, distance, elevation gain, profile image.
- `stage_rider_result`: rider time and gap for a given stage.
- `application_user`: application users.
- `fantasy_team`: one fantasy team per user.
- `fantasy_league`: leagues and participants.

Current imports do not rely on a paid API. They scrape public pages from letour.fr.

## Architecture

The project follows a pragmatic DDD/CQRS structure:

```txt
src/
├── Domain/
├── Application/
├── Infrastructure/
└── UI/
```

- `Domain` contains business concepts and invariants.
- `Application` contains handlers, DTOs, commands, queries, and ports.
- `Infrastructure` contains Doctrine, fixtures, scrapers, and technical implementations.
- `UI` contains controllers, Symfony forms, and console commands.

Controllers are kept thin and delegate business work to application handlers.

## Local Setup

Requirements:

- Docker
- Docker Compose

Start the development stack:

```bash
make up
```

Install dependencies if needed:

```bash
make composer-install
```

The application is available at:

```txt
http://localhost:8080
```

The development PostgreSQL database is exposed at:

```txt
Host: localhost
Port: 54329
Database: monpetittour
User: monpetittour
Password: monpetittour
```

## Useful Commands

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

Run a Symfony command:

```bash
make console
```

or directly:

```bash
docker compose -f compose.dev.yaml exec app php bin/console
```

## Tour de France 2026 Imports

Import or update stages:

```bash
docker compose -f compose.dev.yaml exec app php bin/console app:tour-de-france-2026:import-stages
```

Import the published startlist:

```bash
docker compose -f compose.dev.yaml exec app php bin/console app:tour-de-france-2026:import-published-startlist
```

Import stage results:

```bash
docker compose -f compose.dev.yaml exec app php bin/console app:tour-de-france-2026:import-stage-results 2
```

The stage result import:

- checks the withdrawal page first;
- marks matching riders with `is_still_racing = false`;
- imports rider times and gaps for the stage;
- replaces existing results for that stage so the command stays idempotent.

## Tests and Quality

Before pushing:

```bash
make test
make stan
```

GitHub Actions also runs PHPUnit and PHPStan before deployment.

## Production

Production deployment runs through GitHub Actions on `master`.

The server executes:

```bash
cd /srv/apps/monpetittour
git pull --rebase origin master
docker compose up -d --build
docker image prune -f
```

After deploying changes that modify the database schema, run migrations on the server:

```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

Then run the required imports in production with `--env=prod`.

## Assets

Public images are stored in `public/`.

Examples:

```txt
public/UI/logo.png
public/UI/background.png
```

In Twig, reference them with:

```twig
{{ asset('UI/logo.png') }}
```
