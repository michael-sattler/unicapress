# Unica Press — Brand & Visual Style Guide

**Scope:** This guide governs the **world-agnostic Unica Press** brand — the corporate/marketing surface (homepage, about, how-it-works) that explains the concept without reference to any specific fictional world. It does **not** govern the in-world reading experiences (e.g., The Grand Archive of the Steamlands), which have their own diegetic art direction.

**One-line brief for an implementing model:** Build this like the website of a century-old fine press (Kelmscott / Doves / Folio Society lineage), *not* like a SaaS landing page or a steampunk theme. Everything lives on cream paper, set in book typography, with a single red used like a second letterpress ink. Restraint is the brand.

---

## 1. Brand Essence

- **What it is:** A publishing house that prints stories which exist only once, for one reader.
- **Wordmark:** Unica Press
- **Tagline (lockup):** *One author's world. One-of-a-kind stories. One reader at a time.*
- **Motto (seal/colophon only):** *Every copy the only copy.*
- **Personality:** Lettered, warm, literary, faintly ceremonial, a little serious. The prospectus of an old press — not the pitch deck of a startup.
- **Feeling to evoke:** holding a fine edition. Provenance, permanence, craft.
- **Feeling to avoid:** luxury-minimalist tech aesthetic (thin geometric sans, lots of white, one tasteful gradient); steampunk pastiche (gears, brass, goggles); anything that reads "app."

---

## 2. Color

A three-color system plus one accent. Discipline is the strategy: fine presses signal quality through *fewer* colors used precisely, not more.

| Token | Hex | Role |
|---|---|---|
| `--ink` | `#1A1714` | Primary text & line work. A warm near-black — **never** use pure `#000000`. |
| `--paper` | `#F4EFE3` | Default page background everywhere. A laid-paper cream. **Never** use pure white as a page ground. |
| `--paper-deep` | `#EAE2D0` | Secondary surfaces, subtle panel fills, alternating sections. |
| `--oxblood` | `#9E3328` | THE accent. A letterpress vermilion/oxblood drawn from the seal. Used sparingly — stamps, a single rule, one emphasized word. |
| `--ink-blue` | `#27323F` | Optional, rare. Wordmark only, if a cool note is wanted. Prefer monochrome+red; reach for this only if needed. |
| `--ink-soft` | `#5A5046` | Muted text: captions, metadata, footnotes, fine print. |
| `--rule` | `#C9BB9B` | Hairline rules, borders, dividers (warm tan, low contrast). |

**Usage rules**
- The entire site sits on `--paper`. This single choice carries more brand weight than any font.
- `--oxblood` is precious. If it appears more than ~3 times per viewport, it's overused. Think "second ink," not "accent color." Good uses: the seal/stamp, one rule under a section head, a single word of emphasis, link hover.
- No gradients. No drop shadows on type. No glows. (A single, very soft paper-shadow under a "physical" object like the seal or a sample card is permissible.)
- Contrast: `--ink` on `--paper` is the workhorse pair and is comfortably readable. Verify final AA contrast for body text.
- Dark mode: optional and low-priority. If built, invert to ink ground `#1A1714` with paper-cream text `#F4EFE3` and keep oxblood as accent — but the brand is fundamentally a *light, paper* brand; do not lead with dark mode.

---

## 3. Typography

Type is where a publisher's brand lives. **Set the page like a book, not a landing page.** All fonts below are free Google Fonts.

### Faces
| Role | Font | Notes |
|---|---|---|
| Display / headlines / wordmark | **Cormorant** (or Cormorant Garamond) | Incised, literary, "private press." Primary display face. |
| Alt display (dramatic) | **Playfair Display** | High-contrast Didone. Use only if a more dramatic note is wanted; relates this brand to the Steamlands system which uses Playfair. Pick ONE display face and commit. |
| Body / reading | **EB Garamond** | All body copy. This is also the manuscript reading face — the website is set in the same type as the books, by design. |
| Typed / mono accents (rare) | **Special Elite** | Only for "typed" artifacts (e.g., a faux accession line). Decorative, sparing. |
| UI micro-labels (only if unavoidable) | **Inter** | Tiny functional labels only. Avoid if possible. Never headlines or body. |

**Recommended default:** Cormorant for display + EB Garamond for body. (Reserve Playfair for the Steamlands brand so the two stay distinguishable yet related.)

### Type scale & rhythm (the part that makes it feel expensive)
- **Body:** 19–21px, `line-height: 1.62`, measure capped at **60–66 characters** (`max-width: ~34em`). Generous, bookish, never edge-to-edge.
- **Display sizes:** large and confident — hero headline can run 48–72px on desktop. High-contrast serifs want size to show their modulation.
- **Section labels / eyebrows:** small caps, letter-spaced (`letter-spacing: 0.12em`), in `--ink-soft` or `--oxblood`.
- **Wordmark:** Cormorant in **true small caps**, generously letter-spaced. "UNICA PRESS."

### Microtypography (non-negotiable — ~80% of fine-press credibility)
- Use **true small caps** (`font-feature-settings: "smcp"`) for the wordmark and section labels — not faux uppercasing.
- Use **oldstyle figures** (`font-feature-settings: "onum"`) for numerals in body text so digits sit on the baseline like a book.
- Real **em-dashes** (—), real curly quotes (" " ' '), proper ellipses (…). Enable common ligatures.
- Hang the punctuation / indent paragraphs the way a book does: either first-line indent (`text-indent: 1.2em`, no paragraph gap) OR spaced paragraphs — pick the book convention, not the web default of both.
- Hyphenation on for justified or long-measure text; avoid rivers.

---

## 4. Layout & Composition

Compose like a **title page**, not a dashboard.

- **Grid:** single generous reading column for prose; centered, narrow measure. Avoid multi-card grids and feature-tile rows (they read "SaaS").
- **Whitespace:** ample, but warm — it's the margin of a page, not the negative space of a tech hero. Asymmetry and classical proportion over rigid 12-col symmetry.
- **Rules & ornaments instead of boxes:** separate sections with a thin `--rule` hairline or a centered printer's **fleuron/ornament**, not bordered cards or background-color blocks. Frame a pull-quote with a hairline, not a shadowed box.
- **Section heads:** small-caps eyebrow + serif headline, optionally a short oxblood rule beneath.
- **Hero:** the wordmark, the tagline, and either the seal/sort device or a single quiet line of manifesto. No screenshot, no product UI, no "Get started" button cluster. It should look like the title page of a prospectus.
- **Imagery:** minimal. Prefer the **logo device** (the single lead sort) and the **blind-emboss seal** as recurring marks over photography. If any image is used, it should look printed (engraving, stipple, letterpress), never a stock photo.

---

## 5. Signature Elements

- **The sort (logo):** the single piece of lead type with the (near-symmetric, ideally mirror-reversed) U. Primary mark. Provide three reductions: full engraving (hero/print), simplified (header, ~48px), silhouette (favicon, 16px).
- **The seal (blind emboss):** the Unica Press device rendered as a colorless raised/debossed impression — used as a footer mark and as the visual home of the motto *"Every copy the only copy."* On screen, render with subtle raking-light treatment (a soft top-left highlight + bottom-right shade), not color.
- **The oxblood stamp:** the one place red lives confidently — a circular or oval stamp device, used once per page at most (e.g., beside the tagline or at a section break).
- **The hairline rule + fleuron:** the connective tissue between sections.

---

## 6. Motion

Restraint reads as confidence; bounce/parallax reads as its opposite.

- **Default to near-stillness.** Permanence is the brand; things that jitter feel cheap and disposable — the wrong message for a press selling editions that can never be remade.
- The one permitted signature motion: **type/ink "setting" onto the page** — a brief, subtle reveal as if printed (e.g., a quick ink-darken/settle), and/or the **seal pressing once** on load. Keep it under ~600ms, ease-out, once.
- No scroll-jacking, no parallax, no auto-playing carousels, no float/bob on objects.
- Respect `prefers-reduced-motion`: disable the setting/press animations entirely.

---

## 7. Voice (for any copy generated to fill the page)

- Literary, plain, confident. Short declaratives next to longer lyric sentences. (It should feel adjacent to the prose the press actually publishes.)
- Speak as a *publisher*, not a tech company: "editions," "prepared," "a reader," "the press" — never "users," "content," "AI-powered," "platform."
- **Disclosure is part of the brand, stated plainly and without apology:** the stories are prepared by a machine, from worlds built by human authors, and each exists only once. Honesty is the aesthetic.
- Avoid: hype verbs, exclamation points, growth-marketing cadence, the words "revolutionary," "powered by," "unleash," "seamless."

---

## 8. Quick Reference (for the implementing model)

```css
:root {
  --ink:        #1A1714;
  --paper:      #F4EFE3;
  --paper-deep: #EAE2D0;
  --oxblood:    #9E3328;
  --ink-blue:   #27323F; /* rare; wordmark only */
  --ink-soft:   #5A5046;
  --rule:       #C9BB9B;

  --font-display: "Cormorant", Georgia, serif;   /* or Playfair Display — pick one */
  --font-body:    "EB Garamond", Georgia, serif;
  --font-typed:   "Special Elite", monospace;     /* rare, decorative */

  --measure: 34em;        /* 60–66 char reading column */
  --body-size: 20px;
  --body-leading: 1.62;
}
/* Page ground is always --paper. Text is always --ink (warm, never #000).
   --oxblood is a second ink: stamps, one rule, one emphasized word — sparing.
   Body: --font-body, oldstyle figures + common ligatures, narrow measure.
   Display: --font-display, large, true small caps for wordmark/labels.
   Dividers: --rule hairlines or a centered fleuron — not cards or shadowed boxes.
   Motion: near-still; one "ink sets / seal presses" reveal under 600ms; honor reduced-motion. */
```

**Litmus test for any element:** *Would this look right on the title page of a fine edition?* If yes, ship it. If it looks like a SaaS hero section, cut it.

---

*Unica Press — One author's world. One-of-a-kind stories. One reader at a time.*
