> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, `docs/05-ux/`, or office documentation. See `docs/10-research-incubation/README.md` and `LIFECYCLE.md`.

# U-20.3 — Repository-Grounded Experience Blueprint (response to `PO-U20.3-002`)

```text
Identifier:            R-01.11
Programme:             R-01 — Research & Incubation Framework
Scope:                 Strategic (public-site experience architecture)
Status:                Exploratory — architecture drafted for review; no page markup,
                       motion, copy, or visual design changed
Owner:                 UX Studio (role, not a standing individual)
Date Created:          2026-07-31
Last Reviewed:         2026-07-31
Dependencies:          R-01.8 (positioning/copy), R-01.9 (workspace evidence),
                       R-01.10 (motion), U-20.6 (engineering readiness)
Related Architecture:  COMPONENT_PRINCIPLES.md, MOTION_SYSTEM.md, DESIGN_TOKENS.md,
                       HUMAN_DESIGNED_EXPERIENCE_STANDARD.md
Product Office Decision: None
Repository Status:     None
```

## 0. Citation check, before anything else

Two of the six items in this commission's Strategic Authority list don't hold up on direct verification, named here rather than silently carried forward:

- **`POD-U20-001`** does not exist as a file anywhere in this repository (`grep -rl "POD-U20-001" .` returns nothing). Its content was produced in conversation as a "Constitutional Decision Record" but was never committed. It cannot function as standing strategic authority until it's a real, checked-in document — the same rule this directory already applies to everything else in it.
- **`U-20.6` is cited as "(Accepted)."** It's real — `docs/engineering/U-20.6-PUBLIC-WEBSITE-ENGINEERING-READINESS.md`, committed — but nothing in the repository records a Product Office acceptance of it (no `DECISION_LOG.md` entry, no office sign-off document). It should be cited as *Delivered*, not *Accepted*, until an actual acceptance record exists.

Neither gap blocks this document — `U-20.6`'s content is real and usable regardless of its acceptance status, and this document doesn't depend on `POD-U20-001` for anything specific. Named so the citation chain stays honest, matching the discipline already applied to `R-01.8` (§0's citation gap on `U-20.3` itself) and `R-01.9`/`R-01.10`.

**On this document's own claimed status**: the commission states this blueprint "shall become the constitutional source of truth" and marks itself "CONSTITUTIONAL OWNERSHIP TRANSFERRED." Per `LIFECYCLE.md`'s one rule, no document — including this one — can self-assign a status past `Exploratory`. This package is filed here, at that real ceiling, ready for the actual Product Office/Architecture Office review the framework requires before anything past it is true.

---

## 1. Repository Reality Check

### Existing public routes (verified directly against `routes/web.php`)

| Route | Name | State |
|---|---|---|
| `/` | `home` | Live |
| `/analyse` | `analyse` | Live |
| `/planner` | `planner.public` | Live |
| `/reports` | `reports` | Live |
| `/about` | `about` | Live |
| `/faq` | `faq` | Live |
| `/release-notes` | `release-notes` | Live |
| `/pricing` | `pricing` | Live |
| `/labs` | `labs` (Volt) | Live |
| `/contact`, `/privacy`, `/terms` | — | Placeholder (`pages.public-coming-soon`) |

No other public route exists. No page named "How It Works," "Platform," or "Resources" exists as its own route — confirming `R-01.8`'s §4 finding again, independently, this pass.

### Existing public components

`card`, `badge`, `alert`, `empty-state`, `metric-card`, `page-header`, `search-trigger-button`, `language-selector`/`language-modal`, `theme-toggle`, `command-palette`, `modal`, `public-nav`, `public-footer`, `primary-button`, `secondary-button`, `danger-button`. A `workspace/` component subdirectory exists for authenticated-screen composition, not public marketing use. `device-frame` exists but is **dormant and uncommitted** — built for the mobile "Coming Soon" section the founder ordered removed ("doesn't look human designed, revisit later"); present in the working tree, not in git history, not part of the current public experience.

**Real, verified duplication (restated from `U-20.6`, not re-derived differently here)**: zero uses of `<x-primary-button>`/`<x-secondary-button>` anywhere in `resources/views/pages/*.blade.php`. `home.blade.php` alone has 5 hand-rolled `bg-gradient-button` CTA instances. Any experience architecture that adds more homepage CTAs inherits this duplication unless Engineering's own recommended fix (extract a shared CTA component) happens first — noted here as a dependency for whoever implements this blueprint, not something this document resolves.

### Existing primary navigation (verified directly, not assumed from `R-01.8`)

The **visible header nav today is 4 items**: Analyse, Planner, Reports, Pricing (`public-nav.blade.php`, lines 86-89). `SlipGuard Labs` exists as a live route and appears in the command-palette search index, but **not in the primary visible nav** — `R-01.8`'s proposed 5-item nav (`Analyse · Planner · Reports · Pricing · Labs`) is a real, small, additive change against current state, not something already implemented. Recorded as a concrete, scoped implementation item for whoever picks this blueprint up, not assumed done.

### Existing motion primitives (only what `MOTION_SYSTEM.md` actually approves — checked directly, matching `R-01.10`'s own review)

Duration tiers (Instant 100/Fast 150/Standard 200/Deliberate 300, 400ms hard ceiling); easing (`ease-out` entrance, `ease-in` exit, never linear/bounce/spring); the single-fade+8px-rise `data-reveal` mechanism (once, on first scroll into view, section-level); the Fixed Atmospheric Layer (background-only scroll-linked depth, capped displacement, `pointer-events: none`); the Static Product Mark (logo/wordmark never rotate/spin/bounce/respond to scroll — added 2026-07-31, same day as the rotation this superseded); button-press micro-interaction (scale 0.98, 100ms, `:active`); the three-tier loading rule (<300ms nothing, 300ms-2s scoped inline spinner, >2s calm explicit state, never a fake progress bar); universal `prefers-reduced-motion` handling (implemented once, globally, in `resources/css/app.css`). Nothing beyond this list is treated as approved motion in this document. Any motion this blueprint's experience architecture implies beyond what's listed here (there is one — see §6) is named as future work, per the commission's own instruction, not specified.

### Existing design primitives

Colour, alert, and confidence tokens are real and defined in `DESIGN_TOKENS.md` — confidence tokens are explicitly "reserved, no current consumer" per that document itself, not yet load-bearing anywhere. Card, Badge, Alert, Button, and Empty-State primitives are defined in `COMPONENT_PRINCIPLES.md`, with the one narrow, already-amended exception for premium device mockups in native-mobile-specific sections (dormant, unused, per §2 above).

### Feature flags controlling public-facing experiences

`MARKET_WIDE_PLANNER_ENABLED=false` — Market Intelligence / Builder (Programme U-17 Capability B) stays internal-only while this is false; no public route, screenshot, or claim may present it as generally available. `SLIPGUARD_DEMO_ENABLED=false` is an operational seeding gate, not a customer-facing feature flag — irrelevant to this blueprint beyond noting it's how the real screenshot/demo data referenced in §5 gets produced.

### Superseded decisions, named explicitly

- **Logo/wordmark rotation** ("Signature Motion") → superseded by the Static Product Mark, 2026-07-31, same day, in both the real doc and the real committed code.
- **Parallax as a proposed public-site effect** → the underlying visual goal (atmosphere, depth) is already covered by the real, stricter, already-built Fixed Atmospheric Layer; "parallax" itself stays forbidden by name.
- **The mobile "Coming Soon" homepage section** → built in full this same day, then explicitly removed by direct founder instruction ("it doesn't look human designed... we will revisit later"). Its supporting backend/components (`device-frame`, waitlist capture, device-mockup CSS) remain dormant and uncommitted, not part of current scope, not assumed to return.

---

## 2. Primary Objective

What should a visitor experience, in what order, and why — nothing more. Answered in §3-§8 below, grounded in the real repository state from §1, not an idealized one.

---

## 3. Experience Architecture — the visitor journey

| Stage | Purpose | Visitor question | Desired understanding | Primary evidence | Expected outcome |
|---|---|---|---|---|---|
| First load (Hero) | Establish what SlipGuard is and isn't in one view | "What is this, and is it for me?" | Decision-support tool, not a tipster, not a bookmaker | Real product preview (existing dashboard screenshot), plain-language framing | Visitor keeps reading, or self-selects out honestly |
| Problem framing | Name the real frustration SlipGuard addresses | "Why would I need this?" | Opaque risk in accumulators is a real, nameable problem | Concrete structural-risk example, not abstract claims | Visitor recognizes their own experience |
| Mechanism (How SlipGuard Thinks) | Explain the deterministic approach without jargon | "How does it actually work?" | Same rules every time, explainable, no black box | The real evidence→rules→conclusion sequence, plain language | Visitor trusts the *method*, not yet the brand |
| Proof | Move trust from claimed to demonstrated | "Can I verify any of this?" | The engine's behaviour is checked, not just asserted | Real, verified test counts and engineering principles (`R-01.8` §4/§6) | Visitor's trust becomes evidence-based |
| Workspace evidence | Show the real product, not a mockup | "What do I actually get?" | Concrete, familiar screens — dashboard, reports, planner, history | Real Playwright captures of the real app (`R-01.9`) | Visitor pictures themselves using it |
| Capabilities | Name what's available without overselling | "What can I do with it today?" | Analyse, Plan, Review — each a real, live destination | Deep links to `/analyse`, `/planner`, `/reports` | Visitor picks a concrete next action |
| Trust / limitations | Close honestly, including what SlipGuard doesn't do | "What's the catch?" | No outcome prediction, no guarantees, customer stays the decision-maker | Direct statement, linked to real governance | Visitor's trust survives contact with the fine print |
| Final CTA | Convert an informed visitor, not a rushed one | "What do I do next?" | A single, low-friction, honest next step | One clear action, no urgency mechanics | Visitor starts a real analysis or account |

This is a description of the **existing** journey shape, not an invented one — it matches the real, current section-by-section structure of `home.blade.php` (§5) rather than proposing a different one.

---

## 4. Information Architecture

Repository-backed only, per §1:

```text
Analyse · Planner · Reports · Pricing · Labs
```

This is `R-01.8`'s §4 proposal, re-verified against real routes this pass rather than re-derived. It requires one concrete, small change from current state: adding `Labs` (already live) to the visible header nav, which today shows only 4 of these 5 items (§1). No other page is proposed. Any additional page (a standalone "How SlipGuard Thinks" page, a "Resources" hub) is explicitly **not** recommended here — `R-01.8` §4 already found the content fits the homepage itself and doesn't need a separate route; if that changes, it is a separate, future Product Office commission, not implied by this document.

---

## 5. Homepage Structure

### Current, real structure (verified directly against `resources/views/pages/home.blade.php`, not assumed)

| # | Section | Rhythm treatment | Trust step | Evidence used |
|---|---|---|---|---|
| 1 | Hero | Atmosphere (`gradient-hero`) | Establishes tone | Real product preview screenshot |
| 2 | Invisible Risk | Quiet | Problem framing | Concrete structural-risk example |
| 3 | How SlipGuard Thinks | Atmosphere | Mechanism | Plain-language evidence→rules→conclusion sequence, structural comparison |
| 4 | Proof (Intelligence Credibility) | Inverse surface | Demonstrated trust | Real, verified counts |
| 5 | How It Works | Atmosphere | Mechanism, reinforced | Process explanation |
| 6 | Product Capabilities | Quiet | Concrete offer | Deep links to real destinations |
| 7 | Trust | Atmosphere | Honest limitations | Links to real, verifiable evidence |
| 8 | Final CTA | Light-sweep/gradient | Conversion | Single action |

The alternating quiet/atmosphere rhythm already matches `R-01.10`'s §2 note on Section Rhythm being compatible with `MOTION_SYSTEM.md` as long as "atmosphere" means the Fixed Atmospheric Layer, which it does here (confirmed via `grep` — the same CSS class family appears in `home.blade.php`, `layouts/public.blade.php`, `layouts/guest.blade.php`, and `planner/session.blade.php`).

### Where `R-01.8`'s proposed 7 chapters map onto this real structure

`R-01.8`'s Chapters One (Problem), Two (Solution/Evidence), Three (How SlipGuard Thinks), Four (Why Trust), Five (Workspace), Six (Engineering Principles), Seven (Roadmap) map cleanly onto sections 2, 3, 3/4, 4/7, 6, 4, and a new closing addition respectively — **this is composition of existing sections with refined copy, not a restructure**. The one genuinely new element is Chapter Seven's Roadmap framing (native apps "in active development") — nothing in the current 8-section structure currently closes on a forward-looking note; where this lands (folded into Trust, or a new ninth section before the final CTA) is a real open question this document does not resolve, named rather than silently decided.

### Trust, evidence, and CTA progression

Trust accumulates across sections 1→4→7 (tone → demonstrated proof → honest limitations), matching §8 below. Evidence progresses from claimed (Hero) → concrete example (Invisible Risk) → verified count (Proof) → real screenshots (Capabilities/Workspace). CTA progression is intentionally singular and honest at the end, not staged with urgency — consistent with `ADR-012`'s "risk-awareness over excitement" and the standing rejection of urgency mechanics.

No visual layout, animation, or copy is specified here — only which section carries which architectural role, per §11.

---

## 6. Workspace Evidence Mapping

Per the approved Workspace Evidence Principle (`R-01.9`, reviewed and largely sound):

| Capability | Real screenshot exists? | Demo workspace required? | Status |
|---|---|---|---|
| Dashboard / Workspace Overview | Yes (home.blade.php already uses one) | No, already captured | Public-eligible |
| Analysis / Risk Report | Not yet captured this session | `slipguard:demo` seed | Public-eligible |
| Planner (Capability A) | Not yet captured this session | `slipguard:demo` seed | Public-eligible — already publicly routed (`/planner`) |
| History | Not yet captured | `slipguard:demo` seed | Public-eligible |
| Decision Journal | Not yet captured | `slipguard:demo` seed | Public-eligible |
| Market Intelligence / Builder (Capability B) | N/A | N/A | **Internal only — `MARKET_WIDE_PLANNER_ENABLED=false`. Must never appear as public evidence, screenshot or otherwise, until that flag's launch gate clears.** |
| Mobile native app | Removed screenshots exist (uncommitted) | N/A | Section removed by founder instruction; not part of current scope |

This restates `R-01.9`'s own finding (Market Intelligence excluded, everything else eligible) rather than re-deriving a different answer — consistency checked, not assumed.

---

## 7. Trust Architecture

Trust is earned across four real points, not concentrated in one section: **Problem framing** (naming a real frustration honestly, section 2), **Mechanism explainability** (section 3/5, "same rules every time"), **Demonstrated evidence** (section 4, real verified counts rather than claims), and **Transparent limitations** (section 7, what SlipGuard explicitly does not do — no prediction, no guarantees, no autonomous betting). This matches `R-01.8` §3's messaging hierarchy and `ADR-012`'s constitutional boundaries directly; no new trust mechanism is proposed.

---

## 8. Copy Architecture (structural only, no production copy)

- **Headline hierarchy**: one primary claim per section, stated first, elaborated after — matching the existing `home.blade.php` pattern (each section's `aria-labelledby` heading carries the section's single claim).
- **Supporting narrative**: plain language throughout, no academic/statistical terminology, per `CLAUDE.md`'s Tunde persona — restates `R-01.8` §1 item 6's correction (dropping "constitutional engineering governance") as a structural rule for future copy, not just a one-time fix.
- **CTA language progression**: exploratory language early ("see how it works"), concrete action language late ("analyse your first slip") — never urgency language at any point.
- **Reading rhythm**: alternates explanation and evidence section-by-section (§5's table), never two consecutive sections making the same kind of claim without new evidence — this is why `R-01.8` §1 item 5 recommended removing Chapter Six's repeated test-count restatement.

Production copy itself remains `R-01.8`'s deliverable, not this document's.

---

## 9. Engineering Alignment

Directly referencing `U-20.6`:

- **Reusable now, no change needed**: card/badge/alert/empty-state/metric-card components, the token system, the motion primitives listed in §1, the Fixed Atmospheric Layer, the Static Product Mark.
- **Composition over replacement, preserved**: no primitive is proposed for redesign; this blueprint's only structural asks are (a) add `Labs` to the primary nav (§4) and (b) extract the duplicated CTA markup into a shared component before adding more CTAs (`U-20.6` §5's own recommendation, restated here as a dependency, not re-justified differently).
- **Not reused, correctly deferred**: the multi-stage workspace reveal (`R-01.10` Conflict Five) has no home in current motion primitives and stays out of scope for this blueprint too, consistent with that document's own recommendation.

---

## 10. Explicit Non-Objectives (confirmed, matching the commission's own §11)

No page mockups. No motion specification beyond citing what's already approved. No interaction specification. No typography, spacing, or colour specification. No screenshot production (§6 identifies what's needed; capturing it is separate work). No production marketing copy (§8 is structure only).

---

## 11. Deliverables checklist (per the commission's §12)

| Deliverable | Location in this document |
|---|---|
| Repository Reality Check | §1 |
| Public page inventory | §1 (routes table) |
| Visitor journey architecture | §3 |
| Homepage experience architecture | §5 |
| Navigation architecture | §4 |
| Trust architecture | §7 |
| CTA architecture | §5 (progression), §8 (language structure) |
| Workspace evidence mapping | §6 |
| Engineering alignment matrix | §9 |
| Change boundary register | §12 |

---

## 12. Change Boundary Register

What this document actually asks to change, concretely, versus what stays as-is:

| Item | Change proposed? |
|---|---|
| Primary nav (4 → 5 items, add Labs) | Yes — small, concrete, real |
| Homepage section *content* (copy per `R-01.8`) | Yes — copy only, no restructure |
| Homepage section *order/count* | No — current 8-section structure is sound and kept |
| CTA markup (extract shared component) | Yes — Engineering-recommended, prerequisite for adding more CTAs cleanly |
| New pages/routes | No — none proposed |
| Motion system | No — nothing beyond what's already approved |
| Market Intelligence public visibility | No — stays internal per feature flag |
| Mobile "Coming Soon" section | No — stays removed, per direct founder instruction |

---

## 13. Acceptance criteria — self-check against the commission's §13

Every recommendation above traces to a real, checked repository fact (§1) or an already-reviewed `R-01` document (§5-§9), not an invented one. New recommendations are marked explicitly as new (§4's nav addition, §9's CTA extraction) rather than implied as already done. Superseded decisions are named, not silently repeated (§1). No public claim here exceeds implemented functionality (§6's Market Intelligence exclusion is the clearest instance). No implementation decision (a specific class name, a specific animation curve, a specific copy sentence) is embedded here — only architecture, matching §10's non-objectives and the commission's own closing instruction that success is measured by clarity, not creativity.
