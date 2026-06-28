# Entity Model Extensions
## Feature Scope Document — v0.2 (Working Draft)

**Scope:** World content model additions — named cross-entity relationships, entity typing (class/instance), and tagging  
**Parent doc:** [prd-unicapress.md](./prd-unicapress.md) · [world-content-model-v1.md](./world-content-model.md)  
**Status:** Draft — for implementation planning  
**Date:** June 2026

---

## 1. Overview

This document specifies three additive extensions to the Unica Press entity model. They are scoped together because they interact, but each is independently implementable and independently valuable.

| Extension | What it adds | Primary beneficiary |
|---|---|---|
| **Named relationships** | Typed, directed links between any two entities in a world, with an optional note | Spatial/social/organizational structure without a rigid tree |
| **Entity typing (class/instance)** | Binary flag distinguishing type definitions from named instances | Character classes, creature types, object archetypes |
| **Tagging** | Free-form, author-defined labels across all entity types | Filtering, grouping, skeleton compatibility |

**Design principle binding all three:** these are *authoring and engine aids*, not reader-facing features. Nothing in this scope changes the Telling API, the reading surface, or the accession record. Changes are confined to the world package, the packet assembly pipeline, and the Workshop canvas.

**Relationship to Phase 1 baseline:** Unica Press already ships a `relationships` table and API (`from_entity_type/id`, `to_entity_type/id`, `relationship_type`). This extension **evolves** that model — it does not introduce a parallel parent-child hierarchy. Containment, membership, employment, and other structural ideas are expressed as **named relationships** (e.g. *located in*, *member of*, *instance of*) rather than as `parent_id` fields on entity rows.

---

## 2. Extension 1 — Named Relationships

### 2.1 Motivation

Entities in a rich world connect in many ways: a character is *based in* a location, an organization *controls* a district, an object *belongs to* a character, a theme *manifests in* a place. A rigid parent-child tree per entity type forces awkward modeling (e.g. “which Location is the parent of this Character?”) and duplicates concepts the relationship model already handles better.

Instead, worldbuilders link **entity A → entity B** with:

- A **relationship name** (required) — the type or label of the link, e.g. `located in`, `member of`, `part of`, `ally of`, `owns`
- An optional **note** — freeform prose clarifying the link, e.g. *“Third floor, east wing; key held by the Registrar”*
- A **canon status** — same tier enum as entities (`proposed` | `canon` | `apocrypha`); **defaults to `canon`** when a link is created (unlike new entities, which default to `proposed`)

Relationships are **directed**: A → B is not necessarily the same as B → A. Authors may add reciprocal pairs explicitly when needed (*employs* / *employed by*).

### 2.2 Mechanism

Each relationship is a row scoped to one world:

```
relationship {
  relationship_id
  world_id
  owner_id
  from_entity_type, from_entity_id    -- entity A
  to_entity_type, to_entity_id        -- entity B
  relationship_name:  string          -- required; author-facing label (normalized slug or display string TBD in UI)
  note:               string | null   -- optional; prose context for this specific link
  status:             entity_status   -- proposed | canon | apocrypha; default canon
  created_at, updated_at
}
```

**Cross-type links are first-class.** A Character may relate to a Location, Organization, Object, Theme, or another Character. Same-type links are allowed (*district part of city*, *ministry part of empire*).

**No `parent_id` on entity tables.** Nesting, containment, and membership are not special columns — they are relationship names chosen by the author.

### 2.3 Relationship names

In v1, relationship names are **author-defined strings** (with light normalization: trim, sensible length limit). There is no fixed global ontology enforced in Phase 1, though the workbench may offer **suggested names** per entity-type pair (e.g. Character → Location: *based in*, *visited*; Character → Organization: *member of*, *leads*).

Future milestones may add a world-level **relationship vocabulary** registry (similar to tags) — out of scope for v1 of this extension.

### 2.4 Authoring rules

- Both endpoints must exist in the same world and pass normal entity ownership / RLS checks.
- Self-loops (A → A) are rejected.
- Duplicate rows with the same `(from, to, relationship_name)` may be rejected or merged — implementation should pick one policy and surface a clear error (recommend: reject duplicates).
- Deleting an entity cascades or blocks according to FK policy on `relationships` (today: define explicitly in migration; prefer **block delete** with “remove relationships first” or **cascade delete** edges involving the entity).
- Each relationship has its **own** canon status, independent of either endpoint entity — a canon link between two proposed entities is valid, and an apocrypha link between canon entities is valid.
- New relationships default to **`canon`** at create time (API and UI).
- `*_meta` auto-generation for an entity may receive **related entity names + relationship names + notes** as optional context (see 2.6); omit relationships with `apocrypha` status unless engine rules say otherwise.

### 2.5 Packet assembly — related-entity context

When the Composition Service, Manifestation Service, or Archivist assembles context for a focal entity, it may include **related entities** linked by relationships, with token budgets by hop distance or by relationship name priority (exact rules deferred to engine milestones):

| Context | Typical inclusion |
|---|---|
| Focal entity | Full packet (≤ 2k tokens per A1.2) |
| Directly related entity (one hop) | Summary: name + `*_meta` snippet + relationship name; note if present (truncated) |
| Two hops | Name + relationship name only (optional / P1) |

`apocrypha` endpoint entities and **`apocrypha` relationships** may be omitted from assembled packets per existing canon rules.

This replaces the former ancestry-chain walk; there is **no** materialized `ancestry_path` or `depth` on entities.

### 2.6 Workshop — relationship authoring & display

**Entity detail panel**

- **Relationships** section lists outgoing and incoming links: *{relationship name} → {other entity}* with optional note and a **canon status badge** (defaults to canon on new links).
- Click a relationship row (or its status badge) to **edit inline**: relationship name, note, and status (`proposed` | `canon` | `apocrypha`). Same control pattern as entity canon editing where practical.
- Add relationship: pick target entity (any type), enter relationship name, optional note; status defaults to canon.
- Delete existing links.

**Canvas (W1.9)**

- Primary authoring: drag source card → drop target card → picker for name, optional note, status (default canon).
- Optional later: render edges between cards filtered by relationship name or status.

**Archivist**

- Dialogue context includes a compact list of relationships for the focal entity (name, type, relationship name, note snippet, status). Omit `apocrypha` relationships from context.

### 2.7 Requirements

| ID | Requirement | Priority |
|---|---|---|
| R1.1 | Relationships link `from` entity A to `to` entity B within one world; cross-type links allowed. | P0 |
| R1.2 | Each relationship has a required **relationship name** (string), an optional **note** (string), and a **status** (`proposed` \| `canon` \| `apocrypha`; default `canon`). | P0 |
| R1.3 | API + workbench CRUD for relationships on any entity (list by entity, create, update name/note/status, delete). | P0 |
| R1.4 | Self-loops and cross-world links are rejected at write time. | P0 |
| R1.5 | Packet assembly / Archivist context may include directly related entities with relationship name, note, and status (Phase 1: flat list; omit `apocrypha` relationships; token budgets in engine milestones). | P1 |
| R1.6 | Entity detail panel shows related entities with name, type, relationship name, note, and clickable status badge for inline edit. | P0 |
| R1.7 | Suggested relationship names per source/target entity-type pair in the add-relationship UI (not a closed enum). | P1 |
| R1.8 | Canvas edge rendering or drag-over authoring deferred to W1.9; API must be sufficient without canvas UI. | P1 |
| R1.9 | `*_meta` auto-generation may include related-entity summaries as context (needs W1.4). | P1 |

---

## 3. Extension 2 — Entity Typing (Class / Instance)

### 3.1 Motivation

Some entities in a world are *types* — they define a category with shared attributes, lore, and behavioral rules. Others are *instances* — named, specific individuals that belong to a type. This distinction matters for both authoring and generation:

- **Authoring:** a worldbuilder authors "City Watch Officer" once and instantiates it into many named characters, rather than duplicating lore.
- **Generation:** when the Composition Service needs a minor character in a given role, it can instantiate from a class rather than inventing from nothing, producing canon-plausible names and behaviors.
- **Continuity:** creature and NPC types (Marsh Crawler, Archive Registrar) can appear across many tellings with consistent behavior without requiring a canon-named individual each time.

The Stormtrooper/TK-421 distinction: *Stormtrooper* is a class. *TK-421* is a named instance of that class. The class carries the type lore; the instance inherits it and adds specifics.

### 3.2 Mechanism

Every entity gains a binary `entity_kind` field: `class` or `instance`. Default is `instance` — the common case. A `class` entity defines a type; an `instance` entity may optionally reference a `class_id` pointing to a class entity of the same type.

This is **not** the same as a named relationship, though the ideas compose:

- **Class-instance** = *membership in a type* (TK-421 is a Stormtrooper) — stored on the entity via `class_id`
- **Named relationship** = *any link the author names* (TK-421 *stationed at* Death Star Garrison) — stored in `relationships`

An instance may also use a relationship to a class entity when authors prefer explicit edges over `class_id`; v1 recommends **`class_id` for same-type class membership** and **relationships for everything else**.

**Applies to:** Character, Object, Organization, Location (sparingly — e.g. "Marsh Settlement" as a location class), Theme  
**Most common use cases:** Character classes and creature types; Object archetypes

### 3.3 Data model changes

```
entity {
  ...existing fields...
  entity_kind:  enum('instance', 'class')    -- default: 'instance'
  class_id:     entity_id | null             -- for instances only; must reference a 'class' entity of same type
}
```

### 3.4 Class entity behavior

A `class` entity:

- Has all the same fields as an instance entity (`description`, attributes, `*_meta`, canon tier, tags).
- Its `description` and `*_meta` describe the *type*, not an individual: behavioral norms, visual conventions, cultural role, typical attributes.
- May be `proposed`, `canon`, or `apocrypha`. Non-canon classes are available to the worldbuilder but not compiled into packets.
- Is listed distinctly in the Workshop canvas and nav (see 3.6).
- Cannot itself have a `class_id` — classes do not subclass other classes in v1. (Subclassing is a future consideration.)

### 3.5 Packet assembly — class inheritance

When the engine assembles context for an instance entity that has a `class_id`:

1. The instance's own packet is assembled as normal.
2. The class's `*_meta` is prepended as a *type descriptor* at ≤ 400 tokens.
3. The instance's own attributes override class defaults where they conflict.

When the Composition Service needs to fill a character role slot and no specific named character is designated:

- It may instantiate from a compatible canon class, generating a name via the naming grammar and inheriting class lore.
- The composed (instantiated) character lives in the telling's accession record, not the world package — it is apocryphal until canonized by the worldbuilder (per F1.5).

### 3.6 Workshop canvas — class display

Class entities are visually distinct on the canvas:

- A subtle **double-border** treatment (outer ring, slight gap, inner card border) signals "this is a type, not an individual."
- The type chip in the card header shows a small `◇ class` badge alongside the entity type label.
- Instance entities that reference a class show the class name as a small link below their name: *City Watch Officer ↗*
- A **"Show instances"** action on a class card filters the canvas to show only instances of that class.

The nav count for each entity type shows class and instance counts separately on hover: *Characters: 4 instances · 3 classes*.

### 3.7 Requirements

| ID | Requirement | Priority |
|---|---|---|
| T1.1 | Every entity has a binary `entity_kind` field: `class` or `instance`. Default: `instance`. | P0 |
| T1.2 | An instance entity may optionally reference a `class_id` pointing to a class entity of the same type. | P0 |
| T1.3 | Class entities have all standard entity fields; their `*_meta` describes the type, not an individual. | P0 |
| T1.4 | Packet assembly for an instance with a `class_id` prepends the class `*_meta` as a type descriptor at ≤ 400 tokens. | P0 |
| T1.5 | The Composition Service may instantiate a named character from a canon class when a role slot is unfilled; the composed character is apocryphal until canonized. | P1 |
| T1.6 | Class entities are visually distinct on the canvas (double-border treatment + `◇ class` badge). | P0 |
| T1.7 | Instance entities display their class name as a navigable link in the card. | P0 |
| T1.8 | A "Show instances" action on a class card filters the canvas to that class's instances. | P1 |
| T1.9 | Nav counts show class and instance totals separately on hover. | P1 |
| T1.10 | Classes do not subclass other classes in v1. | P0 (constraint) |

---

## 4. Extension 3 — Tagging

### 4.1 Motivation

Not all grouping relationships imply a typed edge or class membership. Some are cross-cutting descriptors — labels that help the worldbuilder organize, filter, and search, and that help the engine apply skeleton compatibility rules. Tags are the mechanism for this orthogonal grouping.

**The test for tag vs. relationship vs. class:**

| Question | If yes → |
|---|---|
| Does the grouping have *its own lore* that members should inherit in packets? | **Class** (`class_id`) or a **related entity** with its own description/`_meta` |
| Is it a specific link between two entities the author wants to name? | **Named relationship** |
| Is it purely a label — shared quality, no lore inheritance? | **Tag** |

Examples:

- `monster` — a tag on Character and Character Class entities. "Monster" itself has no lore the engine needs to inherit; it is a filter handle.
- `political-figure` — a tag on Characters. Useful for skeleton compatibility ("this skeleton requires a political-figure in the antagonist slot").
- `unreliable` — a tag on Characters. Authoring signal and potential battery rule.
- `penny-dreadful-compatible` — a tag on Locations, Characters, Objects. Used by skeleton retrieval.
- `within-wheels` — a tag on apocrypha-tier entities. Useful for auditing compartmentalization.

Structural links like *Greyworks located in Slatewater* are **relationships**, not tags: *Slatewater* is a real entity with lore; the link is named `located in` with an optional note.

### 4.2 Mechanism

Every entity has a `tags` field: an ordered array of strings. Tags are:

- **Author-defined** — the worldbuilder creates them freely; no fixed taxonomy in v1.
- **World-scoped** — a tag used in one world is not visible in another.
- **Case-insensitive, hyphen-normalized** — `Political Figure`, `political-figure`, and `politicalfigure` resolve to the same tag.
- **Renameable** — renaming a tag renames it across all entities that use it.

A tag registry is maintained per world, listing all tags in use with their entity counts. This enables the worldbuilder to audit, merge, and clean up tags over time.

### 4.3 Tag uses

**Authoring / canvas filtering**  
Tags appear as a filter axis on the canvas alongside type and canon status. The worldbuilder can filter to "show all entities tagged `marsh`" or "show all `apocrypha` entities tagged `within-wheels`."

**Skeleton compatibility**  
Skeleton role slots may specify tag requirements: *"antagonist slot: requires tag `political-figure` or `institutional`."* The Composition Service filters candidate characters by tag during spine composition. This gives the worldbuilder fine-grained control over which entities the engine can cast in which roles, without hardcoding names.

**Battery rules**  
The editorial battery may reference tags in content rules: *"scenes set in locations tagged `sacred` may not use register `penny-dreadful`."* These rules live in the world package invariants, not in code.

**`*_meta` generation**  
Tags are passed to the `*_meta` auto-generation prompt as descriptors, improving the quality of generated summaries for entities whose description is sparse.

### 4.4 Canonical system tags (reserved prefix `_`)

A small set of system-defined tags with reserved behavior. Worldbuilders cannot delete or rename these, but can apply them freely:

| Tag | Behavior |
|---|---|
| `_within-wheels` | Advisory marker for Within Wheels material; does not replace `apocrypha` canon tier but supports auditing |
| `_protagonist-compatible` | Signals this entity is suitable for protagonist-class roles in skeleton casting |
| `_minor` | Signals this character/object is minor; engine may use without full packet assembly |
| `_recurring` | Signals the worldbuilder intends this entity to recur across tellings; anti-repetition logic weights it accordingly |

### 4.5 Workshop canvas — tag display

- Tags appear as small pills in the card footer, below the canon badge, truncated to 2 visible with a `+N` overflow indicator.
- The toolbar filter row gains a **Tags** dropdown for multi-select tag filtering, appearing after the status filters.
- The tag registry is accessible from the World Package settings view, showing all tags, their counts, and rename/merge actions.

### 4.6 Requirements

| ID | Requirement | Priority |
|---|---|---|
| G1.1 | Every entity has a `tags` field: an ordered array of strings, author-defined, world-scoped. | P0 |
| G1.2 | Tags are case-insensitive and hyphen-normalized at write time. | P0 |
| G1.3 | A tag registry per world tracks all tags in use with entity counts. | P0 |
| G1.4 | Tags are renameable world-wide from the tag registry; rename propagates to all entities. | P0 |
| G1.5 | The canvas toolbar supports multi-select tag filtering. | P0 |
| G1.6 | Skeleton role slots may specify tag requirements; the Composition Service filters candidates by tag. | P1 |
| G1.7 | Tags are passed to `*_meta` auto-generation as descriptors. | P1 |
| G1.8 | System tags (reserved `_` prefix) are predefined, undeletable, and renameable only by staff. | P0 |
| G1.9 | Tag pills appear in the entity card on the canvas, truncated with `+N` overflow. | P0 |
| G1.10 | The tag registry supports merge (consolidate two tags into one) and bulk-remove. | P1 |
| G1.11 | Battery rules may reference tags in content guardrails (defined in world package invariants). | P2 |

---

## 5. Interaction Between Extensions

The three extensions are additive and compose cleanly. A single entity may use all three:

```
Entity: "TK-421"
  type:         Character
  entity_kind:  instance
  class_id:     → "Stormtrooper" (Character class)
  tags:         ["imperial", "military", "minor", "_recurring"]

Relationships (examples):
  TK-421  —[stationed at, canon]→  "Death Star Garrison" (Organization)
            note: "Sector 7-G patrol rotation"
  TK-421  —[reports to, canon]→    "Captain Veers" (Character)
  Slatewater —[contains, proposed]→ "The Greyworks" (Location)
            note: "Industrial quarter; Within Wheels adjacent"
```

The packet for TK-421 assembled by the engine would include:

1. TK-421's own `*_meta` and attributes (full budget)
2. Stormtrooper class `*_meta` prepended as type descriptor (≤ 400 tokens)
3. Optional related-entity snippets via relationships (e.g. Garrison org summary under *stationed at*)

The composition service, casting a role slot requiring `["imperial", "military"]`, would find TK-421 as a candidate. If the slot only needs a generic Stormtrooper and no named instance is required, it may instantiate a new character from the Stormtrooper class instead.

---

## 6. Data Migration

The v1 world content model (greenfield, seeded from the Steamlands sourcebook) needs no destructive migration — these fields are additive.

**Entities** — all existing rows default to:

```
entity_kind: 'instance'
class_id:    null
tags:        []
```

**Relationships** — existing Phase 1 rows use `relationship_type` as the relationship name. Migration `011` (or equivalent) should:

- Rename or alias `relationship_type` → `relationship_name` in API/models **or** keep the column name and document it as the relationship name in product copy (implementation choice).
- Add nullable `note` column.
- Add `status public.entity_status not null default 'canon'`; backfill existing rows to `canon`.
- No `parent_id`, `depth`, `children_count`, or `ancestry_path` on entity tables.

No existing packet assembly logic is broken; the new fields add optional enrichment passes on top of the existing pipeline.

---

## 7. Out of Scope (v1 of this extension)

- **Parent-child hierarchy columns** (`parent_id`, materialized ancestry) — replaced by named relationships.
- **Class subclassing** — classes do not inherit from other classes. A flat one-level type system is sufficient for v1.
- **Global relationship ontology** — relationship names are author-defined strings in v1; a curated vocabulary per world is a follow-up.
- **Tag taxonomies / ontologies** — tags are flat strings in v1. A hierarchical tag system (genre → subgenre) is a future consideration.
- **Shared tag libraries across worlds** — tags are world-scoped in v1.
- **Reader-visible tags or relationship graphs** — authoring and engine infrastructure only.
- **Automated relationship or tag suggestion** — the Archivist may suggest links conversationally, but no background inference in v1.

---

## 8. Open Questions

1. **Column naming** — keep DB column `relationship_type` and expose as `relationship_name` in API, or rename in migration?

2. **Duplicate relationships** — allow multiple links with the same name between the same pair (e.g. two *visited* notes at different times) or enforce uniqueness on `(from, to, relationship_name)`?

3. **Object instances in the continuity ledger** — the PRD already notes per-telling object instances live in the continuity ledger. Does the class/instance distinction in the world package interact with the continuity ledger's instance tracking, or are these two separate instance concepts?

4. **Character class casting control** — when the Composition Service instantiates from a class, should the worldbuilder have per-class settings for *how freely* the engine may cast from it?

5. **Tag enforcement vs. suggestion** — skeleton role slot tags: hard filters or soft preferences?

6. **Relationship direction in UI** — when listing links on an entity, show outgoing only, incoming only, or both with clear direction labels?

7. **Package exchange interaction** — when an entity package is imported from another world, do imported class entities become available for local instantiation?

---

*Entity model extensions — named relationships, typing, tagging. Additive to world-content-model-v1. Supersedes v0.1 parent-child hierarchy design.*
