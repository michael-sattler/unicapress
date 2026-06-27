# UNICA PRESS
## Product Requirements Document — v0.1 (Working Draft)

**Product:** Unica Press — an engine that prepares unique, single-edition stories ("tellings") set in an authored world, delivered through an in-world reading experience.
**Flagship world:** The Grand Archive of the Steamlands
**Author/Owner:** J. M. Sattler
**Status:** Concept → Build planning
**Date:** June 2026

---

## 1. Vision

Unica Press is a publishing machine for worlds. A worldbuilder authors a universe — its canon, its story shapes, its voice — and the engine prepares **tellings**: stories generated once, for one reader, fixed permanently upon reading, and never produced again. The reader does not control the plot; the world tells the story. The product's unit of value is not "AI-generated content" but the **unicum** — an edition of one, with provenance, owned by the person it was prepared for.

The first world is the Steamlands, presented as **The Grand Archive**: readers present a request slip to a Victorian difference engine, watch it retrieve their manuscript from the stacks, and read a story that exists for no one else.

**Two-layer thesis (governs this document's structure):**

- **The Engine (Feature Space A)** is the product's real value: a narrative generation system that produces coherent, canon-true, stylistically fingerprinted stories without per-story human editing. It is world-agnostic by design.
- **The Experience (Feature Spaces B–E)** is the Steamlands-specific delivery vehicle: the Retrieval Engine interface, the manuscript reading surface, and the patron systems. It is replaceable skin over the engine — albeit skin that carries most of the brand.

A build plan should treat these as separately schedulable workstreams with a thin contract between them (Section 8).

---

## 2. Goals and Non-Goals

### Goals (v1)
1. A first-time visitor reaches the first scene of a telling prepared for them within ~4 minutes of arrival, with no account required.
2. Tellings are coherent at the whole-story level (composed spine, real ending), canon-consistent with the Steamlands sourcebook, and stylistically consistent with the author's fingerprint.
3. Every telling is generated exactly once, fixed scene-by-scene as read, and永 never regenerable. Fixed text is durable and re-readable forever.
4. The reading experience feels like receiving and owning an artifact, not consuming a feed.
5. Fixed tellings are shareable (lending), and lending is the primary acquisition loop.
6. The engine's world-knowledge is fully externalized into a **World Package** so a second world could, in principle, be loaded without engine changes. (Loading a second world is *not* a v1 goal; the externalization is.)

### Non-Goals (v1)
- **No user plot control.** No choices, branches, prompts, or "what happens next" input. The request slip sets position (where, register, addressee), never plot.
- **No regeneration.** No re-roll, no "try again" on any fixed content, ever. (Buffer-stage regeneration upstream of reading is an internal mechanism, not a user feature.)
- **No chatbot surface.** Readers never converse with a model.
- **No multi-tenant authoring.** Worldbuilder tooling is internal/single-tenant in v1.
- **No marketplace, no canon contributions from readers, no user-generated worlds.**
- **No native apps.** Responsive web only.
- **No audio narration in v1** (architecture should not preclude it; see Roadmap).

---

## 3. Product Principles (binding design rules)

1. **The world tells the story.** Readers supply position, never plot. All narrative authority belongs to the world package and skeleton library.
2. **Fixed once read.** Reading is publication. A scene the reader has seen is immutable. Scarcity in this product comes from irreversibility, not artificial supply limits.
3. **Honest machinery.** Every diegetic display is truthful telemetry in costume: the dial shows real pipeline stages; the stipplegraph frame is used because the images really are machine-printed from data; the colophon discloses the method plainly. No concealment of AI involvement anywhere.
4. **Ceremony at thresholds only.** Full theater for first-time and boundary moments (request, retrieval, fixing, completion). Compressed "standing order" treatment for returning patrons. Nothing ceremonial inside the act of reading.
5. **No brass inside the book.** The skeuomorphic interface ends at the cage door. The reading surface is pure typography on paper.
6. **Provenance over rarity.** Value accrues from the reader's own history (stamps, accession records, shelf) — never from gacha mechanics, drop rates, or limited pulls.
7. **The prose is the product.** All interface theater buys exactly one scene of goodwill. Quality budget concentrates on skeletons, packets, and the editorial battery.
8. **Tellings are apocryphal.** No generated content ever writes back to canon. Only the author canonizes.

---

## 4. Users

**P1 — The Reader.** Consumer of tellings. Arrives via lent copy or direct link. Wants a transporting, complete short reading experience (~15–40 min) that feels personal. No assumed familiarity with the Steamlands, AI products, or steampunk as a genre.

**P2 — The Author/Worldbuilder (internal, v1 = the owner).** Authors the world package, skeleton library, and style fingerprint; reviews quality telemetry; canonizes; manages the gazetteer. Uses internal tooling (Feature Space F), not the consumer surface.

**P3 (future) — Third-party worldbuilders.** Out of scope for v1; the World Package contract is designed with them in mind.

---

## 5. System Overview

Three planes with one-way data flow:

```
WORLD PLANE                GENERATION PLANE              EXPERIENCE PLANE
(authoring, canon)   →     (the Unica engine)      →     (Archive front-end)
World Package              Composition service           Retrieval Engine UI
Skeleton library           Manifestation service         Manuscript reader
Style fingerprint          Editorial battery             Patron & carrel
Canonization tools         Fixing & accession            Lending
                           Plate pipeline
                           Telemetry (SSE)
```

The Experience Plane consumes the Generation Plane only through the Telling API (Section 8). The Generation Plane consumes the World Plane only through versioned, read-only world packages. Nothing flows right-to-left except telemetry and quality flags.

---

## 6. FEATURE SPACE A — The Narrative Generation Engine *(core value; world-agnostic)*

### A1. World Package
The complete, versioned, machine-consumable definition of a world. Replaces "prompt engineering" with authored, structured context assembled per scene.

| ID | Requirement | Priority |
|---|---|---|
| A1.1 | **Invariants packet:** small, always-in-context world rules — technology constraints (e.g., no electricity), calendar, language conventions, tone guardrails. Target ≤ 1.5k tokens. | P0 |
| A1.2 | **Location packets:** one per gazetteer entry (city + immediate region): geography, character, factions, sensory palette, local idiom. Sourced from the Archivist's Sourcebook. Target ≤ 2k tokens each. | P0 |
| A1.3 | **Naming grammar:** generative rules for the world's onomastics (e.g., "Victorian classic with a twisted vowel/syllable"), plus reserved-name and collision lists, so the engine invents canon-plausible names rather than drawing from a finite list. | P0 |
| A1.4 | **Register definitions:** the request-slip taxonomy (deposition, penny dreadful, mariner's account, recovered correspondence, account of the late troubles), each defining narrative voice, framing device, structural tendencies, and compatible skeleton tags. | P0 |
| A1.5 | **Style fingerprint:** author writing samples + extracted stylistic rules (rhythm, diction, dialogue conventions, banned constructions) used for both generation conditioning and the style-scoring pass. | P0 |
| A1.6 | **Canon ledger:** the authoritative fact base (entities, events, relationships) queryable by the editorial battery. Tellings cannot write to it. | P0 |
| A1.7 | Packages are **versioned**; every telling records the package version it was prepared under. | P0 |
| A1.8 | **Compartmentalization:** plot-sensitive canon (e.g., Within Wheels material, the Society) is flagged excluded-from-generation at the package level. | P0 |

### A2. Skeleton Library (story shapes)
Authored, reusable narrative spines. The engine never invents story *shape*; it instantiates authored shapes.

| ID | Requirement | Priority |
|---|---|---|
| A2.1 | **Skeleton spec format:** ordered beats with intent descriptions; character role slots (with constraint notes, not names); reversal and ending definitions; thematic guardrails and invariants (e.g., "the span is sound"-class rules); scene-count range; register and location compatibility tags. | P0 |
| A2.2 | v1 library: **minimum 8 skeletons** spanning at least 3 registers, such that any valid request matches ≥ 2 skeletons (so retrieval has genuine variance). | P0 |
| A2.3 | Skeletons carry **target length** (scenes × words/scene) and pacing notes per beat. | P0 |
| A2.4 | Authoring format is human-writable (structured Markdown/YAML) — skeletons are a *writing* artifact, not a programming artifact. | P0 |
| A2.5 | Per-skeleton **quality telemetry**: completion rate, flag rate, reader drop-off by beat — feeding library curation. | P1 |

### A3. Composition Service (the spine is fixed before the first word renders)
| ID | Requirement | Priority |
|---|---|---|
| A3.1 | On request: derive a **seed** from (request params + patron salt + nonce); select skeleton from compatible set; compose the full telling spine — instantiated characters (named via naming grammar), concrete beats, the ending — in a single composition pass before any scene is manifested. | P0 |
| A3.2 | Composition completes within the retrieval theater window (target ≤ 25s p90). | P0 |
| A3.3 | Composition emits a **title** and **accession number**, and derives deterministic **stack coordinates** (stack/gallery/case/shelf) from the request hash, such that similar requests map to nearby coordinates. | P0 |
| A3.4 | **Anti-repetition:** composition consults the patron's history to bias against recently used skeletons, character archetypes, and signature images. | P1 |
| A3.5 | The composed spine is persisted and immutable for the life of the telling. | P0 |

### A4. Manifestation Service (scene prose, buffered ahead)
| ID | Requirement | Priority |
|---|---|---|
| A4.1 | Scenes are generated **against the fixed spine** — manifestation may vary prose, never plot. Target scene length 800–1,400 words. | P0 |
| A4.2 | **Buffer of 2:** scene N+1 and N+2 are generated/validated while the reader is on scene N. First scene available at theater's end; subsequent scenes page-turn instant (p95 < 1s perceived). | P0 |
| A4.3 | Context assembly per scene: invariants + location packet + spine + **running state** (rolling scene summaries + continuity ledger: who knows what, injuries, time of day, objects in hand). | P0 |
| A4.4 | Running state is updated by an extraction pass after each scene is accepted into the buffer. | P0 |
| A4.5 | Model-agnostic: generation calls go through a provider abstraction; model + parameters are recorded per scene. | P1 |

### A5. Editorial Battery (the automated edit pass; runs only upstream of reading)
| ID | Requirement | Priority |
|---|---|---|
| A5.1 | **Canon check (HoleFinder):** scene claims validated against canon ledger + running state; contradictions → regenerate. | P0 |
| A5.2 | **Continuity check:** scene validated against the spine beat it manifests and the continuity ledger. | P0 |
| A5.3 | **Naming check:** all novel proper nouns validated against naming grammar and collision lists. | P0 |
| A5.4 | **Style score:** scene scored against fingerprint (diction, rhythm, banned constructions); below-threshold → regenerate with critique. | P0 |
| A5.5 | **Content safety pass** per scene; registers carry content ratings; the Archive's catalog is all-ages-appropriate by default. | P0 |
| A5.6 | Max regeneration attempts per scene (default 3); on exhaustion, degrade gracefully (fall back to best-scoring candidate + flag for author review). Readers never see failures or retries. | P0 |
| A5.7 | All battery verdicts logged per scene for author telemetry. | P1 |

### A6. Fixing, Accession & Immutability
| ID | Requirement | Priority |
|---|---|---|
| A6.1 | A scene becomes **fixed** when the reader completes it (scroll/page completion signal). Fixed text is written to durable storage with timestamp and accession record; it can never be altered or regenerated by any system path. | P0 |
| A6.2 | The accession record binds: telling ID, patron (or anonymous token), request card data, package version, spine, per-scene model metadata, stack coordinates, dates. This is the provenance object. | P0 |
| A6.3 | Buffered-but-unread scenes of an abandoned telling remain in "uncut pages" state indefinitely; resuming fixes them on read as normal. | P0 |
| A6.4 | **Return to the stacks:** a reader may abandon a telling; it remains on their shelf as an unfinished copy. No replacement-with-re-roll path exists. | P0 |

### A7. Plate Pipeline (stipplegraph images)
| ID | Requirement | Priority |
|---|---|---|
| A7.1 | 1–3 plates per telling, placed at spine-designated beats. Plate briefs are composed with the spine (subject, composition, caption). | P1 |
| A7.2 | **Deterministic stipple post-process:** raw generated image → algorithmic stippling (fixed dot raster, single ink, cream ground) in code. House style is enforced by the pipeline, never requested from the model. | P1 |
| A7.3 | Plates pass a safety/coherence review pass; a telling degrades gracefully to fewer/no plates rather than shipping a bad plate. | P1 |
| A7.4 | Plate reveal metadata supports the raster-sweep render in the reader. | P2 |

### A8. Telemetry Contract (honest instrumentation)
| ID | Requirement | Priority |
|---|---|---|
| A8.1 | The engine emits **SSE progress events** mapped to real stages: `CATALOG` (composition), `STACK` (scene gen, with coordinates), `COLLATION` (battery), `CARRIER` (typeset/plates), `DELIVERED` (buffer ready). The UI may costume these; it may not invent them. | P0 |
| A8.2 | Quality events (flags, degradations, drop-offs) flow to the author dashboard (F-space F). | P1 |

---

## 7. FEATURE SPACE B — The Retrieval Engine Experience *(the interface; Steamlands skin)*

### B0. Paradigm
2D orthographic **instrument faceplate**, not a 3D scene. Built in DOM/CSS/SVG; every control is a real, accessible HTML control under the skin. Materials (walnut, brass, enamel, bone) conveyed through restrained gradients, texture, and typography — Teenage-Engineering-flat, not pre-rendered-Myst. Fully responsive: panel bays reflow/stack on mobile like rack modules.

### B1. The Request (composition of the slip)
| ID | Requirement | Priority |
|---|---|---|
| B1.1 | **Gazetteer bay:** card rail presenting location cards (trading-card format, fine "engraved" stipple art — curated static assets). v1 launch set: 4 cities; additional cities arrive as "new print runs" (D3). | P0 |
| B1.2 | **Register bank:** 5 cash-register pushbuttons with engraved labels; mechanically mutually exclusive (pressing one releases the others — physical radio buttons). | P0 |
| B1.3 | **Prepared-for line:** optional typed name (typewriter rendering, Special Elite). | P0 |
| B1.4 | **Slip composition:** on commit, the panel prints the request card live — raster-sweep of the selected city vignette, punch of the register hole, typed name — then feeds it into the Engine's mouth with an audible punch-bite. The composed card is persisted (becomes the manuscript's page zero, C2.2). | P0 |
| B1.5 | First-visit constraint: ≤ 3 decisions before commit. No accounts, no settings, no tutorials. | P0 |

### B2. The Retrieval (the theater; driven by A8 telemetry)
| ID | Requirement | Priority |
|---|---|---|
| B2.1 | **Split-flap status board** (CSS 3D flap animation + flutter audio) rendering stage text from SSE events. | P0 |
| B2.2 | **Stack indicator:** semicircular elevator-style dial (Roman-numeraled stacks, sprung brass needle) + fine-position flap line (gallery/case/shelf) showing the engine-supplied coordinates. | P0 |
| B2.3 | **Arrival choreography:** needle settles → flaps `CARRIER INBOUND` → 1.2s silence → low THUNK → single counter-bell ding → cage door swings ajar. Door-ajar is the only call to action. | P0 |
| B2.4 | **Delivery cage + carrel shelf:** capsule (banded with accession number) lands in the cage; pigeonhole rack behind shows the patron's recent manuscripts spine-out. The cage corner is the persistent inventory surface. | P0 |
| B2.5 | Audio gated behind first user gesture; diegetic **SILENCE BELL** toggle on the fascia; full experience degrades gracefully with sound off. | P0 |
| B2.6 | **Standing-order compression:** returning patrons get an abbreviated theater (manuscript "already waiting at your carrel") with a path to replay the full sequence. Ceremony at thresholds only. | P1 |
| B2.7 | Total first-visit time, arrival → scene 1 on screen: target ≤ 4 minutes; retrieval theater 30–45s, padded honestly against real pipeline time. | P0 |
| B2.8 | Accessibility: full keyboard operability; reduced-motion mode replaces theater with a quiet typographic progress treatment; all status text screen-reader-readable. | P0 |

### B3. Fascia System (shared visual language)
| ID | Requirement | Priority |
|---|---|---|
| B3.1 | Type system: IM Fell English SC (placards/engraving), Playfair Display (titles/plates), EB Garamond (book body), Special Elite (typed), Oswald or Saira Condensed (flap cells). | P0 |
| B3.2 | Component library: flap cell, brass pushbutton, bone label, dial, punch row, cage — built once, reused everywhere. | P0 |
| B3.3 | Hard rule: no glows, no backlit surfaces, no cool light sources, no screens-within-the-screen. Light is warm and falls from above. | P0 |

---

## 8. Contract Between Engine and Experience (Telling API)

The Experience Plane integrates with the Generation Plane exclusively through:

1. `POST /tellings` — request params (city, register, addressee, patron token) → telling ID + SSE channel.
2. `SSE /tellings/{id}/progress` — A8.1 stage events.
3. `GET /tellings/{id}/scenes/{n}` — manifested scene (text + plate refs) when buffered.
4. `POST /tellings/{id}/scenes/{n}/fix` — completion signal; returns fixed/accession confirmation.
5. `GET /patrons/{id}/shelf` — accession records for carrel surfaces.

This contract is the seam along which a second world, a different front-end, or a future API product would attach. Keep it boring and stable.

---

## 9. FEATURE SPACE C — The Manuscript (reading experience)

| ID | Requirement | Priority |
|---|---|---|
| C1.1 | Cage-to-book transition: capsule opens → folio expands into the reading surface. **No brass inside the book** — pure typography: cream field, EB Garamond, 60–70ch measure, generous leading; chrome limited to a hairline rule + accession running header. | P0 |
| C1.2 | Scene-at-a-time presentation; next scene loads page-turn instant from buffer. | P0 |
| C2.1 | **Fixing ritual (per scene):** at scene end, the page "sets" (letterpress deepen) + inked date-stamp (scene no., date, Archive device) with the two-part stamp sound. ≤ 2s, non-blocking after first occurrence. | P0 |
| C2.2 | **Page zero:** the composed request card is tipped into the manuscript's front matter as provenance. | P1 |
| C2.3 | **Borrower's card:** back-of-book card accumulating the reader's date stamps — their reading history of this copy. | P2 |
| C2.4 | **Completion rite:** on final fix, blind-emboss of the Archive seal into the colophon (raking-light render); colophon carries the honesty text: *"A Unica edition. Prepared once by the Engine for one reader, and never again,"* plus method disclosure and package version. | P0 |
| C3.1 | Plates render via raster-sweep (needle-print line-by-line, 2–3s) with caption in Playfair. | P1 |
| C3.2 | Re-reading: fixed tellings open instantly from the shelf in reading view, theater-free. | P0 |

---

## 10. FEATURE SPACE D — Patron & Collection

| ID | Requirement | Priority |
|---|---|---|
| D1.1 | **Anonymous-first:** full request → read flow with no account (anonymous token). | P0 |
| D1.2 | **Patron's card prompt** after scene 2 fixes, framed as preservation: "Shall the Archive keep your copy?" Email-only signup; anonymous shelf merges into the patron record. | P0 |
| D2.1 | **Carrel shelf:** the patron's manuscripts (finished and unfinished), re-openable forever. | P0 |
| D3.1 | **Gazetteer collection:** cities unlock through reading (a telling that touches another city earns its card — "You may wish to go where the story went"). No purchases, no rarity tiers. | P1 |
| D3.2 | Cards accumulate **commission stamps** per telling requested from them — provenance on the card itself. | P2 |
| D4.1 | The Archivist remembers: per-patron history drives anti-repetition (A3.4) and returning-patron warmth (B2.6). | P1 |

---

## 11. FEATURE SPACE E — Lending & Acquisition

| ID | Requirement | Priority |
|---|---|---|
| E1.1 | **Lending slip:** any fixed telling generates a share link; the lent view is the full manuscript, read-only, marked "on loan from [patron]'s copy." | P0 |
| E1.2 | Lent-copy footer CTA: *"This copy was prepared for one reader. The Archive will prepare one for you"* → request flow with the lender's city pre-suggested. | P0 |
| E1.3 | Lending requires fixed content only (no lending of uncut pages). | P0 |
| E2.1 | Public "About the Engine" colophon page (out-of-world): what this is, how it works, the disclosure stance. Linked from the front door and every manuscript colophon. | P0 |
| E3.1 | *(Future)* Print-on-demand: physical edition of a fixed telling — real emboss, tipped-in request card. Architecture (A6.2 provenance) must not preclude it. | P2 |

---

## 12. FEATURE SPACE F — Worldbuilder Tooling (internal, v1)

Minimum viable authoring surface for P2 (the owner). Inherits the Amanuensis entity model (narratives → scenes → fragments; characters/locations/objects; canon vs. unofficial).

| ID | Requirement | Priority |
|---|---|---|
| F1.1 | World package editor: structured editing + versioning of packets (A1.1–A1.6); publish = new immutable package version. | P0 |
| F1.2 | Skeleton authoring + validation (lint against spec; compatibility-tag coverage report). | P0 |
| F1.3 | **Proofing bench:** generate full tellings internally against any package/skeleton version without fixing or accessioning; side-by-side with battery verdicts. | P0 |
| F1.4 | Quality dashboard: A5.7/A2.5 telemetry; flagged tellings queue for author review. | P1 |
| F1.5 | Canonization workflow: author may promote reviewed material into the canon ledger; nothing automatic. | P1 |

---

## 13. MVP Cut (proposed)

**In:** Feature Space A complete at P0 (text only — plates may slip); B1, B2 at P0; C1, C2.1, C2.4, C3.2; D1, D2.1; E1, E2.1; F1.1–F1.3. One world, **4 cities** (Slatewater, Skywade, Caralis, Thousand Rocks — two familiar from drafting, two contrasting climates), **3 registers**, **8 skeletons**, telling length 5–8 scenes.

**Out (explicitly):** plates (P1 fast-follow), gazetteer unlocks, borrower's card, standing-order compression, print-on-demand, audio narration, any second world, any public authoring.

**MVP success test:** ≥ 50% of cold first-time visitors who punch a slip finish their telling; ≥ 25% of finishers lend or start a second telling. If the fixing ritual doesn't move people, revisit before building more theater.

---

## 14. Metrics

- **Activation:** arrival → slip punched; slip → scene 1 read (and time-to-first-scene).
- **Quality proxy:** scene-to-scene continuation rate; completion rate per skeleton (A2.5); battery flag/degradation rates.
- **Ownership:** patron conversion at the D1.2 prompt; shelf re-open rate; re-read rate.
- **Loop:** lend rate per fixed telling; lent-copy → new-telling conversion.
- **Cost:** $/telling (composition + scenes + battery + plates), tracked per skeleton and length.

---

## 15. Risks & Mitigations

1. **Prose quality ceiling** — the existential risk. Mitigations: skeleton-constrained generation (no model-invented plots), style fingerprint + scoring, editorial battery with regeneration, proofing bench before any skeleton ships, MVP gate (§13).
2. **Cost per telling.** A 6-scene telling with battery passes is multiple model calls per scene. Mitigations: per-skeleton cost telemetry, buffer-paced generation (only ~2 scenes ahead of demand, nothing wasted on abandonment beyond the buffer), model-tiering (cheaper models for extraction/checking passes).
3. **Canon drift / contamination.** Mitigated structurally: tellings sandboxed (Principle 8), canon ledger read-only to the engine, package versioning.
4. **Skeuomorph fatigue.** Mitigated by Principle 4 and B2.6; watch return-visit drop-off at the theater.
5. **Cultural/AI backlash.** Mitigated by total disclosure (Principle 3, E2.1), the form's honesty (it never impersonates a human-authored novel), and positioning as a publisher of a new form rather than an AI tool.
6. **Trademark (Unica/HCL).** Clearance search in classes 9, 41, 42 before public naming. Compound mark + publishing category provide distance; verify.
7. **Single-author bottleneck.** All quality flows through one worldbuilder. Acceptable at v1 scale; F1 tooling exists to maximize that person's leverage.

---

## 16. Open Questions

1. Telling length & pricing model: free first telling + subscription? Per-telling "commission fee"? (Leaning: first telling free, patronage subscription for unlimited commissions; decide post-MVP test.)
2. How much of the Sourcebook's sensitive material (Within Wheels arc) is excluded vs. lightly drawn upon for texture (A1.8 boundary needs an authored manifest).
3. Plate count economics and whether plates gate MVP or follow it.
4. Mobile-first vs. desktop-first faceplate layout priority for launch traffic (lending links will skew mobile).
5. Whether "return to the stacks" unfinished copies should ever expire from anonymous (non-patron) shelves.
6. Name/registration: unicapress.com secured? Trademark counsel engaged?

---

## 17. Phasing (suggested)

- **Phase 0 — Proof of prose (no UI):** world package v0 + 3 skeletons + manifestation/battery pipeline on the proofing bench. Twenty internally generated tellings; read them cold. *Gate: would you lend one to a stranger?*
- **Phase 1 — The seam:** Telling API + fixing/accession + minimal typographic reader (no theater). Validate buffer pacing and immutability end-to-end.
- **Phase 2 — The Archive:** faceplate, theater, cage, fixing ritual, lending. MVP launch per §13.
- **Phase 3 — Fast-follows:** plates, gazetteer unlocks, standing orders, patron warmth.
- **Phase 4 — The press:** pricing, print-on-demand pilot, second-world feasibility study using the A1/§8 contracts.

---

*Unica Press — every copy the only copy.*
