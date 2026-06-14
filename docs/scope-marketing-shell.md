# UnicaPress Shell — Scope & Workplan

**Status:** Working draft
**Date:** June 2026
**Parent:** [prd-unicapress.md](./prd-unicapress.md) · [standards-architecture+deployment.md](./standards-architecture+deployment.md)
**Supersedes:** [scope-coresaas.md](./scope-coresaas.md) (kept for table-definition reference; see note at top of that file)

---

## 1. Purpose

Unica Press splits into two systems with a deliberately thin, mostly-static seam between them:

```
THE SHELL                              THE APP
(this document)                        (prd-unicapress.md)

PHP / mysqli / Bootstrap / Sass   ↔    React / Node-or-Python / Postgres (Supabase)
cPanel shared hosting                  Vercel / Railway
Marketing site + staff ops console     Worldbuilding tool + Telling engine + reader
```

**The Shell is the part of this stack you've built six times before** — staff auth, content management, support inbox, event logging, feature flags, diagnostics — wrapped around a public marketing site. It is being built **first**, both because the org needs a public presence/contact path immediately, and because it is the candidate for becoming a reusable, templatized starting point for future projects (see §7).

**Build it for UnicaPress first.** Do not generalize ahead of need — extract a template *after* this instance is solid, not before.

---

## 2. In scope (the Shell owns)

### 2.1 Public marketing site
- Home / landing page, "what is Unica Press" overview
- Static informational pages (about, contact)
- The **public "About the Engine" colophon page** (PRD E2.1) — the out-of-world disclosure page. Its body copy lives here in Content Library; if it needs to display a live world-package version number, that single value is fetched from the App's public API at render time (see §4). The page itself is Shell-owned.
- Contact/inquiry form → writes to the support inbox (§2.5)
- Newsletter / mailing-list signup capture (optional, see §8 Phase S6)
- "Visit the Grand Archive" CTA linking out to the App's domain
- SEO basics, Google Analytics via Google Tag Manager (per [standards-frontend.md](./standards-frontend.md))

### 2.2 Staff admin console
- Admin authentication (`adminusers`, `adminonly()`)
- Admin shell layout (nav, alerts, page head) — already stubbed in `/public/app/admin/elements/`
- Diagnostics tools (API health, CORS, DB health, logging) — already stubbed in `/public/api/diagnostic-*.php`
- API tester tool (`/public/app/admin/api-tester.php`)

### 2.3 Content Library
- `displayContentLibrary([content_id])` — DB-managed copy blocks for marketing pages, with `%%VARIABLE%%` interpolation
- Admin CRUD for content entries

### 2.4 Email Library
- `sendEmailFromLibrary([email_id], ...)` — templated outbound email (contact-form acknowledgements, internal notifications)
- Admin CRUD for email templates

### 2.5 Support / Inquiry Inbox
- Public contact-form submissions land here for staff triage
- Repurposes the `supportmessages` shape from scope-coresaas, scoped down to anonymous/contact-form use (no logged-in "user" actor on the Shell side — see §3)

### 2.6 Event Logging
- `eventlogs` / `eventlogtypes` — staff activity and system events on the Shell side (admin logins, content edits, contact-form submissions, etc.)

### 2.7 Feature Flags (staff-only)
- Gates for in-progress admin tools (`adminuser_featureflags`), e.g. rolling out a new diagnostics page to one staff member before general staff release
- **Not** for patron-facing features — those are an App concern (App has its own patrons table)

### 2.8 Image / Asset Library
- `images` / `imagegallery` — marketing media (banner art, gazetteer preview images used on the marketing site, staff-uploaded assets)

---

## 3. Out of scope (lives in the App)

Explicitly **not** rebuilt here — these live in the Postgres/Supabase-backed App:

- **Patron accounts** (anonymous tokens, email signup, carrel shelf — PRD D1/D2). Worldbuilders and readers never touch this Shell's login.
- **World Package data** — locations, characters, objects, themes, factions, relationships, events, skeletons, registers (full schema: [V1-world-content-model.md](./V1-world-content-model.md))
- **Worldbuilder authoring SPA** (PRD Feature Space F) — entity CRUD, packet compilation, proofing bench, canonization
- **Telling generation** — composition, manifestation, editorial battery, continuity ledger (Feature Space A)
- **Telling API** and SSE telemetry (PRD §9)
- **Reading experience / Retrieval faceplate** (Feature Spaces B, C)
- **Lending** (Feature Space E, beyond the static disclosure page in §2.1)
- `promptlibrary` — AI prompt templates belong to the Generation Plane, not the Shell
- A generic `users` table — there is no "regular logged-in user" concept on the Shell. If newsletter signups need persistence beyond `supportmessages`/Content Library, that's a small dedicated table (§8 Phase S6), not a revival of `users`.

---

## 4. The boundary

The Shell and the App are **separate codebases, separate databases, separate deploy targets**, connected only by:

1. **Outbound links** — Shell marketing pages link to App URLs (e.g., `archive.unicapress.com`)
2. **One read-only data point (optional)** — the colophon page (§2.1) may call a public, unauthenticated App endpoint to display the current world-package version. This is the *only* live coupling, and it's allowed to fail gracefully (omit the version line) if the App is down — the Shell must never depend on the App being up.
3. **Shared brand assets** — typography/color tokens (PRD B3.1, B3.3) may be authored once and consumed by both Sass builds, but each system compiles its own CSS. No shared runtime dependency.
4. **Shared analytics property** (GA/GTM) for unified funnel tracking across both domains.

No shared session, no shared database, no shared auth. A staff member managing Content Library has no relationship to a patron's shelf.

---

## 5. Data model

Tables carried over from [scope-coresaas.md](./scope-coresaas.md), trimmed to Shell scope:

| Table | Status | Notes |
|---|---|---|
| `adminusers` | keep as-is | staff/admin accounts |
| `content_library` | keep as-is | marketing copy |
| `email_library` | keep as-is | templated outbound email |
| `eventlogs` / `eventlogtypes` | keep, drop `user_id` relevance | staff/system events only |
| `featureflags` / `adminuser_featureflags` | rename from `user_featureflags` | staff-only gating |
| `supportmessages` | repurpose | `user_id` always null; `anonymoususer_token` optional; this is the contact-form inbox |
| `images` / `imagegallery` | keep as-is | marketing asset library |
| `users` | **drop** | no Shell-side end-user concept |
| `promptlibrary` | **drop** | moves to App |

New (optional, Phase S6):

```sql
CREATE TABLE IF NOT EXISTS `mailinglist_subscribers` (
  `subscriber_id` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_email` varchar(255) NOT NULL,
  `subscriber_source` varchar(64) DEFAULT NULL COMMENT 'which page/form captured this',
  `subscriber_active` tinyint(4) NOT NULL DEFAULT 1,
  `subscriber_datecreated` bigint(20) NOT NULL,
  PRIMARY KEY (`subscriber_id`),
  UNIQUE KEY `idx_subscriber_email` (`subscriber_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Marketing mailing list signups';
```

---

## 6. Tech stack & deployment

Per [specs-platforms.md](./specs-platforms.md) and [standards-architecture+deployment.md](./standards-architecture+deployment.md) — unchanged for the Shell:

- PHP 8.2, mysqli, no framework
- Bootstrap + Sass, light jQuery/vanilla JS, GA via GTM
- Docker (PHP/Apache/MySQL) for local dev
- cPanel shared hosting for production
- Config system (`config.php` + platform configs) as already documented

---

## 7. Reusability / templatization

Once this instance is working end-to-end, the following are good candidates to extract into a reusable starter (in priority order — extract *after* proving out here):

1. Config/platform-detection system (`config.php`, `auth.php`, `database.php`, `debug_log()`)
2. Admin auth + admin shell layout (`adminonly()`, nav/layout elements)
3. Content Library + `displayContentLibrary()`
4. Email Library + `sendEmailFromLibrary()`
5. Event logging
6. Feature flags
7. Diagnostics suite

A future extraction should parameterize **branding** (site name, colors, fonts, nav structure) via a single config file rather than scattered string literals — that's the main thing that differed across past projects.

---

## 8. Workplan (Phase S — Shell)

This phase precedes and runs independently of the App's Phase 0 (World content / Engine). It can start immediately.

[x] Create docker container and test (See /docs/specs-platforms.md)
[x] Create core database entities


### S0 — Fix the inherited template
The current repo is a copy of a prior project ("unbox") with broken paths and leftover naming. Before any new code:

| # | Task |
|---|---|
| S0.1 | Fix path mismatches: `.admintemplate.php`, `adminlogin.php`, `admin/index.php`, `api-tester.php` reference non-existent `/config-app/config.php` and undefined `APP_PUBLIC_PATH` — point at `/public/config/config.php` per standards |
| S0.2 | Fix `public/api/index.php` and `diagnostic-apihealth.php`: replace `/config-api/config.php` and `/config-api/routes.php` references with the actual flat files (`config-api.php`, `routes.php`) |
| S0.3 | Fix `adminroutes.php` syntax error (missing comma, inconsistent route value formats) |
| S0.4 | Rename leftover `unbox`/`unbox_user`/`unbox_password`/`unbox_session` → `unicapress` equivalents in config templates |
| S0.5 | Remove tracked-but-empty `production.config.php`; confirm `.gitignore` covers platform configs |
| S0.6 | Build out `public/app/index.php` / `routes.php` front controller for the public site (currently empty) |

### S1 — Core schema
| # | Task |
|---|---|
| S1.1 | Write SQL migration for `adminusers`, `content_library`, `email_library`, `eventlogs`, `eventlogtypes`, `featureflags`, `adminuser_featureflags`, `supportmessages`, `images`, `imagegallery` into `docker/mysql/init/` |
| S1.2 | Seed at least one `adminuser` for local dev |

### S2 — Admin auth & shell
| # | Task |
|---|---|
| S2.1 | `adminlogin.php` working against `adminusers` |
| S2.2 | `adminonly()` in `functions-universal.php` |
| S2.3 | Admin layout/nav/alert elements wired and rendering |

### S3 — Content & Email Libraries
| # | Task |
|---|---|
| S3.1 | `content_library` CRUD admin tool |
| S3.2 | `displayContentLibrary()` with `%%VARIABLE%%` interpolation |
| S3.3 | `email_library` CRUD admin tool |
| S3.4 | `sendEmailFromLibrary()` |

### S4 — Event logging & feature flags
| # | Task |
|---|---|
| S4.1 | Event logging helper + wire into admin login, content edits |
| S4.2 | `featureflagRequired()` / `hasFeatureFlag()` against `adminuser_featureflags` |

### S5 — Support inbox & marketing site
| # | Task |
|---|---|
| S5.1 | Public contact form → `supportmessages` |
| S5.2 | Admin inbox view for inquiries (new/responded) |
| S5.3 | Marketing home page + about + colophon stub (static copy, Content Library-driven) |
| S5.4 | Image/asset library admin tool; gazetteer preview images for marketing |

### S6 — Optional / fast-follow
| # | Task |
|---|---|
| S6.1 | Mailing-list signup capture (`mailinglist_subscribers`) |
| S6.2 | Colophon page's live world-package-version fetch from App public API (graceful no-op if unavailable) |
| S6.3 | Diagnostics hardening (real `checkOrigin()`/`checkAuthentication()` in `config-api.php`, currently stubbed `return true`) |

### Exit gate
The Shell is "done enough" to begin Phase 0 of the App in parallel when: admin login works, content/email libraries are CRUD-able, the contact form lands in the inbox, and the marketing home page (with colophon stub) is live.

---

## 9. Open questions

1. Domain structure — subdomain split (`unicapress.com` Shell, `archive.unicapress.com` App) vs. path-based split on one domain (harder given different hosts: cPanel vs Vercel/Railway)?
2. Does the contact form need spam protection (captcha) for launch, or is volume low enough to defer?
3. Is the mailing list (S6.1) worth building now, or should signups route to a third-party tool (Mailchimp/Buttondown) embedded via their widget — avoiding building list-management UI at all?
4. Should the "About the Engine" colophon page exist on the Shell at all, or is it better owned entirely by the App (since it's conceptually part of the reading experience's disclosure chain, C2.4)? Current proposal keeps the *page* in the Shell but treats it as a single seam-crossing exception.
