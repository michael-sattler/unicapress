# Worldbuilder Onboarding — Scope Document

**Scope:** Initial self-service onboarding for worldbuilders into the UNIQA Press App  
**Parent doc:** [scope-worldbulding+authoring.md](./scope-worldbulding+authoring.md) · [visual-style-guide.md](./visual-style-guide.md) · [scope-marketing-shell.md](./scope-marketing-shell.md)  
**Status:** First pass — for review  
**Date:** June 2026

---

## 1. Purpose

Today, worldbuilder accounts are **staff-created** via `/admin` (invite-only Supabase Auth). The workshop (`/workbench`) assumes you already have an account and at least one world. There is no public entry point, no registration flow, and no account-level dashboard.

This document scopes the **first worldbuilder-facing front door**: registration and an empty post-login dashboard on `worldbuilder.unicapress.com`, plus a **landing page** (deferred until auth and onboarding work) with an options stripe — all on the **React + FastAPI App stack**, not the PHP Shell.

**Build priority:** registration → dashboard (empty state) → first world → workshop entry → **landing page last**.

**Supersedes (partially):** [scope-worldbulding+authoring.md](./scope-worldbulding+authoring.md) §5.1 — *"admin-created/invited rather than public self-signup."* Self-registration becomes the primary onboarding path; staff invite via `/admin` remains available for ops and early access.

---

## 2. System boundary

```
THE SHELL (PHP)                         THE APP (this scope)
unicapress.com                          worldbuilder.unicapress.com
Marketing, contact, staff CMS           Register, dashboard, workbench; landing page (later)
"Visit the Grand Archive" CTA ────────► outbound link only — no shared auth/session
```

| Concern | Owner |
|---|---|
| Corporate marketing, about, contact | Shell (`unicapress.com`) |
| Worldbuilder landing page (deferred), registration, account dashboard | App (`worldbuilder.unicapress.com`) |
| Workshop canvas, entity CRUD, Archivist | App (`/workbench` — existing) |
| Staff ops (create users, browse worlds, prompt library) | App (`/admin` — existing; separate host or role-gated in v1) |

The Shell may link to `https://worldbuilder.unicapress.com` as the worldbuilder CTA. Until the landing page ships, that URL may resolve to `/login` or `/register`. Landing copy is App-owned when built (hardcoded or a small local content map) — not Shell Content Library in v1.

---

## 3. Domain & routing

### 3.1 Production

| Host | Surface |
|---|---|
| `worldbuilder.unicapress.com` | Worldbuilder public + authenticated experience |
| `api.unicapress.com` (or Railway URL) | FastAPI — unchanged pattern |
| Staff `/admin` | TBD: `admin.unicapress.com` or path on a staff-only host; **not** the worldbuilder subdomain |

### 3.2 Local dev

| URL | Maps to |
|---|---|
| `localhost:5173` | Same React app; host-based routing can be simulated via `VITE_APP_SURFACE=worldbuilder` or path prefix `/wb/*` until subdomain DNS exists |
| `localhost:8000` | API |

### 3.3 Route map (worldbuilder host)

| Path | Auth | Purpose |
|---|---|---|
| `/` | Public | **Interim:** redirect to `/login` until landing page ships (O1.5). **Then:** landing page. |
| `/register` | Public (redirect if logged in) | Create account |
| `/login` | Public (redirect if logged in) | Sign in |
| `/dashboard` | Protected | Account dashboard — three states per §8 (`worlds.length` 0 / 1 / N) |
| `/workbench` | Protected | Existing workshop (unchanged) |
| `/workbench/:worldId` | Protected | Existing workshop with world selected |

**Index behavior on `worldbuilder.unicapress.com`:** must **not** redirect to `/admin` (current App default). Until O1.5, `/` redirects to `/login` (or `/register`).

Authenticated users hitting `/` after the landing page ships may optionally redirect to `/dashboard` — open question (§12). Until O1.5, `/` redirects to `/login`.

---

## 4. User journey

### 4.1 Target journey (after landing page ships)

```
┌───────────────┐     ┌──────────────────┐     ┌─────────────┐     ┌─────────────────┐
│ Landing page  │────►│  Options stripe  │────►│  Register   │────►│ Empty dashboard │
│   (public)    │     │ (on landing page)│     │  or Login   │     │  (protected)    │
└───────────────┘     └──────────────────┘     └─────────────┘     └────────┬────────┘
       │                      │                      │                      │
       └──────────────────────┴──────────────────────┘                      │
                              Sign in ─────────────────────────────────────┘
                                                                            │
                                                                            ▼
                                                                  Create first world
                                                                            │
                                                                            ▼
                                                                  Open Workshop (/workbench)
```

### 4.2 Initial journey (O1.0–O1.4 — landing deferred)

Direct links or `/` → `/login` or `/register` → dashboard → onboard → first owned world → workbench. No public marketing surface until O1.5.

**Happy path (new worldbuilder):**

1. Lands on home — understands what the Workshop is for.
2. Options stripe presents clear next steps.
3. Arrives at `/register` (direct link, Shell CTA, or interim `/` redirect) and registers (email, password, display name)
4. Confirms email if verification is enabled.
5. Arrives at dashboard with a **pre-populated demo world** (The Hobbit, Star Wars, or Harry Potter — one assigned per signup): fully populated, browsable, immediately comprehensible. A single persistent prompt: *"This is someone else's world. Ready to build yours?"* — not intrusive, one-button clear.
   - **Alternate (3a):** zero owned worlds — dashboard **state 1** (§8.1; mockup [unica-workshop.html](./unica-workshop.html)).
6. Chooses onboard path (clears prefab demo world):
   - **6A.** Archivist brainstorming dialogue — conversational, LLM-driven, ~5–8 exchanges → initial world creation. Dialogue concludes with a canvas preview of the entities it derived.
   - **6B.** Start from scratch — manual world create.
7. Dashboard reflects **state 2** (one owned world) → **Enter workshop** → `/workbench/:worldId`.

**Happy path (after landing page):** landing → register → dashboard (demo or state 1) → onboard → state 2 → workbench.

**Returning worldbuilder:** `/login` → dashboard → pick world → workbench.

---

## 5. Landing page *(deferred — O1.5)*

> **Not in the first build slice.** Ship registration, dashboard, and first-world → workshop before building this surface.

### 5.1 Goals

- Orient a visitor who followed a link from the Shell or a direct URL.
- Establish UNIQA Press / Workshop voice without duplicating the full corporate marketing site.
- Drive action via the options stripe (§6).

### 5.2 Content blocks (v1)

| Block | Notes |
|---|---|
| **Wordmark + subline** | "Workshop" or "Worldbuilder Workshop" under Unica Press wordmark. Tone per [visual-style-guide.md](./visual-style-guide.md) — fine press, not SaaS. |
| **Hero** | One headline + one paragraph: build canon, name consistently, workshop with the Archivist, prepare worlds for telling. No steampunk skin; world-agnostic. |
| **Options stripe** | See §6 — primary interactive element below the hero. |
| **Footer** | Link back to `unicapress.com`, sign-in link, minimal legal/contact pointer to Shell. |

### 5.3 Visual treatment

- Reuse App design tokens where they exist; align new marketing-lite pages with the brand guide (`--paper`, `--ink`, `--oxblood`, Cormorant + EB Garamond).
- Uses workshop chrome (dark/canvas UI) — the home/register/dashboard process feels like the workbench.

### 5.4 Out of scope on landing (v1)

- Pricing / billing
- Testimonials, blog, feature comparison tables
- Live world previews or reader samples
- Video embeds

---

## 6. Options stripe *(part of landing page — O1.5)*

A full-width band on the landing page — the main decision surface for visitors. Three option cards in v1:

| Option | Label (draft) | Action | Notes |
|---|---|---|---|
| **Start building** | *Open a workshop* | → `/register` | Primary CTA. For new worldbuilders. |
| **Return** | *Sign in* | → `/login` | Secondary. |
| **Learn** | *How the Workshop works* | Scroll to §5.2 explainer section or expand inline panel | No account required. Brief: worlds, entities, Archivist, canon status — not a full tutorial. |

**Layout:** horizontal stripe on desktop; stacked cards on narrow viewports. One card visually primary (oxblood rule or button on *Open a workshop*).

**Future options (not v1):** join waitlist, accept invite code, explore demo world, import from Amanuensis — do not stub UI unless needed for layout.

---

## 7. Registration & authentication

### 7.1 Registration form

| Field | Required | Notes |
|---|---|---|
| Display name | Yes | Stored in `profiles.display_name` via existing trigger |
| Email | Yes | Unique; Supabase Auth |
| Password | Yes | Min length per Supabase project settings (recommend 12+ in copy) |
| Confirm password | Yes | Client-side match only |

On success → create `auth.users` row → `handle_new_user` trigger creates `profiles` row with `role = 'worldbuilder'` (existing default).

### 7.2 Auth mechanism

- **Supabase Auth** `signUp` from the React client (same as today's `signIn`, which is not yet exposed for worldbuilders).
- **Enable public signup** in Supabase for the App project (reverses current invite-only default).
- **Email verification:** recommended ON for production; user lands on "check your email" interstitial before dashboard access. If verification is OFF in dev, go straight to `/dashboard`.

### 7.3 Login

- Dedicated worldbuilder login page — copy and redirect target differ from today's staff-oriented `/login` ("Admin sign in" → `/admin`).
- Successful login → `/dashboard` (not `/admin`).
- Session: existing `AuthProvider` + Bearer token to API — no change to JWT middleware.

### 7.4 Staff invite path (unchanged)

`POST /admin/users` remains for staff-created accounts. Invited users skip registration UI and use `/login` on the worldbuilder host.

### 7.5 API additions

| Endpoint | Method | Purpose |
|---|---|---|
| `POST /auth/register` | Optional | Server-mediated signup if client-side signup is undesirable (rate limit, captcha). **Default v1:** client-side Supabase signup only; no new API route unless abuse requires it. |
| `GET /me` | Exists | Profile + role for dashboard header |
| `GET /worlds` | Exists (RLS) | Dashboard world list |
| `POST /worlds` | **Add if missing** | Worldbuilder creates own world from dashboard (today world create may be admin-only — confirm and expose RLS-scoped `POST /worlds` for `owner_id = auth.uid()`) |

### 7.6 Security (v1 minimum)

- Supabase rate limiting / leaked-password protection (project settings).
- CORS: add `https://worldbuilder.unicapress.com` to `api_cors_origins`.
- No captcha in v1 unless signup abuse appears — note as follow-up.

---

## 8. Dashboard

The dashboard is the **account-level** home — not the workshop canvas. It answers: *which worlds do I have, and how do I start?*

`GET /worlds` (RLS-scoped) drives which of three states renders. Demo/prefab worlds during first-login onboarding (§4.2 step 3) are a separate overlay — not one of these three owned-world states.

### 8.0 Shared chrome

| Region | Content |
|---|---|
| **Header** | Wordmark, display name, sign out |
| **Main** | One of the three states below |
| **Nav** | Dashboard (active); **Workshop** link when `worlds.length ≥ 1` |

Uses workshop chrome (dark/canvas UI) — consistent with register/login and the workbench.

### 8.1 State 1 — New user, no worlds

**Condition:** `GET /worlds` returns `[]` (no owned worlds; demo prefab cleared or alternate path 4.2 §3a).

**Mockup:** [unica-workshop.html](./unica-workshop.html) — getting-started empty case (*"The case is empty" / "Begin with a city."*).

| Element | Copy direction (draft) |
|---|---|
| Eyebrow | *Your shelf is empty* |
| Headline | *Every press starts with one world.* |
| Body | A world is a canon container; you'll add locations, characters, and themes in the Workshop. |
| Primary CTA | **Create your first world** — opens create form or launches onboard path (§4.2 step 4) |
| Secondary | Link to "How the Workshop works" (landing-page anchor once O1.5 ships; omit until then) |

### 8.2 State 2 — One world

**Condition:** `GET /worlds` returns exactly one row.

Single-world summary — not a list. The account has one canon container; the dashboard orients around it.

| Element | Source / behavior |
|---|---|
| World title | `worlds.world_title` |
| Logline | `worlds.world_logline` (truncated) |
| Updated | `worlds.updated_at` |
| Entity counts (optional v1) | Aggregate counts per type for at-a-glance progress |
| Primary CTA | **Enter workshop** → `/workbench/:worldId` |
| Secondary | **New world** — only if multi-world is enabled; otherwise omit until state 3 |

Default post-onboard landing: after first owned world is created, user sees state 2.

### 8.3 State 3 — Multiple worlds

**Condition:** `GET /worlds` returns two or more rows.

World picker — ordered by `updated_at` desc (most recently touched first).

Shows full-width cards for each owned world. Each card:

| Column / field | Source |
|---|---|
| World title | `worlds.world_title` |
| Logline | `worlds.world_logline` (truncated) |
| Imagery | [image paths for first X number of entities if they exist, arranged as a gallery strip]
| Updated | `worlds.updated_at` |
| Action per row | **Open workshop** → `/workbench/:worldId` |

**Header action:** **New world** → inline form or modal (title required).

### 8.4 Relationship to workbench

- Dashboard = **account / world picker**.
- Workbench = **in-world authoring** (existing `GettingStartedPanel` handles empty *entity* state inside a world).
- Do not merge dashboard and workbench in v1. Navigation: Dashboard ↔ Workshop is explicit.

---

## 9. Implementation notes (App codebase)

### 9.1 React

- Host- or env-based route sets: worldbuilder surface vs staff `/admin` (avoid showing admin nav on worldbuilder host).
- New pages (in build order): `RegisterPage`, `WorldbuilderLoginPage`, `DashboardPage` (3 states); `OnboardDialoguePage` or modal (4A); then `WorldbuilderLandingPage` (O1.5).
- Extend `useAuth` with `signUp` (and optionally `resetPassword` stub for later).
- Worldbuilder layout shell: lighter editorial chrome shared by register, login, dashboard — landing page joins the same shell when built.

### 9.2 FastAPI

- Ensure RLS-scoped `POST /worlds` for authenticated worldbuilder (owner_id from JWT).
- CORS + env docs for production domain.

### 9.3 Deploy

- Vercel (or current web host): add `worldbuilder.unicapress.com` as alias to the App `web` deployment.
- DNS CNAME → Vercel.
- Supabase Auth: add redirect URLs for `https://worldbuilder.unicapress.com/**`.

### 9.4 Analytics

- Optional GTM events: `wb_landing_view` (O1.5), `wb_register_start`, `wb_register_complete`, `wb_demo_cleared`, `wb_first_world_created` — same property as Shell if unified funnel is desired (per marketing shell §4).

---

## 10. Milestones

| ID | Deliverable | Exit gate |
|---|---|---|
| **O1.0** | Subdomain + routing | `worldbuilder.unicapress.com` serves App; `/` redirects to `/login` (not `/admin`); CORS + Auth redirect URLs configured |
| **O1.1** | Registration + login | Public signup enabled; register + login pages; session persists; redirect to dashboard |
| **O1.2** | Dashboard (3 states) | State 1 empty shelf (per mockup); state 2 single-world summary; state 3 world list; `POST /worlds` for self-serve user |
| **O1.3** | Onboard paths | Demo prefab world + clear prompt; 4A Archivist interview → world + entity preview; 4B scratch create |
| **O1.4** | First-world → workshop | After onboard, state 2 → `/workbench/:worldId`; empty-entity getting started panel |
| **O1.5** | Landing page + options stripe *(deferred)* | Hero + three option cards; `/` serves landing instead of login redirect; responsive; brand tokens applied |

**Build sequence:** O1.0 → O1.1 → O1.2 → O1.3 → O1.4 → **O1.5 last**.

---

## 11. Out of scope (v1)

- Paid plans, Stripe billing, entitlements
- OAuth / social login (Google, etc.)
- Organization / team accounts
- Onboarding checklist beyond empty dashboard (e.g. guided tour, progress %)
- In-app notifications or email templates beyond Supabase defaults
- World templates or duplicate-from-demo
- Moving staff `/admin` to worldbuilder host
- Patron / reader accounts (separate PRD track)
- Shell-side registration or worldbuilder account management

---

## 12. Open questions

1. **Logged-in `/` behavior (post-O1.5):** redirect to `/dashboard` automatically, or always show landing page?
2. **Email verification:** required in prod before dashboard, or allow immediate access with banner?
3. **Staff admin host:** `admin.unicapress.com` vs hidden path on internal URL — affects whether one Vercel project serves both surfaces.
4. **Invite codes / closed beta:** gate registration behind a code while options stripe is public — needed for soft launch?
5. **Display name vs pen name:** is `display_name` sufficient, or do we need a separate `author_name` field for Phase 2?
6. **Password reset:** include "Forgot password" in O1.1 or defer to O1.5?
7. **Interim `/` redirect:** `/login` vs `/register` as default before landing page ships?

---

## 13. References

- Dashboard state 1 mockup: [unica-workshop.html](./unica-workshop.html) (getting-started empty case)
- Workshop empty-entity UX (inside a world): `GettingStartedPanel` in `app/web/src/workbench/components/GettingStartedPanel.tsx`
- Profile trigger: `app/supabase/migrations/001_initial_schema.sql`
- Staff user create (parallel path): `POST /admin/users` in `app/api/app/api/routes/admin_users.py`
- Build status: [buildplan.md](./buildplan.md) W1.1 (auth shell — to be extended by O1.x)
