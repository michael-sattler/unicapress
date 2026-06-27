# Unica Press — World Content Model (Draft)

**Status:** Canonical entity schema (deep dive). PRD §6 is the summary; this doc has full field definitions.  
**Date:** June 2026  
**Parent:** [prd-unicapress.md](./prd-unicapress.md) · [workplan.md](./workplan.md)  
**Reference:** [scope-amanuensis.md](./scope-amanuensis.md) (FOR REFERENCE ONLY)

This document factors Amanuensis's entity model into Unica Press scope. It defines **what structured world content exists**, **how it is authored**, and **how the engine consumes it**. It does not redefine the reader experience or generation pipeline — those remain in the PRD.

---

## 1. What carries over vs. what does not

Amanuensis is a **writer's collaborative studio**: iterative drafting, reorderable scenes, AI sidebar, user plot authority. Unica Press is a **publishing engine**: authored world canon, fixed tellings, reader position only. The content *shapes* align; the *workflows* are often inverted.

### Adopt (content shapes)

| Amanuensis entity | Unica Press home | Notes |
|---|---|---|
| Characters | World Package → Canon ledger; Skeleton role slots; per-telling instantiated cast | Canon characters are world facts; telling characters are composed instances |
| Locations | World Package → Gazetteer + location packets | Already in PRD A1.2; this spec adds entity fields |
| Objects | World Package → Canon ledger; per-telling continuity ledger | Artifact types in canon; instances in running state |
| Attributes (key-value on any entity) | All world entities | Flexible extension without schema churn |
| Storybeats | Skeleton Library beats | Authored story *shape*, not user-reorderable plot |
| `*_meta` (AI synopsis fields) | Authoring convenience + packet derivation | Auto-generated from description + attributes; author-editable; see §3.13 |
| `proposed` / `approved` status | Canon tiers | Maps to canonization workflow (F1.5), not inline AI approval |
| ImageGallery (per entity) | Gazetteer cards, entity reference art, plate briefs | Static/curated in v1; not reader-driven generation |
| Changelog | World Package versioning + entity audit trail | Every publish = immutable package version |

### Exclude (workflow & surface — opposite of Unica Press)

| Amanuensis pattern | Why it is out of scope |
|---|---|
| Narratives as user-authored works in progress | World is authored once; **tellings** are engine output, not editable narratives |
| Fragments (notes, drafts, alternate prose) | No iterative scene drafting; prose is manifested once per scene, fixed on read |
| Reorderable / omittable scenes | Spine is composed and immutable (A3.5); reader cannot reshape plot |
| Conversations, Scribes, AI sidebar | No chatbot surface (PRD Non-Goals) |
| `draftFragment`, `rewriteFragment`, `brainstormStorybeats`, etc. | Writer-assist functions; engine composes and manifests instead |
| Background `suggestCharacters` / `suggestNotes` writing back into canon | Tellings are apocryphal (Principle 8); nothing auto-promotes to canon |
| Manuscripts as exportable rearrangements of user narrative | **Manuscript** in Unica Press = the fixed telling reading artifact (Feature Space C) |
| Sections / Chapters as user-organized hierarchy | Tellings are short (5–8 scenes); spine beats map 1:1 to manifested scenes |
| `*_proposed` as inline AI suggestion UX | Author owns canon explicitly via tooling; no reader-facing or chat-driven approval |

### Reframe (same idea, different owner)

| Amanuensis | Unica Press equivalent |
|---|---|
| `summarizeCharacter`, `summarizeLocation`, … | Auto meta generation in F-space on source-field change; feeds packet compilation (§3.13) |
| `suggestContinuity` | Editorial battery continuity + canon checks (A5.1, A5.2) — runs on generated scenes, not author drafts |
| `nameify` | Naming grammar (A1.3) at composition time |
| `sensorize` | Encoded in location/entity attributes and location packets (sensory palette) |

---

## 2. Planes and entity ownership

Entities live in one of three planes. Data flows one way (PRD §5).

```
WORLD PLANE (authored, versioned)
  World, Canon entities, Skeletons, Registers, Style fingerprint
       ↓ read-only at generation time
GENERATION PLANE (per telling, ephemeral until fixed)
  Composed spine, Instantiated cast, Scenes (buffered → fixed), Continuity ledger
       ↓ Telling API only
EXPERIENCE PLANE (reader artifacts)
  Request card, Fixed scenes, Accession record, Shelf entries
```

**Rule:** Nothing in the Generation or Experience plane writes back to the World plane without explicit author canonization (F1.5).

---

## 3. World Plane entities

The World Package (A1) is not a monolith — it is a **versioned bundle** of typed entities plus compiled packets derived from them.

### 3.1 World (root)

The container for all canon content for one authored universe (e.g., the Steamlands).

| Field | Purpose |
|---|---|
| `world_id` | Stable identifier |
| `world_title` | Display name |
| `world_meta` | Short machine-oriented synopsis of the setting |
| `invariants` | Compiled → A1.1 invariants packet |
| `naming_grammar` | Compiled → A1.3 |
| `style_fingerprint` | Compiled → A1.5 |
| `version` | Semver or monotonic publish id (A1.7) |
| `published_at` | Immutable publish timestamp |

### 3.2 Location

Geographic or place entity. Gazetteer entries are **Locations** with `gazetteer_visible: true`.

| Field | Purpose |
|---|---|
| `location_id` | Stable id |
| `location_name` | Canonical name |
| `location_meta` | ≤ ~200 token synopsis for indexing and packet headers |
| `location_sketch` | Author-facing short prose (human-readable) |
| `location_description` | Long-form sourcebook prose |
| `location_status` | `draft` \| `canon` \| `excluded` (A1.8) |
| `gazetteer_visible` | Whether this location appears on the request slip (B1.1) |
| `location_color` | Optional authoring UI accent |
| `packet` | Compiled location packet (A1.2), derived on publish |

**Suggested attributes** (via Attributes entity, §3.7): `climate`, `geography`, `factions`, `sight`, `sound`, `smell`, `touch`, `taste`, `local_idiom`, `technology_notes`, `era_notes`.

Locations may be nested or related (e.g., city → district) via Relationships (§3.8).

### 3.3 Character

A person, institution-as-character, or other dialogue-capable (or narratively agentive) entity in **world canon**. Not the same as a telling's instantiated protagonist.

| Field | Purpose |
|---|---|
| `character_id` | Stable id |
| `character_fullname` | Canonical name (reserved list for naming grammar) |
| `character_firstname`, `character_lastname` | Optional decomposition |
| `character_meta` | Short synopsis for retrieval |
| `character_sketch` | Author-facing summary |
| `character_description` | Long-form biography / sourcebook entry |
| `character_role` | World role tag: `historical`, `faction_head`, `archetype`, `mythic`, `minor`, … — **not** telling role (protagonist/antagonist) |
| `character_status` | `draft` \| `canon` \| `excluded` |
| `character_color` | Optional authoring UI accent |
| `dialogue_capable` | Boolean; false for places/objects treated as character records in sourcebook |

**Suggested attributes:** `gender`, `age`, `affiliations`, `speech_register`, `physical_markers`, `secrets` (with `excluded` flag per attribute if needed).

Telling-level cast is created at composition (A3.1) from skeleton **role slots** + naming grammar, and may *reference* canon characters without being identical to them.

### 3.4 Object

A thing — artifact, technology, document, vehicle, species specimen, etc.

| Field | Purpose |
|---|---|
| `object_id` | Stable id |
| `object_name` | Canonical name |
| `object_meta` | Short synopsis |
| `object_description` | Long-form description |
| `object_status` | `draft` \| `canon` \| `excluded` |
| `object_color` | Optional authoring UI accent |
| `object_kind` | `artifact`, `technology`, `document`, `natural`, `other` |

**Suggested attributes:** `material`, `origin`, `faction`, `capabilities`, `taboos`.

Per-telling object **instances** (e.g., "the wrench in hand") live in the continuity ledger (§4.3), not in world canon unless the object type is a canon entity.

### 3.5 Theme

World-level motifs and thematic vocabulary — distinct from per-skeleton guardrails (A2.1).

| Field | Purpose |
|---|---|
| `theme_id` | Stable id |
| `theme_name` | e.g., "steam vs. sin", "measurement as morality" |
| `theme_meta` | How the theme manifests in this world |
| `theme_status` | `draft` \| `canon` \| `excluded` |
| `compatible_registers` | Optional tag list linking to A1.4 |

Skeletons reference themes by id in their `thematic_guardrails`; composition may select among compatible themes for variance (A3.4).

### 3.6 Faction *(new, implied by location packets)*

Groups, guilds, societies, governments — first-class canon entities rather than burying them only inside location prose.

| Field | Purpose |
|---|---|
| `faction_id` | Stable id |
| `faction_name` | |
| `faction_meta` | |
| `faction_description` | |
| `faction_status` | `draft` \| `canon` \| `excluded` |

Relationships link factions to locations and characters (§3.8).

### 3.7 Attribute

Key-value extension on any world entity. Adopted directly from Amanuensis.

| Field | Purpose |
|---|---|
| `attribute_id` | |
| `entity_type` | `world` \| `location` \| `character` \| `object` \| `theme` \| `faction` \| `skeleton` |
| `entity_id` | |
| `attribute_key` | Namespaced string, e.g. `sensory.smell` |
| `attribute_value` | String or structured JSON |
| `attribute_status` | `draft` \| `canon` \| `excluded` |

Attributes marked `excluded` are omitted from packet compilation and canon queries.

### 3.8 Relationship

Typed edges in the canon ledger (A1.6). Replaces implicit "mentioned in prose" links.

| Field | Purpose |
|---|---|
| `relationship_id` | |
| `from_type`, `from_id` | |
| `to_type`, `to_id` | |
| `relationship_type` | e.g. `located_in`, `member_of`, `rival_of`, `created_by`, `knows_of` |
| `relationship_notes` | Qualifiers, dates, certainty |
| `relationship_status` | `draft` \| `canon` \| `excluded` |

The editorial battery queries relationships for HoleFinder (A5.1).

### 3.9 Event *(canon ledger)*

Historical or ongoing world events — facts the engine must not contradict.

| Field | Purpose |
|---|---|
| `event_id` | |
| `event_name` | |
| `event_meta` | |
| `event_description` | |
| `event_status` | `draft` \| `canon` \| `excluded` |
| `event_date` | In-world dating string or structured calendar ref |

### 3.10 Image (reference gallery)

Curated static assets attached to world entities. Not the plate pipeline output (A7).

| Field | Purpose |
|---|---|
| `image_id` | |
| `entity_type`, `entity_id` | |
| `image_type` | `thumbnail`, `gazetteer_card`, `reference`, `portrait` |
| `image_path` | Or CDN ref |
| `image_caption` | |

Gazetteer cards (B1.1) bind to `image_type: gazetteer_card` on Locations.

### 3.11 Skeleton & Storybeat

Skeletons (A2) are authored narrative shapes. **Storybeats** are their ordered children — adopted from Amanuensis nomenclature, constrained by Unica Press rules.

**Skeleton**

| Field | Purpose |
|---|---|
| `skeleton_id` | |
| `skeleton_title` | |
| `skeleton_meta` | |
| `register_tags` | Compatible registers (A1.4) |
| `location_tags` | Compatible gazetteer locations |
| `theme_tags` | Referenced theme ids |
| `scene_count_min`, `scene_count_max` | |
| `target_words_per_scene` | |
| `status` | `draft` \| `published` \| `retired` |

**Storybeat** (skeleton beat)

| Field | Purpose |
|---|---|
| `storybeat_id` | |
| `skeleton_id` | |
| `storybeat_ordinal` | Order is **authored**, not user-reordered at runtime |
| `storybeat_title` | |
| `storybeat_type` | `setup`, `complication`, `reversal`, `climax`, `resolution`, … |
| `storybeat_content` | Intent description for composition and manifestation |
| `storybeat_meta` | |
| `character_role_slots` | Named slots with constraints, e.g. `protagonist: outsider, competent` |
| `thematic_guardrails` | Beat-level invariants |
| `plate_beat` | Optional boolean — designate stipplegraph beat (A7.1) |

There is no Fragment entity in the skeleton or telling path.

### 3.12 Register

Request-slip taxonomy entry (A1.4). Structured register definition, not a freeform tag.

| Field | Purpose |
|---|---|
| `register_id` | |
| `register_name` | e.g. `penny_dreadful` |
| `register_meta` | |
| `voice_notes` | |
| `framing_device` | |
| `structural_tendencies` | |
| `content_rating` | For A5.5 |

### 3.13 Meta fields (`*_meta`)

Every world entity carries a `*_meta` field: a short, machine-oriented synopsis used for packet headers, retrieval, and publish lint (W1.1). Long-form `*_description` and structured attributes are the **authoring source of truth**; `*_meta` is a derived layer.

**Generation (v1):**

- `*_meta` is **AI-generated on the fly** when source fields change — i.e. on entity save when `*_description`, `*_sketch`, or linked attributes are updated, and on explicit **Regenerate meta** in F-space.
- The author may **edit** `*_meta` directly in tooling at any time.
- **Overwrite rule (v1):** any auto-generation run **replaces** the current `*_meta` in full, including prior author edits. There is no "author override" flag or merge logic yet. If the author needs a specific wording, they edit again after regeneration — or avoid touching source fields until edits are final.
- **Deferred:** detecting author-originated meta and skipping auto-overwrite (e.g. `meta_author_locked` or stale-source-hash comparison).

**Publish:** the `*_meta` values **frozen in the published package snapshot** are whatever is current at publish time. Auto-generation during a later edit session does not alter already-published versions (A1.7).

**Not the same as:** `cast_meta` (§4.2) — telling-runtime state updated by scene extraction (A4.4), not world authoring.

---

## 4. Generation Plane entities (per telling)

These exist only for the life of a telling. They are persisted for provenance (A6.2) but are **apocryphal** — never promoted to canon without author action.

### 4.1 Telling

| Field | Purpose |
|---|---|
| `telling_id` | |
| `world_package_version` | |
| `skeleton_id` | |
| `request` | City, register, addressee, patron token |
| `seed` | Deterministic composition seed (A3.1) |
| `title`, `accession_number`, `stack_coordinates` | |
| `spine` | Immutable composed beat map (A3.5) |
| `status` | `composing`, `buffering`, `in_read`, `fixed_complete`, `abandoned` |

### 4.2 Instantiated character (composed cast)

| Field | Purpose |
|---|---|
| `cast_id` | |
| `telling_id` | |
| `role_slot` | From skeleton beat slot |
| `assigned_name` | Via naming grammar |
| `archetype_notes` | From composition |
| `canon_character_ref` | Optional link if cast maps to a canon character |
| `cast_meta` | Running synopsis updated by extraction (A4.4) |

### 4.3 Continuity ledger (running state)

Per-telling structured state, updated after each accepted scene (A4.3, A4.4). Subsumes Amanuensis continuity concerns without draft fragments.

| Partition | Examples |
|---|---|
| `time` | Time of day, elapsed duration |
| `knowledge` | Who knows what |
| `physical` | Injuries, location of characters |
| `objects_in_play` | Object instances: holder, state, visibility |
| `scene_summaries` | Rolling short summaries of fixed scenes |
| `open_threads` | Beat-level obligations still unresolved |

Serialized JSON document per telling; schema versioned for extraction prompts.

### 4.4 Scene (manifested)

| Field | Purpose |
|---|---|
| `scene_id` | |
| `telling_id` | |
| `scene_ordinal` | Maps to spine beat |
| `scene_text` | Manifested prose |
| `fixed_at` | Null until reader fixes (A6.1) |
| `battery_verdicts` | Logged checks (A5.7) |
| `model_metadata` | Provider, model, params (A4.5) |
| `plate_ref` | Optional (A7) |

Scenes are **not** reorderable, omittable, or author-edited after fixing.

---

## 5. Canon tiers and status workflow

Unified status vocabulary across world entities:

| Status | Meaning |
|---|---|
| `draft` | Authoring in progress; not in published package |
| `canon` | Published, queryable, may compile into packets |
| `excluded` | Intentionally withheld from generation (A1.8); may still exist for author reference |

**Publish** (F1.1): draft → immutable `world_package_version` snapshot containing only `canon` entities and non-excluded attributes/relationships. `excluded` material never enters generation context.

**Canonization** (F1.5): author may promote reviewed **apocryphal** material from flagged tellings into `draft` world entities — never automatic. Promoted content starts as `draft` until the next package publish.

This replaces Amanuensis's inline `*_proposed` JSON approval loop with explicit author workflow.

---

## 6. From entities to packets

Entities are the **authoring source of truth**. Packets are **compiled, token-budgeted views** for model context (A4.3).

| Packet | Compiled from |
|---|---|
| Invariants (A1.1) | World invariants + global attributes |
| Location (A1.2) | Location entity + related factions/characters/themes + sensory attributes |
| Entity slice (new) | On-demand: character, object, faction, or event summaries for scene-relevant ids |
| Canon query (A1.6) | Relationships + events + entity facts; queried by HoleFinder, not fully inlined |

**Compilation rules (proposed requirements):**

| ID | Requirement | Priority |
|---|---|---|
| W1.1 | Every published entity has a stable `*_id` and `*_meta` suitable for machine retrieval | P0 |
| W1.2 | Location packets compile from Location + related entities; target ≤ 2k tokens (A1.2) | P0 |
| W1.3 | Optional **entity packets** for characters, factions, objects: ≤ 1k tokens each, included only when referenced by spine or continuity ledger | P1 |
| W1.4 | Canon ledger supports structured query by entity id, relationship type, and location — not bulk prompt injection | P0 |
| W1.5 | `excluded` status propagates: excluded entity or attribute omitted from all packets and queries | P0 |
| W1.6 | Package publish runs compilation, validation lint, and records content hash per packet | P0 |
| W1.7 | `*_meta` auto-generated from description + attributes on source-field save; author-editable; auto-generation always overwrites in v1 (§3.13) | P0 |

---

## 7. PRD integration map

Sections to extend when this draft is merged into the PRD:

| PRD section | Addition |
|---|---|
| **A1.6 Canon ledger** | Expand to reference §3.3–3.9 entity types and §3.8 relationships |
| **A1 (new rows)** | Entity packets (W1.3); theme registry (§3.5); faction entity (§3.6) |
| **A2.1 Skeleton spec** | Align beat terminology with Storybeat (§3.11) |
| **A4.3 Context assembly** | Explicit: invariants + location packet + entity slices + spine + continuity ledger |
| **A5.1 HoleFinder** | Query canon relationships and events, not just flat facts |
| **F1.1 World package editor** | CRUD for §3 entities; attribute editor; relationship graph; publish compiles packets |
| **F1.2 Skeleton authoring** | Storybeat editor with role slots and theme tags |
| **F1.5 Canonization** | Promote apocryphal → draft entity; changelog on commit |

---

## 8. Worldbuilder tooling (F-space) — minimum CRUD

Inherited from Amanuensis *Core functionality*, stripped to internal single-tenant authoring:

| Capability | Scope |
|---|---|
| CRUD | World, Locations, Characters, Objects, Themes, Factions, Events, Attributes, Relationships, Skeletons, Storybeats, Registers |
| Reorder storybeats | **Within skeleton authoring only** — not tellings |
| Publish package | Version bump + packet compilation + lint |
| Changelog | Entity field before/after on every save and publish |
| Image attach | Reference gallery per location (gazetteer card required for visible gazetteer entries) |

**Not in F-space v1:** conversations, fragments, scene reorder for tellings, writer-assist sidebar, background suggesters writing to canon.

**Meta generation (P0):** auto-summarize into `*_meta` on source-field save + explicit Regenerate control (§3.13). Author may edit the field; auto-generation always overwrites in v1. Not exposed to readers.

---

## 9. MVP cut (content model)

**In (Phase 0–1):**

- Locations (4 gazetteer cities) with full location packets
- Characters, Objects, Factions sufficient to support canon checks for those cities
- Relationships and Events as needed for Steamlands sourcebook facts
- Themes (small set referenced by 8 skeletons)
- Attributes on locations and characters (including sensory keys)
- Skeletons with storybeats and role slots
- Continuity ledger schema v1
- Publish pipeline producing versioned world package

**Deferred (post-MVP):**

- Full relationship graph UI
- Entity packet slicing beyond locations (W1.3) unless proofing bench shows need
- Theme registry as separate entity (can start as skeleton-only tags)
- Event entity if flat canon ledger suffices initially
- Image gallery beyond gazetteer card assets

---

## 10. Open questions

1. **Character overlap:** When composition invents a named cast member, when must they link to a canon `character_id` vs. remain wholly apocryphal?
:: I think they should be linked to canon characters only if the author deems them canon
2. **Object taxonomy depth:** Are object *kinds* in canon enough, or do tellings need typed instance schemas in the continuity ledger from day one?
:: I think objects can be types, unless linked to a specific canon entoty by the author. so maybe we have a parent_object_id in the character table to signal that a given instance is one of a class
3. **Meta generation:** Author-triggered only at save, batch at publish, or hand-authored for MVP?
:: generated by default and overwritable. Authors can edit. We'll sort out whether author changes to _meta are respected in future generations later.
4. **Sourcebook import:** Is the first package a migration from existing prose (Steamlands sourcebook) into these entities, or greenfield entity authoring?
:: We'll probably port sourcebook information in for the initial world
5. **Amanuensis code reuse:** Shared database schema and CRUD layer, or schema-inspired greenfield with different table names and no Scribe/Fragment tables?
:: we should start from scratch

---

*This draft is the structured-data complement to PRD §6 (Engine) and §12 (Worldbuilder Tooling). Tellings remain single-edition and apocryphal; the world remains authored, versioned, and canon-explicit.*
