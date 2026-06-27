# Platform specification (template)

**Purpose:** Define the reusable technical platform used to build internal SaaS apps. This document describes *how the stack works* — auth, hosting, data, API patterns, and deployment — not product or business rules.

**Use this as a template:** Copy or adapt for new monorepos. Product-specific behavior belongs in a separate PRD (e.g. `docs-overallscope.md` for Dreamer Artisan).

**Status legend:** `[x]` implemented in reference repo · `[~]` partial · `[ ]` planned

---

## 1. Platform principles

| Principle | Meaning |
|-----------|---------|
| **Monorepo** | One Git repo: web app, API, DB migrations, and docs together |
| **Thin frontend, capable API** | React handles UI and session; FastAPI owns validation, side effects, and secrets |
| **Supabase for identity + Postgres** | Auth and relational data; RLS on all app tables |
| **Admin-created users only** | No public sign-up; operators vs admins |
| **Twelve-factor config** | All secrets and URLs in environment variables |
| **Separate dev and prod Supabase** | Never point local builds at prod keys by accident |
| **DB in cloud, compute local or hosted** | Supabase is always remote; Vite + FastAPI run in Docker locally and on Vercel/Railway in prod |

---

## 2. Architecture

```
┌─────────────────────────────────────────────────────────────┐
│  Browser — React (Vite) + TypeScript                        │
│  React Router · AuthProvider · fetchApi(Bearer JWT)         │
└──────────────────────────┬──────────────────────────────────┘
                           │ HTTPS — REST JSON (+ multipart)
┌──────────────────────────▼──────────────────────────────────┐
│  FastAPI (Python)                                           │
│  Routers · Pydantic schemas · deps · services · /health     │
└──────────┬─────────────────────────────┬────────────────────┘
           │                             │
┌──────────▼──────────┐         ┌─────────▼─────────┐
│  Supabase           │         │  File storage     │
│  Auth + Postgres    │         │  API disk (V1)    │
│  RLS enforced       │         │  R2/S3 (future)   │
└─────────────────────┘         └───────────────────┘
```

**Request flow (authenticated):**

1. User signs in via Supabase Auth in the browser.
2. Frontend attaches `Authorization: Bearer <access_token>` to API calls.
3. FastAPI validates JWT (`SUPABASE_JWT_SECRET`), loads `profiles.role`.
4. API uses **service role** for DB writes; RLS still applies for direct client reads if used.
5. Binary files (uploads, exports) are read/written on the **API server filesystem** unless migrated to object storage.

---

## 3. Stack (locked decisions)

| Layer | Technology | Role |
|-------|------------|------|
| UI | React 19 + Vite + TypeScript | SPA, routing, forms, admin CRUD |
| API | FastAPI + Pydantic + uvicorn | REST API, image/binary processing, integrations |
| DB + Auth | Supabase (Postgres + GoTrue) | Users, app data, RLS |
| Email | Resend | Transactional mail from template library |
| CI | GitHub Actions | `pytest` + `npm run build` on `main` |
| Web hosting (prod) | Vercel | Static SPA + env-injected build |
| API hosting (prod) | Railway | Dockerized FastAPI + persistent volume for uploads |
| Local dev | Docker Compose | `web` + `api` containers; Supabase in cloud |

**Explicitly not in the platform V1:** PHP, server-rendered pages, public self-service sign-up, Supabase running locally in Docker.

**Future extension (product-dependent):** Cloudflare R2 / S3 for shared binary storage; Anthropic or other LLM APIs for AI features.

---

## 4. Repository layout

```
{repo}/
  apps/
    web/                 # React + Vite
      src/
        components/      # UI components
        contexts/        # AuthProvider, etc.
        lib/             # api.ts, supabase client, helpers
        pages/           # Route-level views
      public/            # Static assets, favicons
      vercel.json        # SPA rewrites → index.html
    api/
      app/
        main.py          # FastAPI app, CORS, static /uploads mount
        config.py        # pydantic-settings from .env
        deps.py          # get_current_user, require_admin
        routers/         # HTTP route modules
        schemas/         # Pydantic request/response models
        services/        # Business logic, integrations
      tests/
      Dockerfile         # Production (Railway)
      Dockerfile.dev     # Local hot reload
      requirements.txt
  supabase/
    migrations/          # Ordered SQL migrations (source of truth)
  docs/
    specs-patform.md     # This file — platform template
    dev-environment.md   # Non-secret env reference (per project)
    docker.md
  docker-compose.yml
  .env.example           # Documented env var template
  .github/workflows/     # CI
  package.json           # Root scripts: dev, db:push, db:types, test:api
```

**Naming:** Replace `{repo}` / `{app}` with your project slug (e.g. `dreamerartisan`).

---

## 5. Environments

| | **Development** | **Production** |
|--|-----------------|----------------|
| Supabase project | `{app}-dev` | `{app}-prod` |
| Web URL | `http://localhost:5173` | `https://app.example.com` |
| API URL | `http://localhost:8000` | `https://api.example.com` |
| `ENVIRONMENT` | `local` | `production` |
| File uploads | `./apps/api/uploads` (bind mount) | Railway volume at `UPLOAD_DIR` |
| OpenAPI `/docs` | Enabled | Disabled |

**Critical rule:** Supabase URL and keys in `.env.local` must match the environment you intend. Sharing one Supabase project between local API disk and Railway disk **without shared object storage** causes “rows exist but files missing” on the other environment.

---

## 6. Authentication and authorization

### 6.1 Supabase Auth (browser)

- Email + password only (V1).
- **Disable public sign-up** in Supabase dashboard.
- Site URL and redirect URLs must include every frontend origin (localhost + prod).
- Session managed by `@supabase/supabase-js`; `AuthProvider` wraps the app.

### 6.2 Roles

Stored on `public.profiles`:

| Role | Typical access |
|------|----------------|
| `operator` | App features; own data where applicable |
| `admin` | User management, libraries, event logs, system pages |

Bootstrap: create first user in Supabase dashboard, then `UPDATE profiles SET role = 'admin'`.

### 6.3 API auth dependencies

```python
get_current_user   # Requires valid Bearer JWT; returns { id, email, role, ... }
require_admin      # 403 unless role == admin
```

JWT verified server-side with `SUPABASE_JWT_SECRET`. Service role key is **never** exposed to the browser.

### 6.4 Frontend route guards

| Pattern | Behavior |
|---------|----------|
| `ProtectedRoute` | Redirect to `/login` if no session |
| `AdminRoute` | Redirect non-admins away from admin pages |
| Role-gated nav | Hide admin links for operators |

---

## 7. Database

### 7.1 Framework tables (every app)

| Table | Purpose |
|-------|---------|
| `profiles` | Extends `auth.users`: email, display_name, role, active, timestamps |
| `content_library` | CMS snippets: key, locale, body |
| `email_library` | Template key, subject, body, `{{placeholders}}`, active |
| `prompt_library` | LLM templates: system/user prompt, model hint, version |
| `event_log_types` | Catalog of auditable event codes |
| `event_logs` | Append-only audit: type, user, entity, payload JSONB |

Trigger `on_auth_user_created` inserts a `profiles` row when an admin creates a user.

### 7.2 Product tables (per app)

Add domain tables via new migrations (e.g. `projects`, `jobs`, `assets`). Keep them in the same Supabase project; document them in the product PRD, not in this file.

**Pattern:** `user_id` FK to `profiles`, RLS policies scoped to owner or admin, status enums in Postgres where lifecycle matters.

### 7.3 Migrations workflow

1. Author SQL in `supabase/migrations/{timestamp}_{name}.sql`.
2. Apply to linked dev project: `npm run db:push`.
3. Regenerate TS types: `npm run db:types` → `apps/web/src/types/database.ts`.
4. Apply same migrations to prod before or during deploy.

Migrations are the **schema source of truth** — not manual dashboard edits.

### 7.4 Row Level Security

- RLS enabled on all application tables.
- Policies: users read/write own rows; admins bypass via `is_admin()` helper or service role on API.
- API uses service role for server-side operations; still enforce authorization in Python for clarity.

---

## 8. API conventions

### 8.1 Structure

| Layer | Responsibility |
|-------|----------------|
| `routers/` | HTTP mapping, status codes, call services |
| `schemas/` | Pydantic in/out models |
| `services/` | Logic, file I/O, external APIs |
| `deps.py` | Auth and shared dependencies |

### 8.2 Standard routes (framework)

| Method | Path | Access |
|--------|------|--------|
| GET | `/health` | Public — `{ status, environment }` |
| GET/PATCH | `/users/me` | Authenticated |
| CRUD | `/admin/users`, `/content`, `/email`, `/prompts` | Admin |
| GET/POST | `/event-logs` | Admin read; limited POST from client |

Product apps add routers under the same app (e.g. `/projects`).

### 8.3 Error handling

- `HTTPException` with clear `detail` strings (surfaced in UI).
- Pydantic validation on all write bodies.
- Log unexpected failures; return generic 500 in production.

### 8.4 Event logging

```python
log_event("entity.action", user_id=..., entity_type=..., entity_id=..., payload={...})
```

Register new codes in `event_log_types` via migration. Use for audit, not as a primary data store.

### 8.5 File URLs and static serving

- Files saved under `{UPLOAD_DIR}/projects/{id}/...`.
- Public URLs: `/uploads/projects/{id}/{filename}` (FastAPI `StaticFiles` mount).
- DB stores path or URL fragment; **existence checked on disk** at runtime for derived URLs.

### 8.6 Cross-environment file availability (production guard)

When `ENVIRONMENT=production`, the API may:

- Compute `files_available` and `missing_files[]` per entity by comparing DB references to on-disk files.
- **Hide** entities with missing files from non-admin list/detail responses.
- **Show** them to admins with a warning badge.

This prevents clients from seeing “broken” records when DB and API disk are out of sync. Does not delete data — only filters the API response.

---

## 9. Frontend conventions

### 9.1 Routing (React Router)

| Path | Purpose |
|------|---------|
| `/login`, `/reset-password` | Auth (public) |
| `/app/*` | Authenticated shell |
| `/app/...` | Product routes |
| `/app/users`, `/app/content`, … | Admin-only |

SPA fallback: all paths → `index.html` (Vercel `vercel.json` rewrites).

### 9.2 API client (`lib/api.ts`)

- `baseUrl` from `import.meta.env.VITE_API_URL` (fallback localhost for dev).
- `fetchApi(path, options, auth?)` — attaches Bearer token when `auth=true`.
- Network failures → user-friendly “Could not reach the API” message.
- **Important:** `VITE_*` vars are baked in at **build time**; changing Vercel env requires **redeploy**.

### 9.3 Auth context

- `useAuth()` → session, profile, `isAdmin`, `signIn`, `signOut`.
- Profile loaded from API or Supabase after login.

### 9.4 UI patterns

- Single app shell: header, side nav, `<Outlet />` for pages.
- Modals via React portal (not nested in overflow-hidden parents).
- Inline error/success text on forms; optional toast library later.
- Minimal custom CSS (no mandated component library).

### 9.5 Cache busting for user-generated images

Append `?v={updated_at}` to artifact URLs when serving from API static mount so browsers refresh after overwrite.

---

## 10. Libraries (framework services)

### Content library

- Admin CRUD for keyed snippets (locale-aware).
- Frontend: `getContent(key)` helper.

### Email library

- Admin CRUD for templates with placeholders.
- Backend: `send_email_from_library(template_key, to, context)` via Resend HTTP API.
- Log sends to `event_logs`.

### Prompt library

- Admin CRUD for LLM prompt templates.
- Backend: `get_prompt(template_key)` for product/AI features.

---

## 11. Configuration reference

Root `.env.local` (gitignored); see `.env.example`.

### Frontend (safe in browser — `VITE_` prefix)

| Variable | Purpose |
|----------|---------|
| `VITE_SUPABASE_URL` | Supabase project URL |
| `VITE_SUPABASE_ANON_KEY` | Publishable anon key |
| `VITE_API_URL` | FastAPI base URL **as seen by the browser** |

### Backend (server only — never `VITE_`)

| Variable | Purpose |
|----------|---------|
| `ENVIRONMENT` | `local` \| `production` — toggles docs, file-availability filtering |
| `SUPABASE_URL` | Same project URL |
| `SUPABASE_SERVICE_ROLE_KEY` | Admin DB/auth operations |
| `SUPABASE_JWT_SECRET` | Verify user JWTs |
| `DATABASE_URL` | Direct Postgres (optional; Supabase client often sufficient) |
| `CORS_ORIGINS` | Comma-separated allowed browser origins |
| `RESEND_API_KEY` | Email sending |
| `RESEND_FROM_EMAIL` | Verified sender address |
| `UPLOAD_DIR` | Absolute path for uploads (Railway: `/app/uploads`) |

Product-specific keys (R2, Anthropic, etc.) added only when those features ship.

---

## 12. Local development

**Default:** Docker Compose (`npm run dev`).

| Service | Port | Notes |
|---------|------|-------|
| web | 5173 | Vite dev server; `VITE_API_URL=http://localhost:8000` |
| api | 8000 | Hot reload; uploads in `apps/api/uploads` |

Supabase runs in the cloud — run migrations on the host:

```bash
npm run db:link    # once per machine
npm run db:push
npm run db:types
npm run test:api
```

**Host-native alternative:** `npm run dev:web` + `npm run dev:api` without Docker.

---

## 13. CI (GitHub Actions)

On push/PR to `main`:

1. **test-api** — `pip install -r apps/api/requirements.txt`; `pytest apps/api/tests`
2. **build-web** — `npm ci` in `apps/web`; `npm run build` (TypeScript + Vite)

Optional later: ESLint, `pip audit`, deploy workflows.

---

## 14. Production deployment

### 14.1 Vercel (web)

- Root directory: `apps/web`
- Build: `npm run build`
- Env (Production): `VITE_SUPABASE_*`, `VITE_API_URL=https://api.{domain}`
- Custom domain: `app.{domain}`
- Redeploy after any `VITE_*` change

### 14.2 Railway (API)

- Deploy from `apps/api/Dockerfile`
- Health check: `GET /health`
- Env: all backend vars; `ENVIRONMENT=production`
- **Persistent volume** mounted at `UPLOAD_DIR` (e.g. `/app/uploads`) — without it, files are lost on redeploy
- Custom domain: `api.{domain}`
- `CORS_ORIGINS` must include the Vercel production origin

### 14.3 Supabase (prod)

- Separate project from dev
- Run all migrations
- Auth Site URL → production web URL
- Redirect URLs include production app paths
- Public sign-up remains disabled

### 14.4 DNS bundle (typical client handoff)

One request to domain owner:

1. Resend domain verification (SPF/DKIM)
2. `app` → CNAME to Vercel
3. `api` → CNAME to Railway

### 14.5 Post-deploy smoke test

1. `GET https://api.{domain}/health` → `{ "status": "ok" }`
2. Login on production web
3. Admin: system status / health badge green
4. Create entity, upload file, confirm file URL loads
5. Confirm operator cannot access admin routes

---

## 15. Environment separation checklist

Use this when spinning up a **new** app from this template:

- [ ] Create **two** Supabase projects (dev + prod)
- [ ] `.env.local` uses **dev** keys only
- [ ] Vercel Production uses **prod** Supabase + prod API URL
- [ ] Railway uses **prod** Supabase + `ENVIRONMENT=production`
- [ ] Never develop against prod Supabase while saving files to local disk
- [ ] Document project refs in `docs/dev-environment.md` (no secrets)
- [ ] Plan object storage (R2) before multi-environment file workflows

---

## 16. Security baseline

- [x] JWT on protected API routes
- [x] Service role server-only
- [x] RLS on Postgres tables
- [x] Admin-only routes enforced in API and UI
- [x] No public sign-up
- [x] Pydantic validation on inputs
- [ ] HTTPS only in production (hosting default)
- [ ] Rate limiting on sensitive endpoints (optional)
- [ ] CI secret scan (service role not in frontend bundle)

---

## 17. Per-app customization

When cloning this platform for a new product, change:

| Item | Action |
|------|--------|
| Repo / Docker project name | Rename slug everywhere |
| Supabase project refs | New dev + prod projects |
| Domains | Vercel + Railway custom domains |
| `event_log_types` | Add product-specific codes via migration |
| Product tables | New migrations + routers + pages |
| `.env.example` | Document product API keys |
| Product PRD | Separate doc for business rules and UX |

**Keep unchanged:** auth model, monorepo layout, `fetchApi` pattern, library tables, CI shape, deploy targets.

---

## 18. Related documents

| Document | Contents |
|----------|----------|
| `docs-coresaas.md` | Framework implementation checklist (Dreamer Artisan instance) |
| `docs-overallscope.md` | Product PRD and pipeline (not platform) |
| `dev-environment.md` | Dev URLs, Supabase ref, local commands |
| `docker.md` | Compose details |
| `.env.example` | Env var glossary |

---

*Platform template derived from the Dreamer Artisan monorepo. Update this file when platform decisions change; keep product docs separate.*
