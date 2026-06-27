# Worldbuilding + Authoring — Scope & Workplan (the App)

**Status:** DRAFT — third pass
**Date:** June 2026
**System:** The App (Node/Python + React, Postgres/Supabase) — see [standards-architecture+deployment.md](./archive/standards-architecture+deployment.md) for the Shell/App boundary, which still holds
**Supersedes:** [archive/workplan-unicapress.md](./archive/workplan-unicapress.md)'s Phase 0–4 W/E/F/X stream structure, for sequencing purposes. The PRD's *feature content* is not superseded — [prd-unicapress.md](./prd-unicapress.md) remains the canonical feature spec; this document reorders and re-scopes how we *build toward* it.
**Inputs:** [prd-unicapress.md](./prd-unicapress.md) (Feature Spaces A & F), [archive/V1-world-content-model.md](./archive/V1-world-content-model.md) (entity schema), `Amanuensis Pitch Dec2025-Short.pdf`, `C:\Users\micha\Amanuensis\docs\scope.md` (original Amanuensis data model)

---

## 1. Why this document exists

The App-side docs had drifted into contradiction. Those are archived in `docs/archive/`. This document restarts App-side planning around three build phases, sequenced the way you actually want to build:

```
PHASE 1 — WORLDBUILDING TOOLS    create a world, populate its canon, name things
                                  consistently, generate reference imagery
PHASE 2 — AUTHORING TOOLS        authorial voice, storytelling rules, consistency
                                  and craft checks
PHASE 3 — STORYTELLING ENGINE    the reader-facing generation pipeline — already
                                  specced in prd-unicapress.md Feature Space A,
                                  not re-scoped here
```

**Explicit acknowledgment:** the worldbuilding and authoring tools are, in real terms, a rebuild of core Amanuensis features, re-homed as the *authoring front-end* for a publishing engine rather than a collaborative writer's studio. Every Amanuensis-derived item below carries a short pedigree note so that lineage stays visible rather than getting laundered away.

Shell-owned utilities (Content Library, Email Library, event/activity logging) are **not** repeated here — the Shell already owns those, and this document only covers what's distinctly App-side.

---

## 2. Two-system reminder

Unchanged: **the Shell** (PHP/mysqli, this repo's `public/`) and **the App** (this document's subject) share no database, session, or auth.

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

## 5. PHASE 1 — Worldbuilding Tools

### 5.1 Accounts

- Supabase Auth (email + password, v1), admin-created/invited rather than public self-signup.
- `profiles.role = 'worldbuilder'`. *(Amanuensis: user/author profile — simplified; no separate `author_id` record in v1, author-voice metadata is a Phase 2 concern.)*
- One worldbuilder may own multiple **Worlds**.
- **Multi-tenant from the start.** The platform is built to support many worldbuilder accounts even though you'll be the only user for now. Tenancy boundary is the worldbuilder account: a World belongs to exactly one worldbuilder, enforced via RLS, and worldbuilders cannot see or query one another's worlds, entities, or conversation history. (Cross-worldbuilder collaboration/sharing on a single World is a different feature, not requested here, and is **not** implied by multi-tenancy — it would be a deliberate future addition.)

### 5.2 World shell

The root container. A World is a canon container, not a manuscript-in-progress.

| Field | Purpose |
|---|---|
| `world_title` | Display name |
| `world_logline` | One-sentence pitch *(Amanuensis: `narrative_logline`)* |
| `world_summary` | Paragraph overview *(Amanuensis: `narrative_synopsis`)* |
| `world_notes` | Free-form scratchpad — deliberately not Amanuensis's reorderable `Fragments`; just a field, until proven insufficient |
| `world_meta` | AI-generated short synopsis — produced by the `summarizeEntity` background function (§5.7) |

### 5.3 Entity CRUD

*(Amanuensis: `Characters`/`Locations`/`Objects`/`Attributes` — direct carryover. Using **Organizations** in place of the earlier working term "Factions.")*

- **Characters** — name, meta, sketch, description, world role tag (protagonist/antagonist/npc/love interest, etc.), status (`proposed`/`canon`/`excluded`), `imageprompt`. Suggested attributes: gender, age, affiliations, speech register, physical markers, secrets
- **Locations** — name, meta, sketch, description, status (`proposed`/`canon`/`excluded`), `imageprompt`, `starting_setting` flag. Suggested attributes: climate, geography, sensory keys, local idiom, tech/era notes
- **Objects** — name, meta, sketch, description, status (`proposed`/`canon`/`excluded`), `imageprompt`, kind (artifact/technology/document/natural/other). Suggested attributes: material, origin, faction/org, capabilities, taboos
- **Organizations** — name, meta, sketch, description, status (`proposed`/`canon`/`excluded`), `imageprompt`. Suggested attributes: location, size, organizing principles
- **Themes** — name, meta, status (`proposed`/`canon`/`excluded`), `imageprompt`. Suggested attributes: how it manifests in this world, compatible registers
- **Attributes** — generic key-value extension on any entity above
- **Relationships** — typed edges between any two entities

**Status workflow:** every entity carries `status: proposed | canon | excluded`. An entity the worldbuilder creates directly is `canon` by default — no separate publish gate in Phase 1. An entity proposed by the LLM dialogue interchange (§5.4) lands as `proposed` and only becomes `canon` once the worldbuilder explicitly reviews and accepts it. `excluded` is reserved for canon material intentionally withheld from later generation use (Phase 3 concern; not exercised meaningfully until then).

**`imageprompt`:** every entity carries a curated, reusable visual description distilled from (or alongside) its `*_description`, used as consistent context across *every* image generated for that entity, so a character or location doesn't visually drift between separate generation calls. Worldbuilder-editable, not purely auto-derived.

### 5.4 LLM dialogue interchange

The worldbuilder gets a chatbot-style interaction with an LLM for advice, brainstorming, and tightening of prose. Scoped against two PRD constraints that still apply: "no chatbot surface" is a **reader**-facing non-goal, not a worldbuilder one; and nothing writes to canon without explicit accept (the `proposed` status above).

- Conversational panel, scoped to the current World generally or to a specific entity being edited
- Read access to relevant world context (the entity + related entities via Relationships) so suggestions are grounded
- **Structured output:** the LLM can respond as plain chat, or as structured JSON intercepted and parsed to update entity data:
  1. Field-level suggestions for an entity already being edited (accept/reject)
  2. **Whole-entity creation as JSON** — the assistant can propose an entirely new character/location/object/etc. as a structured object during brainstorming, landing as `status: proposed` until the worldbuilder reviews and commits it
- Conversation history persists per-world as a means to an end — not itself an exported or versioned artifact

**Deferred:** configurable persona (§4), conversation summarization/expiry, semantic search over past conversations.

### 5.5 Image generation

*(Amanuensis: `generateCharacterImage` / `generateLocationImage` / `generateObjectImage` / `generateNarrativeImage` — direct carryover, brought forward into Phase 1.)*

- On-request image generation for any entity, using the entity's `imageprompt`/description/attributes as context
- Stored in a reference image gallery per entity

### 5.6 World rulesets / skills

Chunks of markdown text capturing worldbuilding rules that don't conform to structured data — per-world documents the dialogue interchange and background functions read as context. Examples:

- **Naming conventions** — e.g., "Character names should be Victorian-classic with a twisted vowel/syllable"
- **Reserved-name and collision list** — checked across all entity types; a "when asked to suggest a name, do this" section; notes on validation rules (warning vs. hard block when a worldbuilder types a name that collides or breaks the stated grammar)
- **Things-not-to-do list** — rules that prohibit or restrict LLM suggestions, e.g. "Electricity doesn't exist. Winged flight/airplanes are considered risky and flimsy. Combustion engines and gasoline are unknown. Common religions like Christianity and Islam don't exist, and references to divine figures or religion-inspired idioms should be avoided. Angels are 'heavenly messengers'; people say 'spirits bless you' instead of 'god bless' when someone sneezes."

This replaces a narrower, structured-only "naming standardization" tool with a more general, author-writable ruleset mechanism — naming grammar is one instance of it, not the whole of it.

### 5.7 Background functions

The LLM can run certain jobs automatically — as triggered by an edit, or on request — scanning world data and suggesting changes:

| Function | Behavior | Pedigree |
|---|---|---|
| `summarizeEntity` | Regenerates `*_meta` whenever a character/location/object/organization/theme's source fields change. A utility, not a user-facing feature — same overwrite-on-regen behavior as before. | Amanuensis: `summarizeCharacter`/`summarizeLocation`/etc., consolidated into one function across entity types |
| `rename` | When an entity's name changes, cascades the update across every other entity's text that mentions it (descriptions, relationships, attributes). | Amanuensis: `changeCharacterNames`/`changeLocationNames`/`changeObjectNames`, consolidated |

### 5.8 Out of scope for Phase 1

- Scenes, Fragments, Manuscripts, Sections, Chapters, reordering — writer's-studio constructs with no equivalent here; tellings are engine-composed and fixed-once-read, there is no user-drafted manuscript to fragment or reorder
- Skeleton/storybeat authoring (Phase 2, see §6.1)
- Package publish/versioning (Phase 3; `canon` entities are simply the current state of the world until Phase 3's publish pipeline exists to snapshot them)
- Silent background canon suggesters (`suggestCharacters`/`suggestNotes`-style) — excluded outright, not deferred; nothing proposes new canon content without the worldbuilder having opened the dialogue tool to ask for it

---

## 6. PHASE 2 — Authoring Tools

Still the least defined phase by design — sketched here, to be scoped properly once Phase 1 ships and there's real entity data to design against.

### 6.1 Authorial voice & storytelling rules

- **Style fingerprint** — writing samples in, extracted style rules out (diction, rhythm, banned constructions). *(PRD A1.5; not Amanuensis-derived as a discrete tool, though Amanuensis's author-style attributes were a primitive version of the same idea.)*
- **Lexical fingerprint** — a multi-point JSON summary of an author's writing style: verb frequency, average sentence length, frequency of sensory words, etc. Likely the structured data layer underneath the style fingerprint above.
- **Writing style** — TBD; flagged for further scoping once Phase 1 exists to test against.
- **Storytelling rules / skeleton authoring** — the author's preferred story shapes, registers, pacing. *(Amanuensis: `Storybeats` + `brainstormStorybeats` — direct carryover, constrained: authored shape, not user-reorderable plot, per PRD A2.)*

### 6.2 Sensory technique tool

*(Amanuensis: `sensorize` — was a one-off prompt suggesting sensory descriptions for a scene/fragment. Reimagined here as a craft tool: ways and recipes for working sensory description into prose generally, tied to the author's voice rather than to any single piece of content, since Phase 2 has no scenes/fragments to target directly.)*

### 6.3 Background critique functions

This is the concrete shape of "plot hole checks, consistency, thematic harmony" from the original ask — automated, content-analysis functions, not dialogue-driven. Each is a reframed Amanuensis prompt-function, now scoped as a structured critique pass rather than a one-off brainstorm:

| Function | Behavior | Pedigree |
|---|---|---|
| **Plot hole finder** | Flags odd or non-sequitur events across storybeats/skeletons. | New name; analogous in spirit to `suggestContinuity` but specifically plot-logic-focused |
| **Continuity critic** | Finds inconsistent terms, descriptions, and details across content — can start surfacing entity-description conflicts even on Phase 1 data, but ships as a tool in Phase 2. | Amanuensis: `suggestContinuity`, reframed as an explicit on-demand critic rather than a silent background writer |
| **Beta-reader critic** | Analyzes prose from a reader's perspective; its output feeds the author's revision pass rather than auto-editing anything. | New — no direct Amanuensis precedent (`analyzeFragment` was closest, but author-voice-focused rather than reader-experience-focused) |
| **Timeline critic** | Charts elapsed time across the story to verify it holds together. | New — no Amanuensis precedent |
| **Exposition critic** | Flags passages where exposition runs long without dialogue or scene movement. | Amanuensis: `dialogueize` — was a prompt suggesting a dialogue rewrite; reimagined as a critique signal rather than a rewrite generator (no rewrite function exists here — Unica Press content is engine-manifested, not author-rewritten, once it reaches Phase 3) |

**Open question carried from this section's framing:** are these the *same* checks as the PRD's automated Editorial Battery (A5), exposed early as author-facing tools, or a deliberately distinct, lighter-weight Phase 2 toolset? Worth a dedicated session once Phase 1 ships.

### 6.4 Out of scope for Phase 2

- Scenes, Fragments, Manuscripts, Sections, Chapters — still no equivalent; tellings are engine-composed and fixed-once-read, so there is no user-drafted manuscript for authoring tools to operate on either
- Package publish/versioning (Phase 3)

---

## 7. PHASE 3 — Storytelling Engine

Already comprehensively specced as **Feature Space A** in [prd-unicapress.md](./prd-unicapress.md) §7, plus the Telling API (§9) and reader experience (Feature Spaces B/C). Phase 1/2 work is the input: entities become packets (A1), the style fingerprint conditions manifestation (A4), skeletons drive composition (A3). Nothing to add here until Phase 1/2 are far enough along to test against.

---

## 8. Proposed build sequence (Phase 1 only, for now)

| # | Deliverable |
|---|---|
| W1.0 | App repo scaffold per §3 stack, Supabase dev project, CI, multi-tenant RLS policies from day one |
| W1.1 | Worldbuilder account (Supabase Auth, `role=worldbuilder`), login, basic shell |
| W1.2 | World CRUD (title/logline/summary/notes), scoped to owning worldbuilder |
| W1.3 | Character / Location / Object / Organization / Theme CRUD + Attributes + Relationships |
| W1.4 | Background functions: `summarizeEntity` (meta on save), `rename` (cascade on name change) |
| W1.5 | World rulesets/skills editor (markdown documents per world) |
| W1.6 | LLM dialogue interchange — world/entity-scoped, field-level suggestions + whole-entity JSON creation (`proposed` status) |
| W1.7 | Image generation per entity + `imageprompt` field |

Phase 2's workplan gets written once Phase 2 itself is properly scoped (§6).

---

## 9. Open questions to resolve together

1. **World notes (§5.2)** — single freeform field, or lightweight structure (dated entries) even at v1?
2. **Dialogue interchange persistence (§5.4)** — confirmed per-world, but unbounded forever, or capped/expired eventually?
3. **Phase 2 critique functions vs. PRD Editorial Battery (§6.3)** — same checks exposed early, or a distinct toolset?
4. **specs-platform-staff-admin.md** (archived but not contradicted) assumed engine-admin lives inside this same App — does Phase 1's worldbuilder tooling share a UI shell with a later staff/engine-admin tier, or are they separate surfaces?
5. **"Writing style" (§6.1)** — placeholder bullet, undefined; what is this meant to capture that the style/lexical fingerprint doesn't already?

---

*Third draft — flag anything still wrong, missing, or premature.*
