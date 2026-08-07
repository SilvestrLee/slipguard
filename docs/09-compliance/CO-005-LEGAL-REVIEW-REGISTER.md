# CO-005 — Legal Review Register

| Field | Value |
|---|---|
| Commission | `PO-CO-002`, Product Office, direct founder instruction, 2026-08-04 |
| Type | Implementation commission — converts existing Compliance planning (`CO-MVP-001`) into production-ready deliverables. Not a redesign of policy; no previously accepted decision revisited except where noted. |
| Predecessor | `docs/09-compliance/CO-MVP-001-COMPLIANCE-TRUST-CERTIFICATION.md` |
| Status | Delivered. **Recommendation: CERTIFIED WITH CONDITIONS** (see §Recommendation). |

---

# Files Created

- `resources/views/pages/terms.blade.php` — real Terms of Service (`CO-01`)
- `resources/views/pages/privacy.blade.php` — real Privacy Policy (`CO-02`)
- `docs/09-compliance/CO-005-LEGAL-REVIEW-REGISTER.md` — this document (`CO-05`)

# Files Modified

- `routes/web.php` — `/terms` and `/privacy` now route to the real pages above, replacing `pages.public-coming-soon`
- `tests/Feature/PublicPagesTest.php` — split the old combined stub test; added a dedicated test asserting the real Terms/Privacy content, the visible legal-review flags, and the real boundary language

---

# CO-01 — Terms of Service: Evidence

Live at `/terms`. Verified directly, not assumed: `tests/Feature/PublicPagesTest.php`'s new test asserts the real content is served (not the old stub string), the 18+ eligibility clause is present, the "does not, and will not" boundary list (no prediction, no funds custody, no autonomous betting, no operator role, no affiliate bias) is present, and the "Clauses requiring legal review" callout is visibly present, not silently omitted. Full suite: 688/695 executed tests passing (7 self-skip by design), 0 failures. `git diff --check`, Pint, and production build all clean.

# CO-02 — Privacy Policy: Evidence

Live at `/privacy`. Verified directly: the new test asserts the real content is served, the accurate cookie disclosure ("session and CSRF-protection cookies" only) is present, the "does not currently use any third-party analytics service" statement is present (matches `PO-MVP-005`'s own independently-verified finding — zero analytics/tracking code exists anywhere in the codebase), and the "Requires legal review" callout is visibly present. Every data category described (account info, betting slip content, journal entries, session cookies, server logs) was checked against real application code before being written — nothing describes a service, processor, or tracker that does not exist. Account deletion is described as a real, existing feature (`resources/views/livewire/profile/delete-user-form.blade.php`, confirmed present) — not aspirational.

# CO-03 — Responsible Gambling: Verification

| Item | Status | Evidence |
|---|---|---|
| Footer wording | ✅ Live | `resources/views/components/public-footer.blade.php:113` — present since `PO-RC1-002` (2026-08-04), re-verified unchanged this pass |
| Consistency across public pages | ✅ Confirmed by construction | The footer is a single shared component (`<x-public-footer>`), included once in `layouts/public.blade.php` and `layouts/labs.blade.php` — every page using `<x-public-layout>` (including the two new pages above) renders it identically; there is no per-page duplication that could drift |
| Help section | ❌ **Not present — real, disclosed gap, not fixed here** | `routes/web.php:79` — `Route::view('help', 'coming-soon', ...)`, an authenticated-only generic stub with no real content of any kind yet. Help's MVP scope remains a **Product Office decision** (`RC1_LAUNCH_BLOCKER_CLOSURE_PLAN.md` `PO-04`, still open) — Compliance Office does not invent Help content unilaterally, per this commission's own "do not redesign UX / alter MVP scope" constraint. RG signposting will extend to Help once it has real content to extend into. |
| No operator-style messaging | ✅ Confirmed | Footer wording deliberately excludes deposit-loss disclaimers and reality-check language (per `CO-MVP-001` C-03's own reasoning — both would misleadingly imply SlipGuard resembles a betting operator); re-checked against `docs/05-ux/TRUST_SIGNALS.md`'s Forbidden Language list — the one match found (`terms.blade.php`'s "safe," "guaranteed," "likely to win") is the boundary list correctly stating what SlipGuard will *never* claim, not a violation |
| Consistency with `ADR-012` | ✅ Confirmed | Cross-checked footer and both new legal pages against every `ADR-012` boundary — no conflict found |

# CO-04 — Age Statement: Verification

| Item | Status | Evidence |
|---|---|---|
| Placement | ✅ Public footer (site-wide) + Terms of Service §3 + Privacy Policy §9 | Three independent, consistent placements, not one fragile mention |
| Wording | ✅ Jurisdiction-neutral | "18 and over" / "at least 18 years old" throughout — no jurisdiction-specific higher age of majority claimed, consistent with `PO-RC1-001`'s "jurisdiction-neutral wording" instruction |
| Consistency across public pages | ✅ Confirmed by construction | Footer placement inherits the same single-source-of-truth guarantee as CO-03 above |

---

# CO-05 — Legal Review Register

## Internally approved (Compliance Office — governance-level, no external counsel required)

- Business model boundary language throughout ToS/Privacy (restates `ADR-012`, introduces no new restriction or exception)
- Product description (deterministic analysis, no outcome prediction) — ToS §1
- Acceptable Use clauses — ToS §5
- Content ownership/licence clause — ToS §6
- No-financial-intermediation clause — ToS §7
- Intellectual property / copyright notice — ToS §8
- Disclaimers (not financial/betting advice) — ToS §9
- Termination and changes-to-terms clauses (standard, jurisdiction-neutral) — ToS §12–13
- Data-collected/data-use description, verified accurate to the real application — Privacy §1–3
- Cookie disclosure (no tracking cookie exists) — Privacy §4
- Third-party processor disclosure (honest "none named yet") — Privacy §5
- Data retention / account deletion description, matches the real Profile feature — Privacy §6, §8 (choices list)
- Security practices description — Privacy §7
- Children's privacy / age statement — ToS §3, Privacy §9, public footer
- Responsible Gambling footer signposting — public footer

## Requires external counsel (flagged inline in the documents, not resolved here)

| Item | Where flagged | Why it can't be resolved internally |
|---|---|---|
| Limitation of Liability | ToS §10 | Enforceable liability caps/exclusions are jurisdiction-specific |
| Governing Law & Dispute Resolution | ToS §11 | No launch market has been named (`RC1_LAUNCH_BLOCKER_CLOSURE_PLAN.md` `CO-06`, still open) |
| Statutory data-subject rights (e.g. GDPR-style rights) | Privacy §8 | Depends entirely on which jurisdiction(s) SlipGuard launches in |
| Whether SlipGuard requires any regulatory licence as a tipster-adjacent service | Not yet drafted anywhere | `CO-MVP-001` C-05 already found this cannot be assessed without a named market |
| Whether Responsible Gambling signposting is a *hard legal requirement* (vs. this commission's own risk-reducing recommendation) | `CO-MVP-001` §8.4 | Same — jurisdiction-dependent; the current footer wording is a prudent baseline, not a claim of regulatory sufficiency |
| Final sign-off that the Terms of Service and Privacy Policy are legally binding and complete | Both documents | Every governance-level clause above is real and accurate; **no document in this repository has been reviewed by a qualified lawyer** — that review remains outstanding regardless of how complete the drafting is |

## Intentionally deferred (explicit scope decisions, not gaps)

| Item | Reason | Owner |
|---|---|---|
| Cookie Policy as a standalone document | Folded into the Privacy Policy — no tracking cookie exists to justify a separate document (`CO-MVP-001` C-02) | Compliance Office — decided, not reopened |
| Acceptable Use Policy as a standalone document | Folded into the Terms of Service — no user-generated/social surface exists yet (`CO-MVP-001` C-02) | Compliance Office — decided, not reopened |
| Jurisdiction-specific legal text | Awaiting Product Office's launch-market decision (`RC1_LAUNCH_BLOCKER_CLOSURE_PLAN.md` `CO-06`) — jurisdiction-neutral wording used in the interim, per `PO-RC1-001`'s own explicit instruction | Product Office |
| Affiliate Compliance Framework execution | Dormant — no affiliate programme has ever been proposed (`CO-MVP-001` C-04) | N/A until triggered |
| Contact page / support email | Still an honest stub — no real support channel exists to name in either legal document; both documents say so explicitly rather than inventing an address | Product Office / Engineering |
| Help section real content | Still a stub — MVP scope decision pending (`RC1_LAUNCH_BLOCKER_CLOSURE_PLAN.md` `PO-04`) | Product Office |
| Pricing / Premium boundary | Separate, unrelated Product Office decision (`RC1_LAUNCH_BLOCKER_CLOSURE_PLAN.md` `PO-02`) | Product Office |

**Nothing above is ambiguous**: every item is in exactly one of the three categories, with a stated reason and owner.

---

# Recommendation

**CERTIFIED WITH CONDITIONS.**

Every Compliance blocker this commission had authority to close is closed: real Terms of Service and Privacy Policy content now live (replacing stubs), Responsible Gambling signposting and the age statement verified consistent site-wide, the business model re-confirmed compliant, and a complete register leaving nothing ambiguous. The remaining conditions are exactly the kind `PO-CO-002`'s own Product Office Expectation anticipated — external, not internal:

1. Qualified legal counsel review of the flagged clauses (Limitation of Liability, Governing Law/Dispute Resolution, statutory data-subject rights) and final sign-off on both documents as a whole.
2. Product Office naming SlipGuard's intended launch market(s) — or explicitly confirming a market-agnostic launch — so the jurisdiction-dependent items above can be finalised.
3. A real support contact channel, so both documents' `[Contact]` placeholders can be completed.

None of these require further Compliance Office planning or another document from this office — they are the genuinely external items `PO-CO-002` itself named as the acceptable remainder.
