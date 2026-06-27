# Worldbuilding + Authoring — Scope & Workplan (the App)

**Status:** DRAFT — third pass
**Date:** June 2026
**System:** The App (Python/FastAPI + React, Postgres/Supabase) — see [standards-architecture+deployment.md](./archive/standards-architecture+deployment.md) for the Shell/App boundary, which still holds
**Supersedes:** [archive/workplan-unicapress.md](./archive/workplan-unicapress.md)'s Phase 0–4 W/E/F/X stream structure, for sequencing purposes. The PRD's *feature content* is not superseded — [prd-unicapress.md](./prd-unicapress.md) remains the canonical feature spec; this document reorders and re-scopes how we *build toward* it.
**Inputs:** [prd-unicapress.md](./prd-unicapress.md) (Feature Spaces A & F), [archive/V1-world-content-model.md](./archive/V1-world-content-model.md) (entity schema), `Amanuensis Pitch Dec2025-Short.pdf`, `C:\Users\micha\Amanuensis\docs\scope.md` (original Amanuensis data model)

---

## 1. Why this document exists

The App-side docs had drifted into contradiction. Those are archived in `docs/archive/`. This document restarts App-side planning around three build phases, sequenced the way you actually want to build:

```
PHASE 1 — WORLDBUILDING TOOLS    create a world, populate its canon, name things consistently, generate reference imagery
PHASE 2 — AUTHORING TOOL        authorial voice, storytelling rules, consistency and craft checks
PHASE 3 — STORYTELLING ENGINE   the reader-facing generation pipeline — already specced in prd-unicapress.md Feature Space A, not re-scoped here
```

**Explicit acknowledgment:** the worldbuilding and authoring tools are, in real terms, a rebuild of core Amanuensis features, re-homed as the *authoring front-end* for a publishing engine rather than a collaborative writer's studio. Every Amanuensis-derived item below carries a short pedigree note so that lineage stays visible rather than getting laundered away.

Shell-owned utilities (Content Library, Email Library, event/activity logging) are **not** repeated here — the Shell already owns those, and this document only covers what's distinctly App-side.

---

## 2. Two-system reminder

Unchanged: **the Shell** (PHP/mysqli, this repo's `public/`) and **the App** (this document's subject) share no database, session, or auth.

Three distinct surfaces, not one admin console:

- **Shell** (`unicapress.com` / staff admin) — marketing site, staff ops, Content/Email Library, event logging
- **App — worldbuilder** — Phase 1/2 tooling in this document
- **App — engine-admin** (future) — prompt library, generation telemetry, staff/engine controls; lives in the App codebase but is not Phase 1 worldbuilder work

---

## 3. Stack

| Layer | Technology | Role |
|---|---|---|
| UI | React + Vite + TypeScript | Worldbuilder/authoring SPA, later the reader |
| API | FastAPI (Python) | REST API, validation, LLM calls, business logic |
| DB + Auth | Supabase (Postgres + GoTrue) | Entities, accounts, RLS — tenant boundary enforced at the RLS layer (§5.1) |
| LLM | Anthropic API (Claude) | Dialogue interchange, meta generation, naming, critique functions, eventually manifestation |
| Image gen | OpenAI | Entity reference imagery |
| Local dev | Docker Compose | `web` + `api`; Supabase stays cloud-hosted (dev project) |
| Hosting (prod) | Vercel (web) / Railway (API) | |

---

## 4. Cross-phase add-on

**Scribe / AI assistant persona** *(Amanuensis: `Scribe` entity — a named, configurable assistant with its own personality attributes — `scribe_name`, `scribe_overview`, gender/age/ethnicity/personality-trait attributes)*. Nice-to-have. Skinning the dialogue interchange (Phase 1) and any authoring-side critique tools (Phase 2) with a configurable persona is a cosmetic layer that can be added once those underlying tools exist — it doesn't gate or get gated by any phase below.

---

## 5. PHASE 1 — WORLDBUILDING TOOLS

### 5.1 Accounts

- Supabase Auth (email + password, v1), admin-created/invited rather than public self-signup.
- `profiles.role = 'worldbuilder'`. *(Amanuensis: user/author profile — simplified; no separate `author_id` record in v1, author-voice metadata is a Phase 2 concern.)*
- One worldbuilder may own multiple **Worlds**.
- **Multi-tenant from the start.** The platform is built to support many worldbuilder accounts even though you'll be the only user for now. Tenancy boundary is the worldbuilder account: a World belongs to exactly one worldbuilder, enforced via RLS, and worldbuilders cannot see or query one another's worlds, entities, or conversation history. (Cross-worldbuilder collaboration/sharing on a single World is a different feature, not requested here, and is **not** implied by multi-tenancy — it would be a deliberate future addition.)

### 5.2 World shell

The root container. *(A World is a canon container, not a manuscript-in-progress.)*

| Field | Purpose |
|---|---|
| `world_title` | Display name |
| `world_logline` | One-sentence pitch *(Amanuensis: `narrative_logline`)* |
| `world_summary` | Paragraph overview *(Amanuensis: `narrative_synopsis`)* |
| `world_notes` | Free-form scratchpad — deliberately not Amanuensis's reorderable `Fragments`; just a field, until proven insufficient |
| `world_meta` | AI-generated short synopsis — produced by the `summarizeEntity` background function (§5.7) |

### 5.3 Entity CRUD

- **Characters** — name, meta, sketch, description, world role tag (protagonist/antagonist/npc/loveinterest, etc.), status (`proposed`/`canon`/`apocrypha`), imageprompt. Suggested attributes (gender, age, affiliations, speech register, physical markers, secrets)
- **Locations** — name, meta, sketch, description, status (`proposed`/`canon`/`apocrypha`), imageprompt, starting_setting flag. Suggested attributes (climate, geography, sensory keys, local idiom, tech/era notes)
- **Objects** — name, meta, sketch, description, status (`proposed`/`canon`/`apocrypha`), imageprompt, kind (artifact/technology/document/natural/other). Suggested attributes (material, origin, faction/org, capabilities, taboos)
- **Organizations** — name, meta, sketch, description, status (`proposed`/`canon`/`apocrypha`), imageprompt. Suggested attributes (location, size, organizing principles)
- **Themes** — name, meta, status (`proposed`/`canon`/`apocrypha`), imageprompt. Suggested attributes (how it manifests in this world, compatible registers)
- **Attributes** — generic key-value extension on any entity above
- **Relationships** — typed edges between any two entities

**Status workflow:** every entity carries `status: proposed | canon | apocrypha`. New entities start as `proposed` — whether the worldbuilder drafts them on the canvas or they arrive from a Telling. The worldbuilder then promotes an entity to `canon` or `apocrypha`, or deletes it outright. Delete is not offered for Telling-origin entities (only canon/apocrypha promotion). `apocrypha` is canon material intentionally withheld from later generation use (Phase 3 compartmentalization; A1.8).

**Image prompt:** all entities carry `imageprompt` as a curated, reusable visual description distilled from (or alongside) the entity's `*_description`, used as consistent context across *every* image generated for that entity, so a character or location doesn't visually drift between separate generation calls. Worldbuilder-editable, not purely auto-derived.

### 5.4 LLM dialogue interchange

The author gets a chatbot-style interaction with an LLM for advice and brainstorming and tightening of prose.

- Conversational panel, scoped to the current World generally or to a specific entity being edited
- Read access to relevant world context (the entity + related entities via Relationships) so suggestions are grounded
- Structured output. The LLM can generate responses as chat returns or as structured JSON to be intercepted, parsed, and used to update entity data:
   1. Field-level suggestions for an entity already being edited (existing `*_proposed`-style accept/reject)
   2. **Whole-entity creation as JSON** — the assistant can propose an entirely new character/location/object/etc. as a structured object during brainstorming, which the worldbuilder can review and commit as a new entity in one action, rather than only ever editing one field of one existing record
- Conversation history persists per-world as a means to an end — not itself an exported or versioned artifact

**Deferred:** configurable persona (§4), conversation summarization/expiry, semantic search over past conversations.

### 5.5 Image generation

An author can regenerate images for entities for creative visualization purposes (`generateCharacterImage` / `generateLocationImage` / `generateObjectImage` / `generateNarrativeImage`).

- On-request image generation for any entity, using the entity's description/attributes as context
- Stored in a reference image gallery per entity

### 5.6 World rulesets / skills

Chunks of text (markdown files) to capture worldbuilding rules that don't conform to structured data. Examples:

- **Per-world naming conventions** — e.g., "Character names should be Victorian-classic with a twisted vowel/syllable"
- **Reserved-name and collision list** — checked across all entity types; "When asked to suggest a name do this:" section; validation rules (warning or hard block when a worldbuilder types a name that collides or breaks the stated grammar)
- **Things not to do list** — rules that prohibit or restrict LLM suggestions, e.g. "Electricity doesn't exist. Winged flight / airplanes are considered risky and flimsy. Combustion engines and gasoline are unknown. Common religions like Christianity and Islam don't exist, and references to divine figures or religion-inspired idioms should be avoided. Angels are 'heavenly messengers,' people say 'spirits bless you' instead of 'god bless' when someone sneezes"

### 5.7 Background functions

The LLM can run certain jobs automatically as triggered or upon request, scanning world data and suggesting changes.

| Function | Behavior |
|---|---|
| `summarizeEntity` | Regenerates `*_meta` whenever a character/location/object/organization/theme's source fields change. A utility, not a user-facing feature — same overwrite-on-regen behavior as before. |
| `rename` | When an entity's name changes, cascades the update across every other entity's text that mentions it (descriptions, relationships, attributes). |

---

## 6. PHASE 2 — AUTHORING TOOLS

A tool for authors to create e-guidelines, frameworks, and guardrails for LLMs to generate copy for users.

### 6.1 Authorial voice & storytelling rules and skills

- Chunks of text (markdown files) to capture authoring rules that allow for the creation of content in the author's style
- **Style fingerprint** — writing samples in, extracted style rules out (diction, rhythm, banned constructions). *(PRD A1.5)*
- **Storytelling rules / skeleton authoring** — the author's preferred story shapes, registers, pacing. *(PRD A2 — authored shape, not user-reorderable plot.)*
- **Lexical fingerprint** — e.g. a multi-point JSON file summarizing an author's writing style (verb frequency, average sentence length, frequency of sensory words, etc.)
- **Writing style** — capturing rules and preferences learned from actions taken by the author (see §6.3 preference generators)
- **Authorial conventions related to outlines, story skeletons, story structures, and storybeats**
- **Authorial conventions related to manuscripts and writing** — Scenes, Fragments, Manuscripts, Sections, Chapters; includes things like chapter length (as rules/preferences, not as a user-drafted manuscript workspace)
- **Authorial preferences for sensory descriptions**
- **Authorial preferences for dialogue and first-person narration**

### 6.2 Background critique functions

A set of automated, content-analysis functions, rules and processes that can run over generated copy to fine tune and critique, making suggestions and questions for future revisions.

| Function | Behavior |
|---|---|
| **Plot hole finder** | Flags odd or non-sequitur events across storybeats/skeletons. |
| **Continuity critic** | Finds inconsistent terms, descriptions, and details across content — can start surfacing entity-description conflicts even on Phase 1 data, but ships as a tool in Phase 2. |
| **Beta-reader critic** | Analyzes prose from a reader's perspective; its output feeds the author's revision pass rather than auto-editing anything. |
| **Timeline critic** | Charts elapsed time across the story to verify it holds together. |
| **Exposition critic** | Flags passages where exposition runs long without dialogue or scene movement. |

### 6.3 Preference generators

Surfaces where the author reacts to **sample generated copy** and the system extracts durable preference rules — not a writing or manuscript-editing interface. The LLM generates text; the author trains it on how to generate text the way the author prefers; the output is stored rules/skills, not a saved draft.

| Function | Behavior |
|---|---|
| **Copy editor** | Author adjusts sample copy inline to their preferred form; each accepted change feeds rule/preference generation for the style fingerprint and writing-style library. |
| **Author reaction tool** | Author reads a chunk of generated copy and notes what was done well and incorrectly; the LLM converts that feedback into durable preference rules. |

### 6.4 Out of scope for Phase 2

- User-drafted Scenes, Fragments, Manuscripts, Sections, Chapters as editable documents — tellings are engine-composed and fixed-once-read (Phase 3); Phase 2 captures *conventions about* those shapes, not a collaborative writing workspace
- Package publish/versioning (Phase 3)

---

## 7. PHASE 3 — STORYTELLING ENGINE

See [scope-storytellingengine.md](./scope-storytellingengine.md). Phase 1/2 output feeds Phase 3: entities become packets, the style fingerprint conditions manifestation, skeletons drive composition.

---

## 8. Proposed build sequence (Phase 1 only, for now)

Shell staff admin (Phase S — marketing site, staff console, Content/Email Library, event logging) is tracked in [scope-marketing-shell.md](./scope-marketing-shell.md). It precedes and runs in parallel with App Phase 1; it is not repeated here.

| # | Deliverable |
|---|---|
| W1.0 | App repo scaffold per §3: React/Vite/TS + FastAPI, Docker Compose (`web` + `api`), Supabase dev project, CI, multi-tenant RLS policies from day one |
| W1.1 | Worldbuilder account (Supabase Auth invite-only, `profiles.role = 'worldbuilder'`), login, basic App UI shell |
| W1.2 | World CRUD (`world_title`, `world_logline`, `world_summary`, `world_notes`), scoped to owning worldbuilder |
| W1.3 | Character / Location / Object / Organization / Theme CRUD + Attributes + Relationships; entity `status` workflow |
| W1.4 | Background functions: `summarizeEntity` (meta on save), `rename` (cascade on name change) |
| W1.5 | World rulesets/skills editor (markdown documents per world) — naming conventions, reserved/collision list, "things not to do"; includes naming-grammar UI and "suggest names" action where applicable |
| W1.6 | LLM dialogue interchange — world/entity-scoped, field-level suggestions + whole-entity JSON creation (new entities land as `proposed`) |
| W1.7 | Image generation per entity + reusable `imageprompt` field + reference image gallery |

Phase 2's workplan gets written once Phase 2 itself is properly scoped (§6).

---

## 9. Open questions to resolve together

1. **World notes (§5.2)** — single freeform field, or lightweight structure (dated entries) even at v1?
2. **Naming grammar scope (§5.6)** — per-world only, or a reusable library across worlds?
3. **Dialogue interchange persistence (§5.4)** — keep conversation history forever, or cap/expire it?
4. **Phase 2 critique functions vs. PRD Editorial Battery (§6.2)** — same checks exposed early, or a distinct toolset?
5. **App UI shells (§2)** — worldbuilder tooling and future engine-admin share one React app with role-gated nav, or separate deploy surfaces?

---

*Third draft — flag anything still wrong, missing, or premature.*
