# Laravel Request System

## Project Description

The Laravel Request System is a web-based application developed using Laravel and MySQL. It is created as part of the DevOps Laboratory 1 activity.

## Student Information

**Name:** Jan Emmanuel Ical  
**Course:** BSIT  
**Year and Section:** 4th Year - Block 3

## Software Requirements

- PHP
- Composer
- Laravel
- MySQL
- XAMPP
- Git
- GitHub
- Node.js and NPM

## Installation

1. Clone the repository.
2. Open the project folder in the terminal.
3. Install the Laravel dependencies:

```bash
composer install
```

## Laboratory 2 - Request Data Model

### Request Table

The Laravel request system uses a `requests` table to store submitted requests.

| Field | Type | Constraint | Purpose |
|---|---|---|---|
| id | BIGINT UNSIGNED | Primary Key, Auto Increment | Unique request number |
| requester_name | VARCHAR(100) | Required | Person submitting the request |
| requester_email | VARCHAR(255) | Required | Contact email of requester |
| item_name | VARCHAR(150) | Required | Requested item or service |
| quantity | INT UNSIGNED | Required, greater than 0 | Requested quantity |
| purpose | TEXT | Required | Reason for the request |
| status | VARCHAR(20) | Default: pending | Current request state |
| created_at | TIMESTAMP | Required | Creation time |
| updated_at | TIMESTAMP | Required | Last update time |

### Migration

Create the migration using:

```bash
php artisan make:migration create_requests_table
```

### Lab 3
Driver: [Jan Emmanuel M. Ical]
Reviewer: [Kris Angela Bon]

Driver Responsibilities:
- Implement the security changes
- Run tests
- Push the feature branch
- Create the pull request

Reviewer Responsibilities:
- Inspect the implementation
- Test the system
- Leave a substantive review comment
- Approve the pull request after fixes

## Laboratory 3 - Secure Request Access

### Ownership Rules

- Guests cannot open any request page; they are redirected to the login page.
- Students see and open only the requests whose `user_id` matches their own account.
- Students can create requests. `user_id`, requester name, requester email and the initial `pending` status are assigned on the server, never taken from the form.
- Students cannot change a request's status.
- Administrators (`is_admin = 1`) can list and open every request and change its status to `pending`, `approved` or `rejected`.

### Denial Response

A student who opens another student's request receives **403 Forbidden**. Every request action calls `Gate::authorize()` with `ServiceRequestPolicy` before any data is returned or written. A request ID that does not exist returns 404.

### Route Summary

All request routes are inside the `auth` middleware group in `routes/web.php`.

| Method | URL | Action | Who may use it |
|---|---|---|---|
| GET | `/requests` | `index` - list and creation form | Students (own requests only), administrators (all) |
| POST | `/requests` | `store` - create a request | Students |
| GET | `/requests/{serviceRequest}` | `show` - request details | Owner or administrator |
| PATCH | `/requests/{serviceRequest}/status` | `updateStatus` - change status | Administrators |

### File Responsibilities

| File or area | Maintainer | Purpose |
|---|---|---|
| `app/Policies/ServiceRequestPolicy.php` | Jan Emmanuel Ical | Authorization rules for viewing, creating and updating requests |
| `app/Http/Controllers/ServiceRequestController.php` | Jan Emmanuel Ical | List scoping, validation, trusted field assignment |
| `routes/web.php` | Jan Emmanuel Ical | Route definitions and auth middleware group |
| `resources/views/requests/` | Jan Emmanuel Ical | Escaped output, CSRF fields, status form |
| `README.md` | Jan Emmanuel Ical, Kris Angela Bon | Ownership policy and setup documentation |

### Testing Steps

1. Run `php artisan migrate`, then create two student accounts and one administrator account (`is_admin = 1`).
2. As a guest, open `/requests` and `/requests/1`: both redirect to `/login`.
3. Log in as each student: the list shows only that student's requests, and their own request opens.
4. As a student, open the other student's request ID directly: 403 Forbidden.
5. As a student, send a status PATCH: 403 Forbidden, and the stored status is unchanged.
6. As the administrator, list all requests and change a status: the new status is saved.
7. Submit quantity `0`, `-1` or `1.5`, or a blank item name: validation errors are shown and no row is saved.
8. Submit extra `user_id`, `status` or `is_admin` fields: they are ignored.
9. Submit text containing `<script>` and an apostrophe: the markup is shown as plain text and the apostrophe is stored correctly.
10. Submit a form with a missing or invalid CSRF token: 419 Page Expired.

## Laboratory 3 Verification
Verification instruction: Test student ownership and deny access to another student's requests.

Verification instruction: Test administrator access and administrator-only status updates.

## Laboratory 4 - Container Environment

The request system now also runs in Docker with three containers: Nginx (`web`), Laravel on PHP-FPM (`app`) and MySQL (`db`). The XAMPP setup above still works and its database `laravel_request_system_db` is not used or changed by the containers.

### Lab 4 Roles
Driver: [Kris Angela Bon]
Reviewer: [Jan Emmanuel M. Ical]

Driver Responsibilities:
- Write the Dockerfile, compose.yaml and Nginx config
- Build and start the containers, run migrations
- Run the persistence tests
- Push the feature branch and create the pull request

Reviewer Responsibilities:
- Check the Docker configuration and security settings
- Test login, ownership rules and admin status updates in the containers
- Reproduce the environment from the repository in a separate folder or computer
- Leave review comments and approve the pull request after fixes

### Docker Requirements

- Docker Desktop with the WSL 2 backend (Windows) or Docker Engine (Linux)
- Docker Compose v2 (`docker compose`)
- Git
- Free local port 8080 (or any port set in `WEB_PORT`)

PHP, Composer, Node.js and MySQL do not need to be installed on the computer for the container setup. They are inside the images.

### Container Concepts

| Concept | Meaning | How it is used in this project |
|---|---|---|
| Container vs virtual machine | A virtual machine runs a full guest operating system. A container shares the host kernel and only packages the app and its libraries, so it starts in seconds and uses less memory. | Nginx, PHP-FPM and MySQL each run in their own small container instead of one big VM or XAMPP. |
| Image | A read-only template with the files and settings needed to run a program. | The Dockerfile builds the `app` image (PHP 8.2 + Laravel code) and the `web` image (Nginx + public folder). MySQL uses the official `mysql:8.4` image. |
| Container | A running instance of an image. It can be removed and created again from the same image. | `app` can be recreated with `--force-recreate` and the data is still there because it is stored in the database volume. |
| Registry | A server that stores images, like Docker Hub. | The base images `php`, `composer`, `node`, `mysql` and `nginxinc/nginx-unprivileged` are pulled from Docker Hub. |
| Network | A virtual network that lets containers find each other by service name. | The `backend` network lets Nginx reach `app:9000` and Laravel reach `db:3306`. |
| Port | A port mapping makes a container port reachable from the host. | Only `web` is published, on `127.0.0.1:8080`. `app` and `db` have no published ports. |
| Volume | Storage managed by Docker that lives outside the container. | The named volume `db_data` holds the MySQL data in `/var/lib/mysql`. |
| Environment variable | A setting given to the container when it starts. | `.env.container` gives Laravel the `APP_KEY` and database settings and gives MySQL the database name and user. |

### Container Diagram

```text
  Browser
     |  http://127.0.0.1:8080
     v
+------------------------------ Docker network: backend ------------------------------+
|                                                                                      |
|  +----------------+   FastCGI    +-------------------+   MySQL    +----------------+ |
|  | web            |  app:9000    | app               |  db:3306   | db             | |
|  | Nginx 1.28     | -----------> | PHP 8.2-FPM       | ---------> | MySQL 8.4      | |
|  | serves public/ |              | Laravel 12        |            |                | |
|  | port 8080      |              | user www-data     |            |                | |
|  +----------------+              +-------------------+            +-------+--------+ |
|                                                                           |          |
+---------------------------------------------------------------------------|----------+
                                                                            v
                                                               volume: db_data
                                                               (/var/lib/mysql)
```

### Service Table

| Service | Purpose | Connection | Configuration |
|---|---|---|---|
| web | Serves the app through Nginx at http://127.0.0.1:8080 | Browser -> `127.0.0.1:8080`, forwards PHP requests to `app:9000` | `nginxinc/nginx-unprivileged:1.28-alpine`, `docker/nginx/default.conf`, root is `/var/www/html/public`, runs as non-root |
| app | Runs Laravel with PHP-FPM | Receives FastCGI from `web`, connects to `db:3306` | `php:8.2-fpm-alpine3.22` with `pdo_mysql`, settings from `.env.container`, runs as `www-data`, no published port |
| db | Stores the request system data | Accepts connections from `app` on the `backend` network only | `mysql:8.4`, database `laravel_request_container_db`, user `request_app`, volume `db_data`, no published port |

### Docker Files

| File | Purpose |
|---|---|
| `Dockerfile` | Multi-stage build for the `app` and `web` images |
| `compose.yaml` | Services, network, volume, health checks and startup order |
| `docker/nginx/default.conf` | Nginx site that only serves `public/` and sends PHP to `app:9000` |
| `.dockerignore` | Keeps `.env` files, keys, `.git`, `vendor`, `node_modules`, backups, archives and logs out of the build |
| `.env.container.example` | Template with placeholder values for `.env.container` |

### Multi-stage Build

The Dockerfile has four stages:

1. `vendor` - uses `composer:2.8` to install the PHP packages from `composer.lock` (no dev packages).
2. `assets` - uses `node:22-alpine3.22` to run `npm ci` from `package-lock.json` and `npm run build` for the Vite/Tailwind files.
3. `app` - starts from `php:8.2-fpm-alpine3.22` and copies only the code, the `vendor` folder and `public/build` from the earlier stages.
4. `web` - starts from Nginx and copies only the `public` folder.

Composer, Node and `node_modules` stay in the build stages, so the final images are smaller and have fewer tools that could be misused. The builds also use the lockfiles, so everyone gets the same package versions.

### Environment Configuration

Real settings are kept in `.env.container`, which is ignored by Git and by `.dockerignore`. Compose passes it to the containers at runtime with `env_file`. Only the template is committed:

```dotenv
APP_NAME="Laravel Request System"
APP_ENV=local
APP_DEBUG=false
APP_KEY=
APP_URL=http://127.0.0.1:8080
LOG_CHANNEL=stderr
LOG_LEVEL=info
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel_request_container_db
DB_USERNAME=request_app
DB_PASSWORD=change_me
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
MYSQL_DATABASE=laravel_request_container_db
MYSQL_USER=request_app
MYSQL_PASSWORD=change_me
MYSQL_RANDOM_ROOT_PASSWORD=yes
```

- `DB_PASSWORD` and `MYSQL_PASSWORD` must be the same value.
- MySQL creates the database and the `request_app` account the first time the `db_data` volume is created. That account only has access to `laravel_request_container_db`.
- The root password is random and not used by Laravel.

### Setup and Startup

```bash
# 1. copy the template and set a strong DB_PASSWORD / MYSQL_PASSWORD
cp .env.container.example .env.container

# 2. generate the APP_KEY once and paste it into .env.container
docker compose build
docker compose run --rm --no-deps app php artisan key:generate --show

# 3. check the config, then build and start
docker compose config --quiet
docker compose up -d --build
docker compose ps
```

Keep the same `APP_KEY` when containers are replaced. If it changes, existing sessions and encrypted data stop working.

### Migrations and Accounts

Run after `db` shows `healthy` in `docker compose ps`:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:status
```

Do not use `migrate:fresh` or `migrate:reset`.

Accounts are made the same way as in Lab 3:

1. Open http://127.0.0.1:8080/register and register Student A, Student B and an administrator account (fictional names and emails).
2. Make the administrator account an admin:

```bash
docker compose exec app php artisan tinker --execute="App\Models\User::where('email', 'ADMIN_EMAIL_HERE')->update(['is_admin' => true]);"
```

### Health Checks

| Service | Check | Why |
|---|---|---|
| db | Logs in as `request_app` over TCP and runs `SELECT 1` | Passes only when MySQL accepts connections for the app user, not just when the container has started |
| app | Checks that PHP-FPM is listening on port 9000 | `web` waits for this before starting |
| web | Requests the Laravel `/up` route through Nginx | Confirms Nginx, PHP-FPM and Laravel work together |

`app` starts only after `db` is healthy, and `web` starts only after `app` is healthy (`depends_on` with `condition: service_healthy`).

```bash
docker compose ps
docker inspect --format "{{json .State.Health.Status}}" $(docker compose ps -q db)
```

### Persistence Test

```bash
# record the request and the app container ID
docker compose ps -q app

# replace only the app container, the db and its volume are not touched
docker compose up -d --force-recreate --no-deps app
docker compose restart web
docker compose ps -q app

# stop and start the whole environment
docker compose down
docker compose up -d
```

Never run `docker compose down -v` or delete `db_data`. That removes the database.

| Check | Request ID | App container ID | Request details | Result |
|---|---|---|---|---|
| Before replacement | [ID] | [container ID] | CONTAINER-PERSIST-01, [owner], qty [n], [status] | Saved |
| After `--force-recreate` | [ID] | [new container ID] | same | [Persisted / Not persisted] |
| After `down` / `up -d` | [ID] | [new container ID] | same | [Persisted / Not persisted] |

The records survive because MySQL writes them to the `db_data` volume, not to the `app` container. Replacing `app` or running `down` removes containers but keeps named volumes.

A volume is still not a backup. It is on the same computer, so `down -v`, a deleted volume, a bad migration or a broken disk would lose the data. A backup is a separate copy (for example a `mysqldump` file stored somewhere else) that can be restored.

### Reproducing the Environment

The reviewer can run a second, separate copy from the repository:

```bash
git clone https://github.com/JanEmmanuelIcal/laravel-request-system.git lab4-review
cd lab4-review
git checkout <commit SHA>
cp .env.container.example .env.container   # fill in values shared privately
```

Then start it with its own project name and port (PowerShell):

```powershell
$env:WEB_PORT = "8081"
docker compose -p lab4-review build
docker compose -p lab4-review run --rm --no-deps app php artisan key:generate --show   # paste into .env.container
docker compose -p lab4-review up -d
docker compose -p lab4-review exec app php artisan migrate
```

Set `APP_URL=http://127.0.0.1:8081` in that `.env.container`. Because of the different project name, Docker creates a separate volume (`lab4-review_db_data`), so the reproduced copy does not touch the original database.

### Security

- `app` runs as `www-data` (check with `docker compose exec app id`) and `web` runs as the non-root `nginx` user. The application code is owned by root, so only `storage` and `bootstrap/cache` are writable.
- `db` has no published port, so MySQL can only be reached from the `backend` network.
- `web` binds only to `127.0.0.1`, so the app is not open to other computers on the network.
- Nginx serves only `public/`, blocks hidden files like `.env` and only runs `index.php`.
- Base images use pinned versions (`php:8.2-fpm-alpine3.22`, `mysql:8.4`, `nginxinc/nginx-unprivileged:1.28-alpine`, `composer:2.8`, `node:22-alpine3.22`).
- Image scan: `docker scout quickview` (needs a Docker Hub login).

**Why credentials are not stored in Docker images:** every image layer can be read by anyone who has the image, using `docker history` or by extracting the files. Images are also pushed to registries, cached on build machines and shared with teammates. A password or `APP_KEY` copied into an image, or passed as a build argument, stays in its layers even if a later step deletes it. Keeping secrets in `.env.container` and passing them at runtime means the same image can be shared safely and the secrets can be changed without rebuilding.

### Lab 4 Verification
Verification instruction: Build and start the containers, run migrations, test login, ownership rules and admin status updates, and confirm the persistence test.
