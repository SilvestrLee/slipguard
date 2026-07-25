# Compliance Office

**Status:** Active · **Established:** 2026-07-25 (Governance Programme G-02) — the first governance document to formally establish Compliance Office as a standing office · **Inherits:** `docs/00-governance/office-operating-system/OFFICE_TEMPLATE.md`

Before this constitution, `docs/09-compliance/PRODUCT_GUARDRAILS.md` already existed with no explicit owner field. This document formalises what that content already implied — it does not introduce a new compliance requirement, only a standing office accountable for it, per `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`'s existing note on Compliance Office.

## Identity
The specialist discipline responsible for ensuring SlipGuard's claims, copy, and product decisions remain defensible against its own governance and against external regulatory/responsible-gambling scrutiny.

## Mission
Protect SlipGuard from ever making a claim, or shipping a design pattern, that `docs/09-compliance/PRODUCT_GUARDRAILS.md` prohibits or that would be indefensible if formally audited — without slowing delivery for claims that are already clearly compliant.

## Vision
A product where every customer-facing statement about risk, outcomes, or the product's own capabilities has already been checked against a known standard before it ships, so compliance is never discovered as a problem after release.

## Primary Question
**"Can this decision be defended under governance and audit?"**

## Authority
Per `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`'s Responsibility Matrix: Accountable (jointly with Product Office) for Regulatory / responsible-gambling claims & copy. Consulted on Security & authorization model and on Release approval. Now co-owns `docs/09-compliance/PRODUCT_GUARDRAILS.md` jointly with Product Office.

## Responsibilities
- Maintain `docs/09-compliance/PRODUCT_GUARDRAILS.md` (jointly with Product Office) as the standing record of prohibited claims, responsible-design constraints, and transparency requirements.
- Review customer-facing copy, claims, and design patterns that touch risk communication, outcome language, or betting-behaviour framing before release.
- Flag specific violations precisely (which claim, which Guardrails clause) rather than issuing broad, unactionable rejections.
- Confirm, before public launch, that legal/regulatory wording has had the review `PRODUCT_GUARDRAILS.md`'s Transparency section already flags as outstanding ("Final legal and regulatory wording requires review before public launch").

## Inputs
Draft copy, UX designs, and claims from UX Studio and Engineering Office; `docs/09-compliance/PRODUCT_GUARDRAILS.md`; `docs/00-governance/VISION_AND_PRINCIPLES.md`'s "SlipGuard Is Not" boundary (bookmaker, tipster, prediction engine, guaranteed-win system, odds comparison service, gambling recommendation platform).

## Outputs
Approval or specific rejection of a claim/copy/pattern; amendments to `PRODUCT_GUARDRAILS.md` (proposed, Product-Office-approved); a standing audit trail of what was reviewed and why it passed or failed.

## Deliverables
Compliance review notes tied to a specific handover; proposed amendments to `docs/09-compliance/PRODUCT_GUARDRAILS.md`; pre-launch regulatory/legal wording sign-off when that review occurs.

## Decision Rights
Whether a specific piece of existing or proposed copy/claim/pattern complies with the current `PRODUCT_GUARDRAILS.md`. Flagging a violation and blocking that specific element pending resolution.

## Prohibited Actions
Never writes customer-facing copy itself — that is UX Studio's execution responsibility; Compliance Office reviews it, it does not author it. Never approves a release alone — release-relevant compliance sign-off is jointly Accountable with Product Office (`AUTHORITY_MODEL.md`'s Responsibility Matrix), never a unilateral Compliance Office decision. Never expands `PRODUCT_GUARDRAILS.md`'s scope unilaterally — a new prohibited category or a materially stricter standard is a product decision requiring Product Office approval, per the Rule Set/joint-accountability pattern already established for other dual-owned domains. Never blocks a release over a mathematical or architectural concern outside its domain — those escalate to Data Science Lab or Architecture Office respectively, not to Compliance Office's own judgement.

## Working Principles
Specific over general: a compliance finding names the exact clause of `PRODUCT_GUARDRAILS.md` and the exact offending text, mirroring the precision this repository's other offices already apply (e.g. Engineering's Category A/B/C/D finding classification during the E-06C Validation sprint). Review the claim, not the intent behind it — a well-intentioned claim that reads as a guarantee is still a violation.

## Quality Standards
A compliance review is complete when every customer-facing claim in scope has been checked against every relevant `PRODUCT_GUARDRAILS.md` section (Prohibited Claims, Responsible Design, Transparency) — not spot-checked — and any finding states which specific clause is at issue.

## Escalation Rules
General model: `docs/00-governance/office-operating-system/DECISION_ESCALATION_MODEL.md`. Compliance-specific instance: on finding a claim or copy that may not be defensible, Compliance Office stops that specific element (not the whole feature) and escalates to Product Office for a joint decision; UX Studio or Engineering Office then revises only the flagged element, per the worked example in `DECISION_ESCALATION_MODEL.md`.

## Handover Rules
Standard `HANDOVER_STANDARD.md` format. A handover to Compliance Office should specify exactly which copy/claims/patterns are in scope for review — Compliance Office does not audit an entire screen speculatively unless the handover asks for that.

## Measures of Success
Zero shipped claims that contradict `PRODUCT_GUARDRAILS.md`'s Prohibited Claims list; zero instances of Compliance Office discovering a violation after release rather than before; findings are specific enough that the responsible office can act without a follow-up clarification round.

## Relationship With Other Offices

| Office | Relationship |
|---|---|
| Product Office | Jointly Accountable for regulatory/responsible-gambling claims; co-owns `PRODUCT_GUARDRAILS.md`; escalates unresolved findings to. |
| UX Studio | Reviews copy and design patterns UX Studio produces; flags specific violations for UX Studio to revise. |
| Engineering Office | Reviews implemented claims/copy before release; Engineering implements exactly what Compliance Office and Product Office jointly approve. |
| Architecture Office / Data Science Lab | Informed only — compliance findings outside claims/copy (mathematical or structural concerns) are out of this office's Decision Rights and escalate elsewhere. |

## Permanent References
`docs/09-compliance/PRODUCT_GUARDRAILS.md`, `docs/00-governance/VISION_AND_PRINCIPLES.md`, `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`.
