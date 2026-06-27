# Technical Design Document — Admin & Staff Tooling

**Product:** Unica Press
**Scope:** Placement and architecture of staff/admin tooling across the Shell and the App
**Status:** Draft for build planning
**Date:** June 2026
**Related:** `prd-unicapress.md` (§5 System Overview, §13 Feature Space F), `scope-marketing-shell.md`, `standards-architecture+deployment.md`


## Overview
*Development*
- Windows environment using Docker
- Docker setup: TBD (specify PHP version, MySQL/MariaDB version, web server)

*Production*
- cPanel instance on a shared hosting server
- PHP version: TBD (specify minimum required version, e.g., PHP 8.0+)
- MySQL/MariaDB version: TBD
- Web server: Apache (cPanel default) or Nginx (if applicable)

*Both*
- Page rendering using PHP (no framework)
- Custom Javascript interacting with a RESTful API
- Database: MySQL/MariaDB via mysqli extension

---

## 1. Summary

"Admin tools" is not one system. It is two, with different data, different blast radius, and different correct homes:

- **Business / SaaS ops** — accounts, billing, support lookups, abuse & moderation, marketing CMS. Plain CRUD over business data that never touches the generation engine. **Stays in the Shell (PHP/mysqli).**
- **Engine / content ops** — prompt and model config, battery verdicts, flagged-telling queue, package versions, proofing/replay. Welded to the generation plane and its data. **Built in Node/Python, App-side.**

The actionable shift: stop treating admin as a fifth standalone product. Treat it as (a) staff-permissioned views layered onto the worldbuilding/authoring/generation tools already being built in the App, plus (b) a thin business console that remains in the Shell.

---

## 2. Decision

| Concern | Home | Stack |
|---|---|---|
| Accounts, billing, subscriptions | Shell | PHP/mysqli |
| Support lookups (read-only patron/telling info) | Shell | PHP/mysqli |
| Abuse & moderation (business-level) | Shell | PHP/mysqli |
| Marketing CMS | Shell | PHP/mysqli |
| Prompt management | App | Python |
| Model config & generation parameters | App | Python |
| Battery verdict inspection | App | Node/Python |
| Flagged-telling queue & author review | App | Node/Python |
| Package version management | App | Node/Python |
| Proofing bench / telling replay | App | Python |

Default principle: **admin lives next to the data it operates on.** Business data lives in the Shell; engine data lives in the App. Admin follows the data, not the org chart.

---

## 3. Rationale

**Data adjacency.** Prompts, generation config, telemetry, flagged queues, and patron shelves are all App data. If engine-admin lived in PHP, every one of those operations becomes a cross-service API to design, version, secure, and keep in sync — across a boundary that by spec (`standards-architecture+deployment.md`) shares no DB, session, or auth. That means standing up an API surface purely so one stack can reach into another's data.

**F-space overlap.** Staff admin for generation is largely the *same* surface as Feature Space F. The worldbuilder tooling already specifies a proofing bench (F1.3), a quality dashboard (F1.4), and a flagged-telling queue (F1.4). "Staff admin for generation" is mostly those views with broader permissions. Building them twice in two languages is waste.

**Generation logic is Python.** Testing a prompt, inspecting token budgets, running the editorial battery, replaying a telling — none of this is doable from PHP without shelling out to the Python services anyway. The admin that exercises the engine belongs where the engine is.

**Prompt-management failure mode.** Prompt management must share the *exact* context-assembly path the engine actually runs (invariants + location packet + entity slices + spine + continuity ledger, per A4.3). If it doesn't, admin previews one assembly while generation produces another. This alone wants prompt admin sitting in Python, next to the assembly code it edits.

**Auth.** Once engine-admin lives in the App, it rides the App's auth and session. Keeping it in the Shell would force staff auth to span both systems or build a second path. The split keeps each admin surface on its own system's auth.

---

## 4. Boundaries

The Shell ↔ App boundary established in the PRD (§5 note, `standards-architecture+deployment.md`) is preserved, not weakened:

- **No shared DB, session, or auth** between Shell and App. This TDD does not introduce any.
- **Business console (Shell)** reads and writes only business data. Where it needs to *display* engine-derived facts (e.g. a support agent viewing a patron's telling status), it consumes the App's existing read endpoints — it does not reach into engine internals.
- **Engine-admin (App)** is a permission tier over F-space and the generation plane, not a separate service. It uses the App's auth, the App's DB, and the same Telling API / context-assembly code paths the engine runs in production.
- The seam between Shell and App stays the **business read surface** — a small, stable, read-mostly contract — not the rich engine-internal API a PHP engine-admin would have required.

---

## 5. Component View

```
SHELL (PHP/mysqli, cPanel)              APP (Node/Python)
─────────────────────────               ─────────────────────────
Marketing site                          Worldbuilding tools (F-space)
Business console:                       Authoring tools
  - accounts / billing                  Story generation + reader
  - support lookups  ──── reads ───►    Engine-admin (staff perm tier):
  - moderation                            - prompt management
  - marketing CMS                         - model config / params
                                          - battery verdicts
   (own auth)                             - flagged-telling queue
                                          - package versions
                                          - proofing / replay
                                         (App auth/session)
```

---

## 6. Permission Model (App-side engine-admin)

Engine-admin is a role layered onto existing App tooling, not a separate login surface:

- **Author/worldbuilder (P2):** full F-space as scoped in PRD §13.
- **Staff/engine-admin:** F-space views plus broader scope — cross-author visibility into flagged queues, prompt and model config, package-version operations, telling replay.
- Distinction is permission scope over shared views, not duplicated tooling.

(Exact role definitions are TBD with the auth design; this TDD fixes only that the tier lives App-side on App auth.)

---

## 7. Open Questions

1. Support-agent read surface: which engine-derived fields the Shell business console legitimately needs to display, and the minimal read contract that exposes them without leaking engine internals.
2. Whether moderation is fully business-level (Shell) or has an engine-level component (content-safety overrides, A5.5) that belongs App-side.
3. Role granularity for engine-admin vs. author — single staff tier or finer-grained (config vs. review vs. replay).
4. Audit/changelog story for engine-admin actions (F1.8 covers entity changelog; prompt/model-config changes need equivalent).