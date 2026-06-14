# Docker Setup for UnicaPress Shell

Local development stack: PHP 8.2 + Apache, MariaDB 10.11, phpMyAdmin.

## Prerequisites

- Docker Desktop installed and running
- Docker Compose (included with Docker Desktop)

## Quick start

1. **Copy the development configuration file** (if needed):
   ```bash
   cp public/config/template.config.php public/config/development.config.php
   ```
   A `development.config.php` with Docker defaults should already exist locally (gitignored).

2. **Start the containers:**
   ```bash
   docker-compose up -d --build
   ```

3. **Access the application:**
   - Web Application: http://localhost:8080
   - API: http://localhost:8080/api
   - phpMyAdmin: http://localhost:8081
   - Database: localhost:3306
     - User: `unicapress_user`
     - Password: `unicapress_password`
     - Database: `unicapress`

4. **Verify:** http://localhost:8080/test-docker.php

## Services

### Web Server (unicapress-web)
- **Port:** 8080
- **Technology:** PHP 8.2 with Apache
- **Document Root:** `/var/www/html` (maps to `./public`)
- **Extensions:** mysqli, pdo_mysql, gd, zip, intl, mbstring, opcache

### Database (unicapress-db)
- **Port:** 3306
- **Technology:** MariaDB 10.11
- **Root Password:** `rootpassword` (development only)
- **Database:** `unicapress`
- **User:** `unicapress_user` / `unicapress_password`

### phpMyAdmin (unicapress-phpmyadmin)
- **Port:** 8081
- **URL:** http://localhost:8081

## Useful commands

```bash
# Start
docker-compose up -d

# Stop
docker-compose down

# Logs
docker-compose logs -f web

# Rebuild after Dockerfile changes
docker-compose up -d --build

# Shell into web container
docker-compose exec web bash

# MySQL CLI
docker-compose exec db mysql -u unicapress_user -punicapress_password unicapress
```

## Database initialization

Place SQL scripts in `docker/mysql/init/`. They run in alphabetical order on first database creation only.

```
docker/mysql/init/01-schema.sql
docker/mysql/init/02-seed.sql
```

To reset the database (destroys all data):

```bash
docker-compose down -v
docker-compose up -d
```

## Port conflicts

If 8080, 8081, or 3306 are already in use by another project, stop that stack first (this project assumes one Docker stack at a time), or change the host-side ports in `docker-compose.yml` and update `APP_URL` in `development.config.php` to match.

## Production

Do not use these Docker credentials or settings in production. Production deploys to cPanel shared hosting per [docs/standards-architecture+deployment.md](../docs/standards-architecture+deployment.md).
