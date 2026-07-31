> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** An assessment of another exploratory package is itself exploratory — it does not carry more authority than what it's assessing. Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, or office documentation. See `docs/10-research-incubation/README.md` and `LIFECYCLE.md`.

# R-01.3 — MECI Readiness Assessment & Graduation Recommendation

Assessed against `docs/10-research-incubation/LIFECYCLE.md`'s classification (§1) and graduation criteria (§4), applied honestly to `R-01.1-MECI`'s actual four documents — not restated from memory of how thorough they are.

---

## 1. Assessment by area

**Architecture** — Coherent as a design: constitutional boundary preservation (`ADR-002`/`003`/`007`/`012`) is verified against real engine code, not assumed; the report-immutability refinement resolves a real UX-integrity problem cleanly. Real, named open items remain: no ADR text has actually been drafted (deliberately deferred to a future `ADR-007` addendum); the customer-facing presentation design is explicitly out of scope, gated behind the Frontend Work Rule; and the specific storage/caching design is contingent on §Compliance below, meaning part of the architecture could still change shape once real answers arrive. **Not a major unknown, but not closed either.**

**Deterministic Design** — Complete for what MECI actually is: it introduces no new scoring mathematics (unlike, say, Capability B's still-deferred ranking model), only a fact-acquisition/attribution boundary that stays outside `ADR-002`'s scoring authority by design. This is a smaller, cleaner bar than a new risk-mathematics model would be, and it's met.

**Evidence Model** — The nine-family taxonomy and the direct/derived split are conceptually sound and, per Parser research, plausible. **Field-level canonical shapes are provisional** — no real payload has confirmed exact structure the way `U-17.3` did for market data before `U-17.5` was written. This taxonomy is one real trial away from being trustworthy, not there yet.

**Parser Readiness** — **Not validated.** `02-PARSER-OFFICE-PHASE-0.md` says so about itself: no live trial was performed, real account creation wasn't authorized. Non-top-5-league coverage (including any league beyond the "top five" European competitions) is unconfirmed for every family. Real per-request cost/budget is unestimated. This is research quality, not validation.

**Compliance Readiness — the actual blocker.** Two written provider enquiries (SportMonks retention-after-cancellation; Visual Crossing storage-tier) are drafted but **not sent**. The OpenWeatherMap ODbL share-alike finding is real and unresolved by anyone qualified to resolve it. The architecture's central promise — an immutable historical snapshot — currently rests on an *unconfirmed* storage right from both primary candidates. This is the single item most likely to force a design change if answered unfavorably, and it hasn't been asked yet.

**Engineering Readiness** — Full production integration: no, blocked by Compliance above. **A narrow, inert scaffold is different and could begin now without introducing architectural uncertainty**: canonical value objects/interfaces, the evidence-store migrations, fixture-identity-resolution's alias-table shape — all behind a disabled feature flag, zero real HTTP calls, zero dependency on the unresolved storage question, mirroring exactly the bounded-package pattern `U-17.5` §10 already used for a different capability. This is real, useful, low-risk work distinguishable from "implementing MECI."

**Product Readiness** — The research is thorough enough to *support* a real Product Office decision. It is not itself that decision. Per `LIFECYCLE.md`'s own rule, nothing in this package can supply the "dated, direct founder instruction given outside the material itself" that `Commissioned` requires — that has to come from you, separately, and hasn't yet.

---

## 2. Graduation recommendation

## **Retain within incubation.**

Under `LIFECYCLE.md` §1's classification, `R-01.1-MECI` has not reached `Parser-Validated` (no real trial) or `Compliance-Cleared` (no real counsel engagement, no sent enquiries) — both are prerequisites the framework itself requires external evidence for, and neither exists yet. It therefore cannot honestly be marked `Commissioned` or graduate out of incubation, regardless of how much discussion or how many documents exist. This isn't a weak result — it's the framework functioning exactly as designed on the first real case put through it.

**Narrow exception, not a contradiction of the above**: the bounded, no-network Engineering scaffold described under Engineering Readiness could reasonably proceed as its own small, separately-tracked action *without* implying MECI as a whole has graduated — the same way `U-17.5` let Engineering begin an internal, flag-gated package before its programme's own compliance questions were fully closed. This would remain real repository code, reviewed and tested normally, but inert (no live provider calls, nothing customer-facing) and would not require re-classifying the package.

---

## 3. Risk assessment (ranked)

| # | Risk | Severity | Note |
|---|---|---|---|
| 1 | Compliance/licensing — unconfirmed permanent-storage rights on both primary candidates; unresolved ODbL share-alike question on OpenWeatherMap | **High** | Blocks any real customer-facing retention; the two written enquiries are the cheapest possible next step and remain unsent |
| 2 | Parser/evidence validation — no real trial, unconfirmed field shapes, unconfirmed non-top-5 coverage, unestimated request cost | **Medium-High** | Same category of risk `U-17.2`/`U-17.3` already resolved for a different capability by actually trialling; this package hasn't reached that step |
| 3 | Product/strategic — this adds a new external-dependency surface (cost, licensing, ongoing maintenance) to a roadmap that, per this repository's real `TASKS.md`, already has active priorities (dashboard evolution, `U-17` Phase 2/3) | **Medium** | Not a defect in the research — a real prioritization question only you can answer |
| 4 | Technical/architectural — fixture-identity-resolution's alias-matching approach is sound in principle, untested in practice | **Low-Medium** | Only provable with real trial data (item #2) |
| 5 | Repository/governance — contained by the incubation framework itself; the residual risk is repeated re-discussion being mistaken for graduation | **Low** | Mitigated structurally by `LIFECYCLE.md`'s external-evidence requirement, not by vigilance alone |

---

## 4. Outstanding Work Register

| Item | Owner | Blocks |
|---|---|---|
| Send the SportMonks retention enquiry (`03-COMPLIANCE-REVIEW.md` §6.1) | Product Office / Compliance | `Compliance-Cleared` for SportMonks |
| Send the Visual Crossing storage-tier enquiry (`03-COMPLIANCE-REVIEW.md` §6.2) | Product Office / Compliance | `Compliance-Cleared` for Visual Crossing |
| Resolve or accept the OpenWeatherMap ODbL question (qualified counsel, or a permanent decision to simply exclude the self-service tier without further legal spend) | Compliance / Product Office | Removes a standing risk even if the answer is "never use it" |
| Real bounded provider trial (real account, real sample payloads, real field-shape confirmation) | Parser Office, requires explicit authorization to create real accounts | `Parser-Validated` |
| UX Studio presentation design | UX Studio, not started, gated by Frontend Work Rule | Any customer-facing work |
| `ADR-007` addendum drafting | Architecture Office, not started | Formal architectural closure |
| A real, dated founder instruction to proceed at all | Founder / Product Office | `Commissioned` |

---

## 5. Suggested next step

Not another research or governance-framework round — the honest bottleneck here is **external validation, not internal assessment**. A further `R-01.4`/`R-01.5` re-discussion of this same material would not change §2's conclusion, because the two things actually missing (a real provider trial, real counsel answers) can only be supplied by real-world action outside this directory.

If this remains a live priority: authorize the two written compliance enquiries and a real, budgeted Parser trial — both are inexpensive, real, and would move the package to `Parser-Validated`/`Compliance-Cleared` or reveal a real blocker either way. If it isn't a current priority against the repository's actual active work, the correct outcome is simply to leave this exactly where it is — `Exploratory`, preserved, not lost, and not pretending to be more finished than it is. Both are legitimate outcomes; neither requires further governance process to reach.
