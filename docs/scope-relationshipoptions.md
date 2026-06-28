# Workshop Canvas — Drag-to-Relate: Relationship Options
## Reference Document — v0.1 (Working Draft)

**Scope:** Entity relationship options surfaced by the drag-to-relate interaction on the Workshop canvas  
**Parent doc:** [prd-unicapress.md](./prd-unicapress.md) · [prd-entity-model-extensions.md](./prd-entity-model-extensions.md)  
**Status:** First pass — for review and expansion  
**Date:** June 2026

---

## Interaction Summary

Dragging one entity card onto another triggers a relationship popup. Three cases determine which options are shown:

| Case | Condition | Lead option |
|---|---|---|
| **Cross-type** | Source and target are different entity types, both instances | Type-filtered relationship list |
| **Same-type** | Source and target are the same entity type, both instances | Type-specific relationship list |
| **Class target** | Target entity has `entity_kind: class` | "Make instance of [class]" + relationship list below |

**General rules:**
- Relationships are directional. The popup labels source → target and offers a swap button before confirming.
- Skeletons are never valid drop targets.
- An entity cannot be related to itself.
- Confirming a relationship creates a data record; it does not alter card position on the canvas.
- If a relationship already exists between the two entities, the popup opens with it pre-selected and a Remove option available.

---

## Cross-Type Relationships

### Character → Location
- is based in
- was born in
- operates from
- is exiled from
- is wanted in
- has a safe house in
- grew up in
- is associated with

### Character → Organization
- is a member of
- leads
- founded
- was expelled from
- is affiliated with
- is employed by
- is opposed to
- operates under cover within

### Character → Object
- owns
- carries
- seeks
- created
- destroyed
- is bonded to

### Character → Event
- witnessed
- participated in
- caused
- was affected by
- is investigating
- survived

### Location → Organization
- is headquartered in
- is controlled by
- is contested by
- hosts

### Location → Event
- is the site of
- was changed by

### Organization → Event
- organized
- was dissolved by
- was founded at
- participated in

### Object → Event
- was used in
- was created during
- was destroyed in
- was discovered at

### Object → Location
- is kept in
- was found in
- is sought in

---

## Same-Type Relationships

### Character → Character
- is allied with
- is opposed to
- is the parent of
- is the child of
- is the sibling of
- is romantically involved with
- is a rival of
- mentors
- is mentored by
- is employed by
- has a secret involving

### Location → Location
*(Note: distinct from parent-child hierarchy, which is structural containment. These are narrative/world relationships between peer locations.)*
- borders
- is visible from
- trades with
- is at war with
- is a district of
- is a rival of

### Organization → Organization
- is a subsidiary of
- is a rival of
- is allied with
- is at war with
- was founded by
- operates as a front for

### Object → Object
- is a component of
- was made from
- is a copy of
- is paired with

### Event → Event
- caused
- preceded
- is a consequence of
- is related to

---

## Class Target

When the drop target has `entity_kind: class`, the popup leads with:

> **↳ Make instance of [class name]**  
> *(Sets `entity_kind: instance` and `class_id` on the source entity)*

The standard cross-type or same-type relationship list follows below this option, since a class entity is also a valid relationship target in its own right (e.g. a Character *belongs to* an Organization class).

---

## Open Questions

1. **Relationship notes field** — should every relationship accept an optional short text note (e.g. *"allies since the siege, but trust is strained"*)? Could be in the popup or deferred to a dedicated relationship detail view.

2. **Bidirectional auto-inversion** — does *Character A is the parent of Character B* automatically generate the inverse record (*Character B is the child of Character A*), or does the worldbuilder author both directions explicitly?

3. **Relationship canon status** — do relationships carry their own `draft` / `canon` / `excluded` tier, or do they inherit from their constituent entities?

4. **Canvas edge display** — do confirmed relationships draw a visible edge between cards on the canvas, or do they exist as data only (surfaced in the Archivist panel)? Edges may clutter the canvas at scale.

5. **Relationship list completeness** — this v0.1 list is a first pass. Each type pairing should be reviewed against the Steamlands sourcebook to ensure world-specific relationship types are captured (e.g. Steamlands-specific political or guild structures may warrant dedicated relationship names).

---

*Workshop canvas — drag-to-relate relationship options. v0.1 first pass.*