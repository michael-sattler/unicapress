# App Build Plan — Checklist

**Status:** W1.3 shipped — entity CRUD next validation; W1.4 background LLM after gate  
**Date:** June 2026  
**Scope source:** [scope-worldbulding+authoring.md](./scope-worldbulding+authoring.md) (Phase 1, W1.0–W1.7)  
**Entity reference:** [archive/V1-world-content-model.md](./archive/V1-world-content-model.md)  
**Shell (parallel):** [scope-marketing-shell.md](./scope-marketing-shell.md) — PHP marketing/admin in this repo; App is separate stack

---

## What “basics running” means

You can stop after **W1.3** and call the App foundation done:

- `docker compose up` starts the App `web` + `api` containers locally
- Supabase dev project connected; RLS enforced; one invited worldbuilder can log in
- Create/edit/delete Worlds and all core entity types scoped to that account
- No LLM, image gen, or rulesets required for this gate

Everything after W1.3 adds intelligence and polish on top of a working CRUD foundation.

---

## Before W1.0 — prerequisites

- [x] **Supabase dev project** created (Postgres + Auth; Storage bucket deferred to W1.7)
- [x] **Env secrets** documented in `app/env.example` (copy to `app/.env`; also at `app/.env.example`):
  - `SUPABASE_URL`, `SUPABASE_ANON_KEY`, `SUPABASE_SERVICE_ROLE_KEY` (API only)
  - `ANTHROPIC_API_KEY` (W1.4+)
  - `OPENAI_API_KEY` (W1.7)
- [x] **Domain plan** noted (local: `localhost:5173` web / `localhost:8000` api; prod: Vercel + Railway per scope §3)
- [x] **Repo layout decision** — `app/web` + `app/api` in monorepo; hub repo `unicapress-app` on GitHub

- [x] **Open questions defaulted for v1** (change later if needed):
  - World notes: single freeform field
  - Naming grammar: per-world only
  - Dialogue history: persist per-world, no expiry in v1
  - One React app; engine-admin nav gated by role later

---

## W1.0 — Scaffold & platform

**Goal:** Empty App runs locally; CI green; database schema + RLS in place before any feature UI.

### Repo & Docker

- [x] Create `app/web` — Vite + React + TypeScript scaffold
- [x] Create `app/api` — FastAPI scaffold with `/health` endpoint
- [x] Add App services to Docker Compose (`web`, `api`); Shell `web`/`db` unchanged — project name `unica-app`
- [x] Wire dev proxy: React dev server → FastAPI (`VITE_API_URL`)
- [x] Add `.env.example` at `app/` with all required vars (`env.example` visible copy too)
- [x] README section: “Running the App locally” (separate from Shell quick start)

### Supabase — schema (migration 001)

- [x] `profiles` — `id` (FK → auth.users), `role` (`worldbuilder`), `display_name`, timestamps
- [x] `worlds` — `world_id`, `owner_id` → profiles, `world_title`, `world_logline`, `world_summary`, `world_notes`, `world_meta`, timestamps
- [x] Entity tables (all include `world_id`, `owner_id`, `status`, `*_meta`, `*_sketch`, `*_description`, `imageprompt`, timestamps):
  - [x] `characters` (+ `world_role_tag`)
  - [x] `locations` (+ `starting_setting` boolean)
  - [x] `objects` (+ `kind`)
  - [x] `organizations`
  - [x] `themes`
- [x] `attributes` — generic key-value on any entity (`entity_type`, `entity_id`, `attr_key`, `attr_value`)
- [x] `relationships` — typed edges (`from_entity_type/id`, `to_entity_type/id`, `relationship_type`)
- [x] Deferred tables (add when milestone needs them):
  - `world_rulesets` (W1.5)
  - `dialogue_messages` (W1.6)
  - `entity_images` (W1.7)

### Supabase — RLS (day one)

- [x] Enable RLS on every App table
- [x] Policy pattern: row visible/writable only when `owner_id = auth.uid()` (or world owned by `auth.uid()`)
- [ ] Worldbuilders cannot read/write any other account’s rows — *spot-check with second test user when convenient*
- [x] Service role used only server-side in FastAPI for admin invite flows — never exposed to browser
- [x] Trigger: on `auth.users` insert → create `profiles` row

### API foundation

- [x] Supabase JWT validation middleware on protected routes
- [ ] Standard error response shape + request logging
- [x] CORS configured for local React origin (+ prod domains when known)
- [x] Pydantic models mirroring core entities — `World` model (more in W1.3)

### Web foundation

- [x] React Router shell: login, worlds list, world detail
- [x] Supabase client for auth session; API client for data (Bearer token)
- [x] Apply [visual-style-guide.md](./visual-style-guide.md) tokens at CSS-variable level (paper/ink/oxblood) — functional UI first, polish later

### CI & hygiene

- [x] GitHub Actions (or equivalent): lint + typecheck web; lint + test api; fail on main PR
- [x] `.gitignore` covers `node_modules`, `.venv`, local env files, build artifacts

**W1.0 exit gate:** [x] `GET /health` returns OK; [x] invited user can authenticate; [x] worlds list loads; [ ] RLS verified with a second test account

---

## W1.1 — Worldbuilder auth & UI shell

**Goal:** Invite-only login and navigable App chrome.

### Auth

- [x] Disable public Supabase signup; invite-only via Supabase dashboard
- [x] Login page (email + password)
- [x] Logout + session persistence + expired-session redirect
- [x] `profiles.role = 'worldbuilder'` set on invite (schema default + trigger)
- [x] Protected routes — unauthenticated users → login

### UI shell

- [x] App layout: top nav, worlds home
- [x] Worlds list page (empty state + “Create world” CTA)
- [x] User menu (email via session, logout)
- [x] 404 / error boundary basics

**W1.1 exit gate:** [x] Invited worldbuilder logs in, sees empty dashboard, logs out cleanly.

---

## W1.2 — World CRUD

**Goal:** Canon container per scope §5.2.

### API

- [x] `GET/POST /worlds` — list + create (scoped to owner)
- [x] `GET/PATCH/DELETE /worlds/{id}` — read, update, delete
- [x] Validate ownership on every world-scoped route (RLS + JWT)

### UI

- [x] Worlds list with title + logline snippet
- [x] Create world form: `world_title`, `world_logline`, `world_summary`, `world_notes`
- [x] World detail/overview page showing all fields
- [x] Edit + delete (delete confirms; **cascades** — removes all entities in world per DB FK)

### Data

- [x] `world_meta` column present but empty until W1.4

**W1.2 exit gate:** [x] Create two worlds under one account; second test account sees neither — *spot-check when convenient*

---

## W1.3 — Entity CRUD + status workflow

**Goal:** Full world content model for Phase 1 entities per scope §5.3.

### API — per entity type

- [x] CRUD routes for characters, locations, objects, organizations, themes (all scoped by `world_id`)
- [x] `POST` sets `status = 'canon'` for direct worldbuilder creates
- [x] `PATCH` allows status transitions: `proposed` → `canon` | `excluded`
- [x] Attributes: nested or sub-resource CRUD on any entity
- [x] Relationships: create/list/delete typed edges between entities in same world

### UI — per entity type

- [x] World detail → entity type tabs (Characters, Locations, Objects, Organizations, Themes)
- [x] List view with name, status badge, meta snippet
- [x] Create/edit form: name, sketch, description, status, type-specific fields, `imageprompt`
- [x] Attributes editor (add/remove key-value pairs)
- [x] Relationships picker (select target entity + relationship type)
- [x] Filter or badge for `proposed` entities (for W1.6 preview)

### Validation

- [x] Required fields enforced (at minimum: name per entity)
- [x] Relationship endpoints reject cross-world links

**W1.3 exit gate:** [ ] Populate a Steamlands seed world with at least one of each entity type, attributes, and two relationships; data survives reload; tenant isolation still holds.

---

## W1.4 — Background functions

**Goal:** Auto meta + rename cascade per scope §5.7. Requires Anthropic API.

- [ ] `summarizeEntity` — on save of description/sketch/attributes, regenerate `*_meta` (overwrite on regen)
- [ ] FastAPI background task or sync call on PATCH; UI shows meta updating
- [ ] `rename` — on name change, scan + update mentions in other entities’ text fields and relationship labels
- [ ] Rename cascade is explicit (user confirms?) or automatic — decide and implement consistently
- [ ] API keys only on server; no client-side LLM calls

**W1.4 exit gate:** Edit a character description → meta refreshes; rename a location → references in another entity’s description update.

---

## W1.5 — World rulesets / skills

**Goal:** Markdown rules per world per scope §5.6.

### Schema & API

- [ ] `world_rulesets` — `world_id`, `ruleset_kind` (naming / reserved_names / things_not_to_do / custom), `title`, `body_md`, sort order
- [ ] CRUD API for rulesets within a world

### UI

- [ ] Rulesets editor (markdown textarea or simple MD editor)
- [ ] Naming conventions section + reserved/collision list
- [ ] “Things not to do” section
- [ ] Name validation on entity save: warn or block on collision / grammar break (start with warn)
- [ ] “Suggest names” action — calls LLM with naming rules + reserved list as context

**W1.5 exit gate:** Save naming rules; creating a character with a reserved name shows a warning; suggest-names returns on-convention suggestions.

---

## W1.6 — LLM dialogue interchange

**Goal:** World/entity-scoped assistant per scope §5.4.

### Schema & API

- [ ] `dialogue_messages` — `world_id`, optional entity reference, role, content, structured_payload JSON, timestamps
- [ ] `POST /worlds/{id}/dialogue` — send message; stream or poll response
- [ ] Context assembly: current entity + related entities via relationships + world rulesets
- [ ] Structured outputs:
  - [ ] Field-level suggestions → `*_proposed` accept/reject in UI
  - [ ] Whole-entity JSON → review modal → create as `proposed` entity

### UI

- [ ] Chat panel on world overview and entity edit screens
- [ ] Accept/reject for field suggestions
- [ ] “Create entity from suggestion” flow for whole-entity JSON
- [ ] Conversation history loads per world on return visit

**W1.6 exit gate:** Brainstorm a new character in chat; accept JSON proposal; entity appears as `proposed`; promote to `canon`.

---

## W1.7 — Image generation

**Goal:** Reference imagery per scope §5.5.

### Schema & API

- [ ] `entity_images` — `entity_type`, `entity_id`, storage path/URL, prompt used, timestamps
- [ ] Supabase Storage bucket with RLS aligned to entity ownership
- [ ] `POST /entities/{type}/{id}/images/generate` — OpenAI image API using `imageprompt` + description
- [ ] `GET` list images for entity; `DELETE` optional

### UI

- [ ] `imageprompt` field on entity forms (editable, separate from description)
- [ ] “Generate image” button + loading state
- [ ] Gallery per entity (thumbnail grid, lightbox optional)

**W1.7 exit gate:** Generate and persist at least one image for a character; reload gallery; second tenant cannot access the object.

---

## Phase 1 complete — full exit gate

- [ ] All W1.0–W1.7 checkboxes above done
- [ ] One real world (Steamlands) populated with canon entities, rulesets, sample dialogue, and reference images
- [ ] Document prod deploy steps (Vercel + Railway + Supabase prod project) — even if deploy is a follow-up task
- [ ] Phase 2 build plan drafted from scope §6 (authoring tools + preference generators)

---

## Phase 2 — placeholder (not started)

Track in a separate checklist once Phase 1 gate passes. Scope summary:

| Area | Deliverables |
|---|---|
| §6.1 Voice & rules | Style fingerprint, storytelling rules, lexical fingerprint, writing-style library, sensory/dialogue preferences |
| §6.2 Critique functions | Plot hole, continuity, beta-reader, timeline, exposition critics |
| §6.3 Preference generators | Copy editor + author reaction tool → durable rules (not manuscript UI) |

See [scope-worldbulding+authoring.md §6](./scope-worldbulding+authoring.md#6-phase-2--authoring-tools).

---

## Phase 3 — placeholder

Reader-facing storytelling engine — [scope-storytellingengine.md](./scope-storytellingengine.md). Starts after Phase 1 world data and Phase 2 authorial rules exist.

---

## Suggested build order (at a glance)

```
W1.0 Scaffold ──► W1.1 Auth/shell ──► W1.2 Worlds ──► W1.3 Entities
                                                      │
                        ┌─────────────────────────────┘
                        ▼
              W1.4 Background LLM ──► W1.5 Rulesets ──► W1.6 Dialogue ──► W1.7 Images
```

**Current sprint:** W1.3 exit gate — seed one world with all entity types; then W1.4.  
**Intelligence layer:** W1.4 → W1.7 in order (each depends on stable entity data).

---

## Notes

- **Shell vs App:** Do not add worldbuilder routes to `public/app/admin/`. Staff admin stays PHP; worldbuilder stays React.
- **Filename typo:** scope doc is `scope-worldbulding+authoring.md` until renamed.
- **Check off items** by changing `[ ]` → `[x]` as you go; add dates or PR links inline if helpful.
