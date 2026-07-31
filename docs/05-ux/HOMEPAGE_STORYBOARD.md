# Homepage Storyboard

| Field | Value |
|---|---|
| Version | 1.2 |
| Status | Approved — **superseded in part by `U-13.0` (`PO-U13.0-001`), 2026-07-27; recomposed `U-15.2` (`PO-U15.2-001`), 2026-07-27; two sections replaced `U-15.3` (`PO-U15.3-001`), 2026-07-28** |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-27 |
| Related Documents | [Design Language](DESIGN_LANGUAGE.md), [Component Principles](COMPONENT_PRINCIPLES.md), [Visual Inspiration](VISUAL_INSPIRATION.md), [Trust Signals](TRUST_SIGNALS.md) |

---

**Amendment, recorded explicitly (`U-13.0`, `docs/00-governance/DECISION_LOG.md`):** this document's original premise — a single homepage teaching every capability in one scroll — is superseded by the public site's new multi-page information architecture. §2 Problem and §3 How SlipGuard Works' **full explanation** lives on `/analyse`, §4's **full example report** lives on `/reports`, and §6 Journal is not a public marketing section (Journal remains an authenticated-only capability, listed in the Product Capabilities section below). The section-by-section detail below is retained as the **historical record of what each moved section originally said** — still the source of truth for that copy's exact wording, now hosted on its new page.

**Second amendment, recorded explicitly (`U-15.2`, `docs/00-governance/DECISION_LOG.md`):** `U-13.0`'s "introduction only" homepage read as flat and under-narrated in practice — a hero, a static capability list, one dark proof band, then a same-weight exit. This amendment does not reverse `U-13.0`'s core decision (the *full* Problem/How-it-works/Example-Report explanations still live on their dedicated pages, not duplicated here) — it recomposes the introduction itself into a genuine narrative arc, each section brief (one to three sentences, never the dedicated page's full explanation verbatim) and visually distinct from its neighbours:

```
Hero
  ↓
Problem (brief — full version on /analyse)
  ↓
Solution (brief)
  ↓
Proof (the Intelligence Credibility metrics — unchanged content)
  ↓
How It Works (brief 3-step — full version on /analyse)
  ↓
Product Capabilities (deep-links to /analyse, /reports, /planner, Dashboard, Journal, History)
  ↓
Trust (deterministic engine, methodology, real reports, release notes, rule set — links out, no badges/seals)
  ↓
CTA
```

- **No two adjacent sections share the same background treatment or vertical rhythm** — this is the primary deliverable of this amendment, not a decorative afterthought: Hero (`gradient-hero`), Problem (`surface-page`, tightest spacing), Solution (`surface-soft` band), Proof (`surface-inverse`/`gradient-inverse`, unchanged), How It Works (`surface-page`, bordered top/bottom), Product Capabilities (`surface-soft`), Trust (`surface-page`, bordered top), CTA (`surface-soft`, bordered top).
- **CTAs are progressive, never repeated verbatim**: Hero — primary "Analyse Your Slip" (routes to registration for guests, to slip entry for customers) + secondary "See Example Report" (→ `/reports`, pulls proof forward rather than making the visitor wait for it). Solution — a single secondary "See how it works" (→ `/analyse`). Product Capabilities — each card is its own destination link (no separate button). Trust — Methodology/Release Notes text links. Closing CTA — the one remaining primary action + a soft "View pricing" text link, never a second primary-styled button (`COMPONENT_PRINCIPLES.md`'s Buttons: "exactly one primary... per screen").
- **No fabricated testimonials or social proof.** A generic SaaS "Proof" step conventionally uses customer testimonials/logos; SlipGuard has none to show honestly at this stage of the product's life, and inventing any would violate `TRUST_SIGNALS.md` and `docs/09-compliance/PRODUCT_GUARDRAILS.md` outright. Proof here is evidentiary instead: the sample report itself (Hero) and the Intelligence Credibility Section's own verified, constitutional facts (deterministic, explainable, test-covered) — proof of how the system behaves, not proof that other people used it.
- **The report preview is treated as a piece of software, not a floating marketing card**: a slim title-bar strip (`SlipGuard — Risk Report`, a small icon, no traffic-light window-chrome cliché) sits above the same metric content that was already there, at one elevation step higher than a standard card, reinforcing "this is what the real product looks like," not an illustration of it.
- Cross-Section Rules below are otherwise unchanged (one CTA style per instance, no urgency/scarcity, `MOTION_SYSTEM.md`'s one-reveal-per-section budget).

The narrative flow of the public homepage, section by section. This is **not** a design spec — no layout, colour, or component decisions here (see `COMPONENT_PRINCIPLES.md` and `DESIGN_TOKENS.md` for those). This document exists so that whoever eventually builds the page — Claude or a human — tells the same story in the same order for the same reasons.

## Flow

```
Hero
  ↓
Problem
  ↓
How SlipGuard Works
  ↓
Example Report
  ↓
Trust
  ↓
Journal
  ↓
CTA
```

Each section is read once, top to bottom, in this order — no reordering, no skipping, no parallel/tabbed sections competing for the same scroll position (`DESIGN_LANGUAGE.md`'s Layout Philosophy, `VISUAL_INSPIRATION.md`'s "scroll storytelling").

---

## 1. Hero

- **Purpose:** state, in one sentence, what SlipGuard is and isn't — before the visitor forms their own (likely sportsbook-shaped) assumption.
- **User emotion:** curious, slightly skeptical ("another betting app?") — the hero's job is to immediately signal *this is different*.
- **Primary message:** SlipGuard analyses the structural risk in your betting slip — it doesn't predict winners or promise safe bets.
- **Visual priority:** the headline sentence itself. Nothing else competes — no stat carousel, no logo wall, no background video.
- **Interaction:** one primary CTA ("Analyze your first slip" or equivalent), one secondary text link to scroll to "How it works."
- **Exit action:** scroll down (implicit) or click the primary CTA (explicit, skips the rest of the story straight to sign-up).

## 2. Problem

- **Purpose:** name the real problem SlipGuard solves — accumulators quietly compound risk in ways that aren't obvious leg-by-leg — without shaming the visitor for building accumulators.
- **User emotion:** recognition ("yes, that's happened to me") — not guilt.
- **Primary message:** adding "just one more leg" changes a slip's risk more than it feels like it should, and it's hard to see that by eye.
- **Visual priority:** a single, plain illustration of the idea (see `IMAGE_GUIDELINES.md`) — not a chart, not a real slip screenshot.
- **Interaction:** none — this section is read, not interacted with.
- **Exit action:** continue scrolling; no CTA here — asking for action before explaining the product undermines trust.

## 3. How SlipGuard Works

- **Purpose:** explain the mechanism in three plain steps: enter your slip → SlipGuard analyses structural risk → you get a clear, explained report.
- **User emotion:** reassured that this requires no statistical knowledge from them.
- **Primary message:** deterministic, explainable analysis — not a prediction, not a black box.
- **Visual priority:** three short steps, evenly weighted, no step visually dominating the others.
- **Interaction:** static; optional subtle scroll-in reveal per step (`MOTION_SYSTEM.md`'s Reveal Animations — once, no re-trigger).
- **Exit action:** continue scrolling.

## 4. Example Report

- **Purpose:** show, concretely, what the user gets — the single most trust-building section on the page, because it's proof, not a claim.
- **User emotion:** "I understand what I'd actually receive."
- **Primary message:** here is a real (anonymised/sample) risk report, following the same hierarchy the real product uses (`UX_RULES.md`'s Risk Report Hierarchy).
- **Visual priority:** the sample report itself, styled exactly like the real in-product report — no exaggerated mockup, no fictional "99% accurate" framing.
- **Interaction:** may be lightly interactive (e.g. an expandable "see full reasons") to preview progressive disclosure, but must not require sign-up to explore.
- **Exit action:** continue scrolling; a soft secondary CTA ("Try it with your own slip") is acceptable here since intent is now warm.

## 5. Trust

- **Purpose:** state plainly what SlipGuard is not, reinforcing the hero's positioning with specifics: no outcome prediction, no guaranteed wins, deterministic and explainable math, user owns the decision.
- **User emotion:** confidence that the product isn't overselling itself — the opposite of typical betting-adjacent marketing.
- **Primary message:** the `docs/09-compliance/PRODUCT_GUARDRAILS.md` disclosure, in plain language, stated as a value rather than legal boilerplate.
- **Visual priority:** short, calm statements — no badges, seals, or manufactured trust signals (no fake "as seen on" logos).
- **Interaction:** none.
- **Exit action:** continue scrolling.

## 6. Journal

- **Purpose:** introduce the reflective/learning aspect of the product — that SlipGuard helps you notice patterns over time, not just this one slip.
- **User emotion:** a longer-term sense of "this helps me improve," not a one-off utility.
- **Primary message:** record what you decided and what you learned; SlipGuard isn't just a single-use checker.
- **Visual priority:** one simple example journal entry, styled like the real Journal Card component (`COMPONENT_PRINCIPLES.md`).
- **Interaction:** none required.
- **Exit action:** continue scrolling.

## 7. CTA

- **Purpose:** close the page with one clear, singular next step.
- **User emotion:** ready, not pressured — this is an invitation, not a countdown.
- **Primary message:** restate the hero's core positioning in one line, then the action.
- **Visual priority:** the CTA button — the last and only decision point on the page.
- **Interaction:** one primary action (sign up / analyze a slip). No secondary competing action here — by this point in the story, dilution costs more than it gains.
- **Exit action:** sign-up/registration flow.

---

## Cross-Section Rules

- Exactly one CTA button style is used throughout (Hero and closing CTA); every other section either has no action or a single, clearly secondary text link.
- No section repeats another section's primary message — each moves the story forward.
- No section uses urgency, scarcity, or countdown framing (`docs/09-compliance/PRODUCT_GUARDRAILS.md`).
- Total motion budget: one reveal-in per section maximum, per `MOTION_SYSTEM.md` — the homepage should feel calm even as it scrolls, not like a slideshow.
