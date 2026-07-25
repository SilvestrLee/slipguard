# Design Language

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-24 |
| Related Documents | [UX Rules](UX_RULES.md), [Visual Inspiration](VISUAL_INSPIRATION.md), [Design Tokens](DESIGN_TOKENS.md), [Component Principles](COMPONENT_PRINCIPLES.md), [Motion System](MOTION_SYSTEM.md), [Explainability System](EXPLAINABILITY_SYSTEM.md) |

---

The UX constitution for SlipGuard. Every frontend decision — Blade, Livewire, or Filament — traces back to a principle here. If an implementation conflicts with this document, this document wins (see `CLAUDE.md`).

This document defines *why* SlipGuard looks and feels the way it does. `DESIGN_TOKENS.md` and `COMPONENT_PRINCIPLES.md` define *what* to build with; `MOTION_SYSTEM.md` defines *how things move*. Read this one first — it explains the intent the others implement.

## Design Philosophy

SlipGuard is a **decision-support instrument**, not a betting product. It should feel closer to a diagnostics tool, a financial statement, or a well-made insurance report than to a sportsbook. The interface's job is to make a risk analysis easy to trust and easy to act on — never to make betting feel exciting, urgent, or effortless.

Every screen should be able to answer, at a glance: what is happening, why it matters, and what to do next (`UX_RULES.md`). If a screen can't answer all three, it isn't finished — it's decorated.

## Emotional Goals

The user (see `PROJECT.md`'s primary user, Tunde — a regular accumulator bettor with limited statistical knowledge) should feel:

- **Respected**, not condescended to or shamed for the slip they built.
- **Clear-headed**, not overwhelmed — the interface lowers arousal, it doesn't raise it.
- **In control**, because the product always tells them what to do next, never what to feel about it.
- **Trusting**, because the reasoning is visible, not because the interface is persuasive.

SlipGuard should never make anyone feel excited to bet more. If a screen makes betting feel *fun*, it has failed regardless of how attractive it looks.

## Product Personality

If SlipGuard were a person: a calm, competent analyst who has seen a lot of betting slips, doesn't judge, and tells you plainly what's risky and why — then stops talking and lets you decide. Not a coach, not a friend, not a hype man, not a croupier.

Personality traits, in order of priority: **clear > calm > confident > warm**. Never: playful, urgent, flashy, or salesy.

## Visual Hierarchy

One primary action per screen. One headline idea per section. Risk information is always the visual focus of any analysis screen — never competes with navigation chrome, branding, or upsell content for attention.

Hierarchy is built with **size, weight, and spacing first; colour second**. A screen that only works because of colour-coding will fail for colour-blind users and looks noisy. See `COMPONENT_PRINCIPLES.md` for how this applies to risk indicators specifically.

## White-Space Philosophy

Space is not empty — it's what tells the user which things belong together and which don't. SlipGuard is generously spaced, closer to Linear or Stripe's marketing pages than to a data-dense dashboard.

Rule of thumb: if a screen feels tight, the fix is almost always more space around groups of content, not smaller text. See `DESIGN_TOKENS.md`'s spacing scale — never hand-roll a spacing value outside it.

## Typography Philosophy

Typography carries most of the product's "premium, trustworthy" feeling — SlipGuard doesn't use colour or imagery to look expensive. One typeface family, a small number of weights, generous line-height, and a clear size scale (see `DESIGN_TOKENS.md`). Long numbers (odds, scores) are set in tabular figures so they don't jitter as they update.

Never use a decorative or "sporty" typeface anywhere. Text is a functional signal system, not a mood board.

## Layout Philosophy

Single-column, top-to-bottom reading order wherever the content is sequential (a risk report is read start to finish, not scanned like a dashboard). Multi-column layouts are reserved for genuinely parallel information (e.g. a leg list where every row has the same shape).

Every layout should work at 375px wide first, then expand — see `RESPONSIVE_RULES.md`. A layout that only makes sense on desktop is a layout that needs rethinking, not just a breakpoint.

## Trust-First Principles

- **Show the reasoning, not just the verdict.** A risk band without the reasons behind it is a black box, and black boxes aren't trusted (`explainability-responsible-betting`).
- **Never manufacture urgency.** No countdowns, no "X people are viewing this," no streak framing (`docs/09-compliance/PRODUCT_GUARDRAILS.md`).
- **Say what SlipGuard doesn't know.** Data quality and analysis limitations are shown, not hidden — hiding uncertainty erodes trust faster than admitting it.
- **Consistency over novelty.** The same risk band always looks the same way, in the same place, with the same words. Predictability is part of trust.

## Progressive Disclosure

Lead with the plain-language headline and the risk band. Let the user go deeper — main reasons, then per-leg detail, then methodology — only if they choose to (`UX_RULES.md`'s Risk Report Hierarchy). Never show the full methodology or every reason code by default; that's for the user who wants to dig in, not the user who just wants an answer.

Default state is always the simplest state. Expansion reveals more; it never hides what's already shown.

## Cognitive Load Reduction

- One decision per screen. Don't ask the user to absorb a risk report and choose their next action and read a disclaimer all with equal visual weight.
- No unexplained jargon. Every technical term (leg, band, accumulator tax, weakest leg) is explained in place the first time it appears on a screen — see `docs/00-governance/PRODUCT_GLOSSARY.md`.
- No chart, table, or number appears unless it changes what the user should do. A chart that's merely "interesting" is noise (`VISUAL_INSPIRATION.md`'s rejected list: unnecessary charts).

## Data Explanation Philosophy

Every number on screen answers "why does this matter to me," not just "what is this value." A risk score of 62 is meaningless alone; "Moderate risk — mainly driven by 3 long-odds legs" is usable. Explanations are written in the second person, in plain sentences, never in statistical vocabulary (`UX_RULES.md`'s Language rules; `docs/00-governance/PRODUCT_GLOSSARY.md`'s "Confidence" entry — never conflate data quality with outcome probability).

## Brand Tone

Plain, direct, calm, second-person, present tense. Short sentences. No exclamation marks. No gambling slang ("bag," "lock," "juice"). No hedge-fund jargon either — the tone sits between a good doctor's bedside manner and a clean bank statement.

## Design Constraints

- Blade + Livewire for customer UI, Filament for internal operations (`CLAUDE.md`, locked).
- Tailwind CSS v4 (`@theme`-based tokens, no separate JS config file) — see `DESIGN_TOKENS.md`.
- No casino aesthetics, ever: no neon, no confetti, no flashing, no slot-machine or roulette motifs (`ICONOGRAPHY.md`, `IMAGE_GUIDELINES.md`).
- Accessibility is a requirement, not a nice-to-have (`ACCESSIBILITY.md`).
- Nothing animates without a stated purpose (`MOTION_SYSTEM.md`).
- Build progressively — don't design components the product doesn't need yet (`CLAUDE.md`'s Documentation Freeze / Just-in-Time Documentation applies to UI just as much as to docs).
