# App Build Plan — Checklist

**Status:** **W1.7 Images — entity gen + world panoramas + mark identity shipped; exit gate pending verification.** Prompt library + W1.6 shipped. **W1.8 class/instance — core done; card polish + Archivist context pending.** **W1.9 click-drag relationships — workbench + API largely complete; exit gate pending.** **W1.11 worldbuilder onboarding (O1) — O1.2–O1.4 largely implemented locally; O1.0 prod routing + O1.5 landing deferred.** **Next:** W1.8/W1.9 exit gates → O1 exit gates → W1.10 Tagging (`020`). W1.3 exit gate and W1.4/W1.5 can interleave.  
**Date:** June 2026  
**Scope source:** [scope-worldbulding+authoring.md](./scope-worldbulding+authoring.md) (Phase 1, W1.0–W1.7)  
**Onboarding:** [scope-onboarding.md](./scope-onboarding.md) (O1.0–O1.5 — registration, dashboard, onboard paths, landing deferred)  
**Entity extensions:** [scope-entity-model-extensions.md](./scope-entity-model-extensions.md) (W1.8 — class/instance; W1.9 — click-drag relationships; W1.10 — tagging)  
**Entity reference:** [archive/V1-world-content-model.md](./archive/V1-world-content-model.md)  
**Shell (parallel):** [scope-marketing-shell.md](./scope-marketing-shell.md) — PHP marketing/admin in this repo; App is separate stack  
**Workbench mockups:** [unica-workshop.html](./unica-workshop.html), [unica-workshop-populated.html](./unica-workshop-populated.html)

---

## What “basics running” means

You can stop after **W1.3** and call the App foundation done:

- `docker compose up` in `app/` starts the App `web` + `api` containers locally (project `unica-app`)
- Supabase dev project connected; RLS enforced; one invited worldbuilder can log in
- **Staff ops:** `/admin` — create worldbuilder accounts, browse a user's worlds, full entity CRUD (service role)
- **Worldbuilder surface:** `/workbench` — workshop canvas, entity cards, slide-in editors, Archivist dialogue (W1.6)
- Create/edit/delete Worlds and all core entity types scoped to that account
- Archivist dialogue with entity proposals + field suggestions (W1.6)
- No background meta/rename (W1.4), rulesets (W1.5), or image gen (W1.7) required for the W1.3 gate

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
  - One React app; **two surfaces:** staff `/admin` (ops CRUD) + worldbuilder `/workbench` (workshop UX); engine-admin nav gated by role later

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
- [x] `worlds` — `world_id`, `owner_id` → profiles, `world_title`, `world_logline`, `world_summary`, `world_notes`, `world_meta`, timestamps; `world_image_style_prompt` (migration `010`); `world_mark_color` (migration `018`); `is_starter_sample`, `starter_pack_key` (migration `013`, O1)
- [x] Entity status enum: `proposed | canon | apocrypha` (migration `003_entity_status_apocrypha.sql` renames legacy `excluded`)
- [x] Entity tables default `status = 'proposed'` on create
- [x] Entity tables (all include `world_id`, `owner_id`, `status`, `*_meta`, `*_sketch`, `*_description`, `imageprompt`, timestamps; default `status = proposed`):
  - [x] `characters` (+ `world_role_tag`)
  - [x] `locations` (+ `starting_setting` boolean)
  - [x] `objects` (+ `kind`)
  - [x] `organizations`
  - [x] `themes`
- [x] `attributes` — generic key-value on any entity (`entity_type`, `entity_id`, `attr_key`, `attr_value`)
- [x] `relationships` — typed edges (`from_entity_type/id`, `to_entity_type/id`, `relationship_type`)
- [x] Deferred tables (add when milestone needs them):
  - `world_rulesets` (W1.5)
  - `entity_images` (W1.7 — migration `009`)
  - `world_image_style_prompt` on `worlds` (W1.7 — migration `010`)
  - `world_panoramas` (W1.7 extension — migration `017`)
- [x] `dialogue_messages` (W1.6 — migration `004`)
- [x] `promptlibrary` (engine-admin — migration `006`; attribute output schema — `007`; choice options — `008`)

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

- [x] React Router: `/login`, `/admin/*` (staff ops), `/workbench` (worldbuilder workshop)
- [x] Supabase client for auth session; API client for data (Bearer token)
- [x] Admin API uses service role server-side; worldbuilder API uses RLS-scoped `/worlds` routes
- [x] Apply [visual-style-guide.md](./visual-style-guide.md) tokens at CSS-variable level (paper/ink/oxblood) on admin/login surfaces
- [x] Workshop UI uses separate dark theme + Tabler icons per workshop mockups (`workbench.css`)

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

- [x] 404 / error boundary basics
- [x] Dual surfaces documented: staff `/admin` vs worldbuilder `/workbench`

### Staff admin (`/admin`) — ops CRUD, not worldbuilder UX

- [x] User list + create worldbuilder accounts (`POST /admin/users`, Supabase admin API)
- [x] Per-user worlds list + create world for user
- [x] World detail → entity type tabs, list/create/edit/delete (admin API prefix, service role)
- [x] Prompt library admin (`/admin/promptlibrary`) — edit LLM prompt text by stable `prompt_key`
- [ ] Role gate: restrict `/admin` to staff/engine-admin (today any logged-in user)

**W1.1 exit gate:** [x] Invited user logs in; staff can create accounts and open worlds; logout works.

---

## W1.2 — World CRUD

**Goal:** Canon container per scope §5.2.

### API

- [x] `GET/POST /worlds` — list + create (scoped to owner)
- [x] `GET/PATCH/DELETE /worlds/{id}` — read, update, delete
- [x] Validate ownership on every world-scoped route (RLS + JWT)

### UI — admin (staff ops)

- [x] Worlds list with title + logline snippet (via user → worlds flow)
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
- [x] `POST` sets `status = 'proposed'` for new entities (worldbuilder draft or future Telling)
- [x] Location create: omit unset type-specific nulls on insert so DB defaults apply (`starting_setting` → `false`; was failing with explicit `NULL`)
- [x] Status workflow: new entities → `proposed`; worldbuilder promotes to `canon` | `apocrypha` or deletes (Telling-origin: no delete — `origin` field when Tellings ship)
- [x] Attributes: nested or sub-resource CRUD on any entity
- [x] Relationships: create/list/delete typed edges between entities in same world

### UI — admin (staff ops)

- [x] World detail → entity type tabs (Characters, Locations, Objects, Organizations, Themes)
- [x] List view with name, status badge, meta snippet
- [x] Create/edit form: name, sketch, description, status, type-specific fields, `imageprompt`
- [x] Attributes editor (add/remove key-value pairs)
- [x] Relationships picker (select target entity + relationship type)
- [x] Filter or badge for `proposed` entities (for W1.6 preview)

### UI — worldbuilder workshop (`/workbench`)

Per [unica-workshop.html](./unica-workshop.html) / [unica-workshop-populated.html](./unica-workshop-populated.html).

- [x] Full-screen workshop shell: left nav, canvas, right Archivist column
- [x] Dot-grid canvas; empty state “getting started” overlay when no entities
- [x] Entity cards on canvas when populated; drag positions persisted in `localStorage`
- [x] Toolbar when populated: type/status filters, swim-lane/group arrange, compact cards, status bar, **Clean up** (grid snap)
- [x] Right-click context menu → create entity by type; **New entity** topbar button opens the same type picker (toolbar placement uses viewport center; right-click places at cursor)
- [x] Create-from-canvas UX: API errors shown in topbar; successful create opens detail panel + pulses card on canvas; type/status filters auto-include new entity
- [x] Collapsible left nav (icons-only + pin on Workshop row); resizable Archivist pane (200px–50vw)
- [x] Entity detail panel: slides in from right as overlay (~58vw); hero thumbnail (latest image); **Notes** section (attributes — title + prose autosave, editable keys); relationships section (W1.9 — list/edit + canvas drag-drop primary); status slider (auto-save); world-role toggle (characters); entity kind + class picker (W1.8); image gallery + generate
- [x] Canvas cards: optional image thumb strip at top (120px; collapses when no image; hidden in compact mode; not clickable — drag/edit unchanged)
- [x] Canvas layout: swim lanes, group arrange, clean up, and default grid spacing account for image-strip card height (`cardLayoutMetrics.ts`; thumbs loaded with entities on initial page load)
- [x] World detail panel: edit icon in left nav → slides in from left (title, logline, summary, notes, image style prompt, **panorama** generate/gallery); `PATCH /worlds/{id}` from workbench
- [x] Incremental state on save/create/delete (no full-page reload)
- [x] Archivist UI wired to `/dialogue` API (W1.6)
- [x] World switcher when user owns multiple worlds (`WorkbenchWorldSwitcher` in left nav; `WorldMark` identity; **All worlds** → `/dashboard` on worldbuilder surface)
- [ ] Card click / double-click behaviors TBD (edit pencil → detail panel works)
- [ ] Canvas card positions stored server-side (today: `localStorage` only)

### Validation

- [x] Required fields enforced (at minimum: name per entity)
- [x] Relationship endpoints reject cross-world links

**W1.3 exit gate:** [ ] Populate a Steamlands seed world with at least one of each entity type, attributes, and two relationships; data survives reload; tenant isolation still holds; verify in both `/admin` and `/workbench`.

---

## What’s next (recommended order)

| Priority | Milestone | Why now |
|---|---|---|
| **1** | **W1.9 Click-drag relationships** | Workbench + API largely done; finish exit gate + admin note/status UI |
| **2** | **W1.8 Class/instance** | Schema + API + core workbench done; finish card polish, Archivist context, exit gate |
| **3** | **W1.11 Onboarding (O1)** | Dashboard + starter sample + register/login shipped locally; O1.0 prod DNS/CORS + O1 exit gates |
| **4** | **W1.7 Images** | Entity + panorama gen complete — spot-check exit gate (tenant isolation + reload) |
| **5** | **W1.10 Tagging** | Separate milestone — registry, filters, card pills (migration `020`) |
| **6** | **W1.3 exit gate** | Seed Steamlands; proves data model end-to-end before Phase 1 wrap |
| **7** | **W1.4 Background LLM** | `summarizeEntity` + `rename` — auto-fills `*_meta` |
| **8** | **W1.5 Rulesets** | Naming/collision rules enrich Archivist context (can stub empty until then) |

**Workbench polish (can interleave):** server-persisted canvas layout, admin role gate, engine-admin surface, SSE streaming for Archivist, dashboard world cards with `WorldMark`.

**W1.7 does not require W1.4 or W1.5** — image gen uses `imageprompt` + description; rulesets optional in prompt later.

**W1.8–W1.10 defer telling-engine-only items** — full packet-assembly token budgets (R1.5, T1.4–T1.5), skeleton tag casting (G1.6), and battery tag rules (G1.11) ship when Composition/Manifestation services exist (Phase 3+). Phase 1 delivers schema, API, workbench UX, and Archivist context hooks per milestone.

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

**Goal:** World/entity-scoped assistant per scope §5.4. Replace `ArchivistConsole` mock replies with persisted, grounded dialogue.

**Existing UI to wire:** `ArchivistConsole.tsx` + `archivist.ts` (mock); workbench already has world + entity list + detail panel for accept/reject flows.

### Slice A — Chat persistence + API (do first)

- [x] Migration `004_dialogue_messages.sql`:
  - `message_id`, `world_id`, `owner_id`
  - optional `entity_type`, `entity_id` (scope anchor when entity detail panel is open)
  - `role` (`user` | `assistant`)
  - `content` (text)
  - `structured_payload` (jsonb, nullable — proposals parsed from assistant turn)
  - `created_at`
- [x] RLS: same owner/world pattern as entities
- [x] `GET /worlds/{id}/dialogue` — list messages (newest last or paginated)
- [x] `POST /worlds/{id}/dialogue` — body: `{ content, entity_type?, entity_id? }`; returns assistant message
- [x] FastAPI `anthropic` client module (keys server-only; `ANTHROPIC_API_KEY` already in env example)
- [x] Context assembly for system prompt:
  - world: title, logline, summary, notes
  - if entity scoped: entity fields + attributes + relationships (both directions)
  - compact entity index (name, type, status) for grounding
  - rulesets: empty stub until W1.5
- [x] Persist user + assistant turns to `dialogue_messages`

### Slice B — Wire workbench Archivist

- [x] Pass `worldId`, auth token, optional `selectedEntityId` / entity type into `ArchivistConsole`
- [x] Load history on mount; append on send; loading/error states
- [x] Replace `nextArchivistReply` / `setTimeout` mock with `POST /dialogue`
- [x] Chips send real prompts (keep current chip labels)
- [x] v1: non-streaming response is fine; add SSE streaming later if latency feels bad

### Slice C — Whole-entity proposals (exit-gate core)

- [x] Assistant system prompt: may emit a fenced JSON block with `type: "entity_proposal"` — entity_type, name, sketch, description, type-specific fields
- [x] API parses JSON from assistant content → store in `structured_payload` on that message
- [x] Chat UI: proposal card on assistant messages with **Review** / **Dismiss**
- [x] Review modal: editable preview → **Create as proposed** calls existing entity `POST` (`status: proposed`)
- [x] On create: refresh canvas; user promotes to canon via existing detail panel
- [x] Dismiss suggestions: clears `structured_payload` + strips JSON from displayed content (`POST .../dismiss-suggestions`)
- [x] Raw JSON masked in chat display; field suggestions card with per-field / save-all apply

### Slice D — Field-level suggestions (stretch / follow-up)

- [x] Assistant may emit `type: "field_suggestions"` with `{ field, proposed_value, rationale }[]`
- [x] Assistant may emit `type: "attribute_suggestions"` for Notes (key/value pairs); `choice_options` for reply chips (migrations `007`–`008`)
- [x] When entity detail panel is open: show inline accept/reject per field (no `*_proposed` DB columns in v1 — apply via PATCH on accept)
- [ ] Optional: surface “Apply to open entity” from chat when scoped to that entity

### Deferred within W1.6

- Admin `/admin` chat panel (workbench-only for v1)
- SSE streaming
- Configurable Scribe persona (scope §4)
- Conversation summarization / expiry
- Semantic search over past conversations

### Ops / schema notes (W1.6)

- [x] Migration `005_entity_status_apocrypha_ensure.sql` — idempotent fix for DBs missing `apocrypha` enum (run in Supabase SQL Editor on older dev DBs)

**W1.6 exit gate:** [x] Brainstorm a new character in Archivist chat; assistant returns entity JSON; accept → entity appears on canvas as `proposed`; open detail panel → promote to `canon`. Conversation history reloads on return visit. *Requires migration `004` (+ `005` if needed) and `ANTHROPIC_API_KEY` on dev.*

---

## Prompt library (engine-admin)

**Goal:** Editable LLM prompt templates per scope (Generation Plane — lives in App, not Shell). Tune Archivist (and future) prompts without code deploys.

### Schema & API

- [x] Migration `006_promptlibrary.sql` — `prompt_key` (stable slug), `entity`, `prompt_type`, `prompt_title`, `prompt_body`, `variables_hint`, timestamps
- [x] RLS enabled, no client policies — reads/writes via FastAPI service role only
- [x] Seed: `archivist.persona`, `archivist.structured_outputs`
- [x] Migrations `007` / `008` — attribute suggestion schema + `choice_options` structured output (dynamic chips in Archivist when 2+ options)
- [x] `get_prompt_from_library(prompt_key)` + `%%VARIABLE%%` substitution helper
- [x] `GET/PATCH /admin/prompts` and `/admin/prompts/{prompt_key}`

### UI

- [x] React admin: `/admin/promptlibrary` — list + edit body/title/variables hint
- [x] Archivist system prompt loads persona + structured-output blocks from library (world/entity context still assembled in code)

**Exit gate:** Edit Archivist persona in admin UI → next dialogue turn reflects new instructions (no API restart required beyond hot reload in dev).

---

## W1.8 — Class / instance

**Goal:** Entity typing (class vs instance) per [scope-entity-model-extensions.md](./scope-entity-model-extensions.md) Extension 2. Authors define type entities once and attach instances via `class_id`. Authoring aid only — no reader-facing or Telling API changes.

**Applies to:** characters, locations, objects, organizations, themes.

**Prerequisite:** W1.7 complete (exit gate optional but recommended).

**Status:** **Core implementation complete** on schema, API, and workbench; P1 canvas/nav polish + Archivist class context + admin UI pending. Exit gate pending verification.

### Open decisions (scope §8 — class-related)

- [ ] **Class casting policy** — per-class “freely instantiable” flag deferred to telling engine (no `class_cast_policy` field in v1)
- [ ] **Packet assembly** — Phase 1: prepend class `*_meta` in Archivist `dialogue_context` only, or build shared `assemble_entity_context()` module with token budgets now? *(Neither shipped yet — deferred with T1.4.)*

### Slice A — Schema (migration `011`)

- [x] Migration `011_entity_class_instance.sql`:
  - [x] `entity_kind` enum: `instance` (default) | `class` on all five entity tables
  - [x] `class_id` nullable FK → same table (instances only; target must be `entity_kind = class`)
  - [x] Index on `class_id` per entity table
- [x] RLS unchanged pattern (owner/world scoped)
- [x] Backfill existing rows: `entity_kind = instance`, `class_id = null`

### Slice B — API & validation (T1.1–T1.3, T1.10)

- [x] Extend Pydantic entity models + create/PATCH handlers: `entity_kind`, `class_id`
- [x] Reject `class_id` on `entity_kind = class` (cleared on class promotion)
- [x] Reject `class_id` pointing to non-class or wrong entity type
- [x] Reject class subclassing (nested `class_id` on class entities) — v1 constraint in `_apply_entity_kind_rules`
- [x] Admin API mirrors worldbuilder shapes (`/admin/worlds/.../entities/...`)

### Slice C — Workbench UI (T1.6–T1.9)

- [x] Detail panel: **Type** toggle (`instance` | `class`) in status bar (`EntityDetailStatusBar`)
- [x] Assign instance → class via canvas link-drag to a class card (“Instance of …” in `RelationshipEditorPopover`) or clear in Relationships section
- [x] Relationships section: shows `instance of` link to class entity (navigate + remove); lists child instances on class entities
- [x] Canvas: class template icon badge on class cards (`is-class` + `wb-card-class-icon`; UI label **Type**)
- [ ] Canvas: double-border treatment on class cards (badge only today — no distinct border CSS yet)
- [ ] Instance cards: class name link below entity name on canvas card (shown in detail panel + instance edge label only)
- [x] Canvas instance lines: dashed edge from instance → class (`CanvasRelationshipEdges`; click → `ClassInstancePopover` to remove link)
- [ ] “Show instances” filter action on class cards (P1)
- [ ] Left nav hover: separate instance vs class counts (P1)

### Slice D — Admin UI

- [ ] Entity forms: `entity_kind`, `class_id` (workbench-only today)

### Slice E — Archivist context

- [ ] `dialogue_context`: prepend class `*_meta` when focal instance has `class_id` (≤ 400 token target)
- [ ] **Deferred:** T1.4–T1.5 Composition instantiation from class; T1.9 `*_meta` auto-gen with class context (needs W1.4)

### Slice F — Tests & exit gate

- [ ] API: reject invalid `class_id`; class cannot reference another class
- [ ] Workbench: create Character class “City Watch Officer”; two instances with `class_id`; canvas shows badge + class link
- [ ] Archivist with focused instance receives class context in replies
- [ ] Second tenant cannot see or edit another world's class fields

**W1.8 exit gate:** [ ] In one world — one character class + two instances linked to it; class card visually distinct; instance cards show class name; toggle kind in detail panel. Second tenant isolated. *(Type toggle + link-drag assignment implemented — formal verification + card polish pending.)*

---

## W1.7 — Image generation

**Goal:** Reference imagery per scope §5.5, plus world-level establishing panoramas and per-world mark identity for nav/switcher.

**Run migrations:** Apply `009_entity_images.sql`, `010_world_image_style_prompt.sql`, `017_world_panoramas.sql`, and `018_world_mark_color.sql` in Supabase SQL Editor before testing.

### Schema & API

- [x] Migration `009_entity_images.sql` — `entity_images` table (`entity_type`, `entity_id`, `world_id`, `owner_id`, storage path, prompt used, timestamps)
- [x] Migration `010_world_image_style_prompt.sql` — `worlds.world_image_style_prompt` (per-world visual style prepended to every image prompt)
- [x] Supabase Storage bucket `entity-images` with RLS aligned to world ownership
- [x] `POST /worlds/{world_id}/entities/{type}/{id}/images/generate` — OpenAI **GPT Image** API (`gpt-image-1` default; DALL·E 2/3 retired May 2026)
- [x] `GET` list images for entity (signed URLs, 1h TTL); `DELETE` image + storage object
- [x] `GET /worlds/{world_id}/entity-image-thumbs` — batch latest image URL per entity (canvas card strips)
- [x] Prompt assembly (no LLM): `Visual style for this world: {world_image_style_prompt}` + `Subject ({type}): {imageprompt | description | sketch}` + fixed constraints (no text/watermark)
- [x] Optional env: `OPENAI_IMAGE_MODEL`, `OPENAI_IMAGE_SIZE`, `OPENAI_IMAGE_QUALITY` (see `app/env.example`)

### Schema & API — world panoramas (extension)

- [x] Migration `017_world_panoramas.sql` — `world_panoramas` table (`world_id`, `owner_id`, storage path, prompt used, timestamps); RLS owner-scoped; cascade on world delete
- [x] `POST /worlds/{world_id}/panoramas/generate` — cinematic 16:9 establishing shot from world meta/logline/summary + `world_image_style_prompt` (`build_world_panorama_prompt`)
- [x] `GET /worlds/{world_id}/panoramas` — list with signed URLs; `DELETE` panorama + storage object
- [x] `GET /worlds/panorama-thumbs` — batch latest panorama URL per owned world (dashboard cards)
- [x] Storage path: `{world_id}/panorama/{panorama_id}.png` (same `entity-images` bucket)

### Schema & API — world mark identity

- [x] Migration `018_world_mark_color.sql` — `worlds.world_mark_color` (`#RRGGBB`, not null; deterministic palette backfill from `world_id` hash)
- [x] `pick_world_mark_color` on world create — avoids collision with sibling worlds for same owner
- [x] `WorldMark` / `WorldMarkForWorld` — first letter on colored circle; used in workbench nav switcher

### UI — entity editor

- [x] `imageprompt` field on entity forms (editable, separate from description) — workbench + admin forms
- [x] **World editor:** `world_image_style_prompt` on world detail panel (+ admin world form)
- [x] **Workbench:** hero thumbnail shows latest generated image (or placeholder); click → full-viewport closeup modal
- [x] Generate image button + loading/error states (spinner on generate button)
- [x] Gallery per entity (thumbnail grid; click opens full size in new tab)

### UI — world editor & dashboard

- [x] **World detail panel:** `WorkbenchWorldPanoramaSection` — generate + delete panoramas; latest shown as hero when present
- [x] **Dashboard:** world cards show latest panorama thumb when available (`fetchWorldPanoramaThumbs`)

### UI — canvas

- [x] Canvas entity cards: latest image thumb strip (120px; collapses when none; hidden in compact mode; `pointer-events: none`)
- [x] Swim lanes, group arrange, **Clean up**, and default card grid use `cardLayoutMetrics.ts` (185px base + 120px strip when entity has image)
- [x] Image thumbs fetched in parallel with entities on workbench load (correct spacing on first paint)

### Deferred — image style library (post–W1.7)

Preset style picker at world level (photorealistic, painterly, comic line art, etc.); custom style prompts; import/share via UGC library. Replaces freeform-only `world_image_style_prompt` UI when built — field remains the storage target for the resolved style text.

**W1.7 exit gate:** [ ] Generate and persist at least one entity image and one world panorama; reload gallery/panel; second tenant cannot access objects. Set `OPENAI_API_KEY` in `app/.env`. *(Implementation done — spot-check tenant isolation + reload.)*

---

## W1.9 — Click-drag relationships

**Goal:** Named cross-entity links per [scope-entity-model-extensions.md](./scope-entity-model-extensions.md) Extension 1 — entity A → entity B with a required **relationship name**, optional **note**, and **canon status** (`proposed` | `canon` | `apocrypha`; **default `canon`**). **Primary authoring is on the canvas:** drag from one card and drop on another to create a link. Detail panel supports review, inline edit (name, note, status), and delete.

**Applies to:** All five Phase 1 entity types; cross-type links are first-class. Builds on the existing `relationships` table and API from W1.3.

**Prerequisite:** W1.8 (class/instance on canvas before relationship gestures).

**Scope refs:** R1.1–R1.9 in scope §2.7.

**Status:** **Implementation largely complete** on workbench + API; admin relationship UI still basic (no note/status edit). Exit gate + automated tests pending.

### Open decisions (scope §8)

- [x] **Column naming** — keep DB column `relationship_type`; API uses `relationship_type` (not renamed to `relationship_name`)
- [x] **Duplicate relationships** — reject `(from, to, relationship_type)` duplicates (`409` on POST)
- [ ] **Entity delete policy** — not enforced; relationships have no FK to entity rows (orphan edges possible on entity delete)
- [x] **Detail panel direction** — both outgoing and incoming listed with `→` / `←` direction labels

### Slice A — Schema (migration `012`)

- [x] Add nullable `note` (`text`) to `relationships`
- [x] Add `status public.entity_status not null default 'canon'` (reuse existing enum; **not** `proposed` like new entities)
- [x] Backfill existing relationship rows: `status = 'canon'`
- [x] Indexes for list-by-entity queries (`idx_relationships_from_entity`, `idx_relationships_to_entity` on `world_id` + entity type/id)
- [x] No `parent_id` or hierarchy columns on entity tables — structural links live only in `relationships`

### Slice B — API & validation (R1.1–R1.4)

- [x] Extend relationship Pydantic models: `relationship_type`, optional `note`, `status` (default `canon` on create)
- [x] `GET /worlds/{id}/relationships` — world-level list; workbench filters per entity client-side (no dedicated per-entity sub-route with resolved names)
- [x] `POST` / `PATCH` / `DELETE` — create, update name/note/status, remove link
- [x] Validate: same world (via entity row lookup); reject self-loops; reject invalid entity types
- [x] Duplicate policy: reject identical `(from, to, relationship_type)`
- [ ] Entity delete: block or cascade per open decision
- [x] Admin API mirrors worldbuilder shapes (`/admin/worlds/.../relationships`)

### Slice C — Canvas click-drag builder (R1.8 — primary UX)

- [x] Drag link icon on source card; drop on target card → `RelationshipEditorPopover`
- [x] Picker: suggested names per entity-type pair (`relationshipOptions.ts`) + custom name + optional note; **status defaults to canon** (editable before save)
- [x] Drag-over target highlighting on valid drop targets (exclude self; `link-drop-target` on card)
- [x] Persist via relationships API; incremental state update (no full reload)
- [x] Render directed edges between cards (relationship name label; stroke reflects status — canon / proposed / apocrypha)
- [x] Click edge label → open edit popover for that relationship
- [ ] **Optional (P1):** filter visible edges by relationship name or status
- [x] **Canvas toolbar:** **Links** toggle to show/hide relationship lines (default on; persisted in `localStorage`)
- [ ] **Nice-to-have:** server-persisted canvas layout for stable edge anchors

### Slice D — Detail panel (R1.3, R1.6, R1.7 — secondary UX)

- [x] `WorkbenchRelationshipsSection` enabled as read/edit surface (canvas drag-drop remains primary)
- [x] List links: relationship name, direction, other entity name + type, optional note preview, **canon status badge**
- [x] **Click row to edit inline:** relationship name, note, and status; save / cancel / delete
- [x] Navigate to related entity (entity name links in list + edit form)
- [x] Fallback **Add relationship** form (defaults status to `canon`)
- [x] Suggested relationship names per source/target type pair (`relationshipOptions.ts` + datalist on edit)

### Slice E — Archivist context (R1.5)

- [x] `dialogue_context`: compact list for focal entity — relationship name, direction (entity types), note snippet, status
- [x] Omit relationships with `apocrypha` status from Archivist prompt
- [ ] Omit `apocrypha` endpoint entities from relationship context (relationship status only today)
- [ ] **Deferred:** multi-hop context and token budgets (telling engine)

### Slice F — Admin UI

- [x] Relationship add/remove in admin entity views (`RelationshipsSection.tsx` on `EntityEditPage`)
- [ ] Admin: note + status fields; inline edit (workbench-only for note/status today)

### Slice G — Tests & exit gate

- [ ] API: self-loop rejected; cross-world rejected; duplicate policy; note + status round-trip; create defaults to `canon`
- [ ] Canvas: Character → Location via drag-drop with relationship name + note (status canon by default)
- [ ] Detail panel: edit name, note, and status (e.g. change to `apocrypha`); delete link
- [ ] Archivist focal entity receives related-entity context (excludes apocrypha relationships)
- [ ] Second tenant isolated

**W1.9 exit gate:** [ ] In one world — create at least two cross-type relationships **via canvas click-drag** (name + note on at least one; status canon by default); **edit** one link in the detail panel (change name, note, or status); delete one link. Archivist mentions related entities for a focused card. Second tenant isolated. *(Workbench flows implemented — formal verification pending.)*

---

## W1.10 — Tagging

**Goal:** Free-form entity tags per [scope-entity-model-extensions.md](./scope-entity-model-extensions.md) Extension 3. Separate from class/instance (W1.8) and relationships (W1.9) — orthogonal labels for filtering, registry management, and future skeleton compatibility.

**Prerequisite:** W1.8 recommended; may ship after W1.9 or in parallel once core entity work is stable.

### Open decisions (scope §8 — tag-related)

- [ ] **Tag filters on skeleton slots** — hard requirement vs soft preference (default: soft until Composition Service exists)?
- [ ] **Tag registry UI** — world detail panel only, or dedicated world settings route in workbench + admin?

### Slice A — Schema (migration `020`)

- [ ] Migration `020_entity_tags.sql`:
  - [ ] `tags` — `text[]` or `jsonb` ordered array on each entity table (default `[]`)
  - [ ] `world_tags` registry: `world_id`, normalized `tag_key`, optional display label, `entity_count`, `is_system`, timestamps
  - [ ] Seed system tags: `_within-wheels`, `_protagonist-compatible`, `_minor`, `_recurring` (G1.8)
  - [ ] GIN index on `tags` if needed for filter queries
- [ ] Backfill existing rows: `tags = []`

### Slice B — API & validation (G1.1–G1.4, G1.8)

- [ ] Normalize tags at write: lowercase, hyphen-separated, trim
- [ ] Upsert/sync `world_tags` counts on entity tag changes
- [ ] `GET /worlds/{id}/tags` — registry with counts
- [ ] `POST /worlds/{id}/tags/rename` — propagate to all entities
- [ ] `POST /worlds/{id}/tags/merge` — consolidate two tags (G1.10 stretch)
- [ ] Protect system tags from delete/rename by worldbuilders
- [ ] Admin API mirrors worldbuilder shapes

### Slice C — Workbench UI (G1.5, G1.9)

- [ ] Detail panel: tag editor (add/remove pills)
- [ ] Canvas card footer: tag pills, max 2 visible + `+N`
- [ ] Toolbar: multi-select **Tags** filter (after status filters)
- [ ] Tag registry view: list, rename, merge, bulk-remove (world settings — location TBD)

### Slice D — Admin UI

- [ ] Entity forms: tags field
- [ ] Tag registry management in admin world detail (or shared component)

### Slice E — Archivist & engine hooks

- [ ] Pass entity tags into Archivist system prompt as descriptors (G1.7 light version)
- [ ] **Deferred:** G1.6 skeleton tag filters; G1.11 battery tag rules; `*_meta` auto-gen with tags (needs W1.4)

### Slice F — Tests & exit gate

- [ ] API: tag normalization; rename propagation; system tag protection
- [ ] Workbench: tag entities; filter canvas by tag; rename tag in registry
- [ ] Second tenant cannot see or edit another world's tags

**W1.10 exit gate:** In one world — apply tags to several entities; filter canvas by tag; rename a tag world-wide from registry. Second tenant isolated.

---

## W1.11 — Worldbuilder onboarding

**Goal:** Self-service registration and account dashboard on `worldbuilder.unicapress.com`, with onboard paths into the existing workshop. Landing page **deferred to O1.5** — ship auth, dashboard, and onboard flows first.

**Scope:** [scope-onboarding.md](./scope-onboarding.md)  
**Mockups:** [unica-workshop.html](./unica-workshop.html) (dashboard state 1 — new user, no worlds); workshop populated reference [unica-workshop-populated.html](./unica-workshop-populated.html)

**Build sequence:** O1.0 → O1.1 → O1.2 → O1.3 → O1.4 → **O1.5 last**

**Status:** **O1.2–O1.4 largely implemented** in local dev (worldbuilder surface default on localhost). Prod subdomain/CORS/Auth redirect URLs (O1.0) and landing (O1.5) not started. Exit gates pending verification.

### Slice 1 — Subdomain & routing

- [ ] `worldbuilder.unicapress.com` DNS + Vercel alias on App `web` deployment
- [x] Interim `/` on worldbuilder surface → `/login` (not `/admin`) — `App.tsx` + `isWorldbuilderSurface()`
- [ ] CORS: add `https://worldbuilder.unicapress.com` to `api_cors_origins`
- [ ] Supabase Auth redirect URLs for `https://worldbuilder.unicapress.com/**`
- [x] Host- or env-based route set: worldbuilder surface vs staff `/admin` (no admin nav on worldbuilder host; `VITE_APP_SURFACE=worldbuilder|staff`)
- [x] Local dev: `VITE_APP_SURFACE=worldbuilder` (default on localhost per `surface.ts`)

**O1.0 exit gate:** [ ] Worldbuilder host serves React app; unauthenticated `/` → login; API accepts Bearer from worldbuilder origin. *(Local dev passes; prod DNS/CORS pending.)*

### Slice 2 — Registration & login

- [ ] Enable public Supabase signup in dev/prod project (reverses W1.1 invite-only default for worldbuilders — verify dashboard Auth settings)
- [x] `signUp` in `useAuth` (email, password, display name → `profiles` via existing trigger)
- [x] `WorldbuilderRegisterPage` — worldbuilder copy; provisions starter sample on success → workbench (or email-verify interstitial)
- [x] `WorldbuilderLoginPage` — distinct from staff `/login`; `PostAuthRedirect` resolves starter sample or dashboard
- [x] Protected `/dashboard`, `/workbench`, `/account`, `/preferences`, `/onboard/archivist` routes on worldbuilder surface
- [x] Staff invite path unchanged (`POST /admin/users` → staff `/login`)

**O1.1 exit gate:** [ ] New user self-registers, signs in, lands on starter sample workbench or dashboard. *(Flows implemented — verify with public signup enabled.)*

### Slice 3 — Dashboard (three states)

Dashboard chrome matches workshop UI (dark theme). State driven by `ownedWorlds()` — excludes `is_starter_sample` rows.

- [x] `DashboardPage` + `WorldbuilderLayout` shell (header: wordmark, workbench shortcut, user menu)
- [x] **State 1 — new user, no owned worlds** (`ownedWorlds.length === 0`): empty shelf + entity preview cards; CTAs **Brainstorm with the Archivist** / **Start from scratch**
- [x] **State 2 — one owned world** (`ownedWorlds.length === 1`): single-world summary card + panorama thumb; primary CTA **Enter workbench**
- [x] **State 3 — multiple owned worlds** (`ownedWorlds.length > 1`): world grid ordered by `updated_at`; **Open workbench** per row; **New world** in header
- [x] RLS-scoped `POST /worlds` for authenticated worldbuilder (`createMyWorld`; `owner_id = auth.uid()`)
- [x] Nav: Dashboard / Account / Preferences tabs; workbench link when `useCurrentWorld` remembers a world

**O1.2 exit gate:** [ ] Manually seed 0 / 1 / N owned worlds for a test user; each state renders correctly; create world from state 1 works.

### Slice 4 — Onboard paths (first login)

Per scope §4.2 — demo world and paths into first **owned** world.

- [x] Migration `013_starter_world.sql` — `is_starter_sample`, `starter_pack_key` on `worlds`
- [x] Starter JSON packs (`app/api/app/data/starter_worlds/` — hobbit, starwars, harrypotter) + `POST /onboard/starter-world`
- [x] On register/login: auto-provision starter sample when user has no owned worlds (`PostAuthRedirect`, `WorldbuilderRegisterPage`)
- [x] Read-only browse in workbench: demo banner (`wb-demo-banner`) with pack label; **Clear sample world** → `DELETE /onboard/starter-world` → dashboard
- [x] Archivist kickoff on first open of starter sample (`onboarding.starter_world` prompt — migrations `014`–`015`; dialogue archive preserved on clear)
- [ ] **Alternate path (3a):** skip demo — land directly in dashboard state 1 (today: demo auto-provisions unless user clears)
- [x] **Path 4A — Archivist interview:** simplified 3-step `OnboardArchivistPage` (logline → title → anchor location → create world + proposed location → workbench) — not full multi-turn LLM dialogue yet
- [x] **Path 4B — scratch:** manual world create from dashboard modal (`WorldForm` → `/workbench/:worldId`)
- [x] After onboard: dashboard shows **state 2** when one owned world exists; starter sample excluded from owned count

**O1.3 exit gate:** [ ] New user clears demo (or starts from state 1); completes 4A or 4B; owns exactly one world; demo data not in `ownedWorlds()`. *(Flows implemented — formal verification pending.)*

### Slice 5 — First world → workshop

- [x] From dashboard state 2: **Enter workbench** → `/workbench/:worldId`
- [x] Existing `GettingStartedPanel` handles empty-entity state inside the new world
- [x] Dashboard ↔ Workshop navigation explicit (`WorldbuilderLayout` workbench link; `WorkbenchUserMenu` → dashboard; switcher **All worlds**)

**O1.4 exit gate:** [ ] Full happy path: register → onboard → state 2 → workbench → create first location on canvas.

### Slice 6— Landing page + options stripe *(deferred)*

> Ship only after O1.0–O1.4. Not blocking registration or onboard.

- [ ] `WorldbuilderLandingPage` — hero, wordmark, fine-press tone per [visual-style-guide.md](./visual-style-guide.md)
- [ ] Options stripe: *Open a workshop* → `/register`, *Sign in* → `/login`, *How the Workshop works* → explainer anchor
- [ ] `/` serves landing (replace login redirect)
- [ ] Footer: link to `unicapress.com`, sign-in
- [ ] Optional GTM: `wb_landing_view`

**O1.5 exit gate:** [ ] Public visitor at `/` sees landing; CTAs reach register/login; logged-in flow unchanged.

### Account shell (parallel — not blocking O1.4)

- [x] `AccountPage` — display name, email change, password change, profile avatar upload (`019_profile_avatars.sql`, `POST/DELETE /me/avatar`)
- [ ] `PreferencesPage` — placeholder sections only (workshop defaults, Archivist, notifications, appearance)

---

## Phase 1 complete — full exit gate

- [ ] All W1.0–W1.7 + W1.8 + W1.9 + W1.10 checkboxes above done
- [ ] W1.11 O1.0–O1.4 exit gates pass (O1.5 landing optional for Phase 1)
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
W1.0 Scaffold ──► W1.1 Auth + admin ops ──► W1.2 Worlds ──► W1.3 Entities + workbench UX
                                                              │
                        ┌─────────────────────────────────────┘
                        ▼
              W1.6 Dialogue ──► Prompt library ──► W1.7 Images ──► W1.8 Class/instance ──► W1.9 Click-drag relationships ──► W1.10 Tagging
                        │
            parallel ───┼─── W1.3 exit gate (seed world)
                        │
                        ├─── O1 Onboarding (after W1.6+ recommended): O1.0 routing ──► O1.1 auth ──► O1.2 dashboard ──► O1.3 onboard ──► O1.4 workshop ──► O1.5 landing (last)
                        ▼
              W1.4 Background LLM ──► W1.5 Rulesets
```

**Current sprint:** W1.9 exit gate verification (canvas link-drag, detail-panel edit, Archivist context). W1.8 class/instance exit gate in parallel. **O1 onboarding exit gates** (register → starter sample → clear → own world → workbench). Migrations `012`–`019` applied on dev. **Next:** W1.10 tagging (`020`); O1.0 prod DNS/CORS; admin relationship note/status UI; entity-delete policy for orphan edges.

---

## Notes

- **Shell vs App:** Do not add worldbuilder routes to `public/app/admin/`. Staff admin stays PHP; worldbuilder stays React (`/workbench`).
- **Admin vs workbench:** `/admin` = staff ops CRUD (service role). `/workbench` = worldbuilder workshop (RLS). Same entity API shapes; different prefixes and UX.
- **Onboarding:** `/dashboard` = account world picker (3 states per [scope-onboarding.md](./scope-onboarding.md) §8). `/register` + `/login` on `worldbuilder.unicapress.com`. Landing page O1.5 — last.
- **Entity extensions:** Split across W1.8 (class/instance), W1.9 (relationships), W1.10 (tagging). Full telling-engine packet assembly and skeleton tag casting deferred — see scope doc and per-milestone Slice E.
- **Filename typo:** scope doc is `scope-worldbulding+authoring.md` until renamed.
- **Jun 2026 workbench polish (with W1.7):** Entity editor **Notes** (renamed from Attributes; slugify keys on save). Archivist `choice_options` → dynamic reply chips. Image API fix: `entity_registry` import path. OpenAI model switched from deprecated `dall-e-3` to `gpt-image-1`. Canvas positions in `localStorage` from before image strips may overlap until user runs **Clean up** once.
- **Jun 2026 canvas create fix:** Location `POST` no longer sends `starting_setting: null` (API `_payload_to_row` skips unset null extras on create). Workbench surfaces create errors in topbar; successful create opens detail panel and pulses the new card.
- **Jun 2026 W1.8 class/instance:** Migration `011` (`entity_kind`, `class_id`). API validation in `_apply_entity_kind_rules`. Workbench **Type** toggle in detail status bar; link-drag “Instance of …” assignment; instance edges on canvas; Relationships section shows instance/class links. Admin entity forms not updated; Archivist class `*_meta` prepend not yet; canvas double-border + instance name on card (P1) pending.
- **Jun 2026 W1.9 relationships:** Migration `012` (note + status). Canvas link-drag → `RelationshipEditorPopover` with type-pair suggestions (`relationshipOptions.ts`). Edge labels clickable for edit. Detail-panel `WorkbenchRelationshipsSection` with inline edit + add form. Archivist includes focal-entity relationships (skips `apocrypha` links). Admin `/admin` entity edit still basic add/remove only.
- **Jun 2026 W1.7 panoramas + world marks:** Migration `017` (`world_panoramas`), `018` (`world_mark_color`). World detail panel panorama generate/delete; dashboard cards show latest panorama thumb. `WorldMark` letter-on-color in `WorkbenchWorldSwitcher`. `pick_world_mark_color` on create avoids sibling collisions.
- **Jun 2026 W1.11 onboarding (O1):** Migrations `013`–`015` (starter sample worlds, Archivist kickoff prompt, dialogue archive on clear). `VITE_APP_SURFACE` + hostname routing. Register/login pages, `DashboardPage` (3 states), starter sample provision + demo banner, simplified `OnboardArchivistPage`, dashboard ↔ workbench nav. Migration `019` profile avatars + `AccountPage`. Prod subdomain/CORS (O1.0) and landing (O1.5) still open.
- **Check off items** by changing `[ ]` → `[x]` as you go; add dates or PR links inline if helpful.
