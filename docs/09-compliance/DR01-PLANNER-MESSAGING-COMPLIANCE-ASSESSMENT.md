# DR-01 — Planner Messaging Philosophy: Compliance Assessment

**Status:** Delivered — assessment only, no decision made, no repository record updated
**Programme:** U-07.2 — Planner Behaviour & Product Policy Decisions
**Work Package:** DR-01 — Planner Messaging Philosophy
**Handover ID:** PO-DR01-001
**From:** Compliance Office · **To:** Product Office
**Date:** 2026-07-26

## 0. Scope Note

Per the handover's explicit constraints: this is an assessment, not a decision. No mathematics, engineering, architecture, or UX has been touched, and no governance record has been updated — DR-01 remains open in `docs/02-architecture/U-07.2-PLANNER-PRODUCT-DECISION-REGISTER.md` pending Product Office's own resolution.

**A limit worth stating plainly, per this office's own Working Principle of specificity over generality:** this assessment is grounded entirely in SlipGuard's own already-documented standards (`docs/09-compliance/PRODUCT_GUARDRAILS.md`, `docs/00-governance/VISION_AND_PRINCIPLES.md`, `CLAUDE.md`'s Locked Decisions, SD-001) plus general, publicly-known regulatory categories relevant to UK gambling-adjacent services (the Gambling Commission's marketing/advertising conditions, the CAP/BCAP advertising codes). It is not, and cannot substitute for, the qualified external legal and regulatory review `PRODUCT_GUARDRAILS.md`'s own Transparency section already flags as outstanding before public launch, and which SD-001 itself separately recommends. Every regulatory risk named below should be read as "a category counsel should examine," not as a legal ruling.

## 1. Executive Compliance Assessment

Of the four options `docs/02-architecture/U-07.2-PLANNER-PRODUCT-DECISION-REGISTER.md`'s DR-01 presents, **compliance risk rises monotonically from Option 1 to Option 4**, and the two directive options (3–4) cross from "reporting a fact" into "directing a decision" — a line this product's own existing standards already treat as significant, not one this assessment invents. **Option 4 in particular sits closest to the "tipster-adjacent service" characterization SD-001 itself already named as a real regulatory exposure**, and is the option most likely to require the deepest external legal review before it could ship, regardless of how carefully worded.

## 2. Grounding — What This Assessment Is Checked Against

- **`docs/09-compliance/PRODUCT_GUARDRAILS.md`** — Prohibited Claims (guaranteed wins, safe bets, certain outcomes, guaranteed profit, elimination of risk); Responsible Design (no urgency designed to increase betting frequency, no loss-chasing prompts, no manipulative streaks, no casino-style pressure, no language treating a risk score as permission to bet); Transparency (every report states SlipGuard evaluates structural risk, does not predict outcomes, sport remains uncertain, the user owns the final decision).
- **`docs/00-governance/VISION_AND_PRINCIPLES.md`**'s "SlipGuard Is Not" boundary — not a bookmaker, tipster, prediction engine, guaranteed-win system, odds comparison service, or gambling recommendation platform.
- **`CLAUDE.md`'s Locked Decisions** — no outcome prediction; no "safe bet" or guaranteed-win claims; AI may explain verified findings only, never determine suitability, ranking, or selection order (those remain deterministic); every planner recommendation must be explainable, evidence-based, and fully customer-editable, with the customer as final decision-maker at all times.
- **`docs/00-governance/PRODUCT_GLOSSARY.md`** — "Weakest Leg: the leg contributing the greatest explainable structural risk... not a prediction that the leg will lose"; "Confidence: normally refers to data quality or analysis completeness, not confidence that an outcome will occur." These definitions already establish the product's general discipline of describing structure, never likelihood — this assessment applies that same discipline to DR-01's specific messaging question.
- **SD-001** (`docs/00-governance/DECISION_LOG.md`) — already names, unprompted, the exact tension this assessment examines: "(1) this sits in real tension with the already-approved 'Accumulator Tax' concept... a tool whose purpose is helping customers build accumulators should not undercut that message; (2) gambling-adjacent planning/advisory tools are the kind of thing that can be specifically regulated (e.g. UK Gambling Commission rules on tipster-adjacent services) — genuine external legal/compliance review is recommended before this ships."

## 3. Assessment by Option

| Option | Compliance Risk | Assessment |
|---|---:|---|
| 1. No proactive messaging | **Low** | Fully consistent with every standard above by construction — there is no new claim to check. The Planner remains a display of already-approved, already-reviewed facts (score, band, rank, reason codes) with no new evaluative layer. |
| 2. Neutral factual trend display | **Low–Medium** | Still no directive claim, but a *displayed trend* ("your score fell from 64 to 41") is one small step closer to reading as guidance than a single static number, purely through repetition and sequence. Still defensible under Transparency's existing language, provided the display never adds an evaluative label ("good," "safe," "ready") to the trend — a plain number/band sequence, not a verdict. |
| 3. Directive completion guidance only | **Medium–High** | This is the first option that makes an evaluative claim on SlipGuard's own initiative rather than the customer's — "no further improvement found" is close to but distinguishable from "this is a safe bet"; it does not claim anything about the sporting outcome, but it does claim something about the *slip's own suitability*, which brushes against the Locked Decision that "AI... never determines suitability... those remain deterministic." A deterministic, factor-based trigger (not an AI judgment) can be built consistently with that Locked Decision only if the trigger condition and its wording are treated with the same rigor as a Rule Set factor — trigger logic and copy both need Compliance sign-off before shipping, not just at review. |
| 4. Full directive guidance (adds abandonment-style messaging) | **High** | This is the option closest to what `VISION_AND_PRINCIPLES.md` explicitly says SlipGuard is *not* (a gambling recommendation platform) and to what SD-001 itself named as the regulatory category of concern (a tipster-adjacent advisory service). Even a carefully structural, no-outcome-prediction phrasing ("this accumulator has remained Very High risk across multiple cycles") functions, in practice, as advice to stop — the *category* of statement, not just its wording, is what raises the SD-001-flagged exposure. Recommend this option not proceed without the external legal/regulatory review SD-001 and `PRODUCT_GUARDRAILS.md` both already call for, completed first, not concurrently. |

## 4. The Five Explicit Questions, Answered

**1. Should SlipGuard ever explicitly recommend abandoning a betting slip?**
Not without the external legal/regulatory review already flagged (SD-001, `PRODUCT_GUARDRAILS.md`) being completed first — this is Option 4's core behaviour, and it is the one category this assessment cannot clear on internal standards alone, precisely because SD-001 named it as externally regulated territory, not merely internally sensitive. If Product Office wants this capability, the recommended sequencing is: commission that external review specifically for this behaviour, before, not alongside, any UX or copy work.

**2. Should SlipGuard instead communicate structural risk without directing customer behaviour?**
Yes, this is achievable today (Options 1–2) with no new regulatory exposure beyond what already exists — every fact involved (score, band, per-cycle history) is already an approved, deterministic output. The distinction that matters, precisely: describing what changed ("your score fell from X to Y") is structurally different from prescribing what to do about it ("you should stop") — the former is Transparency, the latter is advice.

**3. What language preserves SlipGuard's position as a Decision Intelligence Platform rather than a prediction or tipping service?**
Three tests, all already implicit in this product's existing standards rather than newly invented here: (a) **Attribution test** — does the statement attribute its conclusion to a named structural factor (leg count, odds, concentration, complexity — the existing RF-00n vocabulary), never to sporting likelihood? (b) **Reversibility test** — could the exact same statement be made about a hypothetical slip with the numbers reversed, without SlipGuard's own credibility being at stake either way? A statement that only makes sense if the *team* wins or loses has crossed into prediction. (c) **Editability test** — does the statement leave every choice with the customer (`CLAUDE.md`'s "fully customer-editable" requirement), or does it imply an action SlipGuard has already decided is correct? Language failing any of these three should not ship regardless of which DR-01 option is chosen.

**4. What regulatory or responsible-gambling implications arise from each option?**
See §3's per-option risk ratings. The general category, common to Options 3–4 and not to 1–2: UK gambling advertising/marketing regulation (Gambling Commission licence conditions, CAP/BCAP advertising codes) treats "encouragement to continue gambling" and "advice presented as expert guidance on a specific bet" as scrutinized categories — which specific SlipGuard behaviours fall inside them is a legal question this office cannot resolve internally, only flag.

**5. Are additional disclaimer or transparency requirements necessary?**
Yes, regardless of which option Product Office chooses: any planner-specific messaging (Options 2–4) should carry the same Transparency-section disclosure `PRODUCT_GUARDRAILS.md` already requires of every report (structural risk only, no outcome prediction, sport remains uncertain, customer owns the final decision) — restated at the point the *new* messaging appears, not assumed to carry over silently from the existing Risk Report screen the customer may not be looking at in the same session.

## 5. Recommended Messaging Principles

These are principles and required/prohibited *patterns*, not literal customer-facing copy — authoring the exact wording is UX Studio's execution responsibility, once Product Office has chosen an option, per this office's own constitutional boundary.

1. **Structural attribution, always.** Every statement must name the deterministic factor behind it (a factor code, a score delta, a reason code) — never a bare evaluative claim with no cited cause.
2. **No verdicts.** Avoid words that function as a suitability judgment on their own ("good," "safe," "ready," "risky bet") — report the measured fact (score, band, delta) and let the customer supply their own judgment of it.
3. **Customer action stays customer-initiated.** Any messaging must describe what *has happened* to the numbers, never instruct what the customer should do next, except where Product Office has explicitly authorized a directive category (Options 3–4) after the review in Q1 above.
4. **Consistent voice with the existing Risk Report.** Planner messaging should read as an extension of the same, already-approved explanatory voice (`docs/05-ux/EXPLAINABILITY_SYSTEM.md`), not a new tone — a customer should not be able to tell, from register alone, that Planner copy came from a different, more opinionated part of the product.

## 6. Prohibited Messaging (categories, not literal copy)

- Any claim of certainty, safety, or guarantee, about this slip or any leg in it (already prohibited product-wide, restated here as directly applicable to Planner output).
- Any statement that reads as being about the *sporting outcome* rather than the *slip's structure* — e.g., anything implying a team is unlikely to win, rather than that a leg contributes disproportionate structural risk.
- Any urgency, pressure, or streak-style framing applied to the regeneration cycle itself (e.g., framing "cycle 5" as building momentum, or implying more cycles is inherently better) — `PRODUCT_GUARDRAILS.md`'s Responsible Design section already prohibits exactly this pattern generically; it applies with particular force to an iterative feature.
- Any message whose only sensible reading is an instruction to place, change, or abandon a real-money bet, unless and until Product Office has specifically authorized that category following external legal review (Q1).

## 7. Required Messaging (categories, not literal copy)

- The existing Transparency disclosure (structural risk only, no outcome prediction, sport remains uncertain, customer owns the final decision), present wherever new Planner-specific messaging is shown, not only on the original Risk Report screen.
- Explicit sourcing of every claim to a named, already-deterministic fact (a factor, a reason code, a score delta) — never an unattributed assertion.
- If Options 3–4 are chosen: an explicit statement, at the point of that specific message, that this is a structural observation, not advice about whether or how to bet — distinguishing it from ordinary Transparency boilerplate by addressing the more directive framing head-on rather than relying on a generic disclaimer to cover it.

## 8. Identified Regulatory Risks (categories for external counsel, not a ruling)

- **R-1 — Advisory/tipster-service characterization.** SD-001's own already-recorded concern; sharpened specifically by Option 4, present in smaller form even in Option 3. The determining factor is likely to be the pattern of the messaging in aggregate (does the product, taken as a whole, function as advice on what to bet), not any single sentence in isolation.
- **R-2 — Responsible-gambling marketing rules.** Any messaging that could be read as encouraging continued engagement (more cycles, more legs, more attempts) intersects with existing UK gambling marketing/advertising standards on responsible design — relevant to Options 3–4's iterative "keep going" framing risk even if never explicitly worded that way.
- **R-3 — Jurisdictional scope.** This assessment is written against UK-centric frameworks (Gambling Commission, CAP/BCAP) because that is the regulatory context SD-001 itself already names; if SlipGuard operates or plans to operate in other jurisdictions, each has its own gambling-advertising regime and this assessment does not cover them.

## 9. Recommendation to Product Office

Recommend, not decide: proceed with **Option 2** (neutral factual trend display) as the immediately shippable choice, since it requires no new regulatory review and adds real customer value over Option 1. Treat **Options 3–4 as gated on the external legal/regulatory review already flagged by SD-001 and `PRODUCT_GUARDRAILS.md`**, commissioned specifically against the directive-messaging category described here (§3, §4 Q1) — not a generic pre-launch review, since this messaging category is materially different from the rest of the product's existing, already-reviewed factual reporting. This recommendation is not a Compliance Office decision — per this office's own constitution, release-relevant compliance sign-off is jointly Accountable with Product Office, never unilateral.

## 10. Return of Ownership

Per the handover's own terms: constitutional ownership returns to Product Office. No decision has been made on DR-01; no repository record, code, architecture, or UX has been touched.

**Returned By:** Compliance Office
**Status:** Awaiting Product Office decision — not self-decided, per this office's own Prohibited Actions ("never approves a release alone").
