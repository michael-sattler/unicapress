# UnicaPress Shell

Marketing site and staff admin console for Unica Press — PHP/mysqli on cPanel (production), Docker for local development.

The Shell is separate from the App (worldbuilding tool + Telling engine + reader). See [docs/scope-marketing-shell.md](docs/scope-marketing-shell.md) for scope and workplan.

## Project structure

```
unicapress/
├── docker/           # Docker configuration (PHP/Apache/MariaDB)
├── docs/             # PRD, scope, standards
├── public/           # Web root (cPanel document root in production)
│   ├── api/          # REST API + diagnostics
│   ├── app/          # Public site + admin console
│   └── config/       # Platform-aware configuration
```

## Quick start (Docker)

1. **Copy the development config** (if you don't already have one):
   ```bash
   cp public/config/template.config.php public/config/development.config.php
   ```

2. **Start containers:**
   ```bash
   docker-compose up -d --build
   ```

3. **Verify setup:**
   - Web: http://localhost:8080
   - API: http://localhost:8080/api
   - phpMyAdmin: http://localhost:8081
   - Health check page: http://localhost:8080/test-docker.php

### Default database credentials (Docker only)

| Setting  | Value                 |
|----------|-----------------------|
| Host     | `db` (inside Docker)  |
| Database | `unicapress`          |
| User     | `unicapress_user`     |
| Password | `unicapress_password` |

Place SQL init scripts in `docker/mysql/init/` — they run on first database creation.

## Configuration

Platform-specific files (`development.config.php`, `staging.config.php`, `production.config.php`) are **gitignored**. Copy from `public/config/template.config.php` per environment.

See [docs/standards-architecture+deployment.md](docs/standards-architecture+deployment.md) for path, logging, and deployment rules.

## Documentation

| Topic | Document |
|-------|----------|
| Shell scope & Phase S workplan | [docs/scope-marketing-shell.md](docs/scope-marketing-shell.md) |
| Architecture & deployment | [docs/standards-architecture+deployment.md](docs/standards-architecture+deployment.md) |
| Docker details | [docker/README.md](docker/README.md) |
| Full product (the App) | [docs/prd-unicapress.md](docs/prd-unicapress.md) |

## Tech stack

- PHP 8.2, mysqli, no framework
- Bootstrap + Sass, light jQuery/vanilla JS
- MariaDB 10.11 (MySQL-compatible)
- Apache (dev) / cPanel shared hosting (production)
